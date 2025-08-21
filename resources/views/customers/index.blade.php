@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Clientes</h1>
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Novo Cliente
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title mb-0">Lista de Clientes</h5>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('customers.index') }}" class="d-flex">
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="Buscar por nome, CPF/CNPJ ou email..." 
                                       value="{{ $search }}">
                                <button type="submit" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if($search)
                                    <a href="{{ route('customers.index') }}" class="btn btn-outline-danger ms-1">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($customers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nome</th>
                                        <th>CPF/CNPJ</th>
                                        <th>Email</th>
                                        <th>Telefone</th>
                                        <th>Cidade</th>
                                        <th width="150">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customers as $customer)
                                        <tr>
                                            <td>
                                                <strong>{{ $customer->name }}</strong>
                                            </td>
                                            <td>
                                <span class="font-monospace">{{ $customer->formatted_document }}</span>
                            </td>
                                            <td>{{ $customer->email }}</td>
                                            <td>
                                @if($customer->phone)
                                    <span class="font-monospace">{{ $customer->formatted_phone }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                                            <td>
                                @if($customer->address)
                                    {{ $customer->address['city'] ?? '' }}, {{ $customer->address['state'] ?? '' }}
                                @else
                                    -
                                @endif
                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('customers.show', $customer) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="Visualizar">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('customers.edit', $customer) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            title="Excluir"
                                                            onclick="confirmDelete({{ $customer->id }}, '{{ $customer->name }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Mostrando {{ $customers->firstItem() }} a {{ $customers->lastItem() }} 
                                    de {{ $customers->total() }} registros
                                </div>
                                {{ $customers->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhum cliente encontrado</h5>
                            @if($search)
                                <p class="text-muted">Tente ajustar os termos de busca</p>
                                <a href="{{ route('customers.index') }}" class="btn btn-outline-primary">
                                    Ver todos os clientes
                                </a>
                            @else
                                <p class="text-muted">Comece criando seu primeiro cliente</p>
                                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Criar Cliente
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmação de exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o cliente <strong id="customerName"></strong>?</p>
                <p class="text-muted">Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete(customerId, customerName) {
    document.getElementById('customerName').textContent = customerName;
    document.getElementById('deleteForm').action = `/customers/${customerId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>
@endpush
@endsection