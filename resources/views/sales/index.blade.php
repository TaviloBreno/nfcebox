@extends('layouts.app')

@section('title', 'Vendas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Vendas</h1>
                <div class="d-flex gap-2">
                    <x-button 
                        href="{{ route('pos.index') }}" 
                        variant="primary" 
                        icon="fas fa-plus">
                        Nova Venda
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <x-card>
                <form method="GET" action="{{ route('sales.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <x-form-input 
                            name="search" 
                            label="Buscar" 
                            value="{{ request('search') }}" 
                            placeholder="Número da venda ou cliente..." />
                    </div>
                    <div class="col-md-2">
                        <x-form-input 
                            type="select" 
                            name="status" 
                            label="Status" 
                            value="{{ request('status') }}">
                            <option value="">Todos</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Rascunho</option>
                            <option value="authorized_pending" {{ request('status') === 'authorized_pending' ? 'selected' : '' }}>Pendente</option>
                            <option value="authorized" {{ request('status') === 'authorized' ? 'selected' : '' }}>Autorizada</option>
                            <option value="canceled" {{ request('status') === 'canceled' ? 'selected' : '' }}>Cancelada</option>
                        </x-form-input>
                    </div>
                    <div class="col-md-2">
                        <x-form-input 
                            type="date" 
                            name="date_from" 
                            label="Data Inicial" 
                            value="{{ request('date_from') }}" />
                    </div>
                    <div class="col-md-2">
                        <x-form-input 
                            type="date" 
                            name="date_to" 
                            label="Data Final" 
                            value="{{ request('date_to') }}" />
                    </div>
                    <div class="col-md-3 d-flex align-items-center gap-2">
                        <x-button 
                            type="submit" 
                            variant="primary" 
                            icon="fas fa-search">
                            Filtrar
                        </x-button>
                        <x-button 
                            href="{{ route('sales.index') }}" 
                            variant="outline-secondary" 
                            icon="fas fa-times">
                            Limpar
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>

    <!-- Lista de Vendas -->
    <x-data-table>
        <x-slot name="headers">
            <th>Número</th>
            <th>Cliente</th>
            <th>Total</th>
            <th>Pagamento</th>
            <th>Status</th>
            <th>Data</th>
            <th>Ações</th>
        </x-slot>
        
        @foreach($sales as $sale)
            <tr>
                <td>
                    <strong>{{ $sale->number }}</strong>
                </td>
                <td>
                    @if($sale->customer)
                        {{ $sale->customer->name }}
                        <br><small class="text-muted">{{ $sale->customer->document }}</small>
                    @else
                        <span class="text-muted">Consumidor não identificado</span>
                    @endif
                </td>
                <td>
                    <strong class="text-success">R$ {{ number_format($sale->total, 2, ',', '.') }}</strong>
                </td>
                <td>
                    @switch($sale->payment_method)
                        @case('money')
                            <span class="badge bg-success">💵 Dinheiro</span>
                            @break
                        @case('pix')
                            <span class="badge bg-info">📱 PIX</span>
                            @break
                        @case('debit_card')
                            <span class="badge bg-primary">💳 Débito</span>
                            @break
                        @case('credit_card')
                            <span class="badge bg-warning">💳 Crédito</span>
                            @break
                        @case('bank_transfer')
                            <span class="badge bg-secondary">🏦 Transferência</span>
                            @break
                        @case('check')
                            <span class="badge bg-dark">📝 Cheque</span>
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
                <td>
                    {{ $sale->created_at->format('d/m/Y H:i') }}
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <x-button 
                            href="{{ route('sales.show', $sale) }}" 
                            variant="outline-primary" 
                            size="sm" 
                            title="Visualizar">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        @if($sale->status !== 'canceled' && $sale->status !== 'authorized')
                            <form method="POST" action="{{ route('sales.cancel', $sale) }}" class="d-inline" 
                                  onsubmit="return confirm('Tem certeza que deseja cancelar esta venda?')">
                                @csrf
                                @method('PUT')
                                <x-button 
                                    type="submit" 
                                    variant="outline-danger" 
                                    size="sm" 
                                    title="Cancelar">
                                    <i class="fas fa-times"></i>
                                </x-button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach

        <x-slot name="footer">
            <div class="d-flex justify-content-center mt-4">
                {{ $sales->links() }}
            </div>
        </x-slot>
        
        <x-slot name="empty">
            <div class="text-center py-5">
                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhuma venda encontrada</h5>
                <p class="text-muted">Comece realizando vendas através do PDV.</p>
                <x-button href="{{ route('pos.index') }}" variant="primary">
                    <i class="fas fa-cash-register"></i> Ir para PDV
                </x-button>
            </div>
        </x-slot>
    </x-data-table>
</div>
@endsection