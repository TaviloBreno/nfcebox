@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Novo Cliente</h1>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dados do Cliente</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('customers.store') }}" novalidate>
                        @csrf
                        
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
                                           value="{{ old('name') }}" 
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
                                           value="{{ old('document') }}" 
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
                                           value="{{ old('email') }}" 
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
                                           value="{{ old('phone') }}" 
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
                                           value="{{ old('zip_code') }}" 
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
                                                   value="{{ old('street') }}">
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
                                                   value="{{ old('number') }}">
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
                                           value="{{ old('complement') }}">
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
                                           value="{{ old('neighborhood') }}">
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
                                                   value="{{ old('city') }}">
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
                                                <option value="AC" {{ old('state') == 'AC' ? 'selected' : '' }}>AC</option>
                                                <option value="AL" {{ old('state') == 'AL' ? 'selected' : '' }}>AL</option>
                                                <option value="AP" {{ old('state') == 'AP' ? 'selected' : '' }}>AP</option>
                                                <option value="AM" {{ old('state') == 'AM' ? 'selected' : '' }}>AM</option>
                                                <option value="BA" {{ old('state') == 'BA' ? 'selected' : '' }}>BA</option>
                                                <option value="CE" {{ old('state') == 'CE' ? 'selected' : '' }}>CE</option>
                                                <option value="DF" {{ old('state') == 'DF' ? 'selected' : '' }}>DF</option>
                                                <option value="ES" {{ old('state') == 'ES' ? 'selected' : '' }}>ES</option>
                                                <option value="GO" {{ old('state') == 'GO' ? 'selected' : '' }}>GO</option>
                                                <option value="MA" {{ old('state') == 'MA' ? 'selected' : '' }}>MA</option>
                                                <option value="MT" {{ old('state') == 'MT' ? 'selected' : '' }}>MT</option>
                                                <option value="MS" {{ old('state') == 'MS' ? 'selected' : '' }}>MS</option>
                                                <option value="MG" {{ old('state') == 'MG' ? 'selected' : '' }}>MG</option>
                                                <option value="PA" {{ old('state') == 'PA' ? 'selected' : '' }}>PA</option>
                                                <option value="PB" {{ old('state') == 'PB' ? 'selected' : '' }}>PB</option>
                                                <option value="PR" {{ old('state') == 'PR' ? 'selected' : '' }}>PR</option>
                                                <option value="PE" {{ old('state') == 'PE' ? 'selected' : '' }}>PE</option>
                                                <option value="PI" {{ old('state') == 'PI' ? 'selected' : '' }}>PI</option>
                                                <option value="RJ" {{ old('state') == 'RJ' ? 'selected' : '' }}>RJ</option>
                                                <option value="RN" {{ old('state') == 'RN' ? 'selected' : '' }}>RN</option>
                                                <option value="RS" {{ old('state') == 'RS' ? 'selected' : '' }}>RS</option>
                                                <option value="RO" {{ old('state') == 'RO' ? 'selected' : '' }}>RO</option>
                                                <option value="RR" {{ old('state') == 'RR' ? 'selected' : '' }}>RR</option>
                                                <option value="SC" {{ old('state') == 'SC' ? 'selected' : '' }}>SC</option>
                                                <option value="SP" {{ old('state') == 'SP' ? 'selected' : '' }}>SP</option>
                                                <option value="SE" {{ old('state') == 'SE' ? 'selected' : '' }}>SE</option>
                                                <option value="TO" {{ old('state') == 'TO' ? 'selected' : '' }}>TO</option>
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
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Salvar Cliente
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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