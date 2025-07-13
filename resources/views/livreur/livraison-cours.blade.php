@extends('layouts.master')

@section('title', 'Livraisons en cours')

@section('page-title', 'Livraisons en cours')


@section('styles')
    <style>
        #leafletMap { height: 400px; width: 100%; }
        .start-marker, .end-marker, .current-position { text-align: center; }
        .leaflet-popup-content-wrapper { max-width: 200px; }
        #routeInstructions { max-height: 150px; overflow-y: auto; }
    </style>
@endsection

@section('content')
<!-- Current Delivery Section -->
<div class="bg-white rounded-lg shadow-md mb-6">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Livraison actuelle</h2>
    </div>
    
    <div id="currentDeliveryContainer" class="p-4">
        @if($livraisonActuelle)
            <div class="flex flex-col md:flex-row items-start space-y-4 md:space-y-0">
                <!-- Delivery Info Column -->
                <div class="w-full md:w-2/3 pr-0 md:pr-4">
                    <div class="bg-blue-50 rounded-lg p-4 mb-4">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-3 rounded-lg mr-3">
                                <i class="fas fa-box text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Commande #{{ $livraisonActuelle->reference }}</h3>
                                <p class="text-sm text-gray-600">{{ $livraisonActuelle->adresse_depart }}</p>
                            </div>
                            <div class="ml-auto">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    En cours
                                </span>
                            </div>
                        </div>
                        
                        <!-- Delivery Steps -->
                        <div class="flex flex-col space-y-3">
                            <!-- Pickup Location -->
                            <div class="flex items-start">
                                <div class="flex items-center justify-center bg-green-100 rounded-full p-2 mr-3">
                                    <i class="fas fa-store text-green-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700">Point de ramassage</h4>
                                    <p class="text-sm text-gray-600">{{ $livraisonActuelle->adresse_depart }}</p>
                                    @if($livraisonActuelle->details_adresse_depart)
                                        <p class="text-xs text-gray-500">{{ $livraisonActuelle->details_adresse_depart }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Delivery Location -->
                            <div class="flex items-start">
                                <div class="flex items-center justify-center bg-red-100 rounded-full p-2 mr-3">
                                    <i class="fas fa-map-marker-alt text-red-600"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-700">Point de livraison</h4>
                                    <p class="text-sm text-gray-600">{{ $livraisonActuelle->adresse_arrivee }}</p>
                                    @if($livraisonActuelle->details_adresse_arrivee)
                                        <p class="text-xs text-gray-500">{{ $livraisonActuelle->details_adresse_arrivee }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="mt-4">
                            <div class="flex justify-between mb-1 text-xs text-gray-600">
                                <span>Progression</span>
                                <span id="deliveryProgressPercentage">{{ $progressPercentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div id="deliveryProgressBar" class="bg-green-500 h-2 rounded-full" 
                                     style="width: {{ $progressPercentage }}%"></div>
                            </div>
                        </div>
                        
                        <!-- Delivery Stats -->
                        <div class="flex flex-col space-y-2 mt-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Distance restante:</span>
                                <span id="remainingDistance" class="font-medium">
                                    {{ $livraisonActuelle->deliveryRoute ? $livraisonActuelle->deliveryRoute->distance_km : 'N/A' }} km
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Temps estimé:</span>
                                <span id="estimatedTime" class="font-medium">
                                    {{ $livraisonActuelle->deliveryRoute ? $livraisonActuelle->deliveryRoute->duration_minutes : 'N/A' }} min
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Client:</span>
                                <span class="font-medium">{{ $livraisonActuelle->user->name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Prix:</span>
                                <span class="font-medium">{{ number_format($livraisonActuelle->prix_final) }} FCFA</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex space-x-2">
                        <button onclick="openNavigation({{ $livraisonActuelle->id }})" 
                                class="flex-1 bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg shadow-sm flex items-center justify-center">
                               <i class="fas fa-route mr-2"></i>
                            Ouvrir la navigation
                        </button>
                        <a href="tel:{{ $livraisonActuelle->user->phone ?? '' }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg shadow-sm flex items-center justify-center">
                            <i class="fas fa-phone"></i>
                        </a>
                        <button class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-3 rounded-lg shadow-sm flex items-center justify-center">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Map Column -->
                <div class="w-full md:w-1/3">
                    <div class="h-64 bg-gray-100 rounded-lg relative" id="deliveryMap">
                        <!-- Map will be rendered here by JavaScript -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <p class="text-gray-500">Carte de l'itinéraire</p>
                        </div>
                    </div>
                    
                    <!-- Delivery Actions -->
                    <div class="bg-white rounded-lg shadow-sm mt-4 p-4 border border-gray-200">
                        <h3 class="font-medium text-gray-900 mb-2">Actions</h3>
                        <div class="space-y-2">
                            <button onclick="markAsDelivered({{ $livraisonActuelle->id }})" 
                                    class="w-full text-left bg-gray-50 hover:bg-gray-100 text-gray-700 px-4 py-2 rounded flex items-center">
                                <i class="fas fa-clipboard-check text-green-600 mr-2"></i>
                                Marquer comme livré
                            </button>
                            <button onclick="showProblemModal({{ $livraisonActuelle->id }})" 
                                    class="w-full text-left bg-gray-50 hover:bg-gray-100 text-gray-700 px-4 py-2 rounded flex items-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                                Signaler un problème
                            </button>
                            <button onclick="cancelDelivery({{ $livraisonActuelle->id }})" 
                                    class="w-full text-left bg-gray-50 hover:bg-gray-100 text-gray-700 px-4 py-2 rounded flex items-center">
                                <i class="fas fa-ban text-red-600 mr-2"></i>
                                Annuler la livraison
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Aucune livraison en cours actuellement</p>
                <a href="{{ route('livreur.livraisons-disponible') }}" 
                   class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i> Voir les livraisons disponibles
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Pending Deliveries Section -->
<div class="bg-white rounded-lg shadow-md mb-6">
    <div class="p-4 border-b border-gray-200">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Livraisons en attente</h2>
            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                {{ count($livraisonsEnAttente) }}
            </span>
        </div>
    </div>
    
    <div class="p-4">
        @if(count($livraisonsEnAttente) > 0)
            <div class="space-y-4">
                @foreach($livraisonsEnAttente as $livraison)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                        <div class="flex items-start">
                            <div class="bg-purple-100 p-3 rounded-lg mr-3">
                                <i class="fas fa-shopping-bag text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">Commande #{{ $livraison->reference }}</h3>
                                <div class="flex items-center text-sm text-gray-600 mt-1">
                                    <span class="flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1 text-red-500"></i> 
                                        {{ Str::limit($livraison->adresse_depart, 30) }}
                                    </span>
                                    <span class="mx-2">→</span>
                                    <span>{{ Str::limit($livraison->adresse_arrivee, 30) }}</span>
                                </div>
                                <div class="mt-2 flex space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        En attente
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ number_format($livraison->prix_final) }} FCFA
                                    </span>
                                    @if($livraison->type_livraison === 'express')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Express
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button onclick="startDelivery({{ $livraison->id }})" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-sm flex items-center">
                                <i class="fas fa-play mr-2"></i>
                                Démarrer
                            </button>
                            <button onclick="cancelDelivery({{ $livraison->id }})" 
                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg shadow-sm flex items-center">
                                <i class="fas fa-times mr-2"></i>
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Aucune livraison en attente</p>
            </div>
        @endif
    </div>
</div>

<!-- Statistics Section -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex items-center">
            <div class="p-2 rounded-full bg-blue-100 text-blue-600 mr-3">
                <i class="fas fa-box-open"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Livraisons aujourd'hui</p>
                <p class="text-xl font-semibold">{{ $statistiques['livraisons_jour'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex items-center">
            <div class="p-2 rounded-full bg-green-100 text-green-600 mr-3">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Revenus aujourd'hui</p>
                <p class="text-xl font-semibold">{{ number_format($statistiques['revenus_jour']) }} FCFA</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex items-center">
            <div class="p-2 rounded-full bg-yellow-100 text-yellow-600 mr-3">
                <i class="fas fa-truck-moving"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">En cours</p>
                <p class="text-xl font-semibold">{{ $statistiques['en_cours'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex items-center">
            <div class="p-2 rounded-full bg-purple-100 text-purple-600 mr-3">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">En attente</p>
                <p class="text-xl font-semibold">{{ $statistiques['en_attente'] }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Mark as Delivered Modal -->
<div id="deliveredModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmer la livraison</h3>
                <form id="deliveredForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire</label>
                            <textarea name="commentaire_livraison" rows="3" class="w-full rounded-lg border-gray-300"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Photo de livraison</label>
                            <input type="file" name="photo_livraison" accept="image/*" class="w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('deliveredModal')" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                            Annuler
                        </button>
                        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                            Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Problem Modal -->
<!-- <div id="problemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Signaler un problème</h3>
                <form id="problemForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de problème</label>
                            <select name="type_probleme" class="w-full rounded-lg border-gray-300">
                                <option value="client_absent">Client absent</option>
                                <option value="adresse_incorrecte">Adresse incorrecte</option>
                                <option value="colis_endommage">Colis endommagé</option>
                                <option value="autre">Autre problème</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="3" required class="w-full rounded-lg border-gray-300"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Photo (optionnelle)</label>
                            <input type="file" name="photo" accept="image/*" class="w-full rounded-lg border-gray-300">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('problemModal')" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                            Annuler
                        </button>
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                            Signaler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> -->

<!-- Problem Modal -->
<div id="problemModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Signaler un problème</h3>
                <div id="problemFormErrors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"></div>
                
                <form id="problemForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de problème *</label>
                            <select name="type_probleme" required class="w-full rounded-lg border-gray-300">
                                <option value="">Sélectionnez un type</option>
                                <option value="client_absent">Client absent</option>
                                <option value="adresse_incorrecte">Adresse incorrecte</option>
                                <option value="colis_endommage">Colis endommagé</option>
                                <option value="autre">Autre problème</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                            <textarea name="description" rows="3" required class="w-full rounded-lg border-gray-300" placeholder="Décrivez le problème rencontré..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Photo (optionnelle)</label>
                            <input type="file" name="photo" accept="image/*" class="w-full rounded-lg border-gray-300">
                            <p class="text-xs text-gray-500 mt-1">Taille max: 2MB (JPEG, PNG)</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('problemModal')" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                            Annuler
                        </button>
                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center">
                            <span id="submitBtnText">Signaler</span>
                            <i id="submitBtnSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Delivery Modal -->
<div id="cancelModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Annuler la livraison</h3>
                
                <!-- Conteneur pour les erreurs -->
                <div id="cancelFormErrors" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"></div>
                
                <form id="cancelForm" method="POST" action="">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Raison de l'annulation *
                        </label>
                        <textarea 
                            name="raison" 
                            rows="3" 
                            required 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Expliquez pourquoi vous annulez cette livraison..."
                        ></textarea>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('cancelModal')" 
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                            <span class="submit-text">Confirmer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Options Modal -->
<div id="navigationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Navigation</h3>
                <div id="leafletMap" style="height: 400px; width: 100%;"></div>
                <div class="mt-4">
                    <h4 class="text-sm font-medium text-gray-700">Instructions de l'itinéraire</h4>
                    <div id="routeInstructions" class="text-sm text-gray-600 max-h-40 overflow-y-auto"></div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button onclick="closeModal('navigationModal')" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script>
// Global variables
let currentDeliveryId = {{ $livraisonActuelle ? $livraisonActuelle->id : 'null' }};
let positionUpdateInterval;

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    if (currentDeliveryId) {
        initMap();
        startPositionUpdates();
    }
    setupFormSubmissions();
});

// Initialize map
function initMap() {
    console.log('Initializing map for delivery', currentDeliveryId);
    // Intégration réelle via Google Maps / Leaflet ici
}

// Start periodic position updates
function startPositionUpdates() {
    if (positionUpdateInterval) clearInterval(positionUpdateInterval);
    positionUpdateInterval = setInterval(updateDeliveryPosition, 30000);
}

// Update delivery position
function updateDeliveryPosition() {
    if (!navigator.geolocation) return console.error('Géolocalisation non supportée');

    navigator.geolocation.getCurrentPosition(
        position => {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            fetch(`/api/livraisons/${currentDeliveryId}/update-position`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    updateDeliveryStats(data);
                    updateMapPosition(lat, lng);
                }
            })
            .catch(err => console.error('Erreur position:', err));
        },
        error => console.error('Erreur géolocalisation:', error),
        { enableHighAccuracy: true }
    );
}

// Update delivery stats in UI
function updateDeliveryStats(data) {
    if (data.distance_restante !== undefined)
        document.getElementById('remainingDistance').textContent = data.distance_restante + ' km';

    if (data.temps_estime !== undefined)
        document.getElementById('estimatedTime').textContent = data.temps_estime + ' min';

    if (data.progress_percentage !== undefined) {
        document.getElementById('deliveryProgressPercentage').textContent = data.progress_percentage + '%';
        document.getElementById('deliveryProgressBar').style.width = data.progress_percentage + '%';
    }
}

// Update map
function updateMapPosition(lat, lng) {
    console.log('Map position updated to:', lat, lng);
    // Code réel de mise à jour carte ici
}

// Open navigation modal
function openNavigation(deliveryId) {
    fetch(`/livreur/livraisons/${deliveryId}/navigation`)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Erreur navigation');
            const routeData = data.route_data;
            console.log('Route data:', routeData);

            const map = L.map('leafletMap').setView([routeData.current_position.lat, routeData.current_position.lng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            L.marker([routeData.start_point.lat, routeData.start_point.lng]).addTo(map)
                .bindPopup('Point de départ: ' + routeData.start_address)
                .openPopup();
            L.marker([routeData.end_point.lat, routeData.end_point.lng]).addTo(map)
                .bindPopup('Point d\'arrivée: ' + routeData.end_address)
                .openPopup();
            L.marker([routeData.current_position.lat, routeData.current_position.lng]).addTo(map)
                .bindPopup('Position actuelle')
                .openPopup();

            if (routeData.polyline && routeData.polyline.coordinates) {
                const polyline = L.polyline(routeData.polyline.coordinates.map(coord => [coord[1], coord[0]]), {
                    color: 'blue',
                    weight: 4,
                    opacity: 0.7
                }).addTo(map);
                map.fitBounds(polyline.getBounds());
                console.log('Polyline added:', routeData.polyline.coordinates);
            } else {
                console.warn('No valid polyline data:', routeData.polyline);
                alert('Aucune donnée d\'itinéraire disponible.');
            }

            const instructionsDiv = document.getElementById('routeInstructions');
            if (routeData.steps && routeData.steps.length > 0) {
                instructionsDiv.innerHTML = routeData.steps.map(step => `<p>${step.instruction}</p>`).join('');
            } else {
                instructionsDiv.innerHTML = '<p>Aucune instruction disponible.</p>';
                console.warn('No steps data:', routeData.steps);
            }

            openModal('navigationModal');
        })
        .catch(err => {
            console.error('Navigation error:', err);
            alert('Erreur de navigation: ' + err.message);
        });
}
// Start delivery
function startDelivery(deliveryId) {
    if (!navigator.geolocation) return alert('Activez la géolocalisation');

    const startBtn = document.getElementById('startDeliveryBtn');
    if (startBtn) {
        startBtn.disabled = true;
        startBtn.textContent = 'Démarrage...';
    }

    navigator.geolocation.getCurrentPosition(
        position => {
            fetch(`/livreur/livraisons/${deliveryId}/demarrer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur de démarrage');
                    if (startBtn) {
                        startBtn.disabled = false;
                        startBtn.textContent = 'Démarrer';
                    }
                }
            })
            .catch(err => {
                console.error('Erreur:', err);
                alert('Erreur réseau');
                if (startBtn) {
                    startBtn.disabled = false;
                    startBtn.textContent = 'Démarrer';
                }
            });
        },
        () => {
            alert('Position requise');
            if (startBtn) {
                startBtn.disabled = false;
                startBtn.textContent = 'Démarrer';
            }
        },
        { enableHighAccuracy: true }
    );
}

// Mark as delivered
function markAsDelivered(deliveryId) {
    document.getElementById('deliveredForm').action = `/livreur/livraisons/${deliveryId}/marquer-livree`;
    openModal('deliveredModal');
}

// Report problem
function showProblemModal(deliveryId) {
    document.getElementById('problemForm').action = `/livreur/livraisons/${deliveryId}/signaler-probleme`;
    openModal('problemModal');
}

// Cancel delivery - VERSION OPTIMALE AVEC ROUTE NOMMÉE
function cancelDelivery(deliveryId) {
    console.log('Tentative d\'annulation pour la livraison:', deliveryId);
    
    if (confirm('Êtes-vous sûr de vouloir annuler cette livraison ?')) {
        const form = document.getElementById('cancelForm');
        
        // ✅ SOLUTION OPTIMALE: Utiliser la route nommée Laravel
        if (typeof cancelRouteUrl !== 'undefined') {
            form.action = cancelRouteUrl.replace(':commandeId', deliveryId);
        } else {
            // Fallback avec URL directe
            form.action = `/livreur/livraisons/${deliveryId}/annuler`;
        }
        
        form.reset();
        
        const errorContainer = document.getElementById('cancelFormErrors');
        if (errorContainer) {
            errorContainer.classList.add('hidden');
            errorContainer.innerHTML = '';
        }
        
        console.log('URL d\'annulation:', form.action);
        openModal('cancelModal');
    }
}

// ✅ FONCTION SUBMITFORM UNIFIÉE - PLUS DE DUPLICATION
function submitForm(form) {
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const submitText = submitBtn.querySelector('.submit-text') || submitBtn;
    const originalText = submitText.textContent;
    
    // Identifier le conteneur d'erreurs selon le formulaire
    const errorContainerMap = {
        'cancelForm': 'cancelFormErrors',
        'problemForm': 'problemFormErrors', 
        'deliveredForm': 'deliveredFormErrors'
    };
    const errorContainer = document.getElementById(errorContainerMap[form.id]);
    
    // UI Loading state
    submitBtn.disabled = true;
    submitText.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Envoi...';
    
    if (errorContainer) {
        errorContainer.classList.add('hidden');
        errorContainer.innerHTML = '';
    }

    console.log('Soumission du formulaire:', form.action);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(async response => {
        console.log('Réponse reçue:', response.status);
        
        const contentType = response.headers.get('content-type');
        let data;
        
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            console.error('Réponse non-JSON:', text);
            throw new Error('Réponse invalide du serveur');
        }
        
        if (!response.ok) {
            // Gestion spéciale pour les problèmes déjà signalés
            if (data.current_status === 'probleme_signale' && form.id === 'problemForm') {
                if (confirm('Un signalement existe déjà. Voulez-vous le mettre à jour ?')) {
                    return submitForm(form); // Nouvelle tentative
                }
                return;
            }
            
            // Gestion des erreurs HTTP
            if (response.status === 404) {
                throw new Error('Route non trouvée');
            } else if (response.status === 403) {
                throw new Error('Accès refusé');
            } else if (response.status === 422) {
                const errors = data.errors || {};
                const errorMessages = Object.values(errors).flat();
                throw new Error(errorMessages.join('\n') || 'Données invalides');
            } else if (response.status === 500) {
                throw new Error('Erreur serveur');
            }
            
            throw new Error(data.message || `Erreur ${response.status}`);
        }
        
        return data;
    })
    .then(data => {
        if (data && data.success) {
            showAlert('success', data.message || 'Opération réussie');
            
            setTimeout(() => {
                // Fermer le modal correspondant
                const modalMap = {
                    'cancelForm': 'cancelModal',
                    'problemForm': 'problemModal',
                    'deliveredForm': 'deliveredModal'
                };
                closeModal(modalMap[form.id]);
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Réponse inattendue');
        }
    })
    .catch(error => {
        console.error('Erreur soumission:', error);
        const errorMessage = error.message || 'Erreur inattendue';
        
        if (errorContainer) {
            errorContainer.textContent = errorMessage;
            errorContainer.classList.remove('hidden');
        } else {
            showAlert('error', errorMessage);
        }
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitText.textContent = originalText;
    });
}

// Modal helpers
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

// ✅ CONFIGURATION DES FORMULAIRES UNIFIÉE - PLUS DE DUPLICATION
function setupFormSubmissions() {
    const formsConfig = {
        'deliveredForm': { needsGeolocation: true },
        'problemForm': { needsGeolocation: true },
        'cancelForm': { needsGeolocation: false }
    };
    
    Object.entries(formsConfig).forEach(([formId, config]) => {
        const form = document.getElementById(formId);
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (config.needsGeolocation && navigator.geolocation) {
                // Ajouter géolocalisation avant soumission
                navigator.geolocation.getCurrentPosition(
                    position => {
                        // Ajouter ou mettre à jour les coordonnées
                        updateOrCreateHiddenInput(form, 'latitude', position.coords.latitude);
                        updateOrCreateHiddenInput(form, 'longitude', position.coords.longitude);
                        
                        submitForm(form);
                    },
                    error => {
                        console.error('Erreur géolocalisation:', error);
                        alert('Activez la géolocalisation pour ce formulaire.');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                // Soumission directe
                submitForm(form);
            }
        });
    });
}

// Helper: Créer ou mettre à jour un input caché
function updateOrCreateHiddenInput(form, name, value) {
    let input = form.querySelector(`input[name="${name}"]`);
    if (!input) {
        input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        form.appendChild(input);
    }
    input.value = value;
}

// Afficher les alertes
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
    }`;
    alertDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            ${message}
        </div>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => alertDiv.remove(), 5000);
}

// Periodic status refresh
setInterval(() => {
    if (currentDeliveryId) {
        fetch(`/api/livraisons/${currentDeliveryId}/status`)
            .then(res => res.json())
            .then(data => {
                if (data.success) updateDeliveryStats(data);
            })
            .catch(err => console.error('Erreur refresh status:', err));
    }
}, 60000);
</script>
@endsection