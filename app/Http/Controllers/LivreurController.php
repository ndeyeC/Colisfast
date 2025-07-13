<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Commnande;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class LivreurController extends Controller
{
    /**
     * Affiche les commandes disponibles pour les livreurs
     */
    public function commandesDisponibles(Request $request)
    {
        // Récupérer les commandes payées et non assignées à un livreur
       $query = Commnande::whereIn('status', Commnande::statutsDisponibles())
                      ->whereNull('driver_id')
                      ->with(['user']);
        // Filtres optionnels
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('adresse_depart', 'LIKE', "%{$search}%")
                  ->orWhere('adresse_arrivee', 'LIKE', "%{$search}%")
                  ->orWhere('reference', 'LIKE', "%{$search}%");
            });
        }

        // if ($request->filled('distance')) {
        //     // Ici vous pourriez filtrer par distance si vous avez les coordonnées
        //     // Pour l'instant, on simule avec les régions
        //     $distance = $request->distance;
        //     // Logique de filtrage par distance à implémenter selon vos besoins
        // }

        if ($request->filled('type_colis')) {
            $query->where('type_colis', $request->type_colis);
        }

        if ($request->filled('type_livraison')) {
            $query->where('type_livraison', $request->type_livraison);
        }

        $query->orderBy('created_at', 'desc');

        $commandes = $query->paginate(10);

    $stats = [
        'total_disponibles' => Commnande::whereIn('status', Commnande::statutsDisponibles())
                                      ->whereNull('driver_id')
                                      ->count(),
        'total_acceptees' => Commnande::where('driver_id', Auth::id())
                                    ->whereIn('status', ['acceptee', 'en_cours'])
                                    ->count(),
        'revenus_jour' => Commnande::where('driver_id', Auth::id())
                                 ->where('status', 'livree')
                                 ->whereDate('updated_at', today())
                                 ->sum('prix_final')
    ];

        if ($request->ajax()) {
            return response()->json([
                'commandes' => $commandes,
                'stats' => $stats
            ]);
        }

        return view('livreur.livraisons-disponible', compact('commandes', 'stats'));
    }
public function dashboarde()
{
    $livreurId = Auth::id();
    
    $livraisonActuelle = Commnande::where('driver_id', $livreurId)
        ->where('status', 'en_cours')
        ->with(['user'])
        ->first();
    
    $livraisonsDisponibles = Commnande::whereIn('status', ['payee', 'confirmee'])
        ->whereNull('driver_id')
        ->with(['user'])
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
    
    $statistiques = [
        'livraisons_jour' => Commnande::where('driver_id', $livreurId)
            ->whereDate('created_at', today())
            ->count(),
        'revenus_jour' => Commnande::where('driver_id', $livreurId)
            ->where('status', 'livree')
            ->whereDate('updated_at', today())
            ->sum('prix_final') ?? 0,
        'en_cours' => Commnande::where('driver_id', $livreurId)
            ->where('status', 'en_cours')
            ->count(),
        
        'livraisons_completees' => Commnande::where('driver_id', $livreurId)
            ->where('status', 'livree')
            ->whereDate('updated_at', today())
            ->count(),
        'livraisons_total' => Commnande::where('driver_id', $livreurId)
            ->whereDate('updated_at', today())
            ->count(),
        'taux_completion' => $this->calculerTauxCompletion($livreurId),
        'note_moyenne' => $this->calculerNoteMoyenne($livreurId),
        'distance_jour' => 0, 
    ];
    
    return view('livreur.dashboarde', compact(
        'livraisonActuelle',
        'livraisonsDisponibles', 
        'statistiques'
    ));
}
private function sendPushNotification($fcmToken, $title, $body)
    {
        if (!$fcmToken) {
            \Log::error("Échec de l'envoi de la notification : aucun token FCM disponible.");
            return false; 
        }

        try {
            // Utiliser le service Firebase configuré
            $firebase = app('firebase');
            $messaging = $firebase->createMessaging();

            $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(\Kreait\Firebase\Messaging\Notification::create($title, $body))
                ->withData([
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'sound' => 'default',
                    'type' => 'commande'
                ]);

            $result = $messaging->send($message);
            
            \Log::info("Notification envoyée avec succès", [
                'token' => substr($fcmToken, 0, 10) . '...',
                'title' => $title,
                'result' => $result
            ]);

            return true;

        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            \Log::error("Erreur Firebase Messaging", [
                'error' => $e->getMessage(),
                'token' => substr($fcmToken, 0, 10) . '...',
                'title' => $title
            ]);
            return false;
        } catch (\Exception $e) {
            \Log::error("Erreur générale lors de l'envoi de la notification", [
                'error' => $e->getMessage(),
                'token' => substr($fcmToken, 0, 10) . '...',
                'title' => $title
            ]);
            return false;
        }
    }



    /**
     * Accepter une commande
     */
   public function accepterCommande(Request $request, $commandeId)
{
    $commande = Commnande::findOrFail($commandeId);

    if ($commande->driver_id !== null) {
        return response()->json([
            'success' => false,
            'message' => 'Cette commande a déjà été acceptée par un autre livreur.'
        ], 400);
    }

    if (!in_array($commande->status, Commnande::statutsAcceptables())) {
        return response()->json([
            'success' => false,
            'message' => 'Cette commande n\'est pas disponible.'
        ], 400);
    }

    if (Auth::user()->role !== 'livreur') {
        return response()->json([
            'success' => false,
            'message' => 'Vous n\'êtes pas autorisé à accepter des commandes.'
        ], 403);
    }

    $commande->driver_id = Auth::id();
    $commande->status = 'acceptee';
    $commande->date_acceptation = now();
    $commande->save();

    $livreurNom = Auth::user()->name;
    $this->sendPushNotification(
        $commande->user->fcm_token, 
        "Commande acceptée", 
        "Votre commande a été acceptée par **$livreurNom**. Il s'occupera de la livraison !"
    );

    return response()->json([
        'success' => true,
        'message' => 'Commande acceptée avec succès!',
        'commande' => $commande->load('user')
    ]);
}


    /**
     * Démarrer une livraison
     */
    // public function demarrerLivraison(Request $request, $commandeId)
    // {
    //     $commande = Commnande::where('id', $commandeId)
    //                         ->where('driver_id', Auth::id())
    //                         ->where('status', 'acceptee')
    //                         ->firstOrFail();

    //     $commande->status = 'en_cours';
    //     $commande->date_debut_livraison = now();
    //     $commande->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Livraison démarrée!',
    //         'commande' => $commande
    //     ]);
    // }

    /**
     * Terminer une livraison
     */
    // public function terminerLivraison(Request $request, $commandeId)
    // {
    //     $request->validate([
    //         'code_confirmation' => 'sometimes|string|max:10',
    //         'commentaire' => 'nullable|string|max:500',
    //         'photo_livraison' => 'sometimes|image|max:2048'
    //     ]);

    //     $commande = Commnande::where('id', $commandeId)
    //                         ->where('driver_id', Auth::id())
    //                         ->where('status', 'en_cours')
    //                         ->firstOrFail();

    //     // Gérer l'upload de photo si présente
    //     $photoPath = null;
    //     if ($request->hasFile('photo_livraison')) {
    //         $photoPath = $request->file('photo_livraison')->store('livraisons', 'public');
    //     }

    //     $commande->status = 'livree';
    //     $commande->date_livraison = now();
    //     $commande->commentaire_livraison = $request->commentaire;
    //     $commande->photo_livraison = $photoPath;
    //     $commande->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Livraison terminée avec succès!',
    //         'commande' => $commande
    //     ]);
    // }

    /**
     * Mes commandes en cours
     */
    public function mesCommandes()
    {
        $commandes = Commnande::where('driver_id', Auth::id())
                             ->whereIn('status', ['acceptee', 'en_cours', 'livree'])
                             ->with(['user'])
                             ->orderBy('updated_at', 'desc')
                             ->paginate(10);

        return view('livreur.commandes.mes-commandes', compact('commandes'));
    }

    /**
     * Détails d'une commande
     */
    public function detailsCommande($commandeId)
    {
        $commande = Commnande::with(['user'])
                            ->findOrFail($commandeId);

        // Vérifier l'autorisation
        if ($commande->driver_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403, 'Non autorisé');
        }

        return view('livreur.commandes.details', compact('commande'));
    }

    /**
     * API pour récupérer les commandes (pour mobile app)
     */
    public function apiCommandesDisponibles(Request $request)
    {
        $query = Commnande::whereIn('status', ['payee', 'confirmee'])
                          ->whereNull('driver_id')
                          ->with(['user:id,name,phone']);

        // Filtres
        if ($request->filled('lat') && $request->filled('lng')) {
            // Filtrer par proximité géographique
            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->radius ?? 10; // 10km par défaut

            $query->selectRaw("
                *, 
                (6371 * acos(cos(radians(?)) * cos(radians(lat_depart)) * cos(radians(lng_depart) - radians(?)) + sin(radians(?)) * sin(radians(lat_depart)))) AS distance
            ", [$lat, $lng, $lat])
            ->having('distance', '<', $radius)
            ->orderBy('distance');
        }

        $commandes = $query->orderBy('created_at', 'desc')
                          ->limit(20)
                          ->get();

        return response()->json([
            'success' => true,
            'data' => $commandes->map(function($commande) {
                return [
                    'id' => $commande->id,
                    'reference' => $commande->reference,
                    'adresse_depart' => $commande->adresse_depart,
                    'adresse_arrivee' => $commande->adresse_arrivee,
                    'type_colis' => $commande->type_colis,
                    'type_livraison' => $commande->type_livraison,
                    'prix_final' => $commande->prix_final,
                    'distance' => $commande->distance ?? null,
                    'client' => [
                        'name' => $commande->user->name ?? 'Client',
                        'phone' => $commande->user->phone ?? null
                    ],
                    'created_at' => $commande->created_at->format('Y-m-d H:i:s')
                ];
            })
        ]);
    }

    /**
     * Statistiques du livreur
     */
    public function statistiques()
    {
        $livreurId = Auth::id();
        
        $stats = [
            'commandes_jour' => Commnande::where('driver_id', $livreurId)
                                        ->whereDate('date_livraison', today())
                                        ->count(),
            'commandes_semaine' => Commnande::where('driver_id', $livreurId)
                                           ->whereBetween('date_livraison', [now()->startOfWeek(), now()->endOfWeek()])
                                           ->count(),
            'commandes_mois' => Commnande::where('driver_id', $livreurId)
                                        ->whereMonth('date_livraison', now()->month)
                                        ->whereYear('date_livraison', now()->year)
                                        ->count(),
            'revenus_jour' => Commnande::where('driver_id', $livreurId)
                                      ->whereDate('date_livraison', today())
                                      ->sum('prix_final'),
            'revenus_semaine' => Commnande::where('driver_id', $livreurId)
                                         ->whereBetween('date_livraison', [now()->startOfWeek(), now()->endOfWeek()])
                                         ->sum('prix_final'),
            'revenus_mois' => Commnande::where('driver_id', $livreurId)
                                      ->whereMonth('date_livraison', now()->month)
                                      ->whereYear('date_livraison', now()->year)
                                      ->sum('prix_final'),
            'taux_completion' => $this->calculerTauxCompletion($livreurId),
            'note_moyenne' => $this->calculerNoteMoyenne($livreurId)
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Calculer le taux de completion
     */
    private function calculerTauxCompletion($livreurId)
    {
        $totalAcceptees = Commnande::where('driver_id', $livreurId)->count();
        $totalLivrees = Commnande::where('driver_id', $livreurId)
                                ->where('status', 'livree')
                                ->count();

        return $totalAcceptees > 0 ? round(($totalLivrees / $totalAcceptees) * 100, 2) : 0;
    }

    /**
     * Calculer la note moyenne (si vous avez un système de notation)
     */
    private function calculerNoteMoyenne($livreurId)
    {
        // À adapter selon votre système de notation
        return 4.5; // Valeur par défaut
    }

 
public function index()
{

    $livreurs = User::where('role', 'livreur')->paginate(10);

    return view('admin.livreurs.index', compact('livreurs'));
}
public function show($id)
{

    $livreur = User::findOrFail($id);

    return view('admin.livreurs.show', compact('livreur'));
}
    
}