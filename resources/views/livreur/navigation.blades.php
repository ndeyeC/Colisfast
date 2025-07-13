@extends('layouts.master')

@section('title', 'Navigation GPS Livreur')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Navigation en temps réel</h5>
                    <button id="refreshPosition" class="btn btn-sm btn-light">
                        <i class="fas fa-sync-alt me-1"></i>Actualiser
                    </button>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-route me-2"></i>Détails de l'itinéraire</h6>
                </div>
                <div class="card-body">
                    <div id="routeDetails">
                        <div class="text-center my-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <p class="mt-2">Chargement des données de navigation...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-directions me-2"></i>Instructions</h6>
                </div>
                <div class="card-body p-0">
                    <div id="stepInstructions" class="list-group list-group-flush">
                        <!-- Instructions générées dynamiquement -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <button id="startNavigation" class="btn btn-primary w-100">
                                <i class="fas fa-play-circle me-2"></i>Démarrer
                            </button>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button id="completeDelivery" class="btn btn-success w-100">
                                <i class="fas fa-check-circle me-2"></i>Terminer
                            </button>
                        </div>
                        <div class="col-md-4 mb-3">
                            @if(isset($commande) && $commande->telephone_client)
                                <a href="tel:{{ $commande->telephone_client }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-phone me-2"></i>Appeler
                                </a>
                            @else
                                <button class="btn btn-outline-secondary w-100" disabled>
                                    <i class="fas fa-phone me-2"></i>Téléphone N/A
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Informations de debug (à supprimer en production) -->
<div class="d-none" id="debugInfo">
    <p>Commande ID: {{ $commande->id ?? 'NON DÉFINI' }}</p>
    <p>Route: {{ $route ? 'DÉFINIE' : 'NON DÉFINIE' }}</p>
</div>
@endsection

@section('scripts')
<!-- CSRF Token pour les requêtes AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Leaflet CSS/JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Routing Machine -->
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
$(document).ready(function() {
    // Variables globales avec vérification
    let map, routeControl, userMarker;
    
    // Récupération sécurisée de l'ID de commande
    const commandeId = @if(isset($commande) && $commande->id) {{ $commande->id }} @else null @endif;
    let routeData = @if(isset($route)) @json($route) @else null @endif;

    console.log('ID Commande:', commandeId);
    console.log('Route Data:', routeData);

    // Vérifier si l'ID de commande existe
    if (!commandeId) {
        console.error("ID de commande manquant");
        $('#routeDetails').html(`
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Erreur:</strong> ID de commande manquant.<br>
                <small>Veuillez accéder à cette page depuis la liste des commandes.</small>
            </div>
        `);
        
        // Désactiver les boutons
        $('#startNavigation, #completeDelivery').prop('disabled', true);
        return;
    }

    // Initialisation de la carte
    function initMap() {
        // Coordonnées par défaut (Dakar)
        const defaultLat = 14.716677;
        const defaultLng = -17.467686;
        
        map = L.map('map').setView([defaultLat, defaultLng], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        // Marqueur utilisateur avec icône personnalisée
        userMarker = L.marker([defaultLat, defaultLng], {
            icon: L.divIcon({
                html: '<i class="fas fa-truck fa-2x text-primary"></i>',
                iconSize: [30, 30],
                className: 'leaflet-user-marker'
            })
        }).addTo(map).bindPopup("Votre position actuelle");

        // Essayer d'obtenir la position actuelle
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                userMarker.setLatLng([lat, lng]);
                map.setView([lat, lng], 15);
            }, function(error) {
                console.warn("Erreur géolocalisation:", error.message);
            });
        }
    }

    // Charger les données de l'itinéraire
    function loadRoute() {
        console.log("Chargement de l'itinéraire pour la commande:", commandeId);
        
        $.ajax({
            url: `/livreur/commandes/${commandeId}/route-data`,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log("Données route reçues:", response);
                routeData = response;
                updateRouteDisplay();
            },
            error: function(xhr, status, error) {
                console.error("Erreur chargement itinéraire:", error);
                $('#routeDetails').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Aucun itinéraire disponible. Cliquez sur "Démarrer" pour commencer la navigation.
                    </div>
                `);
            }
        });
    }

    // Afficher l'itinéraire sur la carte
    function updateRouteDisplay() {
        if (!routeData || !routeData.start_point || !routeData.end_point) {
            console.warn("Données d'itinéraire incomplètes");
            return;
        }

        console.log("Mise à jour de l'affichage de l'itinéraire");

        // Nettoyer les anciens éléments
        if (routeControl) {
            map.removeControl(routeControl);
        }

        // Créer les waypoints
        const startPoint = L.latLng(routeData.start_point.lat, routeData.start_point.lng);
        const endPoint = L.latLng(routeData.end_point.lat, routeData.end_point.lng);

        // Afficher le nouvel itinéraire
        routeControl = L.Routing.control({
            waypoints: [startPoint, endPoint],
            routeWhileDragging: false,
            showAlternatives: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            createMarker: function(i, waypoint, n) {
                const icon = i === 0 ? 
                    L.divIcon({html: '<i class="fas fa-play-circle fa-2x text-success"></i>', iconSize: [30, 30], className: 'leaflet-start-marker'}) :
                    L.divIcon({html: '<i class="fas fa-flag-checkered fa-2x text-danger"></i>', iconSize: [30, 30], className: 'leaflet-end-marker'});
                return L.marker(waypoint.latLng, {icon});
            },
            lineOptions: {
                styles: [{ color: '#3b82f6', opacity: 0.7, weight: 5 }]
            }
        }).addTo(map);

        // Mettre à jour les détails
        updateRouteDetails();
    }

    // Mettre à jour les informations textuelles
    function updateRouteDetails() {
        if (!routeData) return;

        const estimatedArrival = new Date();
        if (routeData.duration_minutes) {
            estimatedArrival.setMinutes(estimatedArrival.getMinutes() + parseInt(routeData.duration_minutes));
        }

        $('#routeDetails').html(`
            <div class="d-flex align-items-center mb-3">
                <div class="me-3">
                    <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                </div>
                <div>
                    <p class="text-muted mb-0">Position de départ</p>
                    <p class="fw-bold mb-0">${routeData.start_address || 'Position actuelle'}</p>
                </div>
            </div>
            <div class="d-flex justify-content-start ps-5 my-2">
                <div class="border-start border-2 border-primary" style="height: 40px;"></div>
            </div>
            <div class="d-flex align-items-center mb-3">
                <div class="me-3">
                    <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                </div>
                <div>
                    <p class="text-muted mb-0">Destination</p>
                    <p class="fw-bold mb-0">${routeData.end_address || 'Adresse de livraison'}</p>
                </div>
            </div>
            <hr>
            <div class="row text-center">
                <div class="col-4">
                    <h4 class="fw-bold text-primary">${routeData.distance_km || '0'} km</h4>
                    <p class="text-muted small">Distance</p>
                </div>
                <div class="col-4">
                    <h4 class="fw-bold text-primary">${routeData.duration_minutes || '0'} min</h4>
                    <p class="text-muted small">Durée</p>
                </div>
                <div class="col-4">
                    <h4 class="fw-bold text-primary">${estimatedArrival.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})}</h4>
                    <p class="text-muted small">Arrivée prévue</p>
                </div>
            </div>
        `);

        // Mettre à jour les instructions
        updateStepInstructions();
    }

    // Mettre à jour les instructions étape par étape
    function updateStepInstructions() {
        const $stepsContainer = $('#stepInstructions').empty();
        
        if (routeData && routeData.steps && routeData.steps.length > 0) {
            routeData.steps.forEach((step, index) => {
                const isLast = index === routeData.steps.length - 1;
                $stepsContainer.append(`
                    <div class="list-group-item d-flex align-items-center py-3">
                        <div class="me-3">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fas fa-${isLast ? 'flag-checkered text-success' : 'arrow-right text-primary'}"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="mb-1">${step.instruction}</p>
                            <p class="text-muted mb-0 small">${step.distance}m • ${step.duration} min</p>
                        </div>
                    </div>
                `);
            });
        } else {
            $stepsContainer.append(`
                <div class="list-group-item text-center py-4">
                    <p class="text-muted mb-0">Instructions détaillées seront disponibles après le démarrage de la navigation</p>
                </div>
            `);
        }
    }

    // Démarrer la navigation
    $('#startNavigation').click(function() {
        console.log("Démarrage de la navigation");
        
        if (!navigator.geolocation) {
            alert("La géolocalisation n'est pas supportée par votre navigateur");
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Démarrage...');

        navigator.geolocation.getCurrentPosition(function(position) {
            console.log("Position obtenue:", position.coords);
            
            $.ajax({
                url: `/livreur/commandes/${commandeId}/start-tracking`,
                method: 'POST',
                data: {
                    current_lat: position.coords.latitude,
                    current_lng: position.coords.longitude,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log("Navigation démarrée:", response);
                    routeData = response;
                    updateRouteDisplay();
                    
                    // Démarrer le suivi de position
                    startPositionTracking();
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.success('Navigation démarrée avec succès');
                    } else {
                        alert('Navigation démarrée avec succès');
                    }
                    
                    $btn.removeClass('btn-primary').addClass('btn-success')
                        .html('<i class="fas fa-check-circle me-2"></i>En cours').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error("Erreur démarrage navigation:", xhr.responseJSON);
                    const message = xhr.responseJSON?.error || "Erreur lors du démarrage de la navigation";
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.error(message);
                    } else {
                        alert(message);
                    }
                    
                    $btn.prop('disabled', false).html('<i class="fas fa-play-circle me-2"></i>Démarrer');
                }
            });
        }, function(error) {
            console.error("Erreur géolocalisation:", error);
            const message = "Impossible d'obtenir votre position: " + error.message;
            
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                alert(message);
            }
            
            $btn.prop('disabled', false).html('<i class="fas fa-play-circle me-2"></i>Démarrer');
        });
    });

    // Suivi continu de la position
    function startPositionTracking() {
        console.log("Démarrage du suivi de position");
        
        if (window.positionTracker) {
            clearInterval(window.positionTracker);
        }

        window.positionTracker = setInterval(() => {
            navigator.geolocation.getCurrentPosition(function(position) {
                const { latitude, longitude } = position.coords;
                
                // Mettre à jour le marqueur
                userMarker.setLatLng([latitude, longitude]);
                
                // Envoyer la position au serveur
                $.ajax({
                    url: `/livreur/commandes/${commandeId}/update-position`,
                    method: 'POST',
                    data: {
                        lat: latitude,
                        lng: longitude,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log("Position mise à jour");
                    },
                    error: function(xhr, status, error) {
                        console.warn("Erreur mise à jour position:", error);
                    }
                });
            }, function(error) {
                console.warn("Erreur suivi position:", error);
            });
        }, 30000); // Toutes les 30 secondes
    }

    // Terminer la livraison
    $('#completeDelivery').click(function() {
        if (!confirm("Confirmez-vous la fin de cette livraison ?")) return;

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Finalisation...');

        $.ajax({
            url: `/livreur/commandes/${commandeId}/complete`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (typeof toastr !== 'undefined') {
                    toastr.success('Livraison marquée comme terminée');
                } else {
                    alert('Livraison marquée comme terminée');
                }
                
                // Arrêter le suivi de position
                if (window.positionTracker) {
                    clearInterval(window.positionTracker);
                }
                
                // Rediriger après 1.5 secondes
                setTimeout(() => {
                    window.location.href = '/livreur/commandes';
                }, 1500);
            },
            error: function(xhr, status, error) {
                console.error("Erreur finalisation:", xhr.responseJSON);
                const message = xhr.responseJSON?.error || "Erreur lors de la finalisation";
                
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
                
                $btn.prop('disabled', false).html('<i class="fas fa-check-circle me-2"></i>Terminer');
            }
        });
    });

    // Actualiser la position manuellement
    $('#refreshPosition').click(function() {
        const $btn = $(this);
        $btn.html('<i class="fas fa-spinner fa-spin me-1"></i>Actualisation...');
        
        navigator.geolocation.getCurrentPosition(function(position) {
            userMarker.setLatLng([position.coords.latitude, position.coords.longitude]);
            map.setView([position.coords.latitude, position.coords.longitude], 15);
            
            if (typeof toastr !== 'undefined') {
                toastr.info('Position actualisée');
            }
            
            $btn.html('<i class="fas fa-sync-alt me-1"></i>Actualiser');
        }, function(error) {
            console.error("Erreur actualisation position:", error);
            $btn.html('<i class="fas fa-sync-alt me-1"></i>Actualiser');
        });
    });

    // Initialisation
    console.log("Initialisation de la navigation");
    initMap();
    
    if (routeData) {
        console.log("Route existante trouvée:", routeData);
        updateRouteDisplay();
    } else {
        console.log("Chargement des données de route");
        loadRoute();
    }
});
</script>

<style>
    #map {
        z-index: 1;
    }
    .leaflet-user-marker {
        background: transparent;
        border: none;
    }
    .leaflet-routing-container {
        background: white;
        padding: 10px;
        border-radius: 5px;
    }
    .leaflet-start-marker, .leaflet-end-marker {
        background: transparent;
        border: none;
    }
</style>
@endsection