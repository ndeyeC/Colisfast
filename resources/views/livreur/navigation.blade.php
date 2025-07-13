@extends('layouts.master')

@section('title', 'Navigation GPS Livreur')

@section('content')
<div class="container-fluid">
    <!-- Carte -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Navigation en temps réel</h5>
                    <button id="refreshPosition" class="btn btn-sm btn-light">
                        <i class="fas fa-sync-alt me-1"></i>Actualiser</button>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 500px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Infos itinéraire -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-route me-2"></i>Détails de l'itinéraire</h6>
                </div>
                <div class="card-body" id="routeDetails">
                    @if(!empty($route))
                        <p><strong>Départ :</strong> {{ $route['start_address'] ?? 'N/A' }}</p>
                        <p><strong>Arrivée :</strong> {{ $route['end_address'] ?? 'N/A' }}</p>
                        <p><strong>Distance :</strong> {{ $route['distance_km'] ?? '?' }} km</p>
                        <p><strong>Durée estimée :</strong> {{ $route['duration_minutes'] ?? '?' }} min</p>
                    @else
                        <div class="text-center my-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">Aucune route définie</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-directions me-2"></i>Instructions</h6>
                </div>
                <div class="card-body p-0">
                    <div id="stepInstructions" class="list-group list-group-flush">
                        @if(!empty($route['steps']))
                            @foreach($route['steps'] as $step)
                                <div class="list-group-item">
                                    <strong>{{ $step['instruction'] ?? 'Continuer' }}</strong><br>
                                    <small>{{ $step['distance'] ?? '?' }} m – {{ $step['duration'] ?? '?' }} min</small>
                                </div>
                            @endforeach
                        @else
                            <div class="list-group-item text-muted">Aucune instruction disponible</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
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
                                    <i class="fas fa-phone me-2"></i>Appeler client
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

    <!-- Debug -->
    <div class="d-none">
        <p>Commande ID: {{ $commande->id ?? 'NON DÉFINI' }}</p>
        <p>Route: {{ isset($route) && !empty($route) ? 'DÉFINIE' : 'NON DÉFINIE' }}</p>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('map').setView([14.6928, -17.4467], 13); // Position par défaut Dakar
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        @if(!empty($route))
            // Affichage des marqueurs
            L.marker([{{ $route['start_point']['lat'] }}, {{ $route['start_point']['lng'] }}])
                .addTo(map).bindPopup('Départ').openPopup();

            L.marker([{{ $route['end_point']['lat'] }}, {{ $route['end_point']['lng'] }}])
                .addTo(map).bindPopup('Arrivée');

            // Trajet
            @if(!empty($route['polyline']))
                const geojson = {!! json_encode($route['polyline']) !!};
                L.geoJSON(geojson, { color: 'blue' }).addTo(map);
            @endif
        @endif

        @if(isset($commande))
            const commandeId = {{ $commande->id }};

            document.getElementById('completeDelivery').addEventListener('click', function () {
                fetch(`/navigation/${commandeId}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    alert('Livraison terminée ✅');
                    location.reload();
                })
                .catch(() => alert('Erreur lors de la confirmation'));
            });

            document.getElementById('startNavigation').addEventListener('click', function () {
                navigator.geolocation.getCurrentPosition(function (pos) {
                    fetch(`/navigation/${commandeId}/start`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            current_lat: pos.coords.latitude,
                            current_lng: pos.coords.longitude
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert('Navigation démarrée 🚀');
                        location.reload();
                    })
                    .catch(() => alert('Erreur lors du démarrage'));
                });
            });
        @endif
    });
</script>
@endsection
