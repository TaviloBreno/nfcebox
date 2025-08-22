@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <x-card>
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('Novo Produto') }}</h4>
                        <x-button href="{{ route('products.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">
                            {{ __('Voltar') }}
                        </x-button>
                    </div>
                </x-slot>

                @if (session('error'))
                    <x-alert type="danger" dismissible>
                        {{ session('error') }}
                    </x-alert>
                @endif

                    <form method="POST" action="{{ route('products.store') }}" novalidate>
                        @csrf

                        <!-- Informações Básicas -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary border-bottom pb-2">{{ __('Informações Básicas') }}</h5>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <x-form-input 
                                    name="name" 
                                    label="{{ __('Nome') }}" 
                                    value="{{ old('name') }}" 
                                    required 
                                    autofocus />
                            </div>
                            <div class="col-md-4">
                                <x-form-input 
                                    name="sku" 
                                    label="{{ __('SKU') }}" 
                                    value="{{ old('sku') }}" 
                                    required />
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <x-form-input 
                                    name="description" 
                                    label="{{ __('Descrição') }}" 
                                    type="textarea" 
                                    value="{{ old('description') }}" 
                                    rows="3" />
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
                                <x-form-input 
                                    name="ncm" 
                                    label="{{ __('NCM') }}" 
                                    value="{{ old('ncm') }}" 
                                    required 
                                    maxlength="8" 
                                    data-mask="00000000" 
                                    placeholder="12345678" 
                                    help="8 dígitos numéricos" />
                            </div>
                            <div class="col-md-4">
                                <x-form-input 
                                    name="cfop" 
                                    label="{{ __('CFOP') }}" 
                                    value="{{ old('cfop') }}" 
                                    required 
                                    maxlength="4" 
                                    data-mask="0000" 
                                    placeholder="5102" 
                                    help="4 dígitos numéricos" />
                            </div>
                            <div class="col-md-4">
                                <x-form-input 
                                    name="cest" 
                                    label="{{ __('CEST') }}" 
                                    value="{{ old('cest') }}" 
                                    maxlength="9" 
                                    data-mask="00.000.00" 
                                    placeholder="01.001.00" 
                                    help="Formato: XX.XXX.XX" />
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
                                <x-form-input 
                                    name="unit" 
                                    label="{{ __('Unidade') }}" 
                                    type="select" 
                                    value="{{ old('unit') }}" 
                                    required 
                                    :options="[
                                        '' => '{{ __(\'Selecione...\') }}',
                                        'UN' => 'UN - Unidade',
                                        'PC' => 'PC - Peça',
                                        'KG' => 'KG - Quilograma',
                                        'G' => 'G - Grama',
                                        'L' => 'L - Litro',
                                        'ML' => 'ML - Mililitro',
                                        'M' => 'M - Metro',
                                        'CM' => 'CM - Centímetro',
                                        'M2' => 'M² - Metro Quadrado',
                                        'M3' => 'M³ - Metro Cúbico'
                                    ]" />
                            </div>
                            <div class="col-md-4">
                                <x-form-input 
                                    name="price" 
                                    label="{{ __('Preço') }}" 
                                    value="{{ old('price') }}" 
                                    required 
                                    prefix="R$" 
                                    data-mask="#.##0,00" 
                                    data-mask-reverse="true" 
                                    placeholder="0,00" />
                            </div>
                            <div class="col-md-4">
                                <x-form-input 
                                    name="stock" 
                                    label="{{ __('Estoque') }}" 
                                    value="{{ old('stock') }}" 
                                    required 
                                    data-mask="#.##0,000" 
                                    data-mask-reverse="true" 
                                    placeholder="0,000" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <x-button href="{{ route('products.index') }}" variant="outline-secondary" class="me-md-2">
                                        {{ __('Cancelar') }}
                                    </x-button>
                                    <x-button type="submit" variant="primary" icon="fas fa-save">
                                        {{ __('Salvar Produto') }}
                                    </x-button>
                                </div>
                            </div>
                        </div>
                    </form>
            </x-card>
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