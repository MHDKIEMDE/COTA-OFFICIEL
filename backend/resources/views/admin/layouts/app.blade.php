<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - COTA Admin</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#6366F1',
                        secondary: '#8B5CF6',
                        success: '#10B981',
                        warning: '#F59E0B',
                        danger: '#EF4444',
                        dark: {
                            100: '#1E293B',
                            200: '#0F172A',
                            300: '#0D1321',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .sidebar-link.active {
            background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
            color: white;
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .glass-effect {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
        }
        /* Fix pour les graphiques Chart.js - éviter les débordements */
        .chart-container {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden !important;
            clip-path: inset(0 0 0 0);
            -webkit-clip-path: inset(0 0 0 0);
        }
        .chart-container canvas {
            max-width: 100% !important;
            max-height: 100% !important;
            width: 100% !important;
            height: 100% !important;
            display: block !important;
            box-sizing: border-box !important;
        }
        /* S'assurer que les conteneurs de graphiques ne débordent pas */
        .bg-dark-100.overflow-hidden {
            overflow: hidden !important;
            position: relative;
        }
        /* Mobile drawer styles */
        @media (max-width: 1023px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0 !important;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 40;
            }
            .sidebar-overlay.open {
                display: block;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="h-full bg-dark-200 text-gray-100">
    <!-- Sidebar Overlay (mobile only) -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    
    <div class="flex h-full">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar fixed inset-y-0 left-0 z-50 w-64 bg-dark-100 border-r border-gray-700/50 flex flex-col lg:translate-x-0">
            <!-- Logo -->
            <div class="flex items-center justify-center h-16 border-b border-gray-700/50 bg-gradient-to-r from-primary to-secondary">
                <span class="text-2xl font-bold text-white">⚽ COTA Admin</span>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <ul class="space-y-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie w-5"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <!-- Pronostics -->
                    <li>
                        <a href="{{ route('admin.predictions.index') }}" 
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.predictions*') ? 'active' : '' }}">
                            <i class="fa-solid fa-futbol w-5"></i>
                            <span>Pronostics</span>
                        </a>
                    </li>
                    
                    <!-- Utilisateurs -->
                    <li>
                        <a href="{{ route('admin.users.index') }}" 
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                            <i class="fa-solid fa-users w-5"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </li>
                    
                    <!-- Affiliations -->
                    <li>
                        <a href="{{ route('admin.affiliates.index') }}" 
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.affiliates*') ? 'active' : '' }}">
                            <i class="fa-solid fa-handshake w-5"></i>
                            <span>Affiliations</span>
                        </a>
                    </li>
                    
                    <!-- Bookmakers -->
                    <li>
                        <a href="{{ route('admin.bookmakers.index') }}" 
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.bookmakers*') ? 'active' : '' }}">
                            <i class="fa-solid fa-link w-5"></i>
                            <span>Bookmakers</span>
                        </a>
                    </li>
                    
                    <!-- Compétitions (TENDANCES) -->
                    <li>
                        <a href="{{ route('admin.competitions.index') }}" 
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.competitions*') ? 'active' : '' }}">
                            <i class="fa-solid fa-trophy w-5"></i>
                            <span>Compétitions</span>
                            <span class="ml-auto px-2 py-0.5 text-xs bg-orange-500/20 text-orange-400 rounded-full">🔥</span>
                        </a>
                    </li>
                    
                    <!-- Statistiques avancées -->
                    <li>
                        <a href="{{ route('admin.stats.index') }}"
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.stats*') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-column w-5"></i>
                            <span>Statistiques</span>
                        </a>
                    </li>

                    <!-- Abonnements -->
                    <li>
                        <a href="{{ route('admin.subscriptions.index') }}"
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}">
                            <i class="fa-solid fa-credit-card w-5"></i>
                            <span>Abonnements</span>
                        </a>
                    </li>

                    <!-- Parrainages -->
                    <li>
                        <a href="{{ route('admin.referrals.index') }}"
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.referrals*') ? 'active' : '' }}">
                            <i class="fa-solid fa-people-arrows w-5"></i>
                            <span>Parrainages</span>
                        </a>
                    </li>

                    <!-- Feedbacks -->
                    <li>
                        <a href="{{ route('admin.feedbacks.index') }}"
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.feedbacks*') ? 'active' : '' }}">
                            <i class="fa-solid fa-comment-dots w-5"></i>
                            <span>Feedbacks</span>
                            @php $openCount = \App\Models\Feedback::where('status','open')->count(); @endphp
                            @if($openCount > 0)
                                <span class="ml-auto px-2 py-0.5 text-xs bg-warning/20 text-warning rounded-full">{{ $openCount }}</span>
                            @endif
                        </a>
                    </li>

                    <li class="pt-4 border-t border-gray-700/50 mt-4">
                        <span class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Système</span>
                    </li>
                    
                    <!-- Paramètres -->
                    <li>
                        <a href="{{ route('admin.settings.index') }}"
                           class="sidebar-link flex items-center gap-3 px-4 py-3 rounded-lg text-gray-300 hover:bg-gray-700/50 transition {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                            <i class="fa-solid fa-cog w-5"></i>
                            <span>Paramètres</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- User Info -->
            <div class="border-t border-gray-700/50 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                        <i class="fa-solid fa-user-shield text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400">Super Admin</p>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="p-2 text-gray-400 hover:text-danger transition" title="Déconnexion">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="main-content flex-1 ml-0 lg:ml-64">
            <!-- Top Bar -->
            <header class="h-16 bg-dark-100 border-b border-gray-700/50 flex items-center justify-between px-4 lg:px-6 sticky top-0 z-40">
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Button -->
                    <button 
                        id="mobileMenuButton"
                        onclick="toggleSidebar()"
                        class="lg:hidden p-2 text-gray-400 hover:text-white transition"
                        aria-label="Toggle menu"
                    >
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg lg:text-xl font-semibold text-white">@yield('page-title', 'Dashboard')</h1>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Notifications -->
                    <button class="relative p-2 text-gray-400 hover:text-white transition">
                        <i class="fa-solid fa-bell"></i>
                        <span class="absolute top-0 right-0 w-2 h-2 bg-danger rounded-full"></span>
                    </button>
                    
                    <!-- Refresh -->
                    <button onclick="location.reload()" class="p-2 text-gray-400 hover:text-white transition" title="Rafraîchir">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                    
                    <!-- Date -->
                    <span class="hidden sm:inline text-sm text-gray-400">
                        {{ now()->format('d/m/Y H:i') }}
                    </span>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-3 sm:p-6">
                <!-- Alerts -->
                @if(session('success'))
                    <div class="mb-6 p-4 bg-success/20 border border-success/50 text-success rounded-lg flex items-center gap-3" id="alert-success">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>{{ session('success') }}</span>
                        <button onclick="document.getElementById('alert-success').remove()" class="ml-auto hover:opacity-75">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-6 p-4 bg-danger/20 border border-danger/50 text-danger rounded-lg flex items-center gap-3" id="alert-error">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <span>{{ session('error') }}</span>
                        <button onclick="document.getElementById('alert-error').remove()" class="ml-auto hover:opacity-75">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="mb-6 p-4 bg-warning/20 border border-warning/50 text-warning rounded-lg flex items-center gap-3" id="alert-warning">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        <span>{{ session('warning') }}</span>
                        <button onclick="document.getElementById('alert-warning').remove()" class="ml-auto hover:opacity-75">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="mb-6 p-4 bg-danger/20 border border-danger/50 text-danger rounded-lg">
                        <div class="flex items-center gap-3 mb-2">
                            <i class="fa-solid fa-exclamation-circle"></i>
                            <span class="font-medium">Erreurs de validation :</span>
                        </div>
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }
        
        // Close sidebar when clicking on a link (mobile only)
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('.sidebar-link');
            const isMobile = window.innerWidth < 1024;
            
            if (isMobile) {
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        setTimeout(() => {
                            toggleSidebar();
                        }, 100);
                    });
                });
            }
            
            // Close sidebar on window resize if switching to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    const sidebar = document.getElementById('sidebar');
                    const overlay = document.getElementById('sidebarOverlay');
                    sidebar.classList.remove('open');
                    overlay.classList.remove('open');
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>

