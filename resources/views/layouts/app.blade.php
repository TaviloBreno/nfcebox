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
<body class="d-flex flex-column min-vh-100">
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
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="navbarUserDropdown">
                            @if(Auth::user()->avatar)
                                <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                            @else
                                <i class="fas fa-user-circle me-2"></i>
                            @endif
                            <span>{{ Auth::user()->name }}</span>
                            @if(Auth::user()->is_admin)
                                <span class="badge bg-primary ms-2">Administrador</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                            <li><h6 class="dropdown-header">{{ Auth::user()->email }}</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                <i class="fas fa-user me-2"></i>Meu Perfil
                            </a></li>
                            @if(Auth::user()->is_admin)
                                <li><a class="dropdown-item" href="{{ route('configurations.index') }}">
                                    <i class="fas fa-cog me-2"></i>Configurações
                                </a></li>
                            @endif
                            @if(!Auth::user()->hasVerifiedEmail())
                                <li><a class="dropdown-item" href="{{ route('verification.notice') }}">
                                    <i class="fas fa-envelope-open me-2"></i>Verificar E-mail
                                </a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content with sidebar offset -->
        <main class="py-4 main-content flex-grow-1" style="margin-left: 250px;">
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
        <main class="py-4 flex-grow-1">
            <div class="container">
                @yield('content')
            </div>
        </main>
    @endauth

    <!-- Footer -->
    <footer class="bg-light py-4 mt-auto @auth main-content @endauth" @auth style="margin-left: 250px;" @endauth>
        <div class="container @auth container-fluid @endauth">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos os direitos reservados.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Customizado pela <strong>B-Web</strong> com <span class="text-danger">&hearts;</span> usando Laravel e Bootstrap</p>
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
        /* Mobile devices (up to 768px) */
        @media (max-width: 768px) {
            /* Hide sidebar on mobile */
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
                z-index: 1050;
                width: 280px !important;
            }
            
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 0 0 20px rgba(0,0,0,0.5);
            }
            
            /* Mobile overlay */
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.5);
                z-index: 1040;
                display: none;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
            
            /* Adjust navbar for mobile */
            .navbar {
                margin-left: 0 !important;
                padding: 0.5rem 1rem;
                position: sticky;
                top: 0;
                z-index: 1030;
            }
            
            /* Adjust main content for mobile */
            main {
                margin-left: 0 !important;
                padding: 0.5rem;
            }
            
            /* Container adjustments */
            .container-fluid {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            
            /* Card adjustments */
            .card {
                margin-bottom: 1rem;
                border-radius: 0.5rem;
            }
            
            /* Table responsiveness */
            .table-responsive {
                font-size: 0.875rem;
            }
            
            /* Button adjustments */
            .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
            
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            
            /* Form adjustments */
            .form-control, .form-select {
                font-size: 1rem;
                padding: 0.5rem 0.75rem;
            }
            
            /* Dropdown positioning */
            .navbar .dropdown-menu {
                position: absolute;
                right: 1rem;
                left: auto;
                min-width: 200px;
                max-width: calc(100vw - 2rem);
            }
            
            /* Hide some elements on very small screens */
            .d-none-mobile {
                display: none !important;
            }
        }
        
        /* Tablet devices (769px to 1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar {
                width: 220px !important;
            }
            
            .navbar, main {
                margin-left: 220px !important;
            }
            
            .main-content.sidebar-collapsed {
                margin-left: 70px !important;
            }
            
            /* Adjust container for tablets */
            .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            /* Table adjustments for tablets */
            .table {
                font-size: 0.9rem;
            }
            
            /* Card adjustments */
            .card-body {
                padding: 1rem;
            }
            
            /* Button group adjustments */
            .btn-group .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
        }
        
        /* Desktop and larger screens */
        @media (min-width: 1025px) {
            .sidebar {
                width: 250px !important;
            }
            
            .navbar, main {
                margin-left: 250px !important;
            }
            
            .main-content.sidebar-collapsed {
                margin-left: 70px !important;
            }
        }
        
        /* Large desktop screens */
        @media (min-width: 1400px) {
            .container-fluid {
                max-width: 1320px;
                margin: 0 auto;
            }
        }
        
        /* Common responsive utilities */
        @media (max-width: 576px) {
            .col-form-label {
                margin-bottom: 0.25rem;
            }
            
            .row > * {
                margin-bottom: 0.75rem;
            }
            
            h1, .h1 { font-size: 1.75rem; }
            h2, .h2 { font-size: 1.5rem; }
            h3, .h3 { font-size: 1.25rem; }
            h4, .h4 { font-size: 1.1rem; }
        }
    </style>
    
    <!-- Sidebar toggle functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
             
             // Sidebar toggle functionality
             const sidebarToggle = document.getElementById('sidebarToggle');
             const sidebar = document.querySelector('.sidebar');
             const mainContent = document.querySelectorAll('.main-content');
             
             if (sidebarToggle && sidebar) {
                 // Create overlay for mobile
                 let overlay = document.querySelector('.sidebar-overlay');
                 if (!overlay) {
                     overlay = document.createElement('div');
                     overlay.className = 'sidebar-overlay';
                     document.body.appendChild(overlay);
                 }
                 
                 // Function to handle sidebar toggle based on screen size
                 function handleSidebarToggle(e) {
                     e.preventDefault();
                     
                     if (window.innerWidth <= 768) {
                         // Mobile behavior
                         sidebar.classList.toggle('show');
                     } else {
                         // Desktop behavior
                         sidebar.classList.toggle('collapsed');
                         
                         // Toggle main content margin
                         mainContent.forEach(function(element) {
                             element.classList.toggle('sidebar-collapsed');
                         });
                         
                         // Store state in localStorage
                         const isCollapsed = sidebar.classList.contains('collapsed');
                         localStorage.setItem('sidebarCollapsed', isCollapsed);
                     }
                 }
                 
                 // Add click event listener
                 sidebarToggle.addEventListener('click', handleSidebarToggle);
                 
                 // Show/hide overlay with sidebar
                 const observer = new MutationObserver(function(mutations) {
                     mutations.forEach(function(mutation) {
                         if (mutation.attributeName === 'class') {
                             if (sidebar.classList.contains('show')) {
                                 overlay.classList.add('show');
                             } else {
                                 overlay.classList.remove('show');
                             }
                         }
                     });
                 });
                 observer.observe(sidebar, { attributes: true });
                 
                 // Close sidebar when clicking overlay
                 overlay.addEventListener('click', function() {
                     sidebar.classList.remove('show');
                 });
                 
                 // Restore sidebar state from localStorage (only for desktop)
                 if (window.innerWidth > 768) {
                     const savedState = localStorage.getItem('sidebarCollapsed');
                     if (savedState === 'true') {
                         sidebar.classList.add('collapsed');
                         mainContent.forEach(function(element) {
                             element.classList.add('sidebar-collapsed');
                         });
                     }
                 }
                 
                 // Handle window resize
                 window.addEventListener('resize', function() {
                     if (window.innerWidth > 768) {
                         sidebar.classList.remove('show');
                         overlay.classList.remove('show');
                         
                         // Restore collapsed state for desktop
                         const savedState = localStorage.getItem('sidebarCollapsed');
                         if (savedState === 'true') {
                             sidebar.classList.add('collapsed');
                             mainContent.forEach(function(element) {
                                 element.classList.add('sidebar-collapsed');
                             });
                         }
                     } else {
                         // Remove desktop classes on mobile
                         sidebar.classList.remove('collapsed');
                         mainContent.forEach(function(element) {
                             element.classList.remove('sidebar-collapsed');
                         });
                     }
                 });
             }
         });
    </script>
</body>
</html>