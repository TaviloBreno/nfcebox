@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('Produtos') }}</h4>
                    <a href="{{ route('products.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('Novo Produto') }}
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Formulário de Busca -->
                    <form method="GET" action="{{ route('products.index') }}" class="mb-4">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           value="{{ request('search') }}" 
                                           placeholder="Buscar por nome ou SKU...">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i> {{ __('Buscar') }}
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                @if(request('search'))
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-times"></i> {{ __('Limpar') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($products->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>{{ __('Nome') }}</th>
                                        <th>{{ __('SKU') }}</th>
                                        <th>{{ __('NCM') }}</th>
                                        <th>{{ __('CFOP') }}</th>
                                        <th>{{ __('Unidade') }}</th>
                                        <th>{{ __('Preço') }}</th>
                                        <th>{{ __('Estoque') }}</th>
                                        <th width="200">{{ __('Ações') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <strong>{{ $product->name }}</strong>
                                                @if($product->description)
                                                    <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td><code>{{ $product->sku }}</code></td>
                                            <td>{{ $product->ncm }}</td>
                                            <td>{{ $product->cfop }}</td>
                                            <td>{{ $product->unit }}</td>
                                            <td>R$ {{ number_format($product->price, 2, ',', '.') }}</td>
                                            <td>
                                                <span class="badge {{ $product->stock > 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ number_format($product->stock, 3, ',', '.') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('products.show', $product) }}" 
                                                       class="btn btn-sm btn-outline-info" 
                                                       title="{{ __('Visualizar') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('products.edit', $product) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="{{ __('Editar') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('products.destroy', $product) }}" 
                                                          method="POST" 
                                                          style="display: inline-block;"
                                                          onsubmit="return confirm('{{ __('Tem certeza que deseja excluir este produto?') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                title="{{ __('Excluir') }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
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
                            <h5 class="text-muted">{{ __('Nenhum produto encontrado') }}</h5>
                            @if(request('search'))
                                <p class="text-muted">{{ __('Tente ajustar os termos de busca') }}</p>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                                    {{ __('Ver todos os produtos') }}
                                </a>
                            @else
                                <p class="text-muted">{{ __('Comece criando seu primeiro produto') }}</p>
                                <a href="{{ route('products.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> {{ __('Criar Produto') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
                
                @if($products->count() > 0)
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                Mostrando {{ $products->firstItem() }} a {{ $products->lastItem() }} 
                                de {{ $products->total() }} resultados
                            </div>
                            {{ $products->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection