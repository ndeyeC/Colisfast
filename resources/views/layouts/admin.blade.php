<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
     <meta name="description" content="Interface d'administration ColisFast">
    <title>ColisFast - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')

    <style>
        /* Styles améliorés pour la sidebar */
        .sidebar {
            background: linear-gradient(135deg, #2c3e50, #1a252f);
            min-height: 100vh;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .sidebar .text-white {
            color: #ffffff !important;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .sidebar .text-white-50 {
            color: #94a3b8 !important;
            font-size: 0.9rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sidebar .nav-item {
            margin-bottom: 0.5rem;
        }

        .sidebar .nav-link {
            color: #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.8rem 1rem;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background-color: rgba(59, 130, 246, 0.2);
            color: #ffffff;
            border-left: 4px solid #3b82f6;
        }

        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
            font-size: 1rem;
        }

        .sidebar hr.text-white-50 {
            border-color: rgba(255, 255, 255, 0.1);
            margin: 1.5rem 0;
        }

        /* Animation pour les liens */
        .sidebar .nav-link {
            position: relative;
            overflow: hidden;
        }

        .sidebar .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #3b82f6;
            transition: width 0.3s ease;
        }

        .sidebar .nav-link:hover::after {
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h2 class="text-white">ColisFast</h2>
                        <p class="text-white-50">Administration</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Tableau de bord
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.livreurs.index') ? 'active' : '' }}" href="{{ route('admin.livreurs.index') }}">
                                <i class="fas fa-users me-2"></i>
                                Gestion des livreurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.tarifs.create') ? 'active' : '' }}" href="{{ route('admin.tarifs.create') }}">
                                <i class="fas fa-money-bill me-2"></i>
                                Configuration des tarifs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.livraisons.index') ? 'active' : '' }}" href="{{ route('admin.livraisons.index') }}">
                                <i class="fas fa-truck me-2"></i>
                                Suivi des livraisons
                            </a>
                        </li>
                        <!-- <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.statistiques.*') ? 'active' : '' }}" href="{{ route('admin.statistiques.index') }}">
                                <i class="fas fa-chart-bar me-2"></i>
                                Statistiques
                            </a>
                        </li> -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.communications.index') ? 'active' : '' }}" href="{{ route('admin.communications.index') }}">
                                <i class="fas fa-comments me-2"></i>
                                Communication
                            </a>
                        </li>
                    </ul>
<li class="nav-item">
    <a href="{{ route('logout') }}" 
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
       class="nav-link">
        <i class="fas fa-sign-out-alt me-2"></i>
        Déconnexion
    </a>
</li>


<!-- Formulaire caché pour la déconnexion -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>
                    
  <hr class="text-white-50">
                    
                    
     </div>
                
     </div>
            
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">@yield('title')</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        @yield('actions')
                    </div>
                </div>
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Autres dépendances -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="{{ asset('js/admin.js') }}"></script>
    @yield('scripts')
    @stack('scripts')

</body>
</html>