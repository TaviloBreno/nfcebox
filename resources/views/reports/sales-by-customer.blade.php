@extends('layouts.app')

@section('title', 'Relatório de Vendas por Cliente')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtros
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.sales-by-customer') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="date_from" class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ $request->date_from ?? now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="date_to" class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ $request->date_to ?? now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="customer_id" class="form-label">Cliente Específico</label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">Todos os clientes</option>
                                @if(isset($customers))
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                                {{ $request->customer_id == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} - {{ $customer->document }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                Filtrar
                            </button>
                            <a href="{{ route('reports.sales-by-customer') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($salesByCustomer))
            <!-- Resumo -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $summary['total_customers'] }}</h4>
                                    <p class="mb-0">Clientes Ativos</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $summary['total_sales'] }}</h4>
                                    <p class="mb-0">Total de Vendas</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shopping-cart fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">R$ {{ number_format($summary['total_amount'], 2, ',', '.') }}</h4>
                                    <p class="mb-0">Valor Total</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">R$ {{ number_format($summary['average_ticket'], 2, ',', '.') }}</h4>
                                    <p class="mb-0">Ticket Médio</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Clientes -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table me-2"></i>
                        Vendas por Cliente
                    </h5>
                    <div>
                        <button class="btn btn-success btn-sm me-2" onclick="exportToCsv()">
                            <i class="fas fa-file-csv me-1"></i>
                            CSV
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="exportToPdf()">
                            <i class="fas fa-file-pdf me-1"></i>
                            PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($salesByCustomer->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="customerTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Qtd. Vendas</th>
                                    <th>Valor Total</th>
                                    <th>Ticket Médio</th>
                                    <th>Última Compra</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesByCustomer as $customer)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $customer->name }}</strong>
                                                @if($customer->total_sales >= 10)
                                                    <span class="badge bg-warning ms-2">VIP</span>
                                                @elseif($customer->total_sales >= 5)
                                                    <span class="badge bg-info ms-2">Frequente</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $customer->document }}</code>
                                    </td>
                                    <td>
                                        @if($customer->email)
                                            <a href="mailto:{{ $customer->email }}" class="text-decoration-none">
                                                {{ $customer->email }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($customer->phone)
                                            <a href="tel:{{ $customer->phone }}" class="text-decoration-none">
                                                {{ $customer->phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6">{{ $customer->total_sales }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            R$ {{ number_format($customer->total_amount, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        R$ {{ number_format($customer->average_ticket, 2, ',', '.') }}
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($customer->last_sale_date)->format('d/m/Y H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('customers.show', $customer->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Ver Cliente">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    onclick="viewCustomerSales({{ $customer->id }})" 
                                                    title="Ver Vendas">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    @if(method_exists($salesByCustomer, 'links'))
                        <div class="d-flex justify-content-center mt-4">
                            {{ $salesByCustomer->appends(request()->query())->links() }}
                        </div>
                    @endif
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum cliente encontrado</h5>
                        <p class="text-muted">Não há vendas para clientes no período selecionado.</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para visualizar vendas do cliente -->
<div class="modal fade" id="customerSalesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Vendas do Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="customerSalesContent">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function exportToCsv() {
    // Implementar exportação CSV
    alert('Funcionalidade de exportação CSV será implementada em breve.');
}

function exportToPdf() {
    // Implementar exportação PDF
    alert('Funcionalidade de exportação PDF será implementada em breve.');
}

function viewCustomerSales(customerId) {
    // Implementar visualização de vendas do cliente
    const modal = new bootstrap.Modal(document.getElementById('customerSalesModal'));
    document.getElementById('customerSalesContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>';
    modal.show();
    
    // Aqui seria feita uma requisição AJAX para buscar as vendas do cliente
    setTimeout(() => {
        document.getElementById('customerSalesContent').innerHTML = '<p>Funcionalidade será implementada em breve.</p>';
    }, 1000);
}

// Inicializar DataTable se disponível
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#customerTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
            },
            "pageLength": 25,
            "order": [[ 5, "desc" ]], // Ordenar por valor total decrescente
            "columnDefs": [
                { "orderable": false, "targets": 8 } // Coluna de ações não ordenável
            ]
        });
    }
});
</script>
@endpush

@push('styles')
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

.table th {
    border-top: none;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush