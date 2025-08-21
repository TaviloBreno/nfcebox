@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('Editar Produto') }}</h4>
                    <div>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info me-2">
                            <i class="fas fa-eye"></i> {{ __('Visualizar') }}
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('Voltar') }}
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('products.update', $product) }}" novalidate>
                        @csrf
                        @method('PUT')

                        <!-- Informações Básicas -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">{{ __('Informações Básicas') }}</h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="name" class="form-label">{{ __('Nome') }} <span class="text-danger">*</span></label>
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       name="name" value="{{ old('name', $product->name) }}" required autocomplete="name" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="sku" class="form-label">{{ __('SKU') }} <span class="text-danger">*</span></label>
                                <input id="sku" type="text" class="form-control @error('sku') is-invalid @enderror" 
                                       name="sku" value="{{ old('sku', $product->sku) }}" required>
                                @error('sku')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label for="description" class="form-label">{{ __('Descrição') }}</label>
                                <textarea id="description" class="form-control @error('description') is-invalid @enderror" 
                                          name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Informações Fiscais -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">{{ __('Informações Fiscais') }}</h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="ncm" class="form-label">{{ __('NCM') }} <span class="text-danger">*</span></label>
                                <input id="ncm" type="text" class="form-control @error('ncm') is-invalid @enderror" 
                                       name="ncm" value="{{ old('ncm', $product->ncm) }}" required maxlength="8" 
                                       data-mask="00000000" placeholder="12345678">
                                @error('ncm')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <div class="form-text">8 dígitos numéricos</div>
                            </div>
                            <div class="col-md-4">
                                <label for="cfop" class="form-label">{{ __('CFOP') }} <span class="text-danger">*</span></label>
                                <input id="cfop" type="text" class="form-control @error('cfop') is-invalid @enderror" 
                                       name="cfop" value="{{ old('cfop', $product->cfop) }}" required maxlength="4" 
                                       data-mask="0000" placeholder="5102">
                                @error('cfop')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <div class="form-text">4 dígitos numéricos</div>
                            </div>
                            <div class="col-md-4">
                                <label for="cest" class="form-label">{{ __('CEST') }}</label>
                                <input id="cest" type="text" class="form-control @error('cest') is-invalid @enderror" 
                                       name="cest" value="{{ old('cest', $product->cest) }}" maxlength="9" 
                                       data-mask="00.000.00" placeholder="01.001.00">
                                @error('cest')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                                <div class="form-text">Formato: XX.XXX.XX</div>
                            </div>
                        </div>

                        <!-- Preço e Estoque -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">{{ __('Preço e Estoque') }}</h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="unit" class="form-label">{{ __('Unidade') }} <span class="text-danger">*</span></label>
                                <select id="unit" class="form-select @error('unit') is-invalid @enderror" name="unit" required>
                                    <option value="">{{ __('Selecione...') }}</option>
                                    <option value="UN" {{ old('unit', $product->unit) == 'UN' ? 'selected' : '' }}>UN - Unidade</option>
                                    <option value="PC" {{ old('unit', $product->unit) == 'PC' ? 'selected' : '' }}>PC - Peça</option>
                                    <option value="KG" {{ old('unit', $product->unit) == 'KG' ? 'selected' : '' }}>KG - Quilograma</option>
                                    <option value="G" {{ old('unit', $product->unit) == 'G' ? 'selected' : '' }}>G - Grama</option>
                                    <option value="L" {{ old('unit', $product->unit) == 'L' ? 'selected' : '' }}>L - Litro</option>
                                    <option value="ML" {{ old('unit', $product->unit) == 'ML' ? 'selected' : '' }}>ML - Mililitro</option>
                                    <option value="M" {{ old('unit', $product->unit) == 'M' ? 'selected' : '' }}>M - Metro</option>
                                    <option value="CM" {{ old('unit', $product->unit) == 'CM' ? 'selected' : '' }}>CM - Centímetro</option>
                                    <option value="M2" {{ old('unit', $product->unit) == 'M2' ? 'selected' : '' }}>M² - Metro Quadrado</option>
                                    <option value="M3" {{ old('unit', $product->unit) == 'M3' ? 'selected' : '' }}>M³ - Metro Cúbico</option>
                                </select>
                                @error('unit')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="price" class="form-label">{{ __('Preço') }} <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input id="price" type="text" class="form-control @error('price') is-invalid @enderror" 
                                           name="price" value="{{ old('price', number_format($product->price, 2, ',', '.')) }}" required 
                                           data-mask="#.##0,00" data-mask-reverse="true" placeholder="0,00">
                                    @error('price')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="stock" class="form-label">{{ __('Estoque') }} <span class="text-danger">*</span></label>
                                <input id="stock" type="text" class="form-control @error('stock') is-invalid @enderror" 
                                       name="stock" value="{{ old('stock', number_format($product->stock, 3, ',', '.')) }}" required 
                                       data-mask="#.##0,000" data-mask-reverse="true" placeholder="0,000">
                                @error('stock')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary me-md-2">
                                        {{ __('Cancelar') }}
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> {{ __('Atualizar Produto') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    // Aplicar máscaras
    $('[data-mask]').each(function() {
        var mask = $(this).data('mask');
        var reverse = $(this).data('mask-reverse') || false;
        $(this).mask(mask, {reverse: reverse});
    });
});
</script>
@endpush