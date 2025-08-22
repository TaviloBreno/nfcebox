@extends('layouts.app')

@section('title', 'Relatório de Vendas por Período')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Filtros -->
            <x-card title="Filtros" icon="fas fa-filter" class="mb-4">
                <form method="GET" action="{{ route('reports.sales-by-period') }}" class="row g-3">
                    <div class="col-md-3">
                        <x-form-input 
                            name="date_from" 
                            label="Data Inicial" 
                            type="date" 
                            :value="$request->date_from ?? now()->startOfMonth()->format('Y-m-d')" 
                            required />
                    </div>
                    <div class="col-md-3">
                        <x-form-input 
                            name="date_to" 
                            label="Data Final" 
                            type="date" 
                            :value="$request->date_to ?? now()->format('Y-m-d')" 
                            required />
                    </div>
                    <div class="col-md-3">
                        <x-form-input 
                            name="status" 
                            label="Status" 
                            type="select" 
                            :value="$request->status ?? ''" 
                            :options="[
                                '' => 'Todos (exceto canceladas)',
                                'draft' => 'Rascunho',
                                'authorized_pending' => 'Pendente Autorização',
                                'authorized' => 'Autorizada',
                                'canceled' => 'Cancelada'
                            ]" />
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <x-button type="submit" variant="primary" icon="fas fa-search" class="me-2">
                            Filtrar
                        </x-button>
                        <x-button href="{{ route('reports.sales-by-period') }}" variant="secondary" icon="fas fa-times">
                            Limpar
                        </x-button>
                    </div>
                </form>
            </x-card>

            @if(isset($sales))
            <!-- Resumo -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <x-stat-card 
                        title="Total de Vendas" 
                        :value="$summary['total_sales']" 
                        icon="fas fa-shopping-cart" 
                        variant="primary" 
                        animated />
                </div>
                <div class="col-md-3">
                    <x-stat-card 
                        title="Valor Total" 
                        :value="'R$ ' . number_format($summary['total_amount'], 2, ',', '.')" 
                        icon="fas fa-dollar-sign" 
                        variant="success" 
                        animated />
                </div>
                <div class="col-md-3">
                    <x-stat-card 
                        title="Ticket Médio" 
                        :value="'R$ ' . number_format($summary['average_ticket'], 2, ',', '.')" 
                        icon="fas fa-chart-line" 
                        variant="info" 
                        animated />
                </div>
                <div class="col-md-3">
                    <x-stat-card 
                        title="Período" 
                        :value="$summary['period_from'] . ' a ' . $summary['period_to']" 
                        icon="fas fa-calendar" 
                        variant="warning" />
                </div>
            </div>

            <!-- Tabela de Vendas -->
            <x-card title="Vendas Detalhadas" icon="fas fa-list">
                <x-slot name="actions">
                    <x-button 
                        href="{{ route('reports.sales-by-period.export-csv', request()->query()) }}" 
                        variant="success" 
                        size="sm" 
                        icon="fas fa-file-csv" 
                        class="me-2">
                        CSV
                    </x-button>
                    <x-button 
                        href="{{ route('reports.sales-by-period.export-pdf', request()->query()) }}" 
                        variant="danger" 
                        size="sm" 
                        icon="fas fa-file-pdf" 
                        target="_blank">
                        PDF
                    </x-button>
                </x-slot>

                @if($sales->count() > 0)
                    <x-data-table 
                        :headers="['Número', 'Data/Hora', 'Cliente', 'Forma Pagamento', 'Status', 'Total', 'Ações']"
                        :data="$sales->map(function($sale) {
                            return [
                                '<strong>' . $sale->number . '</strong>',
                                $sale->created_at->format('d/m/Y H:i'),
                                $sale->customer->name ?? 'Cliente não identificado',
                                view('components.status-badge', ['type' => 'payment', 'value' => $sale->payment_method])->render(),
                                view('components.status-badge', ['type' => 'status', 'value' => $sale->status])->render(),
                                '<strong>R$ ' . number_format($sale->total, 2, ',', '.') . '</strong>',
                                '<a href="' . route('sales.show', $sale) . '" class="btn btn-sm btn-outline-primary" title="Ver Detalhes"><i class="fas fa-eye"></i></a>'
                            ];
                        })->toArray()"
                        striped
                        hover
                        responsive
                        searchable
                        sortable />
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma venda encontrada</h5>
                        <p class="text-muted">Não há vendas no período selecionado com os filtros aplicados.</p>
                    </div>
                @endif
            </x-card>
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