<div class="bg-dark text-white vh-100 position-fixed" style="width: 250px; z-index: 1000;">
    <div class="p-3">
        <h5 class="mb-0">{{ config('app.name', 'Laravel') }}</h5>
    </div>
    
    <nav class="nav flex-column px-3">
        <a class="nav-link text-white {{ request()->routeIs('home') ? 'active bg-primary rounded' : '' }}" href="{{ route('home') }}">
            <i class="fas fa-home me-2"></i>
            Dashboard
        </a>
        
        <a class="nav-link text-white {{ request()->routeIs('profile.*') ? 'active bg-primary rounded' : '' }}" href="#">
            <i class="fas fa-user me-2"></i>
            Perfil
        </a>
        
        <a class="nav-link text-white {{ request()->routeIs('settings.*') ? 'active bg-primary rounded' : '' }}" href="#">
            <i class="fas fa-cog me-2"></i>
            Configurações
        </a>
        
        <hr class="text-white-50">
        
        <a class="nav-link text-white {{ request()->routeIs('users.*') ? 'active bg-primary rounded' : '' }}" href="#">
            <i class="fas fa-users me-2"></i>
            Usuários
        </a>
        
        <a class="nav-link text-white {{ request()->routeIs('reports.*') ? 'active bg-primary rounded' : '' }}" href="#">
            <i class="fas fa-chart-bar me-2"></i>
            Relatórios
        </a>
    </nav>
    
    <div class="position-absolute bottom-0 w-100 p-3">
        <div class="dropdown">
            <a class="nav-link text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="sidebarUserDropdown">
                <i class="fas fa-user-circle me-2"></i>
                {{ Auth::user()->name }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sidebarUserDropdown">
                <li><a class="dropdown-item" href="#">
                    <i class="fas fa-user me-2"></i>
                    Meu Perfil
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>
                            Sair
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
.nav-link {
    transition: all 0.3s ease;
    margin-bottom: 5px;
}

.nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    border-radius: 0.375rem;
}

.nav-link.active {
    font-weight: 500;
}

/* Fix dropdown positioning in sidebar */
.position-absolute.bottom-0 .dropdown-menu {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    margin-bottom: 0.5rem;
    transform: none;
}

.position-absolute.bottom-0 .dropdown-menu.show {
    display: block;
}
</style>