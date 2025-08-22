@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <x-card>
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('Detalhes do Produto') }}</h4>
                        <div>
                            <x-button 
                                href="{{ route('products.edit', $product) }}" 
                                variant="outline-primary" 
                                class="me-2" 
                                icon="fas fa-edit">
                                {{ __('Editar') }}
                            </x-button>
                            <x-button 
                                href="{{ route('products.index') }}" 
                                variant="outline-secondary" 
                                icon="fas fa-arrow-left">
                                {{ __('Voltar') }}
                            </x-button>
                        </div>
                    </div>
                </x-slot>
                    <!-- Informações Básicas -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle"></i> {{ __('Informações Básicas') }}
                            </h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">{{ __('Nome') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">{{ $product->name }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('SKU') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">
                                <code class="text-dark">{{ $product->sku }}</code>
                            </p>
                        </div>
                    </div>

                    @if($product->description)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">{{ __('Descrição') }}</label>
                                <p class="form-control-plaintext border rounded p-2 bg-light">{{ $product->description }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Informações Fiscais -->
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-file-invoice"></i> {{ __('Informações Fiscais') }}
                            </h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('NCM') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">
                                <code class="text-dark">{{ $product->ncm }}</code>
                            </p>
                            <small class="text-muted">Nomenclatura Comum do Mercosul</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('CFOP') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">
                                <code class="text-dark">{{ $product->cfop }}</code>
                            </p>
                            <small class="text-muted">Código Fiscal de Operações e Prestações</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('CEST') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">
                                @if($product->cest)
                                    <code class="text-dark">{{ $product->cest }}</code>
                                @else
                                    <span class="text-muted">{{ __('Não informado') }}</span>
                                @endif
                            </p>
                            <small class="text-muted">Código Especificador da Substituição Tributária</small>
                        </div>
                    </div>

                    <!-- Preço e Estoque -->
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-dollar-sign"></i> {{ __('Preço e Estoque') }}
                            </h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('Unidade') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">
                                <span class="badge bg-secondary">{{ $product->unit }}</span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('Preço') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">
                                <span class="h5 text-success mb-0">
                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ __('Estoque') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">
                                <span class="badge {{ $product->stock > 0 ? 'bg-success' : 'bg-danger' }} fs-6">
                                    {{ number_format($product->stock, 3, ',', '.') }}
                                </span>
                                @if($product->stock <= 0)
                                    <br><small class="text-danger">{{ __('Produto em falta') }}</small>
                                @elseif($product->stock <= 10)
                                    <br><small class="text-warning">{{ __('Estoque baixo') }}</small>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Informações do Sistema -->
                    <div class="row mb-4 mt-4">
                        <div class="col-12">
                            <h5 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-cog"></i> {{ __('Informações do Sistema') }}
                            </h5>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Criado em') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">
                                <i class="fas fa-calendar-plus text-muted me-2"></i>
                                {{ $product->created_at->format('d/m/Y H:i:s') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">{{ __('Última atualização') }}</label>
                            <p class="form-control-plaintext border rounded p-2 bg-light">
                                <i class="fas fa-calendar-edit text-muted me-2"></i>
                                {{ $product->updated_at->format('d/m/Y H:i:s') }}
                            </p>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <form action="{{ route('products.destroy', $product) }}" 
                                      method="POST" 
                                      style="display: inline-block;"
                                      onsubmit="return confirm('{{ __('Tem certeza que deseja excluir este produto?') }}\n\n{{ __('Esta ação não pode ser desfeita!') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <x-button 
                                        type="submit" 
                                        variant="outline-danger" 
                                        class="me-md-2" 
                                        icon="fas fa-trash">
                                        {{ __('Excluir Produto') }}
                                    </x-button>
                                </form>
                                <x-button 
                                    href="{{ route('products.edit', $product) }}" 
                                    variant="primary" 
                                    icon="fas fa-edit">
                                    {{ __('Editar Produto') }}
                                </x-button>
                            </div>
                        </div>
                    </div>
            </x-card>

            <!-- Card com Vendas Relacionadas (se houver) -->
            @if($product->saleItems()->exists())
                <x-card class="mt-4">
                    <x-slot name="header">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-cart"></i> {{ __('Vendas Relacionadas') }}
                        </h5>
                    </x-slot>
                    
                    <x-alert type="info" icon="fas fa-info-circle">
                        {{ __('Este produto possui') }} <strong>{{ $product->saleItems()->count() }}</strong> 
                        {{ __('item(ns) de venda associado(s).') }}
                        <br>
                        <small>{{ __('Por isso, não pode ser excluído até que todas as vendas sejam removidas.') }}</small>
                    </x-alert>
                </x-card>
            @endif
        </div>
    </div>
</div>
@endsection