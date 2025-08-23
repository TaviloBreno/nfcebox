@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <x-card title="{{ __('Produtos') }}">
                <x-slot name="actions">
                    <x-button href="{{ route('products.create') }}" variant="primary" icon="fas fa-plus">
                        {{ __('Novo Produto') }}
                    </x-button>
                </x-slot>

                @if (session('success'))
                    <x-alert type="success" dismissible class="mb-4">
                        {{ session('success') }}
                    </x-alert>
                @endif

                @if (session('error'))
                    <x-alert type="danger" dismissible class="mb-4">
                        {{ session('error') }}
                    </x-alert>
                @endif

                <!-- Formulário de Busca -->
                <form method="GET" action="{{ route('products.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-10">
                            <x-form-input 
                                name="search" 
                                :value="request('search')" 
                                placeholder="Buscar por nome ou SKU..." 
                                suffix-button="true" 
                                suffix-button-text="{{ __('Buscar') }}" 
                                suffix-button-icon="fas fa-search" 
                                suffix-button-type="submit" />
                        </div>
                        <div class="col-md-2">
                            @if(request('search'))
                                <x-button href="{{ route('products.index') }}" variant="outline-danger" icon="fas fa-times" block>
                                    {{ __('Limpar') }}
                                </x-button>
                            @endif
                        </div>
                    </div>
                </form>

                @php
                    // PRÉ-MONTA AS LINHAS — sem "use" aqui!
                    $rows = $products->map(function ($product) {
                        $nameColumn = '<strong>' . e($product->name) . '</strong>';
                        if ($product->description) {
                            $nameColumn .= '<br><small class="text-muted">' 
                                . e(\Illuminate\Support\Str::limit($product->description, 50)) 
                                . '</small>';
                        }

                        $stockBadge = '<span class="badge ' . ($product->stock > 0 ? 'bg-success' : 'bg-danger') . '">'
                                    . e(number_format((float)$product->stock, 3, ',', '.'))
                                    . '</span>';

                        // (Opcional) mover para um partial Blade
                        $actions = '<div class="btn-group" role="group">'
                                 .    '<a href="' . e(route('products.show', $product)) . '" class="btn btn-sm btn-outline-info" title="' . e(__('Visualizar')) . '"><i class="fas fa-eye"></i></a>'
                                 .    '<a href="' . e(route('products.edit', $product)) . '" class="btn btn-sm btn-outline-primary" title="' . e(__('Editar')) . '"><i class="fas fa-edit"></i></a>'
                                 .    '<form action="' . e(route('products.destroy', $product)) . '" method="POST" style="display:inline-block;" onsubmit="return confirm(\'' . e(__('Tem certeza que deseja excluir este produto?')) . '\')">'
                                 .          csrf_field() . method_field('DELETE')
                                 .          '<button type="submit" class="btn btn-sm btn-outline-danger" title="' . e(__('Excluir')) . '"><i class="fas fa-trash"></i></button>'
                                 .    '</form>'
                                 . '</div>';

                        return [
                            // headers numéricos => array indexado na mesma ordem
                            $nameColumn,
                            '<code>' . e($product->sku) . '</code>',
                            e($product->ncm),
                            e($product->cfop),
                            e($product->unit),
                            'R$ ' . number_format((float)$product->price, 2, ',', '.'),
                            $stockBadge,
                            $actions,
                        ];
                    })->toArray();
                @endphp

                @if($products->count() > 0)
                    <x-data-table 
                        :headers="[__('Nome'), __('SKU'), __('NCM'), __('CFOP'), __('Unidade'), __('Preço'), __('Estoque'), __('Ações')]"
                        :data="$rows"
                        striped
                        hover
                        responsive
                        searchable
                        sortable
                    />
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('Nenhum produto encontrado') }}</h5>
                        @if(request('search'))
                            <p class="text-muted">{{ __('Tente ajustar os termos de busca') }}</p>
                            <x-button href="{{ route('products.index') }}" variant="outline-primary">
                                {{ __('Ver todos os produtos') }}
                            </x-button>
                        @else
                            <p class="text-muted">{{ __('Comece criando seu primeiro produto') }}</p>
                            <x-button href="{{ route('products.create') }}" variant="primary" icon="fas fa-plus">
                                {{ __('Criar Produto') }}
                            </x-button>
                        @endif
                    </div>
                @endif

                <x-slot name="footer">
                    @if($products->count() > 0)
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Mostrando {{ $products->firstItem() }} a {{ $products->lastItem() }} 
                                de {{ $products->total() }} resultados
                            </div>
                            {{ $products->links() }}
                        </div>
                    @endif
                </x-slot>
            </x-card>
        </div>
    </div>
</div>
@endsection
