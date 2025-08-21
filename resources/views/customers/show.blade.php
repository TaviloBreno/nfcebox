@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Detalhes do Cliente</h1>
                <div>
                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Dados Pessoais -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user"></i> Dados Pessoais
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Nome:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $customer->name }}
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>CPF/CNPJ:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="font-monospace">{{ $customer->formatted_document }}</span>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Email:</strong>
                                </div>
                                <div class="col-sm-8">
                                    <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong>Telefone:</strong>
                                </div>
                                <div class="col-sm-8">
                                    @if($customer->phone)
                                        <a href="tel:{{ $customer->phone }}" class="font-monospace">
                                            {{ $customer->formatted_phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">Não informado</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-sm-4">
                                    <strong>Cadastrado em:</strong>
                                </div>
                                <div class="col-sm-8">
                                    {{ $customer->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Endereço -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-map-marker-alt"></i> Endereço
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($customer->address)
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>CEP:</strong> {{ $customer->address['cep'] ?? '-' }}</p>
                                        <p><strong>Logradouro:</strong> {{ $customer->address['street'] ?? '-' }}</p>
                                        <p><strong>Número:</strong> {{ $customer->address['number'] ?? '-' }}</p>
                                        <p><strong>Complemento:</strong> {{ $customer->address['complement'] ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Bairro:</strong> {{ $customer->address['neighborhood'] ?? '-' }}</p>
                                        <p><strong>Cidade:</strong> {{ $customer->address['city'] ?? '-' }}</p>
                                        <p><strong>Estado:</strong> {{ $customer->address['state'] ?? '-' }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">Nenhum endereço cadastrado.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Histórico de Vendas (placeholder para futuro) -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-shopping-cart"></i> Histórico de Compras
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Nenhuma compra realizada ainda</p>
                                <small class="text-muted">As vendas aparecerão aqui quando o módulo de vendas for implementado</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs"></i> Ações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Editar Cliente
                                </a>
                                <button type="button" 
                                        class="btn btn-danger" 
                                        onclick="confirmDelete({{ $customer->id }}, '{{ $customer->name }}')">
                                    <i class="fas fa-trash"></i> Excluir Cliente
                                </button>
                            </div>
                        </div>
                    </div>
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