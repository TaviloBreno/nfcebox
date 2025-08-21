<div class="sidebar bg-dark text-white vh-100 position-fixed" style="width: 250px; z-index: 1000;">
    <div class="p-3 sidebar-header">
        <h5 class="mb-0 sidebar-text">{{ config('app.name', 'Laravel') }}</h5>
    </div>
    
    <nav class="nav flex-column px-3">
        <a class="nav-link text-white {{ request()->routeIs('home') ? 'active bg-primary rounded' : '' }}" href="{{ route('home') }}">
            <i class="fas fa-home me-2"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>
        
        <hr class="text-white-50">
        
        <h6 class="text-white-50 px-3 mb-2 mt-3 sidebar-section-title">CADASTROS</h6>
        
        <a class="nav-link text-white {{ request()->routeIs('customers.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('customers.index') }}">
            <i class="fas fa-users me-2"></i>
            <span class="sidebar-text">Clientes</span>
        </a>
        
        <a class="nav-link text-white {{ request()->routeIs('products.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('products.index') }}">
            <i class="fas fa-box me-2"></i>
            <span class="sidebar-text">Produtos</span>
        </a>
        
        <hr class="text-white-50">
        
        <h6 class="text-white-50 px-3 mb-2 mt-3 sidebar-section-title">SISTEMA</h6>
        
        <a class="nav-link text-white {{ request()->routeIs('profile.*') ? 'active bg-primary rounded' : '' }}" href="#">
            <i class="fas fa-user me-2"></i>
            <span class="sidebar-text">Perfil</span>
        </a>
        
        <a class="nav-link text-white {{ request()->routeIs('settings.*') ? 'active bg-primary rounded' : '' }}" href="#">
            <i class="fas fa-cog me-2"></i>
            <span class="sidebar-text">Configurações</span>
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
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 5px;
}

.nav-link.active {
    background-color: #0d6efd;
    border-radius: 5px;
}
</style>