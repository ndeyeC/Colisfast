<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Commnande;
use App\Models\User;

class SuiviLivraisonController extends Controller
{
    public function index(Request $request)
    {
        $query = Commnande::with(['user', 'driver'])
            ->whereNotNull('driver_id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $livraisons = $query->orderBy('created_at', 'desc')->paginate(15);

        $stats = $this->getStatistiques();

        $livreurs = User::where('role', 'livreur')->get();

        return view('admin.livraisons.index', compact('livraisons', 'stats', 'livreurs'));
    }

    public function show($id)
    {
        $livraison = Commnande::with(['user', 'driver', 'paiement', 'detailLivraisons', 'evaluation'])
            ->findOrFail($id);

        $problemeSignale = null;
        if ($livraison->probleme_signale) {
            // Si string JSON, on décode, sinon on prend directement le tableau
            if (is_string($livraison->probleme_signale)) {
                $problemeSignale = json_decode($livraison->probleme_signale, true);
            } else {
                $problemeSignale = $livraison->probleme_signale;
            }
        }

        return view('admin.livraisons.show', compact('livraison', 'problemeSignale'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:' . implode(',', [
                Commnande::STATUT_EN_ATTENTE,
                Commnande::STATUT_PAYEE,
                Commnande::STATUT_CONFIRMEE,
                Commnande::STATUT_ACCEPTEE,
                Commnande::STATUT_EN_COURS,
                Commnande::STATUT_LIVREE,
                Commnande::STATUT_ANNULEE
            ]),
            'commentaire' => 'nullable|string|max:500'
        ]);

        $livraison = Commnande::findOrFail($id);
        $ancienStatut = $livraison->status;

        $livraison->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);

        $this->logChangementStatut($livraison, $ancienStatut, $request->status, $request->commentaire);

        return redirect()->back()->with('success', 'Statut mis à jour avec succès');
    }

    public function assignDriver(Request $request, $id)
    {
        $request->validate([
            'driver_id' => 'required|exists:users,id'
        ]);

        $livraison = Commnande::findOrFail($id);

        $driver = User::whereHas('role', function ($q) {
            $q->where('name', 'livreur');
        })->findOrFail($request->driver_id);

        $livraison->update([
            'driver_id' => $request->driver_id,
            'status' => Commnande::STATUT_ACCEPTEE
        ]);

        return redirect()->back()->with('success', 'Livreur assigné avec succès');
    }

    public function resoudreProbleme(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|string|in:resolu,reassigner,annuler',
            'commentaire_admin' => 'required|string|max:500',
            'nouveau_driver_id' => 'required_if:action,reassigner|exists:users,id'
        ]);

        $livraison = Commnande::findOrFail($id);

        if (!$livraison->probleme_signale) {
            return redirect()->back()->with('error', 'Aucun problème signalé pour cette livraison');
        }

        // Gestion correcte du cast : si string on décode, sinon on prend direct
        $probleme = is_string($livraison->probleme_signale) ? json_decode($livraison->probleme_signale, true) : $livraison->probleme_signale;

        $probleme['status'] = 'traite';
        $probleme['action_admin'] = $request->action;
        $probleme['commentaire_admin'] = $request->commentaire_admin;
        $probleme['date_traitement'] = now()->toDateTimeString();

        switch ($request->action) {
            case 'resolu':
                $livraison->update([
                    'status' => Commnande::STATUT_EN_COURS,
                    'probleme_signale' => json_encode($probleme)
                ]);
                break;

            case 'reassigner':
                $livraison->update([
                    'driver_id' => $request->nouveau_driver_id,
                    'status' => Commnande::STATUT_ACCEPTEE,
                    'probleme_signale' => json_encode($probleme)
                ]);
                break;

            case 'annuler':
                $livraison->update([
                    'status' => Commnande::STATUT_ANNULEE,
                    'probleme_signale' => json_encode($probleme)
                ]);
                break;
        }

        return redirect()->back()->with('success', 'Problème traité avec succès');
    }
public function problemesSignales()
{
    $livraisons = Commnande::with(['user', 'driver']) // ✅ Correction ici
        ->where('status', 'probleme_signale')
        ->whereNotNull('probleme_signale')
        ->orderBy('updated_at', 'desc')
        ->paginate(10);

    // Décodage sécurisé
    $livraisons->getCollection()->transform(function ($livraison) {
        $probleme = $livraison->probleme_signale;

        if (is_string($probleme)) {
            try {
                $probleme = json_decode($probleme, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Exception $e) {
                $probleme = [
                    'error' => 'Erreur de décodage JSON',
                    'raw_data' => $livraison->probleme_signale
                ];
            }
        }

        $livraison->probleme_decode = $probleme;

        return $livraison;
    });

    $livreurs = User::where('role', 'livreur')->get();

    return view('admin.livraisons.problemes', compact('livraisons', 'livreurs'));
}


    private function getStatistiques()
    {
        return [
            'total_livraisons' => Commnande::whereNotNull('driver_id')->count(),
            'en_cours' => Commnande::whereIn('status', Commnande::statutsEnCours())->count(),
            'livrees' => Commnande::where('status', Commnande::STATUT_LIVREE)->count(),
            'problemes_signales' => Commnande::whereNotNull('probleme_signale')
                ->whereRaw("JSON_EXTRACT(probleme_signale, '$.status') = 'en_attente'")
                ->count(),
            'livraisons_aujourdhui' => Commnande::whereDate('created_at', today())
                ->whereNotNull('driver_id')
                ->count()
        ];
    }

    private function logChangementStatut($livraison, $ancienStatut, $nouveauStatut, $commentaire = null)
    {
        \Log::info('Changement de statut de livraison', [
            'livraison_id' => $livraison->id,
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => $nouveauStatut,
            'admin_id' => auth()->id(),
            'commentaire' => $commentaire,
            'timestamp' => now()
        ]);
    }

    public function export(Request $request)
    {
        $query = Commnande::with(['user', 'driver'])->whereNotNull('driver_id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $livraisons = $query->get();

        $filename = 'livraisons_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ];

        $callback = function () use ($livraisons) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID', 'Client', 'Email Client', 'Livreur', 'Adresse Départ',
                'Adresse Arrivée', 'Statut', 'Prix', 'Date Création', 'Date Livraison'
            ]);

            foreach ($livraisons as $livraison) {
                fputcsv($file, [
                    $livraison->id,
                    $livraison->user->name ?? '',
                    $livraison->user->email ?? '',
                    $livraison->driver->name ?? '',
                    $livraison->adresse_depart,
                    $livraison->adresse_arrivee,
                    $livraison->status,
                    $livraison->prix_final,
                    $livraison->created_at->format('d/m/Y H:i'),
                    $livraison->updated_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
