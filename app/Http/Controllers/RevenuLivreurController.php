<?php

namespace App\Http\Controllers;

use App\Models\Commnande;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RevenuLivreurController extends Controller
{
    /**
     * Statistiques générales du livreur (API JSON)
     */
    public function getStats($livreurId)
    {
        $revenuMois = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->whereMonth('updated_at', now()->month)
            ->sum('revenu_livreur');

        $revenuTotal = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->sum('revenu_livraison');

        $livraisonsMois = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->whereMonth('updated_at', now()->month)
            ->count();

        $moyenne = $livraisonsMois > 0 ? $revenuMois / $livraisonsMois : 0;

        return response()->json([
            'revenu_mois' => round($revenuMois, 2),
            'revenu_total' => round($revenuTotal, 2),
            'livraisons_mois' => $livraisonsMois,
            'moyenne_livraison' => round($moyenne, 2)
        ]);
    }

    /**
     * Historique des paiements du livreur (API JSON)
     */
    public function getHistoriquePaiements($livreurId)
    {
        $paiements = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->whereNotNull('revenu_livraison')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($commande) {
                return [
                    'reference' => 'CMD-' . $commande->id,
                    'date' => $commande->updated_at->format('d/m/Y'),
                    'description' => 'Livraison #' . $commande->id,
                    'montant' => $commande->revenu_livraison,
                    'statut' => $commande->statut_paiement_livreur ?? 'en_attente'
                ];
            });

        return response()->json($paiements);
    }

    /**
     * Données de revenus journaliers pour un graphique (API JSON)
     */
    public function getGraphData($livreurId)
    {
        $revenusJournaliers = [];
        $dates = [];

        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dates[] = $date->format('d M');

            $total = Commnande::where('driver_id', $livreurId)
                ->where('status', Commnande::STATUT_LIVREE)
                ->whereDate('updated_at', $date)
                ->sum('revenu_livraison');

            $revenusJournaliers[] = round($total, 2);
        }

        $typesLivraison = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->selectRaw('type_livraison, sum(revenu_livraison) as total')
            ->groupBy('type_livraison')
            ->get()
            ->pluck('total', 'type_livraison');

        return response()->json([
            'revenus_journaliers' => $revenusJournaliers,
            'dates' => $dates,
            'repartition_types' => $typesLivraison
        ]);
    }

    /**
     * Performances du livreur (API JSON)
     */
    public function getPerformances($livreurId)
    {
        $livraisonsMois = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->whereMonth('updated_at', now()->month)
            ->count();

        $noteMoyenne = Commnande::where('driver_id', $livreurId)
            ->whereNotNull('note_client')
            ->avg('note_client');

        $livraisonsExpress = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->where('type_livraison', 'express')
            ->whereMonth('updated_at', now()->month)
            ->count();

        return response()->json([
            'objectifs' => [
                [
                    'nom' => '50 livraisons',
                    'progression' => $livraisonsMois,
                    'total' => 50,
                    'recompense' => 5000
                ],
                [
                    'nom' => 'Note 4.8+',
                    'progression' => round($noteMoyenne, 1),
                    'total' => 5.0,
                    'recompense' => 3000
                ],
                [
                    'nom' => '20 livraisons express',
                    'progression' => $livraisonsExpress,
                    'total' => 20,
                    'recompense' => 4000
                ]
            ],
            'solde_portefeuille' => Commnande::where('driver_id', $livreurId)
                ->where('statut_paiement_livreur', 'en_attente')
                ->sum('revenu_livraison')
        ]);
    }

    /**
     * Affichage de la page Blade avec les revenus (vue web)
     */
    public function revenusView()
    {
        $livreurId = auth()->id();

        $revenuMois = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->whereMonth('updated_at', now()->month)
            ->sum('revenu_livraison');

        $revenuTotal = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->sum('revenu_livraison');

        $livraisonsMois = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->whereMonth('updated_at', now()->month)
            ->count();

        $moyenne = $livraisonsMois > 0 ? $revenuMois / $livraisonsMois : 0;

        $paiements = Commnande::where('driver_id', $livreurId)
            ->where('status', Commnande::STATUT_LIVREE)
            ->whereNotNull('revenu_livraison')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('livreur.revenus', [
            'revenuMois' => round($revenuMois, 2),
            'revenuTotal' => round($revenuTotal, 2),
            'livraisonsMois' => $livraisonsMois,
            'moyenne' => round($moyenne, 2),
            'paiements' => $paiements
        ]);
    }
}
