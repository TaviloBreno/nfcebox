@extends('layouts.app')

@section('title', 'Relatórios')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Relatórios de Vendas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Relatório de Vendas por Período -->
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card h-100 border-primary">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-calendar-alt fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title">Vendas por Período</h5>
                                    <p class="card-text text-muted">
                                        Relatório detalhado de vendas filtrado por período e status.
                                    </p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('reports.sales-by-period') }}" class="btn btn-primary">
                                            <i class="fas fa-eye me-1"></i>
                                            Visualizar
                                        </a>
                                        <a href="{{ asset('docs/SYSTEM_FLOWS.md#relatórios') }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Relatório de Vendas por Forma de Pagamento -->
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card h-100 border-success">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-credit-card fa-3x text-success"></i>
                                    </div>
                                    <h5 class="card-title">Vendas por Pagamento</h5>
                                    <p class="card-text text-muted">
                                        Análise de vendas agrupadas por forma de pagamento.
                                    </p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('reports.sales-by-payment') }}" class="btn btn-success">
                                            <i class="fas fa-eye me-1"></i>
                                            Visualizar
                                        </a>
                                        <a href="{{ asset('docs/SYSTEM_FLOWS.md#relatórios') }}" class="btn btn-outline-success btn-sm" target="_blank">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Relatório de Vendas por Cliente -->
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card h-100 border-info">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-users fa-3x text-info"></i>
                                    </div>
                                    <h5 class="card-title">Vendas por Cliente</h5>
                                    <p class="card-text text-muted">
                                        Ranking dos melhores clientes por volume de compras.
                                    </p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('reports.sales-by-customer') }}" class="btn btn-info">
                                            <i class="fas fa-eye me-1"></i>
                                            Visualizar
                                        </a>
                                        <a href="{{ asset('docs/SYSTEM_FLOWS.md#relatórios') }}" class="btn btn-outline-info btn-sm" target="_blank">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Relatório de Produtos Mais Vendidos -->
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card h-100 border-warning">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-trophy fa-3x text-warning"></i>
                                    </div>
                                    <h5 class="card-title">Produtos Mais Vendidos</h5>
                                    <p class="card-text text-muted">
                                        Ranking dos produtos com maior volume de vendas.
                                    </p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('reports.top-products') }}" class="btn btn-warning">
                                            <i class="fas fa-eye me-1"></i>
                                            Visualizar
                                        </a>
                                        <a href="{{ asset('docs/SYSTEM_FLOWS.md#relatórios') }}" class="btn btn-outline-warning btn-sm" target="_blank">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informações Adicionais -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5 class="alert-heading">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Informações sobre os Relatórios
                                </h5>
                                <hr>
                                <ul class="mb-0">
                                    <li><strong>Período:</strong> Todos os relatórios permitem filtrar por período específico</li>
                                    <li><strong>Status:</strong> Por padrão, vendas canceladas são excluídas dos relatórios</li>
                                    <li><strong>Exportação:</strong> Os relatórios podem ser exportados em formato CSV e PDF</li>
                                    <li><strong>Atualização:</strong> Os dados são atualizados em tempo real</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.fa-3x {
    font-size: 3rem;
}
</style>
@endpush