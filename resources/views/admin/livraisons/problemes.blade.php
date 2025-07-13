@extends('layouts.admin')

@section('title', 'Problèmes Signalés - Livraisons')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4><i class="fas fa-exclamation-triangle text-warning"></i> Problèmes Signalés</h4>
        <p class="text-muted">Gestion des problèmes signalés par les livreurs</p>
    </div>
    <a href="{{ route('admin.livraisons.index') }}" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left"></i> Retour aux livraisons
    </a>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h3>{{ $livraisons->where('probleme_decode.status', 'en_attente')->count() }}</h3>
                <p class="mb-0">En attente de traitement</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h3>{{ $livraisons->where('probleme_decode.status', 'traite')->count() }}</h3>
                <p class="mb-0">Problèmes traités</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h3>{{ $livraisons->total() }}</h3>
                <p class="mb-0">Total des signalements</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <button class="nav-link active" data-filter="all">
                    Tous les problèmes ({{ $livraisons->total() }})
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-filter="en_attente">
                    En attente ({{ $livraisons->where('probleme_decode.status', 'en_attente')->count() }})
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-filter="traite">
                    Traités ({{ $livraisons->where('probleme_decode.status', 'traite')->count() }})
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        @forelse($livraisons as $livraison)
            @php $probleme = $livraison->probleme_decode; @endphp
            <div class="card mb-3 probleme-item" data-status="{{ $probleme['status'] ?? 'en_attente' }}">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <i class="fas fa-truck"></i> Commande #{{ $livraison->id }}
                            @if(($probleme['status'] ?? 'en_attente') === 'en_attente')
                                <span class="badge bg-warning ms-2">Nouveau problème</span>
                            @else
                                <span class="badge bg-success ms-2">Traité</span>
                            @endif
                        </h6>
                       @if(isset($probleme['date_signalement']))
    <small class="text-muted">
        Signalé le {{ \Carbon\Carbon::parse($probleme['date_signalement'])->format('d/m/Y à H:i') }}
    </small>
@endif

                    </div>
                    <div class="text-end">
                        @if(($probleme['status'] ?? 'en_attente') === 'en_attente')
                            <button type="button" class="btn btn-sm btn-warning" onclick="traiterProbleme({{ $livraison->id }})">
                           <i class="fas fa-tools"></i> Traiter
                         </button>

                        @endif
                        <a href="{{ route('admin.livraisons.show', $livraison->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> Voir détails
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-sm-6">
                                    <strong>Client:</strong> {{ $livraison->user->name ?? 'N/A' }}<br>
                                    <strong>Livreur:</strong> {{ $livraison->driver->name ?? 'N/A' }}<br>
                                    <strong>Type de problème:</strong> 
                                    <span class="badge bg-secondary ms-1">{{ ucfirst(str_replace('_', ' ', $probleme['type_probleme'] ?? '')) }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Adresse de livraison:</strong><br>
                                    <span class="text-muted">{{ $livraison->adresse_arrivee }}</span><br>
                                    <strong>Prix:</strong> {{ number_format($livraison->prix_final, 0, ',', ' ') }} FCFA
                                </div>
                            </div>
                            
                            <div class="alert alert-light">
                                <strong><i class="fas fa-comment"></i> Description du problème:</strong><br>
                                {{ $probleme['description'] ?? 'Aucune description' }}
                            </div>
                            
                            @if(isset($probleme['action_admin']))
                                <div class="alert alert-success">
                                    <strong><i class="fas fa-check-circle"></i> Action effectuée:</strong> 
                                    <span class="badge bg-success">{{ ucfirst($probleme['action_admin']) }}</span><br>
                                    <strong>Commentaire admin:</strong> {{ $probleme['commentaire_admin'] }}<br>
                                    <small class="text-muted">
                                        Traité le {{ \Carbon\Carbon::parse($probleme['date_traitement'])->format('d/m/Y à H:i') }}
                                    </small>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            @if(isset($probleme['photo']) && $probleme['photo'])
                                <div class="mb-3">
                                    <strong>Photo du problème:</strong><br>
                                    <img src="{{ asset('storage/' . $probleme['photo']) }}" 
                                         class="img-fluid rounded shadow-sm mt-2" 
                                         style="max-height: 200px; cursor: pointer;"
                                         onclick="agrandirImage(this.src)">
                                </div>
                            @endif
                            
                            <div class="bg-light p-3 rounded">
                                <h6><i class="fas fa-info-circle"></i> Informations</h6>
                                <small>
                                    <strong>Statut commande:</strong> 
                                    @php
                                        $statusColors = [
                                            'en_attente_paiement' => 'secondary',
                                            'payee' => 'info',
                                            'confirmee' => 'primary',
                                            'acceptee' => 'warning',
                                            'en_cours' => 'primary',
                                            'livree' => 'success',
                                            'probleme_signale' => 'danger',
                                            'annulee' => 'dark'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$livraison->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $livraison->status)) }}
                                    </span><br>
                                    <strong>Date création:</strong> {{ $livraison->created_at->format('d/m/Y H:i') }}<br>
                                    <strong>Temps écoulé:</strong> {{ $livraison->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <h5>Aucun problème signalé</h5>
                <p class="text-muted">Toutes les livraisons se déroulent bien !</p>
            </div>
        @endforelse
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $livraisons->links() }}
        </div>
    </div>
</div>

<!-- Modal pour traiter les problèmes -->
<div class="modal fade" id="modalTraiterProbleme" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formTraiterProbleme" method="POST" action="">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Traiter le problème</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Ici tes champs de formulaire, par ex : -->
          <label>Action</label>
          <select name="action" class="form-select" required>
            <option value="">Choisir une action</option>
            <option value="resolu">Marquer comme résolu</option>
            <option value="reassigner">Réassigner</option>
            <option value="annuler">Annuler</option>
          </select>

          <label class="mt-3">Commentaire</label>
          <textarea name="commentaire_admin" class="form-control" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-warning">Traiter le problème</button>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Modal pour agrandir les images -->
<div class="modal fade" id="modalImage" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Photo du problème</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="imageAgrandie" src="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Filtrage par onglets
document.querySelectorAll('[data-filter]').forEach(button => {
    button.addEventListener('click', function() {
        // Mettre à jour les onglets actifs
        document.querySelectorAll('[data-filter]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        const items = document.querySelectorAll('.probleme-item');
        
        items.forEach(item => {
            if (filter === 'all' || item.dataset.status === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Traiter un problème
function traiterProbleme(livraisonId) {
  // Mettre l'URL POST correcte dans l'attribut action du formulaire
  document.getElementById('formTraiterProbleme').action = `/admin/livraisons/${livraisonId}/resoudre-probleme`;
  
  // Ouvrir le modal Bootstrap
  var modal = new bootstrap.Modal(document.getElementById('modalTraiterProbleme'));
  modal.show();
}


// Gestion de l'affichage du champ nouveau livreur
document.querySelector('select[name="action"]').addEventListener('change', function() {
    const divNouveauLivreur = document.getElementById('divNouveauLivreurModal');
    const selectLivreur = document.querySelector('select[name="nouveau_driver_id"]');
    
    if (this.value === 'reassigner') {
        divNouveauLivreur.style.display = 'block';
        selectLivreur.required = true;
    } else {
        divNouveauLivreur.style.display = 'none';
        selectLivreur.required = false;
    }
});

// Agrandir une image
function agrandirImage(src) {
    document.getElementById('imageAgrandie').src = src;
    new bootstrap.Modal(document.getElementById('modalImage')).show();
}

// Auto-refresh de la page toutes les 2 minutes pour les nouveaux problèmes
setInterval(function() {
    if (document.querySelector('[data-filter="en_attente"].active')) {
        location.reload();
    }
}, 120000); // 2 minutes

// Notification sonore pour les nouveaux problèmes (optionnel)
function jouerSonNotification() {
    // Vous pouvez ajouter un son de notification ici
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('Nouveau problème signalé', {
            body: 'Un livreur a signalé un problème sur une livraison',
            icon: '/favicon.ico'
        });
    }
}

// Demander permission pour les notifications
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
</script>
@endpush