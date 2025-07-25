@extends('layouts.master')

@section('title', 'Livraisons disponibles')

@section('page-title', 'Livraisons disponibles')

@section('content')
<!-- Statistiques rapides -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-box"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Disponibles</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_disponibles'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100 text-green-600">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Mes acceptées</p>
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_acceptees'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <i class="fas fa-coins"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Revenus aujourd'hui</p>
                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['revenus_jour']) }} FCFA</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters Section -->
<div class="bg-white rounded-lg shadow-md mb-6">
    <div class="p-4">
        <form method="GET" action="{{ route('livreur.livraisons-disponible') }}" class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full pl-10 p-2.5" 
                           placeholder="Rechercher une adresse...">
                </div>
                
                <div class="inline-block">
                    <select name="type_colis" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        <option value="">Tous les types</option>
                        <option value="0-5kg" {{ request('type_colis') == '0-5kg' ? 'selected' : '' }}>0-5 kg</option>
                        <option value="5-10kg" {{ request('type_colis') == '5-10kg' ? 'selected' : '' }}>5-10 kg</option>
                        <option value="10-20kg" {{ request('type_colis') == '10-20kg' ? 'selected' : '' }}>10-20 kg</option>
                        <option value="20kg+" {{ request('type_colis') == '20-50kg' ? 'selected' : '' }}>20 kg et +</option>
                        <option value="20kg+" {{ request('type_colis') == '250kg+' ? 'selected' : '' }}>20 kg et +</option>

                    </select>
                </div>

                <div class="inline-block">
                    <select name="type_livraison" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        <option value="">Tous les types</option>
                        <option value="standard" {{ request('type_livraison') == 'standard' ? 'selected' : '' }}>Standard</option>
                        <option value="express" {{ request('type_livraison') == 'express' ? 'selected' : '' }}>Express</option>
                    </select>
                </div>
            </div>
            
            <div class="flex space-x-2">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-search mr-2"></i> Rechercher
                </button>
                <a href="{{ route('livreur.livraisons-disponible') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-times mr-2"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Livraisons disponibles -->
<div class="bg-white rounded-lg shadow-md">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">
            Livraisons disponibles ({{ $commandes->total() }})
        </h2>
    </div>
    <div class="p-4">
        @if($commandes->count() > 0)
            <div class="space-y-4">
                @foreach($commandes as $commande)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center space-y-4 md:space-y-0">
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">{{ $commande->reference }}</h3>
                            <div class="flex items-center text-sm text-gray-600 mt-1">
                                <span class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-1 text-red-500"></i> 
                                    {{ $commande->adresse_depart }}
                                </span>
                                <span class="mx-2">→</span>
                                <span class="flex items-center">
                                    <i class="fas fa-flag-checkered mr-1 text-green-500"></i>
                                    {{ $commande->adresse_arrivee }}
                                </span>
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $commande->type_colis == 'petit' ? 'bg-blue-100 text-blue-800' : 
                                       ($commande->type_colis == 'moyen' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($commande->type_colis) }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ number_format($commande->prix_final) }} FCFA
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $commande->type_livraison == 'express' ? 'bg-red-100 text-red-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $commande->type_livraison == 'express' ? 'Express' : 'Standard' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $commande->region_depart }} → {{ $commande->region_arrivee }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                    {{ $commande->created_at->diffForHumans() }}
                                </span>
                            </div>

                            <div class="mt-2 text-sm text-gray-600">
                                <span class="flex items-center">
                                    <i class="fas fa-user mr-1"></i>
                                    Client: {{ $commande->user->name ?? 'Non spécifié' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex space-x-2">
                            <button onclick="voirDetails({{ $commande->id }})" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg shadow-sm">
                                <i class="fas fa-info-circle"></i>
                            </button>
                            <button onclick="accepterCommande({{ $commande->id }})" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow-sm accepter-btn">
                                <i class="fas fa-check mr-1"></i> Accepter
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $commandes->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-500">Aucune commande disponible pour le moment.</p>
                <button onclick="window.location.reload()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-refresh mr-2"></i> Actualiser
                </button>
            </div>
        @endif
    </div>
</div>

<div id="detailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Détails de la commande</h3>
                <div id="detailsContent"></div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button onclick="fermerModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg">
                        Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let isAccepting = false;

function accepterCommande(commandeId) {
    if (isAccepting) return;
    if (!confirm('Êtes-vous sûr de vouloir accepter cette commande ?')) return;
    isAccepting = true;

    const btn = event.target.closest('.accepter-btn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Acceptation...';
    btn.disabled = true;

    fetch(`/livreur/commandes/${commandeId}/accepter`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fas fa-check mr-1"></i> Acceptée';
            btn.classList.remove('bg-green-500', 'hover:bg-green-600');
            btn.classList.add('bg-gray-500', 'cursor-not-allowed');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert(data.message || 'Une erreur est survenue');
        }
    })
    .catch(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Erreur réseau');
    })
    .finally(() => isAccepting = false);
}

function voirDetails(commandeId) {
    fetch(`/livreur/commandes/${commandeId}/details`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const c = data.commande;
            document.getElementById('detailsContent').innerHTML = `
                <div class="space-y-3">
                    <div><p class="text-sm font-medium text-gray-500">Référence</p><p class="text-sm">${c.reference}</p></div>
                    <div><p class="text-sm font-medium text-gray-500">Client</p><p class="text-sm">${c.user.name} (${c.user.phone})</p></div>
                    <div><p class="text-sm font-medium text-gray-500">Adresse de départ</p><p class="text-sm">${c.adresse_depart}</p></div>
                    <div><p class="text-sm font-medium text-gray-500">Adresse d'arrivée</p><p class="text-sm">${c.adresse_arrivee}</p></div>
                    <div><p class="text-sm font-medium text-gray-500">Type de colis</p><p class="text-sm">${c.type_colis}</p></div>
                    <div><p class="text-sm font-medium text-gray-500">Prix</p><p class="text-sm">${c.prix_final} FCFA</p></div>
                    <div><p class="text-sm font-medium text-gray-500">Date de création</p><p class="text-sm">${new Date(c.created_at).toLocaleString()}</p></div>
                    ${c.notes ? `<div><p class="text-sm font-medium text-gray-500">Notes</p><p class="text-sm">${c.notes}</p></div>` : ''}
                </div>`;
            document.getElementById('detailsModal').classList.remove('hidden');
        } else {
            alert('Impossible de charger les détails');
        }
    })
    .catch(() => alert('Erreur lors du chargement des détails'));
}

function fermerModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}
</script>
@endsection
