@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Editar Cliente</h1>
                <div>
                    <x-button href="{{ route('customers.show', $customer) }}" variant="info" class="me-2">
                        <i class="fas fa-eye"></i> Visualizar
                    </x-button>
                    <x-button href="{{ route('customers.index') }}" variant="secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </x-button>
                </div>
            </div>

            @if(session('error'))
                <x-alert type="danger" dismissible>
                    {{ session('error') }}
                </x-alert>
            @endif

            <x-card>
                <x-slot name="header">
                    <h5 class="card-title mb-0">Dados do Cliente</h5>
                </x-slot>
                    <form method="POST" action="{{ route('customers.update', $customer) }}" novalidate>
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Dados Pessoais -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Dados Pessoais</h6>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $customer->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="document" class="form-label">CPF/CNPJ <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('document') is-invalid @enderror" 
                                           id="document" 
                                           name="document" 
                                           value="{{ old('document', $customer->document) }}" 
                                           placeholder="000.000.000-00 ou 00.000.000/0000-00"
                                           required>
                                    @error('document')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $customer->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telefone</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $customer->phone) }}" 
                                           placeholder="(00) 00000-0000">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Endereço -->
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Endereço</h6>
                                
                                <div class="mb-3">
                                    <label for="zip_code" class="form-label">CEP</label>
                                    <input type="text" 
                                           class="form-control @error('zip_code') is-invalid @enderror" 
                                           id="zip_code" 
                                           name="zip_code" 
                                           value="{{ old('zip_code', $customer->address['zip_code'] ?? '') }}" 
                                           placeholder="00000-000">
                                    @error('zip_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="street" class="form-label">Logradouro</label>
                                            <input type="text" 
                                                   class="form-control @error('street') is-invalid @enderror" 
                                                   id="street" 
                                                   name="street" 
                                                   value="{{ old('street', $customer->address['street'] ?? '') }}">
                                            @error('street')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="number" class="form-label">Número</label>
                                            <input type="text" 
                                                   class="form-control @error('number') is-invalid @enderror" 
                                                   id="number" 
                                                   name="number" 
                                                   value="{{ old('number', $customer->address['number'] ?? '') }}">
                                            @error('number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="complement" class="form-label">Complemento</label>
                                    <input type="text" 
                                           class="form-control @error('complement') is-invalid @enderror" 
                                           id="complement" 
                                           name="complement" 
                                           value="{{ old('complement', $customer->address['complement'] ?? '') }}">
                                    @error('complement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="neighborhood" class="form-label">Bairro</label>
                                    <input type="text" 
                                           class="form-control @error('neighborhood') is-invalid @enderror" 
                                           id="neighborhood" 
                                           name="neighborhood" 
                                           value="{{ old('neighborhood', $customer->address['neighborhood'] ?? '') }}">
                                    @error('neighborhood')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="city" class="form-label">Cidade</label>
                                            <input type="text" 
                                                   class="form-control @error('city') is-invalid @enderror" 
                                                   id="city" 
                                                   name="city" 
                                                   value="{{ old('city', $customer->address['city'] ?? '') }}">
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="state" class="form-label">UF</label>
                                            <select class="form-select @error('state') is-invalid @enderror" 
                                                    id="state" 
                                                    name="state">
                                                <option value="">Selecione</option>
                                                @php $currentState = old('state', $customer->address['state'] ?? '') @endphp
                                                <option value="AC" {{ $currentState == 'AC' ? 'selected' : '' }}>AC</option>
                                                <option value="AL" {{ $currentState == 'AL' ? 'selected' : '' }}>AL</option>
                                                <option value="AP" {{ $currentState == 'AP' ? 'selected' : '' }}>AP</option>
                                                <option value="AM" {{ $currentState == 'AM' ? 'selected' : '' }}>AM</option>
                                                <option value="BA" {{ $currentState == 'BA' ? 'selected' : '' }}>BA</option>
                                                <option value="CE" {{ $currentState == 'CE' ? 'selected' : '' }}>CE</option>
                                                <option value="DF" {{ $currentState == 'DF' ? 'selected' : '' }}>DF</option>
                                                <option value="ES" {{ $currentState == 'ES' ? 'selected' : '' }}>ES</option>
                                                <option value="GO" {{ $currentState == 'GO' ? 'selected' : '' }}>GO</option>
                                                <option value="MA" {{ $currentState == 'MA' ? 'selected' : '' }}>MA</option>
                                                <option value="MT" {{ $currentState == 'MT' ? 'selected' : '' }}>MT</option>
                                                <option value="MS" {{ $currentState == 'MS' ? 'selected' : '' }}>MS</option>
                                                <option value="MG" {{ $currentState == 'MG' ? 'selected' : '' }}>MG</option>
                                                <option value="PA" {{ $currentState == 'PA' ? 'selected' : '' }}>PA</option>
                                                <option value="PB" {{ $currentState == 'PB' ? 'selected' : '' }}>PB</option>
                                                <option value="PR" {{ $currentState == 'PR' ? 'selected' : '' }}>PR</option>
                                                <option value="PE" {{ $currentState == 'PE' ? 'selected' : '' }}>PE</option>
                                                <option value="PI" {{ $currentState == 'PI' ? 'selected' : '' }}>PI</option>
                                                <option value="RJ" {{ $currentState == 'RJ' ? 'selected' : '' }}>RJ</option>
                                                <option value="RN" {{ $currentState == 'RN' ? 'selected' : '' }}>RN</option>
                                                <option value="RS" {{ $currentState == 'RS' ? 'selected' : '' }}>RS</option>
                                                <option value="RO" {{ $currentState == 'RO' ? 'selected' : '' }}>RO</option>
                                                <option value="RR" {{ $currentState == 'RR' ? 'selected' : '' }}>RR</option>
                                                <option value="SC" {{ $currentState == 'SC' ? 'selected' : '' }}>SC</option>
                                                <option value="SP" {{ $currentState == 'SP' ? 'selected' : '' }}>SP</option>
                                                <option value="SE" {{ $currentState == 'SE' ? 'selected' : '' }}>SE</option>
                                                <option value="TO" {{ $currentState == 'TO' ? 'selected' : '' }}>TO</option>
                                            </select>
                                            @error('state')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <x-button href="{{ route('customers.index') }}" variant="secondary">
                                Cancelar
                            </x-button>
                            <x-button type="submit" variant="primary">
                                <i class="fas fa-save"></i> Atualizar Cliente
                            </x-button>
                        </div>
                    </form>
            </x-card>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
$(document).ready(function() {
    // Máscaras para os campos
    $('#document').mask('000.000.000-00', {
        translation: {
            '0': {pattern: /[0-9]/}
        },
        onKeyPress: function(val, e, field, options) {
            var masks = ['000.000.000-00', '00.000.000/0000-00'];
            var mask = (val.length > 14) ? masks[1] : masks[0];
            $('#document').mask(mask, options);
        }
    });
    
    $('#phone').mask('(00) 00000-0000');
    $('#zip_code').mask('00000-000');
    
    // Busca CEP
    $('#zip_code').blur(function() {
        var cep = $(this).val().replace(/\D/g, '');
        if (cep.length === 8) {
            $.getJSON('https://viacep.com.br/ws/' + cep + '/json/', function(data) {
                if (!data.erro) {
                    $('#street').val(data.logradouro);
                    $('#neighborhood').val(data.bairro);
                    $('#city').val(data.localidade);
                    $('#state').val(data.uf);
                }
            });
        }
    });
});
</script>
@endpush
@endsection