@extends('layouts.master')

@section('title', 'Mes revenus')
@section('page-title', 'Mes revenus')

@section('content')
<div class="container-fluid">
    <!-- Résumé des revenus -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Revenus (Ce mois)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($revenuMois ?? 0) }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Revenus (Total)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($revenuTotal ?? 0) }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Livraisons (Ce mois)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $livraisonsMois ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Moyenne par livraison
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($moyenneParLivraison ?? 0) }} FCFA
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div
                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Aperçu des revenus</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Période:</div>
                            <a class="dropdown-item active" href="#">Ce mois</a>
                            <a class="dropdown-item" href="#">Les 3 derniers mois</a>
                            <a class="dropdown-item" href="#">Cette année</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="earningsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div
                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Répartition des revenus</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink2"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow animated--fade-in"
                            aria-labelledby="dropdownMenuLink2">
                            <div class="dropdown-header">Options:</div>
                            <a class="dropdown-item" href="#">Par type de colis</a>
                            <a class="dropdown-item" href="#">Par zone</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="earningsSourceChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-primary"></i> Livraisons standard
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Livraisons express
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Livraisons volumineuses
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique des paiements -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Historique des paiements</h6>
                    <div>
                        <select class="form-select form-select-sm" id="paymentMonthFilter">
                            <option value="2025-03" selected>Mars 2025</option>
                            <option value="2025-02">Février 2025</option>
                            <option value="2025-01">Janvier 2025</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody id="paiementsTableBody">
                                @forelse ($paiements ?? [] as $paiement)
                                    <tr>
                                        <td>{{ $paiement->reference }}</td>
                                        <td>{{ $paiement->updated_at->format('d/m/Y') }}</td>
                                        <td>{{ $paiement->description }}</td>
                                        <td class="{{ $paiement->montant >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $paiement->montant >= 0 ? '+' : '-' }}{{ number_format(abs($paiement->montant)) }} FCFA
                                        </td>
                                        <td>
                                            @php
                                                $statut = strtolower($paiement->statut ?? '');
                                            @endphp
                                            @if($statut === 'payé')
                                                <span class="badge bg-success">Payé</span>
                                            @elseif($statut === 'en attente')
                                                <span class="badge bg-warning">En attente</span>
                                            @else
                                                <span class="badge bg-danger">{{ ucfirst($paiement->statut) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Aucun paiement trouvé.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Portefeuille & Performances -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Mon portefeuille</h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success fw-bold">{{ number_format($soldePortefeuille ?? 0) }} FCFA</h2>
                    <p class="text-muted">Solde actuel disponible</p>
                    <button class="btn btn-primary">
                        <i class="fas fa-money-bill-wave me-2"></i> Demander un retrait
                    </button>

                    <hr>

                    <h6 class="mb-3">Options de paiement</h6>

                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="orange" checked>
                            <label class="form-check-label" for="orange">
                                <i class="fas fa-mobile-alt text-warning me-1"></i> Orange Money
                            </label>
                        </div>
                        <div class="text-muted">*****7890</div>
                    </div>

                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="wave">
                            <label class="form-check-label" for="wave">
                                <i class="fas fa-water text-info me-1"></i> Wave
                            </label>
                        </div>
                        <div class="text-muted">*****5432</div>
                    </div>

                    <button class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fas fa-plus-circle me-1"></i> Ajouter une méthode de paiement
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance et bonus</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info d-flex align-items-center" role="alert">
                        <div class="me-3">
                            <i class="fas fa-info-circle fa-2x text-info"></i>
                        </div>
                        <div>
                            <p class="mb-0">Vous êtes à 8 livraisons de débloquer un bonus de 5,000 FCFA ce mois-ci!</p>
                        </div>
                    </div>

                    <h6 class="mb-3">Progression vers les bonus</h6>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Objectif 50 livraisons</span>
                            <span>{{ $livraisonsMois ?? 0 }}/50</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ (($livraisonsMois ?? 0) / 50) * 100 }}%"
                                aria-valuenow="{{ $livraisonsMois ?? 0 }}" aria-valuemin="0" aria-valuemax="50">
                            </div>
                        </div>
                        <small class="text-muted">Récompense: 5,000 FCFA</small>
                    </div>
                    <!-- Ajouter d'autres bonus si besoin -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('paymentMonthFilter').addEventListener('change', function () {
        const mois = this.value;
        fetch("{{ route('paiements.par.mois') }}?mois=" + mois)
            .then(response => response.json())
            .then(data => {
                document.getElementById('paiementsTableBody').innerHTML = data.html;
            })
            .catch(() => {
                alert('Erreur lors du chargement des paiements.');
            });
    });
</script>
@endsection
