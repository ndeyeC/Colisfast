<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\LivreurController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CommnandeController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\StatistiquesController;
use App\Http\Controllers\LivraisonEnCoursController;
use App\Http\Controllers\NavigationController;
use App\Http\Controllers\SuiviLivraisonController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserMessagesController;
use App\Http\Controllers\UserController;







// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return view('welcome');
})->name('welcome');



// Route pour les admin
// Route::get('/admin', function () {
//     return view('admin.dashboard');
// })->name('admin.dashboard');

Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profil/edit', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil/update', [ProfilController::class, 'update'])->name('profil.update');
    Route::delete('/profil', [ProfilController::class, 'destroy'])->name('profil.destroy');
});

Route::prefix('livreur')->middleware(['auth', 'role:livreur'])->group(function() {
    Route::post('/commandes/{commande}/start-tracking', [NavigationController::class, 'startTracking'])
        ->name('livreur.commandes.start-tracking');
});

// Routes obligatoires
Route::post('/livreur/commandes/{commande}/start-tracking', [NavigationController::class, 'startTracking'])
     ->name('livreur.commandes.start-tracking');

Route::post('/livreur/commandes/{commande}/update-position', [NavigationController::class, 'updatePosition']);

Route::post('/livreur/commandes/{commande}/complete', [NavigationController::class, 'completeDelivery']);
  // Route pour récupérer les données de route (appelée par AJAX)
    Route::get('commandes/{commande}/route-data', [NavigationController::class, 'getRouteData'])
        ->name('commandes.route-data');

Route::get('/livreur/commandes', [CommnandeController::class, 'indexLivreur'])
     ->name('livreur.commandes')
     
     ->middleware(['auth', 'role:livreur']);
     Route::prefix('livreur')->group(function() {
    Route::post('/commandes/{commande}/start', [NavigationController::class, 'start']);
    Route::post('/commandes/{commande}/complete', [NavigationController::class, 'complete']);
    Route::post('/commandes/{commande}/position', [NavigationController::class, 'updatePosition']);
});

Route::middleware(['auth'])->group(function() {
    // Route affichage les messages
    Route::get('/messages', [MessageController::class, 'index'])
         ->name('user.messages');
    
    // Route pour envoyer des messages
    Route::post('/messages/send', [MessageController::class, 'sendToAdmin'])
         ->name('user.messages.send');
    
    // Route pour vérifier les nouveaux messages
    Route::post('/messages/check-new', [MessageController::class, 'checkNewMessages'])
         ->name('user.messages.check');
});

Route::get('/commnandes/confirmation/{id}', [CommnandeController::class, 'confirmation'])->name('commnandes.confirmation');
Route::get('/commnandes', [CommnandeController::class, 'liste'])->name('commnandes.liste');
// Route::get('/debug-paytech', [CommnandeController::class, 'debugPaytech'])->middleware('auth');

// Routes de paiement
Route::get('/commnandes/payment/success', [CommnandeController::class, 'paymentSuccess'])->name('commnandes.payment.success');
Route::get('/commnandes/payment/cancel', [CommnandeController::class, 'paymentCancel'])->name('commnandes.payment.cancel');


Route::match(['GET', 'POST'], '/commnandes/payment/ipn', [CommnandeController::class, 'ipnCallback'])
     ->name('commnandes.payment.ipn');
// Route IPN (ne nécessite pas d'authentification)
// Route::post('/commnandes/payment/ipn', [CommnandeController::class, 'ipnCallback'])->name('commnandes.payment.ipn');

// Routes pour les commandes
Route::get('/commnandes/create', [CommnandeController::class, 'create'])->name('commnandes.create');
Route::post('/commnandes', [CommnandeController::class, 'store'])->name('commnandes.store');
Route::get('/commnandes/index', [CommnandeController::class, 'index'])->name('commnandes.index');
Route::get('/commnandes/{commnande}', [CommnandeController::class, 'show'])->name('commnandes.show');

// Routes pour les livreurs accessibles par tout le monde
Route::get('/livreur.devenir_livreur', function () {
    return view('livreur.devenir_livreur');
})->name('livreur.devenir_livreur');

// // Routes pour l'espace livreur
// Route::get('/dashboarde', function () {
//     return view('livreur.dashboarde');
// })->name('livreur.dashboarde');

Route::get('/dashboarde', [LivreurController::class, 'dashboarde'])->name('livreur.dashboarde');
// Route::get('livreur/dashboarde', [LivreurController::class, 'dashboarde'])
//     ->name('livreur.dashboarde')
//     ->middleware('auth');

// Route::get('/livraisons-disponible', function () {
//     return view('livreur.livraisons-disponible');
// })->name('livreur.livraisons-disponible');

Route::get('/livraison-cours', function () {
    return view('livreur.livraison-cours');
})->name('livreur.livraison-cours');

Route::get('/livraison', function () {
    // Dans une version statique, l'ID sera ignore
    return view('livreur.details-livraison');
})->name('livreur.details-livraison');

Route::get('/navigation', function () {
    return view('livreur.navigation');
})->name('livreur.navigation');

// Profil du livreur
Route::get('/profil', function () {
    return view('livreur.profil');
})->name('livreur.profil');

// Statistiques de performance
Route::get('/statistiques', function () {
    return view('livreur.statistics');
})->name('livreur.statistics');

// Revenus et paiements
Route::get('/revenus', function () {
    return view('livreur.revenus');
})->name('livreur.revenus');

// Routes pour les clients
Route::get('/client/dashboard', function () {
    return view('client.dashboard');
})->name('client.dashboard');

Route::get('/delivery/new', function () {
    return view('delivery.new');
})->name('delivery.new');

Route::get('/delivery/history', function () {
    return view('delivery.history');
})->name('delivery.history');

Route::get('/client/deliverers', function () {
   return view('client.deliverers');
})->name('client.deliverers');

// Routes pour l'administration
Route::prefix('admin')->name('admin.')->group(function () {
    // Routes pour les livreurs
    Route::get('/livreurs', [LivreurController::class, 'index'])->name('livreurs.index');
    Route::get('/livreurs/{livreur}', [LivreurController::class, 'show'])->name('livreurs.show');
    Route::get('/livreurs/{id}/json', [LivreurController::class, 'showJson']);
    Route::delete('/livreurs/{livreur}', [LivreurController::class, 'destroy'])->name('livreurs.destroy');
    
    // Routes pour les tarifs
    Route::get('/tarifs', [TarifController::class, 'index'])->name('tarifs.index');
    Route::get('/tarifs/create', [TarifController::class, 'create'])->name('tarifs.create');
    Route::post('/tarifs', [TarifController::class, 'store'])->name('tarifs.store');
    Route::get('/tarifs/{tarif}/edit', [TarifController::class, 'edit'])->name('tarifs.edit');
    Route::put('/tarifs/{tarif}', [TarifController::class, 'update'])->name('tarifs.update');
    Route::delete('/tarifs/{tarif}', [TarifController::class, 'destroy'])->name('tarifs.destroy');
    
    // Routes pour les communications
    // Route::get('/communications', [CommunicationController::class, 'index'])->name('communications.index');
    // Route::post('/communications', [CommunicationController::class, 'store'])->name('communications.store');
    // Route::get('/communications/conversation', [CommunicationController::class, 'conversation'])->name('communications.conversation');
    // Route::get('/communications/new', [CommunicationController::class, 'getNewMessages'])->name('communications.new');
    // Route::get('/communications/unread-counts', [CommunicationController::class, 'getUnreadCounts'])->name('communications.unread-counts');
    
    // Routes pour les notifications admin
    // Route::post('/notifications/mark-all-read', function() {
    //     Auth::user()->notifications()->update(['is_read' => true]);
    //     return response()->json(['success' => true]);
    // })->name('notifications.mark-all-read');
    
    // Route::post('/notifications/mark-read/{id}', [NotificationController::class, 'markAsRead'])
    //      ->name('notifications.mark-read');
         
    // Route::post('/notifications/delete/{id}', [NotificationController::class, 'destroy'])
    //      ->name('notifications.delete');
    
    Route::get('/communications', [CommunicationController::class, 'index'])->name('communications.index');
Route::post('/communications', [CommunicationController::class, 'store'])->name('communications.store');
Route::get('/communications/conversation', [CommunicationController::class, 'conversation'])->name('communications.conversation');
Route::get('/communications/user-conversation', [CommunicationController::class, 'getUserConversation'])->name('communications.user-conversation');
Route::post('/communications/new', [CommunicationController::class, 'getNewMessages'])->name('communications.new');
Route::get('/communications/unread-counts', [CommunicationController::class, 'getUnreadCounts'])->name('communications.unread-counts');

// Route pour envoyer un message depuis l'interface utilisateur
Route::post('/communications/send-from-user', [CommunicationController::class, 'sendFromUser'])->name('communications.send-from-user');

// Routes pour les notifications admin
Route::post('/notifications/mark-all-read', function() {
    Auth::user()->notifications()->update(['is_read' => true]);
    return response()->json(['success' => true]);
})->name('notifications.mark-all-read');

Route::post('/notifications/mark-read/{id}', [NotificationController::class, 'markAsRead'])
     ->name('notifications.mark-read');
     
Route::delete('/notifications/delete/{id}', [NotificationController::class, 'delete'])
     ->name('notifications.delete');
    // Autres routes d'administration
    Route::get('/livraisons', function () {
        return view('admin.livraisons.index');
    })->name('livraisons.index');
    
    Route::get('/statistiques', function () {
        return view('admin.statistiques.index');
    })->name('statistiques.index');
});

// Route de suppression de notification globale
// Route::delete('/notifications/delete/{id}', [NotificationController::class, 'delete'])
//      ->name('notifications.delete');

// // Route de communication conversation
// Route::get('/communications/conversation', [CommunicationController::class, 'conversation'])
//      ->name('admin.communications.conversation');

// Routes pour les messages utilisateur
Route::middleware(['auth'])->group(function () {
    Route::get('/messages', [UserMessagesController::class, 'index'])->name('user.messages');
    Route::post('/messages/send', [UserMessagesController::class, 'send'])->name('user.messages.send');
    Route::post('/messages/check', [UserMessagesController::class, 'checkNewMessages'])->name('user.messages.check');
});

 Route::get('/suggestions', [SuggestionController::class, 'getSuggestedCities']);


     Route::middleware(['auth'])->group(function () {
        Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
        Route::post('/tokens/purchase', [TokenController::class, 'purchase'])->name('tokens.purchase');
        Route::get('/api/tokens/balance', [TokenController::class, 'getBalance']);
    });

    // Routes pour les livreurs (protégées par authentification et rôle livreur)
Route::middleware(['auth', 'role:livreur'])->prefix('livreur')->name('livreur.')->group(function() {
    
    // Commandes disponibles
    //  Route::get('/livraisons-disponible', [LivreurController::class, 'livraisonsDisponible'])
    //      ->name('livraisons-disponible');

    Route::get('/livraisons-disponible', [LivreurController::class, 'commandesDisponibles'])
     ->name('livraisons-disponible');
    // Accepter une commande
    Route::post('/commandes/{id}/accepter', [LivreurController::class, 'accepterCommande'])
         ->name('commandes.accepter');
    
    // Démarrer une livraison
    // Route::post('/commandes/{id}/demarrer', [LivreurController::class, 'demarrerLivraison'])
    //      ->name('commandes.demarrer');
    
    // Terminer une livraison
    Route::post('/commandes/{id}/terminer', [LivreurController::class, 'terminerLivraison'])
         ->name('commandes.terminer');
    
    // Mes commandes
    Route::get('/mes-commandes', [LivreurController::class, 'mesCommandes'])
         ->name('commandes.mes-commandes');
    
    // Détails d'une commande
    Route::get('/commandes/{id}', [LivreurController::class, 'detailsCommande'])
         ->name('commandes.details');
    
    // Statistiques
    Route::get('/statistiques', [LivreurController::class, 'statistiques'])
         ->name('statistiques');
});

// Routes API pour mobile app
Route::middleware(['auth:sanctum'])->prefix('api/livreur')->group(function() {
    
    // API commandes disponibles
    Route::get('/commandes-disponibles', [LivreurController::class, 'apiCommandesDisponibles']);
    
    // API accepter commande
    Route::post('/commandes/{id}/accepter', [LivreurController::class, 'accepterCommande']);
    
    // API démarrer livraison
    // Route::post('/commandes/{id}/demarrer', [LivreurController::class, 'demarrerLivraison']);
    
    // API terminer livraison
    Route::post('/commandes/{id}/terminer', [LivreurController::class, 'terminerLivraison']);
    
    // API statistiques
    Route::get('/statistiques', [LivreurController::class, 'statistiques']);
});

// // Routes pour les livraisons en cours (dans routes/web.php)
// Route::middleware(['auth', 'role:livreur'])->group(function () {
    
//     // Page principale des livraisons en cours
//     Route::get('/livreur/livraisons-en-cours', [LivraisonEnCoursController::class, 'index'])
//         ->name('livreur.livraisons-en-cours');
    
//     // API AJAX pour récupérer les données
//     Route::get('/api/livreur/livraisons-en-cours', [LivraisonEnCoursController::class, 'apiLivraisonsEnCours'])
//         ->name('api.livreur.livraisons-en-cours');
    
//     // Actions sur les livraisons
//     Route::post('/livreur/livraison/{commandeId}/demarrer', [LivraisonEnCoursController::class, 'demarrerLivraison'])
//         ->name('livreur.demarrer-livraison');
    
//     Route::post('/livreur/livraison/{commandeId}/position', [LivraisonEnCoursController::class, 'updatePosition'])
//         ->name('livreur.update-position');
    
//     Route::post('/livreur/livraison/{commandeId}/livrer', [LivraisonEnCoursController::class, 'marquerLivree'])
//         ->name('livreur.marquer-livree');
    
//     Route::post('/livreur/livraison/{commandeId}/probleme', [LivraisonEnCoursController::class, 'signalerProbleme'])
//         ->name('livreur.signaler-probleme');
    
//     Route::post('/livreur/livraison/{commandeId}/annuler', [LivraisonEnCoursController::class, 'annulerLivraison'])
//         ->name('livreur.annuler-livraison');
    
//     Route::get('/livreur/livraison/{commandeId}/navigation', [LivraisonEnCoursController::class, 'ouvrirNavigation'])
//         ->name('livreur.ouvrir-navigation');
// });


// Route::post('/livreur/commande/{id}/livree', [LivraisonEnCoursController::class, 'marquerCommeLivree'])
//     ->name('livreur.commande.marquerLivree');


// Routes API pour l'application mobile (dans routes/api.php)
// Route::middleware(['auth:sanctum', 'role:livreur'])->prefix('livreur')->group(function () {
    
//     Route::get('/livraisons-en-cours', [LivraisonEnCoursController::class, 'apiLivraisonsEnCours']);
//     Route::post('/livraison/{commandeId}/demarrer', [LivraisonEnCoursController::class, 'demarrerLivraison']);
//     Route::post('/livraison/{commandeId}/position', [LivraisonEnCoursController::class, 'updatePosition']);
//     Route::post('/livraison/{commandeId}/livrer', [LivraisonEnCoursController::class, 'marquerLivree']);
//     Route::post('/livraison/{commandeId}/probleme', [LivraisonEnCoursController::class, 'signalerProbleme']);
//     Route::post('/livraison/{commandeId}/annuler', [LivraisonEnCoursController::class, 'annulerLivraison']);
//     Route::get('/livraison/{commandeId}/navigation', [LivraisonEnCoursController::class, 'ouvrirNavigation']);
// });

Route::middleware(['auth', 'role:livreur'])->group(function () {
    
    // Page principale des livraisons en cours
    Route::get('/livreur/livraison-cours', [LivraisonEnCoursController::class, 'index'])
        ->name('livreur.livraison-cours');
    
    // API AJAX pour récupérer les données
    Route::get('/api/livreur/livraison-cours', [LivraisonEnCoursController::class, 'apiLivraisonsEnCours'])
        ->name('api.livreur.livraison-cours');
    
    // Actions sur les livraisons
    Route::post('/livreur/livraisons/{commandeId}/demarrer', [LivraisonEnCoursController::class, 'demarrerLivraison'])
        ->name('livreur.demarrer-livraison');
    
    Route::post('/livreur/livraisons/{commandeId}/update-position', [LivraisonEnCoursController::class, 'updatePosition'])
        ->name('livreur.update-position');
    
    Route::post('/livreur/livraisons/{commandeId}/marquer-livree', [LivraisonEnCoursController::class, 'marquerLivree'])
        ->name('livreur.marquer-livree');
    
    Route::post('/livreur/livraisons/{commandeId}/signaler-probleme', [LivraisonEnCoursController::class, 'signalerProbleme'])
        ->name('livreur.signaler-probleme');
    
    Route::post('/livreur/livraisons/{commandeId}/annuler', [LivraisonEnCoursController::class, 'annulerLivraison'])
        ->name('livreur.annuler-livraison');
    
     Route::get('/livreur/livraisons/{commandeId}/navigation', [LivraisonEnCoursController::class, 'ouvrirNavigation'])
        ->name('livreur.ouvrir-navigation');

    // Route pour obtenir le statut d'une livraison (pour les mises à jour AJAX)
    Route::get('/api/livraisons/{commandeId}/status', function($commandeId) {
        $commande = App\Models\Commnande::where('id', $commandeId)
            ->where('driver_id', Auth::id())
            ->where('status', 'en_cours')
            ->with('deliveryRoute')
            ->first();
        
        if (!$commande) {
            return response()->json(['success' => false, 'message' => 'Livraison non trouvée'], 404);
        }
        
        $controller = new LivraisonEnCoursController();
        $route = $commande->deliveryRoute;
        
        if ($route && $route->current_position && $route->end_point) {
            $distanceRestante = $controller->calculateDistance(
                $route->current_position['lat'],
                $route->current_position['lng'],
                $route->end_point['lat'],
                $route->end_point['lng']
            );
            
            $tempsEstime = round(($distanceRestante / 30) * 60); // 30 km/h moyenne
            $progressPercentage = $controller->calculateProgress($route);
        } else {
            $distanceRestante = 0;
            $tempsEstime = 0;
            $progressPercentage = 0;
        }
        
        return response()->json([
            'success' => true,
            'distance_restante' => $distanceRestante,
            'temps_estime' => $tempsEstime,
            'progress_percentage' => $progressPercentage
        ]);
    })->name('api.livraison.status');
    
    // Route pour les mises à jour de position (API)
    Route::post('/api/livraisons/{commandeId}/update-position', [LivraisonEnCoursController::class, 'updatePosition'])
        ->name('api.livraison.update-position');
});

Route::get('/api/livraisons/{commandeId}/status', [LivraisonEnCoursController::class, 'getDeliveryStatus'])
    ->name('api.livraison.status')
    ->middleware(['auth', 'role:livreur']);

// // Route pour naviguer vers les livraisons en cours (lien du menu)
// Route::get('/livreur/livraison-cours', function() {
//     return redirect()->route('livreur.livraison-cours');
// })->name('livreur.livraison-cours');

Route::middleware(['auth'])->group(function () {
    // Page principale des statistiques
    Route::get('/statistiques', [StatistiquesController::class, 'index'])->name('statistiques.index');
    
    // API pour récupérer les données par période
    Route::get('/statistiques/periode', [StatistiquesController::class, 'getStatistiquesPeriode'])->name('statistiques.periode');
});

Route::post('/user/fcm-token', [AuthController::class, 'saveFcmToken'])->middleware('auth');
// Route::get('/livreur/dashboarde', [LivreurController::class, 'dashboarde'])
//     ->name('livreur.dashboarde')
//     ->middleware('auth');


Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Routes principales des livraisons
    Route::get('/livraisons', [SuiviLivraisonController::class, 'index'])->name('livraisons.index');
    Route::get('/livraisons/{id}', [SuiviLivraisonController::class, 'show'])->name('livraisons.show');
    Route::post('/livraisons/{id}/update-status', [SuiviLivraisonController::class, 'updateStatus'])->name('livraisons.update-status');
    Route::post('/livraisons/{id}/assign-driver', [SuiviLivraisonController::class, 'assignDriver'])->name('livraisons.assign-driver');
    
    // Routes pour la gestion des problèmes
    Route::get('/livraisons/problemes/liste', [SuiviLivraisonController::class, 'problemesSignales'])->name('livraisons.problemes');
    Route::post('/livraisons/{id}/resoudre-probleme', [SuiviLivraisonController::class, 'resoudreProbleme'])->name('livraisons.resolve');

    // Route::post('/livraisons/{id}/resoudre-probleme', [LivraisonsController::class, 'resoudreProbleme'])->name('livraisons.resoudre-probleme');
     Route::get('/livraisons/{id}/json', [SuiviLivraisonController::class, 'showJson'])->name('livraisons.json');

    
    // Route d'export
    Route::get('/livraisons/export', [SuiviLivraisonController::class, 'export'])->name('livraisons.export');
    
    // API pour récupérer les détails d'une livraison (pour les modals)
    Route::get('/api/livraisons/{id}/details', [SuiviLivraisonController::class, 'getDetails'])->name('livraisons.details');
});

Route::post('/user/fcm-token', [UserController::class, 'saveFcmToken'])->middleware('auth');



Route::get('/paiements/par-mois', [RevenuLivreurController::class, 'filterPaiementsParMois'])->name('paiements.par.mois');

require __DIR__.'/auth.php';