@extends('layouts.app')

@section('title', 'Relatório de Vendas por Período')

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
                    <form method="GET" action="{{ route('reports.sales-by-period') }}" class="row g-3">
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
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos (exceto canceladas)</option>
                                <option value="draft" {{ $request->status == 'draft' ? 'selected' : '' }}>Rascunho</option>
                                <option value="authorized_pending" {{ $request->status == 'authorized_pending' ? 'selected' : '' }}>Pendente Autorização</option>
                                <option value="authorized" {{ $request->status == 'authorized' ? 'selected' : '' }}>Autorizada</option>
                                <option value="canceled" {{ $request->status == 'canceled' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                Filtrar
                            </button>
                            <a href="{{ route('reports.sales-by-period') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($sales))
            <!-- Resumo -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
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
                    <div class="card bg-success text-white">
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
                    <div class="card bg-info text-white">
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
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="mb-0">{{ $summary['period_from'] }} a {{ $summary['period_to'] }}</h6>
                                    <p class="mb-0">Período</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabela de Vendas -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Vendas Detalhadas
                    </h5>
                    <div>
                        <a href="{{ route('reports.sales-by-period.export-csv', request()->query()) }}" class="btn btn-success btn-sm me-2">
                            <i class="fas fa-file-csv me-1"></i>
                            CSV
                        </a>
                        <a href="{{ route('reports.sales-by-period.export-pdf', request()->query()) }}" class="btn btn-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>
                            PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($sales->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="salesTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Número</th>
                                    <th>Data/Hora</th>
                                    <th>Cliente</th>
                                    <th>Forma Pagamento</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sales as $sale)
                                <tr>
                                    <td><strong>{{ $sale->number }}</strong></td>
                                    <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $sale->customer->name ?? 'Cliente não identificado' }}</td>
                                    <td>
                                        @switch($sale->payment_method)
                                            @case('dinheiro')
                                                <span class="badge bg-success">Dinheiro</span>
                                                @break
                                            @case('cartao_credito')
                                                <span class="badge bg-primary">Cartão Crédito</span>
                                                @break
                                            @case('cartao_debito')
                                                <span class="badge bg-info">Cartão Débito</span>
                                                @break
                                            @case('pix')
                                                <span class="badge bg-warning">PIX</span>
                                                @break
                                            @case('boleto')
                                                <span class="badge bg-secondary">Boleto</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $sale->payment_method }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @switch($sale->status)
                                            @case('draft')
                                                <span class="badge bg-secondary">Rascunho</span>
                                                @break
                                            @case('authorized_pending')
                                                <span class="badge bg-warning">Pendente</span>
                                                @break
                                            @case('authorized')
                                                <span class="badge bg-success">Autorizada</span>
                                                @break
                                            @case('canceled')
                                                <span class="badge bg-danger">Cancelada</span>
                                                @break
                                            @default
                                                <span class="badge bg-light text-dark">{{ $sale->status }}</span>
                                        @endswitch
                                    </td>
                                    <td><strong>R$ {{ number_format($sale->total, 2, ',', '.') }}</strong></td>
                                    <td>
                                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-outline-primary" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma venda encontrada</h5>
                        <p class="text-muted">Não há vendas no período selecionado com os filtros aplicados.</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
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
</script>
@endpush