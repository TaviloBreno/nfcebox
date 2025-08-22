<div class="sidebar bg-dark text-white vh-100 position-fixed d-flex flex-column" style="width: 250px; z-index: 1000;">
    <div class="p-3 sidebar-header flex-shrink-0">
        <h5 class="mb-0 sidebar-text">{{ config('app.name', 'Laravel') }}</h5>
    </div>
    
    <div class="sidebar-nav-container flex-grow-1" style="overflow-y: auto; padding-bottom: 80px;">
        <nav class="nav flex-column">
        <a class="nav-link text-white {{ request()->routeIs('home') ? 'active bg-primary rounded' : '' }}" href="{{ route('home') }}">
            <i class="fas fa-home me-2"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>
        
        <hr class="text-white-50">
        
        <h6 class="text-white-50 px-3 mb-2 mt-3 sidebar-section-title">VENDAS</h6>
        
        <a class="nav-link text-white {{ request()->routeIs('pos.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('pos.index') }}">
            <i class="fas fa-cash-register me-2"></i>
            <span class="sidebar-text">PDV</span>
        </a>
        
        <a class="nav-link text-white {{ request()->routeIs('sales.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('sales.index') }}">
            <i class="fas fa-receipt me-2"></i>
            <span class="sidebar-text">Vendas</span>
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
        
        <a class="nav-link text-white {{ request()->routeIs('profile.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('profile.show') }}">
            <i class="fas fa-user me-2"></i>
            <span class="sidebar-text">Perfil</span>
        </a>
        
        @if(Auth::user()->is_admin)
            <a class="nav-link text-white {{ request()->routeIs('configurations.index') || request()->routeIs('configurations.edit') ? 'active bg-primary rounded' : '' }}" href="{{ route('configurations.index') }}">
                <i class="fas fa-cog me-2"></i>
                <span class="sidebar-text">Configurações</span>
            </a>
            
            <a class="nav-link text-white {{ request()->routeIs('configurations.users*') ? 'active bg-primary rounded' : '' }}" href="{{ route('configurations.users') }}">
                <i class="fas fa-users-cog me-2"></i>
                <span class="sidebar-text">Usuários</span>
            </a>
            
            <a class="nav-link text-white {{ request()->routeIs('configurations.certificates*') ? 'active bg-primary rounded' : '' }}" href="{{ route('configurations.certificates') }}">
                <i class="fas fa-certificate me-2"></i>
                <span class="sidebar-text">Certificados A1</span>
            </a>
        @endif
        </nav>
    </div>
    
    <div class="position-absolute bottom-0 w-100 p-3 bg-dark flex-shrink-0">
        <!-- Seção Administrador -->
        <div class="mb-3">
            <hr class="text-white-50">
        </div>
        
        <div class="dropdown">
            <a class="nav-link text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="sidebarUserDropdown">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="Avatar" class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover;">
                @else
                    <i class="fas fa-user-circle me-2"></i>
                @endif
                <span class="sidebar-text">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sidebarUserDropdown">
                <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                    <i class="fas fa-user me-2"></i>
                    Meu Perfil
                </a></li>
                @if(Auth::user()->is_admin)
                    <li><a class="dropdown-item" href="{{ route('configurations.index') }}">
                        <i class="fas fa-cog me-2"></i>
                        Configurações
                    </a></li>
                @endif
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

/* Estilização da barra de rolagem */
.sidebar-nav-container::-webkit-scrollbar {
    width: 6px;
}

.sidebar-nav-container::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

.sidebar-nav-container::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 3px;
}

.sidebar-nav-container::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Para Firefox */
.sidebar-nav-container {
    scrollbar-width: thin;
    scrollbar-color: rgba(255, 255, 255, 0.3) rgba(255, 255, 255, 0.1);
}
</style>