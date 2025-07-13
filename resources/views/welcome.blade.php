<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>colisFast | Livraison rapide au Sénégal</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .hero-bg {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('image/logo.png'); /* Chemin de votre image */
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
        }
        
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        .animate-pulse-slow {
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
        }
        
        .section-divider {
            background: linear-gradient(90deg, transparent, #dc2626, transparent);
            height: 2px;
        }
        
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }
        
        .mobile-menu.active {
            transform: translateX(0);
        }
    </style>
</head>
<body class="bg-gray-50 overflow-x-hidden">
    <!-- Navigation -->
    <nav class="bg-white/95 backdrop-blur-md shadow-lg fixed w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-3xl font-black tracking-tight">
                            <span class="text-red-600">Colis</span><span class="text-red-800">Fast</span>
                        </span>
                    </div>
                    <div class="hidden md:ml-8 md:flex md:space-x-8">
                        <a href="#" class="border-red-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-semibold transition-colors hover:text-red-600">Accueil</a>
                        <a href="#comment-ca-marche" class="border-transparent text-gray-600 hover:border-red-300 hover:text-red-600 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all">Comment ça marche</a>
                        <a href="#avantages" class="border-transparent text-gray-600 hover:border-red-300 hover:text-red-600 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all">Avantages</a>
                        <a href="#contact" class="border-transparent text-gray-600 hover:border-red-300 hover:text-red-600 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-all">Contact</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-red-600 px-3 py-2 rounded-lg text-sm font-medium transition-colors">Connexion</a>
                    <a href="{{ route('register') }}" class="btn-primary px-6 py-2 rounded-full shadow-lg text-sm font-semibold text-white">Inscription</a>
                </div>
                <div class="md:hidden flex items-center">
                    <button type="button" id="mobile-menu-btn" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-red-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500 transition-colors">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu fixed inset-y-0 left-0 w-64 bg-white shadow-2xl z-50 md:hidden">
            <div class="flex flex-col h-full">
                <div class="flex items-center justify-between p-4 border-b">
                    <span class="text-2xl font-black">
                        <span class="text-red-600">Colis</span><span class="text-red-800">Fast</span>
                    </span>
                    <button id="close-menu" class="p-2 rounded-md text-gray-400 hover:text-red-600 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <nav class="flex-1 px-4 py-6 space-y-4">
                    <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-red-600 bg-red-50">Accueil</a>
                    <a href="#comment-ca-marche" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 transition-colors">Comment ça marche</a>
                    <a href="#avantages" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 transition-colors">Avantages</a>
                    <a href="#contact" class="block px-3 py-2 rounded-md text-base font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 transition-colors">Contact</a>
                </nav>
                <div class="p-4 border-t">
                    <a href="{{ route('register') }}" class="block w-full btn-primary text-center py-3 rounded-lg text-white font-semibold">Commencer</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative min-h-screen hero-bg overflow-hidden">
        <!-- Animated background elements -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-10 w-20 h-20 bg-white/10 rounded-full animate-float"></div>
            <div class="absolute top-40 right-20 w-32 h-32 bg-white/5 rounded-full animate-pulse-slow"></div>
            <div class="absolute bottom-20 left-1/4 w-16 h-16 bg-white/10 rounded-full animate-float" style="animation-delay: 2s;"></div>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-16">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center min-h-screen">
                <div class="text-white space-y-8">
                    <div class="space-y-4">
                        <h1 class="text-5xl lg:text-7xl font-black leading-tight">
                            La livraison
                            <span class="block text-red-200">n'a jamais été</span>
                            <span class="block">aussi simple</span>
                        </h1>
                        <div class="w-24 h-1 bg-red-200 rounded-full"></div>
                    </div>
                    
                    <p class="text-xl lg:text-2xl text-white leading-relaxed max-w-xl">
                        Connectez-vous avec des livreurs professionnels et faites livrer vos colis rapidement, en toute sécurité et à prix abordable au Sénégal.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                      
                        <a href="#comment-ca-marche" class="glass-effect px-8 py-4 rounded-full text-lg font-semibold text-white hover:bg-white/20 transition-all">
                            <i class="fas fa-play mr-2"></i>
                            Voir la démo
                        </a>
                    </div>
                </div>
                
                <div class="hidden lg:flex justify-center items-center">
                    <div class="relative">
                        <div class="absolute inset-0 bg-white/20 rounded-3xl blur-xl"></div>
                        <div class="relative bg-white/10 backdrop-blur-md rounded-3xl p-8 border border-white/20">
                            <div class="grid grid-cols-2 gap-6 text-center text-white">
                                <div class="space-y-2">
                                    <div class="text-4xl font-black">15min</div>
                                    <div class="text-sm opacity-80">Livraison express</div>
                                </div>
                                <div class="space-y-2">
                                    <div class="text-4xl font-black">100+</div>
                                    <div class="text-sm opacity-80">Livreurs actifs</div>
                                </div>
                                <div class="space-y-2">
                                    <div class="text-4xl font-black">4.8★</div>
                                    <div class="text-sm opacity-80">Note client</div>
                                </div>
                                <div class="space-y-2">
                                    <div class="text-4xl font-black">24/7</div>
                                    <div class="text-sm opacity-80">Support client</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="relative -mt-20 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card-hover bg-white rounded-2xl shadow-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bolt text-2xl text-red-600"></i>
                    </div>
                    <div class="text-4xl font-black text-gray-900 mb-2">15min</div>
                    <p class="text-gray-600 font-medium">Temps de livraison moyen en zone urbaine</p>
                </div>
                
                <div class="card-hover bg-white rounded-2xl shadow-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-2xl text-red-600"></i>
                    </div>
                    <div class="text-4xl font-black text-gray-900 mb-2">100+</div>
                    <p class="text-gray-600 font-medium">Livreurs partenaires dans tout le Sénégal</p>
                </div>
                
                <div class="card-hover bg-white rounded-2xl shadow-2xl p-8 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-star text-2xl text-red-600"></i>
                    </div>
                    <div class="text-4xl font-black text-gray-900 mb-2">4.8/5</div>
                    <p class="text-gray-600 font-medium">Note moyenne de satisfaction client</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Comment ça marche Section -->
    <div id="comment-ca-marche" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">Comment ça marche</h2>
                <div class="section-divider w-24 mx-auto mb-6"></div>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Un processus simple en trois étapes pour des livraisons sans tracas</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card-hover bg-white rounded-2xl shadow-xl p-8 text-center relative">
                    <div class="absolute -top-6 left-1/2 transform -translate-x-1/2">
                        <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold text-lg">1</div>
                    </div>
                    <div class="mt-4">
                        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-mobile-alt text-3xl text-red-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Commandez</h3>
                        <p class="text-gray-600 leading-relaxed">Inscrivez-vous sur notre plateforme et passez votre commande de livraison en quelques clics.</p>
                    </div>
                </div>

                <div class="card-hover bg-white rounded-2xl shadow-xl p-8 text-center relative">
                    <div class="absolute -top-6 left-1/2 transform -translate-x-1/2">
                        <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold text-lg">2</div>
                    </div>
                    <div class="mt-4">
                        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-motorcycle text-3xl text-red-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Suivez</h3>
                        <p class="text-gray-600 leading-relaxed">Un livreur proche prend en charge votre colis et vous pouvez suivre son trajet en temps réel.</p>
                    </div>
                </div>

                <div class="card-hover bg-white rounded-2xl shadow-xl p-8 text-center relative">
                    <div class="absolute -top-6 left-1/2 transform -translate-x-1/2">
                        <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center text-white font-bold text-lg">3</div>
                    </div>
                    <div class="mt-4">
                        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-check-circle text-3xl text-red-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Recevez</h3>
                        <p class="text-gray-600 leading-relaxed">Votre destinataire reçoit le colis rapidement et vous êtes notifié de la livraison.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Benefits Section -->
    <div id="avantages" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">Pourquoi choisir colisFast ?</h2>
                <div class="section-divider w-24 mx-auto mb-6"></div>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Des avantages exclusifs pour révolutionner vos livraisons</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="card-hover bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-8 border border-red-200">
                    <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-tachometer-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Livraison Ultra Rapide</h3>
                    <p class="text-gray-700 leading-relaxed">Notre réseau de livreurs couvre toutes les zones urbaines du Sénégal pour des livraisons en un temps record.</p>
                </div>

                <div class="card-hover bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-8 border border-red-200">
                    <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-wallet text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Tarifs Compétitifs</h3>
                    <p class="text-gray-700 leading-relaxed">Profitez de prix abordables grâce à notre système de paiement par jetons et notre optimisation des trajets.</p>
                </div>

                <div class="card-hover bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-8 border border-red-200">
                    <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-shield-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Sécurité Garantie</h3>
                    <p class="text-gray-700 leading-relaxed">Tous nos livreurs sont vérifiés et vous pouvez suivre votre livraison en temps réel pour plus de tranquillité.</p>
                </div>

                <div class="card-hover bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-8 border border-red-200">
                    <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-map-marked-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Couverture Nationale</h3>
                    <p class="text-gray-700 leading-relaxed">Nous livrons dans toutes les grandes villes du Sénégal et continuons d'étendre notre réseau.</p>
                </div>

                <div class="card-hover bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-8 border border-red-200">
                    <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-headset text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Support Client 24/7</h3>
                    <p class="text-gray-700 leading-relaxed">Notre équipe est disponible à tout moment pour répondre à vos questions et résoudre vos problèmes.</p>
                </div>

                <div class="card-hover bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-8 border border-red-200">
                    <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mb-6">
                        <i class="fas fa-mobile-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Application Intuitive</h3>
                    <p class="text-gray-700 leading-relaxed">Interface simple et conviviale pour commander une livraison en quelques clics seulement.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Section -->
    <div id="contact" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-black text-gray-900 mb-4">Contactez-nous</h2>
                <div class="section-divider w-24 mx-auto mb-6"></div>
                <p class="text-xl text-gray-600">Une question ? Notre équipe est là pour vous aider</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div class="card-hover bg-white rounded-2xl shadow-xl p-8 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-phone text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Téléphone</h3>
                    <p class="text-lg text-gray-600">+221 774836026</p>
                </div>

                <div class="card-hover bg-white rounded-2xl shadow-xl p-8 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-envelope text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Email</h3>
                    <p class="text-lg text-gray-600 break-all">ColisFast293@gmail.com</p>
                </div>

                <div class="card-hover bg-white rounded-2xl shadow-xl p-8 text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-map-marker-alt text-2xl text-red-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Adresse</h3>
                    <p class="text-lg text-gray-600">Dakar, Sénégal</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="space-y-6">
                    <div class="text-3xl font-black">
                        <span class="text-red-500">colis</span><span class="text-red-400">Fast</span>
                    </div>
                    <p class="text-gray-400 leading-relaxed">La solution de livraison innovante au Sénégal. Rapide, fiable et économique.</p>
                    <div class="flex space-x-4">
                        <a href="https://facebook.com/colisfast" target="_blank" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/colisfast" target="_blank" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://instagram.com/colisfast" target="_blank" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://linkedin.com/company/colisfast" target="_blank" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-red-600 transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Services</h3>
                    <ul class="space-y-3">
                        <li><a href="/services/livraison-express" class="text-gray-300 hover:text-red-400 transition-colors">Livraison express</a></li>
                        <li><a href="/services/livraison-programmee" class="text-gray-300 hover:text-red-400 transition-colors">Livraison programmée</a></li>
                        <li><a href="/services/solutions-ecommerce" class="text-gray-300 hover:text-red-400 transition-colors">Solutions e-commerce</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Entreprise</h3>
                    <ul class="space-y-3">
                        <li><a href="/apropos" class="text-gray-300 hover:text-red-400 transition-colors">À propos</a></li>
                        <li><a href="/carrieres" class="text-gray-300 hover:text-red-400 transition-colors">Carrières</a></li>
                        <li><a href="/blog" class="text-gray-300 hover:text-red-400 transition-colors">Blog</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Légal</h3>
                    <ul class="space-y-3">
                        <li><a href="/legal/conditions" class="text-gray-300 hover:text-red-400 transition-colors">Conditions d'utilisation</a></li>
                        <li><a href="/legal/confidentialite" class="text-gray-300 hover:text-red-400 transition-colors">Politique de confidentialité</a></li>
                        <li><a href="/legal/mentions" class="text-gray-300 hover:text-red-400 transition-colors">Mentions légales</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="mt-12 pt-8 border-t border-gray-800">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">© 2025 colisFast. Tous droits réservés.</p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="/legal/cookies" class="text-gray-400 hover:text-red-400 text-sm transition-colors">Politique de cookies</a>
                        <a href="/sitemap" class="text-gray-400 hover:text-red-400 text-sm transition-colors">Plan du site</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile menu functionality
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const closeMenuBtn = document.getElementById('close-menu');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
        
        closeMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
        
        // Close menu when clicking outside
        mobileMenu.addEventListener('click', (e) => {
            if (e.target === mobileMenu) {
                mobileMenu.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = 'auto';
                }
            });
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('shadow-2xl');
                nav.classList.remove('shadow-lg');
            } else {
                nav.classList.remove('shadow-2xl');
                nav.classList.add('shadow-lg');
            }
        });
        
        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.card-hover').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
        
        // Counter animation for stats
        const animateCounters = () => {
            const counters = document.querySelectorAll('.text-4xl.font-black');
            counters.forEach(counter => {
                const target = counter.textContent;
                const numericValue = parseInt(target.replace(/\D/g, ''));
                
                if (numericValue && numericValue > 0) {
                    let current = 0;
                    const increment = numericValue / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= numericValue) {
                            counter.textContent = target;
                            clearInterval(timer);
                        } else {
                            if (target.includes('min')) {
                                counter.textContent = Math.floor(current) + 'min';
                            } else if (target.includes('+')) {
                                counter.textContent = Math.floor(current) + '+';
                            } else if (target.includes('/')) {
                                counter.textContent = (current / 10).toFixed(1) + '/5';
                            } else {
                                counter.textContent = Math.floor(current);
                            }
                        }
                    }, 30);
                }
            });
        };
        
        // Trigger counter animation when stats section is visible
        const statsSection = document.querySelector('.relative.-mt-20');
        if (statsSection) {
            const statsObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounters();
                        statsObserver.unobserve(entry.target);
                    }
                });
            });
            statsObserver.observe(statsSection);
        }
    </script>
</body>
</html>