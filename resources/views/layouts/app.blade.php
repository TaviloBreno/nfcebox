<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @auth
        <!-- Sidebar -->
        @include('components.sidebar')
        
        <!-- Top Navbar for authenticated users -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom main-content" style="margin-left: 250px;" id="mainNavbar">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link" type="button" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h4 class="mb-0 ms-3">@yield('page-title', 'Dashboard')</h4>
                </div>
                
                <div class="d-flex align-items-center">
                    @if(!Auth::user()->hasVerifiedEmail())
                        <span class="badge bg-warning text-dark me-3">
                            <i class="fas fa-exclamation-triangle"></i> E-mail não verificado
                        </span>
                    @endif
                    
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="navbarUserDropdown">
                            <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                            <li><h6 class="dropdown-header">{{ Auth::user()->email }}</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-edit"></i> Perfil</a></li>
                            @if(!Auth::user()->hasVerifiedEmail())
                                <li><a class="dropdown-item" href="{{ route('verification.notice') }}"><i class="fas fa-envelope-open"></i> Verificar E-mail</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content with sidebar offset -->
        <main class="py-4 main-content" style="margin-left: 250px;">
            <div class="container-fluid">
                @yield('content')
            </div>
        </main>
    @else
        <!-- Navbar for guests -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Sobre</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Contato</a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('register') ? 'active' : '' }}" href="{{ route('register') }}">Registrar</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Main Content for guests -->
        <main class="py-4">
            <div class="container">
                @yield('content')
            </div>
        </main>
    @endauth

    <!-- Footer -->
    <footer class="bg-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos os direitos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Desenvolvido com <span class="text-danger">&hearts;</span> usando Laravel e Bootstrap</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- CSS for dropdowns and responsive design -->
    <style>
        .dropdown-menu {
            z-index: 1050;
            max-width: 250px;
            white-space: nowrap;
        }
        
        /* Fix navbar dropdown positioning to prevent horizontal scroll */
        .navbar .dropdown-menu {
            right: 0;
            left: auto;
            transform: none;
        }
        
        /* Ensure dropdown items don't overflow */
        .dropdown-item {
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* Sidebar Toggle Styles */
        .sidebar {
            transition: width 0.3s ease;
        }
        
        .sidebar.collapsed {
            width: 70px !important;
        }
        
        .sidebar.collapsed .sidebar-text {
            display: none;
        }
        
        .sidebar.collapsed .sidebar-header {
            display: none;
        }
        
        .sidebar.collapsed .sidebar-section-title {
            display: none;
        }
        
        .sidebar.collapsed .nav-link {
            text-align: center;
            padding: 0.75rem 0.5rem;
        }
        
        .sidebar.collapsed .nav-link i {
            margin-right: 0 !important;
        }
        
        .sidebar.collapsed .dropdown {
            display: none;
        }
        
        .sidebar.collapsed hr {
            margin: 0.5rem 0;
        }
        
        /* Adjust main content when sidebar is collapsed */
        .main-content {
            transition: margin-left 0.3s ease;
        }
        
        .main-content.sidebar-collapsed {
            margin-left: 70px !important;
        }
        
        /* Responsive Design */
         @media (max-width: 768px) {
             /* Hide sidebar on mobile */
             .sidebar {
                 transform: translateX(-100%);
                 transition: transform 0.3s ease-in-out;
                 z-index: 1050;
             }
             
             .sidebar.show {
                 transform: translateX(0);
                 box-shadow: 0 0 20px rgba(0,0,0,0.5);
             }
             
             /* Overlay for mobile sidebar */
             .sidebar.show::after {
                 content: '';
                 position: fixed;
                 top: 0;
                 left: 250px;
                 right: 0;
                 bottom: 0;
                 background: rgba(0,0,0,0.5);
                 z-index: -1;
             }
             
             /* Adjust navbar for mobile */
             .navbar {
                 margin-left: 0 !important;
                 padding-left: 1rem;
                 padding-right: 1rem;
             }
             
             /* Adjust main content for mobile */
             main {
                 margin-left: 0 !important;
                 padding: 1rem;
             }
             
             /* Show mobile toggle button */
             #sidebarToggle {
                 display: block !important;
             }
             
             /* Adjust dropdown positioning on mobile */
             .navbar .dropdown-menu {
                 position: absolute;
                 right: 1rem;
                 left: auto;
                 min-width: 200px;
             }
         }
        
        @media (min-width: 769px) {
            /* Show toggle button on desktop */
            #sidebarToggle {
                display: block !important;
            }
        }
        
        /* Tablet adjustments */
        @media (max-width: 1024px) and (min-width: 769px) {
            .sidebar {
                width: 200px;
            }
            
            .navbar, main {
                margin-left: 200px;
            }
        }
    </style>
    
    <!-- Working dropdown implementation -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for Bootstrap to fully load
            setTimeout(function() {
                // Get all dropdown toggles
                const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
                
                dropdownToggles.forEach(function(toggle) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Close all other dropdowns first
                        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
                            if (menu !== toggle.nextElementSibling) {
                                menu.classList.remove('show');
                            }
                        });
                        
                        // Toggle current dropdown
                        const menu = toggle.nextElementSibling;
                        if (menu && menu.classList.contains('dropdown-menu')) {
                            menu.classList.toggle('show');
                        }
                    });
                });
                
                // Close dropdowns when clicking outside
                 document.addEventListener('click', function(e) {
                     if (!e.target.closest('.dropdown')) {
                         document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                             menu.classList.remove('show');
                         });
                     }
                 });
                 
             }, 200);
             
             // Sidebar toggle functionality
             const sidebarToggle = document.getElementById('sidebarToggle');
             const sidebar = document.querySelector('.sidebar');
             const mainContent = document.querySelectorAll('.main-content');
             
             if (sidebarToggle && sidebar) {
                 sidebarToggle.addEventListener('click', function(e) {
                     e.preventDefault();
                     
                     // Toggle sidebar collapsed state
                     sidebar.classList.toggle('collapsed');
                     
                     // Toggle main content margin
                     mainContent.forEach(function(element) {
                         element.classList.toggle('sidebar-collapsed');
                     });
                     
                     // Store state in localStorage
                     const isCollapsed = sidebar.classList.contains('collapsed');
                     localStorage.setItem('sidebarCollapsed', isCollapsed);
                 });
                 
                 // Restore sidebar state from localStorage
                 const savedState = localStorage.getItem('sidebarCollapsed');
                 if (savedState === 'true') {
                     sidebar.classList.add('collapsed');
                     mainContent.forEach(function(element) {
                         element.classList.add('sidebar-collapsed');
                     });
                 }
                 
                 // Mobile specific behavior
                 if (window.innerWidth <= 768) {
                     // Close sidebar when clicking outside on mobile
                     document.addEventListener('click', function(e) {
                         if (!e.target.closest('.sidebar') && 
                             !e.target.closest('#sidebarToggle') &&
                             sidebar.classList.contains('show')) {
                             sidebar.classList.remove('show');
                         }
                     });
                 }
             }
         });
    </script>
</body>
</html>