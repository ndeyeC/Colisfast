@extends('layouts.template')

@section('title', 'ColisFast - Client')

@section('content')
<div class="bg-gray-50 min-h-screen pb-16">
    <div class="max-w-md mx-auto bg-white min-h-screen shadow-sm">
        <!-- Contenu dynamique basé sur l'onglet sélectionné -->
        <div id="homeTab" class="tab-content">
            <!-- Header avec solde -->
            <div class="bg-red-600 text-white p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-xl font-bold">Bienvenue <span>{{ Auth::user()->name ?? Auth::user()->email }}</span></h1>
                        <p class="text-sm opacity-90 mt-1">Solde: <span class="font-bold">{{ Auth::user()->token_balance ?? 0 }} jetons</span></p>
                    </div>
                    <div class="bg-red-700 rounded-full px-3 py-1 flex items-center">
                        <i class="fas fa-coins text-yellow-300 mr-2"></i>
                    </div>
                </div>
            </div>

            <!-- CTA Principal -->
            <div class="p-4">
                <button onclick="window.location.href='{{ url('commnandes/create') }}'" 
                      class="w-full bg-red-600 text-white py-3 px-4 rounded-lg font-bold flex items-center justify-center hover:bg-red-700 transition-colors">
                      <i class="fas fa-plus-circle mr-2"></i> NOUVELLE LIVRAISON
                </button>
            </div>

            <!-- Livraison en cours -->
            <div class="p-4 border-b">
                <h2 class="font-bold text-lg mb-2 flex items-center">
                    <i class="fas fa-truck-moving text-red-600 mr-2"></i> Votre livraison
                </h2>
                
                <div id="commandeEnCours">
                    @if(isset($commandeEnCours) && $commandeEnCours)
                        @include('client.partials.commande-en-cours', ['commande' => $commandeEnCours])
                    @else
                        <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg text-center">
                            <i class="fas fa-box text-gray-400 text-2xl mb-2"></i>
                            <p class="text-gray-500">Aucune livraison en cours</p>
                            <button onclick="window.location.href='{{ url('commnandes/create') }}'" 
                                    class="mt-2 text-red-600 hover:text-red-800 font-medium">
                                Créer une livraison
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Livreurs disponibles -->
            <div class="p-4">
                <h2 class="font-bold text-lg mb-2 flex items-center">
                    <i class="fas fa-users text-red-600 mr-2"></i> Livreurs disponibles
                    <span class="ml-auto text-sm bg-green-100 text-green-800 px-2 py-1 rounded-full" id="livreursCount">
                        {{ isset($livreursDisponibles) ? count($livreursDisponibles) : 0 }} en ligne
                    </span>
                </h2>
                
                <div id="livreursDisponibles" class="grid grid-cols-2 gap-3">
                    @if(isset($livreursDisponibles) && $livreursDisponibles->count() > 0)
                        @foreach($livreursDisponibles as $livreur)
                            @include('client.partials.livreur-card', ['livreur' => $livreur])
                        @endforeach
                    @else
                        <div class="col-span-2 text-center py-8">
                            <i class="fas fa-users-slash text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-500">Aucun livreur disponible pour le moment</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques rapides -->
            <div class="p-4 border-t">
                <h3 class="font-bold mb-3">Vos statistiques</h3>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="border rounded-lg p-2">
                        <div class="text-xl font-bold text-red-600">{{ $statistiques['total_commandes'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500">Commandes</div>
                    </div>
                    <div class="border rounded-lg p-2">
                        <div class="text-xl font-bold text-green-600">{{ isset($statistiques['note_moyenne']) ? number_format($statistiques['note_moyenne'], 1) : '0.0' }}</div>
                        <div class="text-xs text-gray-500">Note moyenne</div>
                    </div>
                    <div class="border rounded-lg p-2">
                        <div class="text-xl font-bold text-blue-600">{{ isset($statistiques['montant_total']) ? number_format($statistiques['montant_total']) : 0 }}</div>
                        <div class="text-xs text-gray-500">FCFA dépensés</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onglet Profil -->
        <div id="profileTab" class="tab-content hidden p-4">
            <h2 class="font-bold text-xl mb-4 flex items-center">
                @if(Auth::user()->genre == 'F')
                    <i class="fas fa-female text-pink-500 mr-2"></i>
                @elseif(Auth::user()->genre == 'M')
                    <i class="fas fa-male text-blue-500 mr-2"></i>
                @else
                    <i class="fas fa-user text-gray-500 mr-2"></i>
                @endif
                Mon Profil
            </h2>

            <!-- Infos utilisateur -->
            <div class="flex items-center mb-6">
                <div class="rounded-full w-16 h-16 mr-4 flex items-center justify-center
                    @if(Auth::user()->genre == 'F') bg-pink-100 text-pink-500
                    @elseif(Auth::user()->genre == 'M') bg-blue-100 text-blue-500
                    @else bg-purple-100 text-purple-500 @endif">
                    @if(Auth::user()->genre == 'F')
                        <i class="fas fa-female text-2xl"></i>
                    @elseif(Auth::user()->genre == 'M')
                        <i class="fas fa-male text-2xl"></i>
                    @else
                        <i class="fas fa-user text-2xl"></i>
                    @endif
                </div>
                <div>
                    <h3 class="font-bold text-lg">
                        {{ Auth::user()->name ?? (Auth::user()->prenom . ' ' . Auth::user()->nom) }}
                    </h3>
                    <p class="text-gray-600">{{ Auth::user()->email }}</p>
                    <p class="text-sm text-gray-500">{{ Auth::user()->numero_telephone ?? 'Non renseigné' }}</p>
                </div>
            </div>

            <!-- Menu profil -->
            <div class="space-y-2">
                <a href="{{ route('profil.edit') }}" class="flex items-center p-3 border rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <div class="bg-blue-100 text-blue-600 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-edit"></i>
                    </div>
                    <span>Modifier mon profil</span>
                </a>

                <a href="#" class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                    <div class="bg-yellow-100 text-yellow-600 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <span>Aide & Support</span>
                </a>

                <a href="{{ route('user.messages') }}" class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                    <div class="bg-orange-100 text-orange-600 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <span>Voir mes messages</span>
                </a>

                <a href="{{ route('logout') }}" 
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="flex items-center p-3 border rounded-lg hover:bg-gray-50">
                    <div class="bg-red-100 text-red-600 w-10 h-10 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <span>Déconnexion</span>
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Barre de navigation basse -->
<div class="fixed bottom-0 left-0 right-0 bg-white border-t max-w-md mx-auto flex justify-around py-2">
    <button onclick="showTab('homeTab')" class="text-center p-2 text-red-600">
        <i class="fas fa-home block text-xl mx-auto"></i>
        <span class="text-xs">Accueil</span>
    </button>

    <a href="{{ route('tokens.index') }}" class="text-center p-2 text-gray-500 hover:text-red-600">
        <i class="fas fa-coins block text-xl mx-auto"></i>
        <span class="text-xs">Jetons</span>
    </a>

    <a href="{{ route('commnandes.index') }}" class="text-center p-2 text-gray-500 hover:text-red-600">
        <i class="fas fa-history block text-xl mx-auto"></i>
        <span class="text-xs">Historique</span>
    </a>

    <button onclick="showTab('profileTab')" class="text-center p-2 text-gray-500 hover:text-red-600">
        <i class="fas fa-user block text-xl mx-auto"></i>
        <span class="text-xs">Profil</span>
    </button>
</div>

<script>
    // Gestion des onglets
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });
        document.getElementById(tabId).classList.remove('hidden');
        
        // Mettre à jour la couleur des icônes
        document.querySelectorAll('.fixed button, .fixed a').forEach(btn => {
            btn.classList.remove('text-red-600');
            btn.classList.add('text-gray-500');
        });
        
        if (event && event.currentTarget) {
            event.currentTarget.classList.remove('text-gray-500');
            event.currentTarget.classList.add('text-red-600');
        }
    }
    
    // Actualisation automatique des données
    function refreshDashboardData() {
        fetch('/api/dashboard-data')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCommandeEnCours(data.data.commande_en_cours);
                    updateLivreursDisponibles(data.data.livreurs_disponibles);
                }
            })
            .catch(error => {
                console.error('Erreur lors de l\'actualisation:', error);
            });
    }
    
    // Mettre à jour la commande en cours
    function updateCommandeEnCours(commande) {
        const container = document.getElementById('commandeEnCours');
        if (commande) {
            container.innerHTML = `
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                    <div class="flex justify-between">
                        <span class="font-bold">${commande.reference}</span>
                        <span class="text-sm bg-${getStatusColor(commande.status)}-100 text-${getStatusColor(commande.status)}-800 px-2 py-1 rounded-full">
                            ${commande.status_label}
                        </span>
                    </div>
                    <div class="mt-2 text-sm">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                            <span>${commande.adresse_depart}</span>
                        </div>
                        <div class="flex items-center mt-1">
                            <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                            <span>${commande.adresse_arrivee}</span>
                        </div>
                    </div>
                    ${commande.driver ? `
                        <div class="mt-3 flex items-center text-sm">
                            <img src="${commande.driver.photo}" class="rounded-full w-8 h-8 mr-2" alt="Livreur">
                            <div>
                                <p class="font-medium">${commande.driver.name}</p>
                                <div class="flex items-center">
                                    <i class="fas fa-star text-yellow-400 text-xs mr-1"></i>
                                    <span>${commande.driver.note_moyenne}</span>
                                </div>
                            </div>
                            <a href="tel:${commande.driver.telephone}" class="ml-auto bg-red-600 text-white px-3 py-1 rounded-full text-xs">
                                <i class="fas fa-phone-alt mr-1"></i> Appeler
                            </a>
                        </div>
                    ` : ''}
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg text-center">
                    <i class="fas fa-box text-gray-400 text-2xl mb-2"></i>
                    <p class="text-gray-500">Aucune livraison en cours</p>
                    <button onclick="window.location.href='{{ url('commnandes/create') }}'" 
                            class="mt-2 text-red-600 hover:text-red-800 font-medium">
                        Créer une livraison
                    </button>
                </div>
            `;
        }
    }
    
    // Mettre à jour les livreurs disponibles
    function updateLivreursDisponibles(livreurs) {
        const container = document.getElementById('livreursDisponibles');
        const countElement = document.getElementById('livreursCount');
        
        countElement.textContent = `${livreurs.length} en ligne`;
        
        if (livreurs.length > 0) {
            container.innerHTML = livreurs.map(livreur => `
                <div class="border rounded-lg p-3 text-center">
                    <img src="${livreur.photo}" class="rounded-full w-14 h-14 mx-auto mb-2">
                    <h3 class="font-medium">${livreur.name}</h3>
                    <div class="flex items-center justify-center">
                        <i class="fas fa-star text-yellow-400 text-xs mr-1"></i>
                        <span class="text-xs">${livreur.note_moyenne}</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">${livreur.total_livraisons} livraisons</div>
                    <button onclick="ajouterFavori(${livreur.id})" class="mt-2 text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full hover:bg-red-200">
                        <i class="fas fa-plus mr-1"></i> Favoris
                    </button>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="col-span-2 text-center py-8">
                    <i class="fas fa-users-slash text-gray-400 text-3xl mb-2"></i>
                    <p class="text-gray-500">Aucun livreur disponible pour le moment</p>
                </div>
            `;
        }
    }
    
    // Obtenir la couleur du statut
    function getStatusColor(status) {
        const colors = {
            'en_attente_paiement': 'orange',
            'payee': 'blue',
            'acceptee': 'yellow',
            'en_cours': 'green',
            'livree': 'green',
            'annulee': 'red'
        };
        return colors[status] || 'gray';
    }
    
    // Ajouter un livreur aux favoris
    function ajouterFavori(livreurId) {
        fetch('/api/livreur-favori', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ livreur_id: livreurId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Livreur ajouté aux favoris', 'success');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors de l\'ajout aux favoris', 'error');
        });
    }
    
    // Afficher une notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-3 rounded-lg text-white z-50 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Afficher l'onglet Accueil par défaut
    document.addEventListener('DOMContentLoaded', function() {
        showTab('homeTab');
        
        // Actualiser les données toutes les 30 secondes
        setInterval(refreshDashboardData, 30000);
    });
</script>
@endsection