@extends('layouts.app')

@section('title', 'Venda #' . $sale->number)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Venda #{{ $sale->number }}</h1>
                    <small class="text-muted">Criada em {{ $sale->created_at->format('d/m/Y H:i:s') }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    @if($sale->status !== 'canceled' && $sale->status !== 'authorized')
                        <form method="POST" action="{{ route('sales.cancel', $sale) }}" class="d-inline" 
                              onsubmit="return confirm('Tem certeza que deseja cancelar esta venda?')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times"></i> Cancelar Venda
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informações da Venda -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Informações da Venda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Número da Venda:</label>
                                <p class="mb-0">{{ $sale->number }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <div>
                                    @switch($sale->status)
                                        @case('draft')
                                            <span class="badge bg-secondary fs-6">Rascunho</span>
                                            @break
                                        @case('authorized_pending')
                                            <span class="badge bg-warning fs-6">Pendente Autorização</span>
                                            @break
                                        @case('authorized')
                                            <span class="badge bg-success fs-6">Autorizada</span>
                                            @break
                                        @case('canceled')
                                            <span class="badge bg-danger fs-6">Cancelada</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark fs-6">{{ $sale->status }}</span>
                                    @endswitch
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Forma de Pagamento:</label>
                                <div>
                                    @switch($sale->payment_method)
                                        @case('money')
                                            <span class="badge bg-success fs-6">💵 Dinheiro</span>
                                            @break
                                        @case('pix')
                                            <span class="badge bg-info fs-6">📱 PIX</span>
                                            @break
                                        @case('debit_card')
                                            <span class="badge bg-primary fs-6">💳 Cartão de Débito</span>
                                            @break
                                        @case('credit_card')
                                            <span class="badge bg-warning fs-6">💳 Cartão de Crédito</span>
                                            @break
                                        @case('bank_transfer')
                                            <span class="badge bg-secondary fs-6">🏦 Transferência Bancária</span>
                                            @break
                                        @case('check')
                                            <span class="badge bg-dark fs-6">📝 Cheque</span>
                                            @break
                                        @default
                                            <span class="badge bg-light text-dark fs-6">{{ $sale->payment_method }}</span>
                                    @endswitch
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cliente:</label>
                                <div>
                                    @if($sale->customer)
                                        <p class="mb-0">{{ $sale->customer->name }}</p>
                                        <small class="text-muted">{{ $sale->customer->document }}</small>
                                        @if($sale->customer->email)
                                            <br><small class="text-muted">{{ $sale->customer->email }}</small>
                                        @endif
                                    @else
                                        <p class="mb-0 text-muted">Consumidor não identificado</p>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Data/Hora:</label>
                                <p class="mb-0">{{ $sale->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                            @if($sale->nfce_key)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Chave NFCe:</label>
                                    <p class="mb-0 font-monospace">{{ $sale->nfce_key }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Itens da Venda -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shopping-cart"></i> Itens da Venda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>SKU</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-end">Valor Unit.</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->product->name }}</strong>
                                            @if($item->product->description)
                                                <br><small class="text-muted">{{ Str::limit($item->product->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $item->product->sku }}</code>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark">{{ $item->qty }}</span>
                                        </td>
                                        <td class="text-end">
                                            R$ {{ number_format($item->unit_price, 2, ',', '.') }}
                                        </td>
                                        <td class="text-end">
                                            <strong>R$ {{ number_format($item->total, 2, ',', '.') }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-active">
                                    <th colspan="4" class="text-end">Total da Venda:</th>
                                    <th class="text-end">
                                        <h5 class="mb-0 text-success">R$ {{ number_format($sale->total, 2, ',', '.') }}</h5>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumo -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Resumo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="border rounded p-3">
                                <h3 class="text-success mb-0">R$ {{ number_format($sale->total, 2, ',', '.') }}</h3>
                                <small class="text-muted">Valor Total</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-primary mb-0">{{ $sale->saleItems->count() }}</h4>
                                <small class="text-muted">Tipos de Produto</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3">
                                <h4 class="text-info mb-0">{{ $sale->saleItems->sum('qty') }}</h4>
                                <small class="text-muted">Itens Vendidos</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        @if($sale->status === 'authorized')
                            <button class="btn btn-success" disabled>
                                <i class="fas fa-check"></i> Venda Autorizada
                            </button>
                        @elseif($sale->status === 'canceled')
                            <button class="btn btn-danger" disabled>
                                <i class="fas fa-times"></i> Venda Cancelada
                            </button>
                        @elseif($sale->status === 'authorized_pending')
                            <button class="btn btn-warning" disabled>
                                <i class="fas fa-clock"></i> Aguardando Autorização
                            </button>
                        @else
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-edit"></i> Rascunho
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @if($sale->customer)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user"></i> Dados do Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Nome:</strong> {{ $sale->customer->name }}</p>
                        <p class="mb-2"><strong>Documento:</strong> {{ $sale->customer->document }}</p>
                        @if($sale->customer->email)
                            <p class="mb-2"><strong>Email:</strong> {{ $sale->customer->email }}</p>
                        @endif
                        @if($sale->customer->phone)
                            <p class="mb-2"><strong>Telefone:</strong> {{ $sale->customer->phone }}</p>
                        @endif
                        <div class="d-grid">
                            <a href="{{ route('customers.show', $sale->customer) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i> Ver Cliente
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection