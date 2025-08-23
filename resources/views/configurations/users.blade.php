@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Gerenciar Usuários</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('configurations.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Novo Usuário
                    </a>
                    <a href="{{ route('configurations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar às Configurações
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Lista de Usuários</h5>
                </div>
                <div class="card-body">
                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Avatar</th>
                                        <th>Nome</th>
                                        <th>E-mail</th>
                                        <th>Telefone</th>
                                        <th>Tipo</th>
                                        <th>Cadastrado em</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>
                                                @if($user->avatar)
                                                    <img src="{{ asset('storage/' . $user->avatar) }}" 
                                                         alt="Avatar" 
                                                         class="rounded-circle" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                                         style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $user->name }}</strong>
                                                    @if($user->bio)
                                                        <br><small class="text-muted">{{ Str::limit($user->bio, 50) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->phone ?? '-' }}</td>
                                            <td>
                                                @if($user->is_admin)
                                                    <span class="badge bg-danger">Administrador</span>
                                                @else
                                                    <span class="badge bg-primary">Usuário</span>
                                                @endif
                                            </td>
                                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                @if($user->id !== Auth::id())
                                                    <div class="btn-group" role="group">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editUserModal{{ $user->id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-{{ $user->is_admin ? 'warning' : 'success' }}" 
                                                                onclick="toggleAdmin({{ $user->id }}, {{ $user->is_admin ? 'false' : 'true' }})">
                                                            <i class="fas fa-{{ $user->is_admin ? 'user-minus' : 'user-plus' }}"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="badge bg-info">Você</span>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Modal para editar usuário -->
                                        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Editar Usuário: {{ $user->name }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('configurations.users.update', $user->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Informações do Usuário</label>
                                                                <div class="row">
                                                                    <div class="col-sm-4"><strong>Nome:</strong></div>
                                                                    <div class="col-sm-8">{{ $user->name }}</div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-4"><strong>E-mail:</strong></div>
                                                                    <div class="col-sm-8">{{ $user->email }}</div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-sm-4"><strong>Cadastrado:</strong></div>
                                                                    <div class="col-sm-8">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" 
                                                                           type="checkbox" 
                                                                           id="is_admin{{ $user->id }}" 
                                                                           name="is_admin" 
                                                                           {{ $user->is_admin ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="is_admin{{ $user->id }}">
                                                                        <strong>Administrador</strong>
                                                                        <br><small class="text-muted">Usuários administradores têm acesso total ao sistema</small>
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            @if($user->is_admin)
                                                                <div class="alert alert-warning" role="alert">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                                    <strong>Atenção:</strong> Remover privilégios de administrador impedirá o acesso às configurações do sistema.
                                                                </div>
                                                            @else
                                                                <div class="alert alert-info" role="alert">
                                                                    <i class="fas fa-info-circle me-2"></i>
                                                                    <strong>Informação:</strong> Conceder privilégios de administrador dará acesso total ao sistema.
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginação -->
                        @if($users->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $users->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5>Nenhum usuário encontrado</h5>
                            <p class="text-muted">Não há usuários cadastrados no sistema.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Estatísticas -->
            <div class="row mt-4">
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-users fa-2x text-primary mb-2"></i>
                            <h5>{{ $users->total() }}</h5>
                            <small class="text-muted">Total de Usuários</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-user-shield fa-2x text-danger mb-2"></i>
                            <h5>{{ $users->where('is_admin', true)->count() }}</h5>
                            <small class="text-muted">Administradores</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-user fa-2x text-success mb-2"></i>
                            <h5>{{ $users->where('is_admin', false)->count() }}</h5>
                            <small class="text-muted">Usuários Comuns</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-calendar fa-2x text-info mb-2"></i>
                            <h5>{{ $users->where('created_at', '>=', now()->subDays(30))->count() }}</h5>
                            <small class="text-muted">Novos (30 dias)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleAdmin(userId, isAdmin) {
        const action = isAdmin ? 'conceder privilégios de administrador' : 'remover privilégios de administrador';
        
        if (confirm(`Tem certeza que deseja ${action} para este usuário?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/configurations/users/${userId}/permissions`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PUT';
            form.appendChild(methodField);
            
            if (isAdmin) {
                const adminField = document.createElement('input');
                adminField.type = 'hidden';
                adminField.name = 'is_admin';
                adminField.value = '1';
                form.appendChild(adminField);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endpush

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
    }
    
    .btn-group .btn {
        border-radius: 0.375rem;
        margin-right: 0.25rem;
    }
    
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem;
        }
        
        .h3 {
            font-size: 1.5rem;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
        
        .btn-sm {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
    }
</style>
@endpush