@extends('layouts.app')

@section('title', 'Relatório de Produtos Mais Vendidos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Filtros -->
            <x-card title="Filtros" icon="fas fa-filter" class="mb-4">
                <form method="GET" action="{{ route('reports.top-products') }}" class="row g-3">
                    <div class="col-md-3">
                        <x-form-input 
                            type="date" 
                            name="date_from" 
                            label="Data Inicial" 
                            value="{{ $request->date_from ?? now()->startOfMonth()->format('Y-m-d') }}" 
                            required />
                    </div>
                    <div class="col-md-3">
                        <x-form-input 
                            type="date" 
                            name="date_to" 
                            label="Data Final" 
                            value="{{ $request->date_to ?? now()->format('Y-m-d') }}" 
                            required />
                    </div>
                    <div class="col-md-2">
                        <x-form-input 
                            type="select" 
                            name="limit" 
                            label="Top" 
                            value="{{ $request->limit ?? 10 }}">
                            <option value="10" {{ ($request->limit ?? 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ ($request->limit ?? 10) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ ($request->limit ?? 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($request->limit ?? 10) == 100 ? 'selected' : '' }}>100</option>
                        </x-form-input>
                    </div>
                    <div class="col-md-2">
                        <x-form-input 
                            type="select" 
                            name="order_by" 
                            label="Ordenar por" 
                            value="{{ $request->order_by ?? 'quantity' }}">
                            <option value="quantity" {{ ($request->order_by ?? 'quantity') == 'quantity' ? 'selected' : '' }}>Quantidade</option>
                            <option value="revenue" {{ ($request->order_by ?? 'quantity') == 'revenue' ? 'selected' : '' }}>Receita</option>
                        </x-form-input>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <x-button 
                            type="submit" 
                            variant="primary" 
                            icon="fas fa-search" 
                            class="me-2">
                            Filtrar
                        </x-button>
                        <x-button 
                            href="{{ route('reports.top-products') }}" 
                            variant="secondary" 
                            icon="fas fa-times">
                            Limpar
                        </x-button>
                    </div>
                </form>
            </x-card>

            @if(isset($topProducts))
            <!-- Resumo -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <x-stat-card 
                        title="Produtos Vendidos" 
                        value="{{ $summary['total_products'] }}" 
                        icon="fas fa-box" 
                        color="primary" />
                </div>
                <div class="col-md-3">
                    <x-stat-card 
                        title="Qtd. Total Vendida" 
                        value="{{ number_format($summary['total_quantity']) }}" 
                        icon="fas fa-cubes" 
                        color="success" />
                </div>
                <div class="col-md-3">
                    <x-stat-card 
                        title="Receita Total" 
                        value="R$ {{ number_format($summary['total_revenue'], 2, ',', '.') }}" 
                        icon="fas fa-dollar-sign" 
                        color="info" />
                </div>
                <div class="col-md-3">
                    <x-stat-card 
                        title="Período" 
                        value="{{ $summary['period_from'] }} a {{ $summary['period_to'] }}" 
                        icon="fas fa-calendar" 
                        color="warning" />
                </div>
            </div>

            <!-- Gráfico e Tabela -->
            <div class="row">
                <!-- Gráfico -->
                <div class="col-md-6">
                    <x-card 
                        title="Top {{ $request->limit ?? 10 }} Produtos @if(($request->order_by ?? 'quantity') == 'quantity')(por Quantidade)@else(por Receita)@endif" 
                        icon="fas fa-chart-bar">
                        <canvas id="productsChart" width="400" height="400"></canvas>
                    </x-card>
                </div>

                <!-- Top 5 Cards -->
                <div class="col-md-6">
                    <x-card 
                        title="Top 5 Produtos" 
                        icon="fas fa-trophy">
                            @foreach($topProducts->take(5) as $index => $product)
                            <div class="d-flex align-items-center mb-3 p-3 border rounded {{ $index == 0 ? 'bg-warning bg-opacity-10' : ($index == 1 ? 'bg-secondary bg-opacity-10' : ($index == 2 ? 'bg-info bg-opacity-10' : '')) }}">
                                <div class="me-3">
                                    @if($index == 0)
                                        <i class="fas fa-trophy text-warning fa-2x"></i>
                                    @elseif($index == 1)
                                        <i class="fas fa-medal text-secondary fa-2x"></i>
                                    @elseif($index == 2)
                                        <i class="fas fa-award text-info fa-2x"></i>
                                    @else
                                        <span class="badge bg-primary fs-5">{{ $index + 1 }}º</span>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $product->name }}</h6>
                                    <small class="text-muted">{{ $product->code }}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-success me-2">
                                            {{ number_format($product->total_quantity) }} unidades
                                        </span>
                                        <span class="badge bg-info">
                                            R$ {{ number_format($product->total_revenue, 2, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                    </x-card>
                </div>
            </div>

            <!-- Tabela Completa -->
            <x-card 
                title="Produtos Mais Vendidos" 
                icon="fas fa-table" 
                class="mt-4">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-table me-2"></i>
                            Produtos Mais Vendidos
                        </h5>
                        <div>
                            <x-button 
                                href="{{ route('reports.top-products.export-csv', request()->query()) }}" 
                                variant="success" 
                                size="sm" 
                                icon="fas fa-file-csv" 
                                class="me-2">
                                CSV
                            </x-button>
                            <x-button 
                                variant="danger" 
                                size="sm" 
                                icon="fas fa-file-pdf" 
                                onclick="exportToPdf()">
                                PDF
                            </x-button>
                        </div>
                    </div>
                </x-slot>
                    @if($topProducts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="productsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Posição</th>
                                    <th>Produto</th>
                                    <th>Código</th>
                                    <th>Categoria</th>
                                    <th>Preço Unit.</th>
                                    <th>Qtd. Vendida</th>
                                    <th>Receita Total</th>
                                    <th>% Receita</th>
                                    <th>Estoque Atual</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $index => $product)
                                <tr>
                                    <td>
                                        @if($index < 3)
                                            @if($index == 0)
                                                <i class="fas fa-trophy text-warning me-1"></i>
                                            @elseif($index == 1)
                                                <i class="fas fa-medal text-secondary me-1"></i>
                                            @else
                                                <i class="fas fa-award text-info me-1"></i>
                                            @endif
                                        @endif
                                        <strong>{{ $index + 1 }}º</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary rounded d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-box text-white"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $product->name }}</strong>
                                                @if($product->description)
                                                    <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code>{{ $product->code }}</code>
                                    </td>
                                    <td>
                                        @if($product->category)
                                            <span class="badge bg-secondary">{{ $product->category }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>R$ {{ number_format($product->price, 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-success fs-6">{{ number_format($product->total_quantity) }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            R$ {{ number_format($product->total_revenue, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ number_format(($product->total_revenue / $summary['total_revenue']) * 100, 1) }}%
                                        </span>
                                    </td>
                                    <td>
                                        @if($product->stock_quantity <= 5)
                                            <span class="badge bg-danger">{{ $product->stock_quantity }}</span>
                                        @elseif($product->stock_quantity <= 20)
                                            <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <x-button 
                                                href="{{ route('products.show', $product->id) }}" 
                                                variant="outline-primary" 
                                                size="sm" 
                                                icon="fas fa-eye" 
                                                title="Ver Produto" />
                                            <x-button 
                                                href="{{ route('products.edit', $product->id) }}" 
                                                variant="outline-warning" 
                                                size="sm" 
                                                icon="fas fa-edit" 
                                                title="Editar Produto" />
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum produto encontrado</h5>
                        <p class="text-muted">Não há produtos vendidos no período selecionado.</p>
                    </div>
                    @endif
            </x-card>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if(isset($topProducts) && $topProducts->count() > 0)
// Dados para o gráfico
const productsData = {
    labels: [
        @foreach($topProducts->take(10) as $product)
            '{{ Str::limit($product->name, 20) }}',
        @endforeach
    ],
    datasets: [{
        label: @if(($request->order_by ?? 'quantity') == 'quantity') 'Quantidade Vendida' @else 'Receita (R$)' @endif,
        data: [
            @foreach($topProducts->take(10) as $product)
                @if(($request->order_by ?? 'quantity') == 'quantity')
                    {{ $product->total_quantity }},
                @else
                    {{ $product->total_revenue }},
                @endif
            @endforeach
        ],
        backgroundColor: [
            '#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d',
            '#17a2b8', '#fd7e14', '#6f42c1', '#e83e8c', '#20c997'
        ],
        borderColor: '#fff',
        borderWidth: 2
    }]
};

// Configuração do gráfico
const config = {
    type: 'bar',
    data: productsData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        @if(($request->order_by ?? 'quantity') == 'quantity')
                            return context.parsed.y.toLocaleString('pt-BR') + ' unidades';
                        @else
                            return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        @endif
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    @if(($request->order_by ?? 'quantity') == 'quantity')
                        callback: function(value) {
                            return value.toLocaleString('pt-BR');
                        }
                    @else
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        }
                    @endif
                }
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        }
    }
};

// Criar o gráfico
const ctx = document.getElementById('productsChart').getContext('2d');
const productsChart = new Chart(ctx, config);
@endif

function exportToCsv() {
    // Implementar exportação CSV
    alert('Funcionalidade de exportação CSV será implementada em breve.');
}

function exportToPdf() {
    // Implementar exportação PDF
    alert('Funcionalidade de exportação PDF será implementada em breve.');
}

// Inicializar DataTable se disponível
$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#productsTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
            },
            "pageLength": 25,
            "order": [[ 0, "asc" ]], // Ordenar por posição crescente
            "columnDefs": [
                { "orderable": false, "targets": 9 } // Coluna de ações não ordenável
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

#productsChart {
    max-height: 400px;
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