@extends('layouts.app')

@section('title', 'Relatório de Vendas por Forma de Pagamento')

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
                    <form method="GET" action="{{ route('reports.sales-by-payment') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" 
                                   value="{{ $request->date_from ?? now()->startOfMonth()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" 
                                   value="{{ $request->date_to ?? now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>
                                Filtrar
                            </button>
                            <a href="{{ route('reports.sales-by-payment') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($salesByPayment))
            <!-- Resumo -->
            <div class="row mb-4">
                <div class="col-md-4">
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
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <div class="card bg-info text-white">
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

            <!-- Gráfico e Tabela -->
            <div class="row">
                <!-- Gráfico -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                Distribuição por Forma de Pagamento
                            </h5>
                        </div>
                        <div class="card-body">
                            <canvas id="paymentChart" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Tabela -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-table me-2"></i>
                                Detalhamento
                            </h5>
                            <div>
                                <a href="{{ route('reports.sales-by-payment.export-csv', request()->query()) }}" class="btn btn-success btn-sm me-2">
                                    <i class="fas fa-file-csv me-1"></i>
                                    CSV
                                </a>
                                <button class="btn btn-danger btn-sm" onclick="exportToPdf()">
                                    <i class="fas fa-file-pdf me-1"></i>
                                    PDF
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($salesByPayment->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="paymentTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Forma de Pagamento</th>
                                            <th>Qtd. Vendas</th>
                                            <th>Valor Total</th>
                                            <th>Ticket Médio</th>
                                            <th>%</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($salesByPayment as $payment)
                                        <tr>
                                            <td>
                                                @switch($payment->payment_method)
                                                    @case('dinheiro')
                                                        <span class="badge bg-success me-2">Dinheiro</span>
                                                        @break
                                                    @case('cartao_credito')
                                                        <span class="badge bg-primary me-2">Cartão Crédito</span>
                                                        @break
                                                    @case('cartao_debito')
                                                        <span class="badge bg-info me-2">Cartão Débito</span>
                                                        @break
                                                    @case('pix')
                                                        <span class="badge bg-warning me-2">PIX</span>
                                                        @break
                                                    @case('boleto')
                                                        <span class="badge bg-secondary me-2">Boleto</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-light text-dark me-2">{{ $payment->payment_method }}</span>
                                                @endswitch
                                            </td>
                                            <td><strong>{{ $payment->total_sales }}</strong></td>
                                            <td><strong>R$ {{ number_format($payment->total_amount, 2, ',', '.') }}</strong></td>
                                            <td>R$ {{ number_format($payment->average_ticket, 2, ',', '.') }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ number_format(($payment->total_amount / $summary['total_amount']) * 100, 1) }}%
                                                </span>
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
                                <p class="text-muted">Não há vendas no período selecionado.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(isset($salesByPayment) && $salesByPayment->count() > 0)
// Dados para o gráfico
const paymentData = {
    labels: [
        @foreach($salesByPayment as $payment)
            @switch($payment->payment_method)
                @case('dinheiro')
                    'Dinheiro',
                    @break
                @case('cartao_credito')
                    'Cartão Crédito',
                    @break
                @case('cartao_debito')
                    'Cartão Débito',
                    @break
                @case('pix')
                    'PIX',
                    @break
                @case('boleto')
                    'Boleto',
                    @break
                @default
                    '{{ $payment->payment_method }}',
            @endswitch
        @endforeach
    ],
    datasets: [{
        data: [
            @foreach($salesByPayment as $payment)
                {{ $payment->total_amount }},
            @endforeach
        ],
        backgroundColor: [
            '#28a745', // Dinheiro - Verde
            '#007bff', // Cartão Crédito - Azul
            '#17a2b8', // Cartão Débito - Azul claro
            '#ffc107', // PIX - Amarelo
            '#6c757d', // Boleto - Cinza
            '#dc3545', // Outros - Vermelho
        ],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

// Configuração do gráfico
const config = {
    type: 'pie',
    data: paymentData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = 'R$ ' + context.parsed.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        const percentage = ((context.parsed / {{ $summary['total_amount'] }}) * 100).toFixed(1) + '%';
                        return label + ': ' + value + ' (' + percentage + ')';
                    }
                }
            }
        }
    }
};

// Criar o gráfico
const ctx = document.getElementById('paymentChart').getContext('2d');
const paymentChart = new Chart(ctx, config);
@endif

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

@push('styles')
<style>
#paymentChart {
    max-height: 400px;
}
</style>
@endpush