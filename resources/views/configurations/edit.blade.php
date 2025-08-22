@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Editar Configurações da Empresa</h1>
                <a href="{{ route('configurations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('configurations.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Informações da Empresa -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Informações da Empresa</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="corporate_name" class="form-label">Razão Social *</label>
                                    <input type="text" 
                                           class="form-control @error('corporate_name') is-invalid @enderror" 
                                           id="corporate_name" 
                                           name="corporate_name" 
                                           value="{{ old('corporate_name', $config->corporate_name ?? '') }}" 
                                           required>
                                    @error('corporate_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="trade_name" class="form-label">Nome Fantasia *</label>
                                    <input type="text" 
                                           class="form-control @error('trade_name') is-invalid @enderror" 
                                           id="trade_name" 
                                           name="trade_name" 
                                           value="{{ old('trade_name', $config->trade_name ?? '') }}" 
                                           required>
                                    @error('trade_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="cnpj" class="form-label">CNPJ *</label>
                                    <input type="text" 
                                           class="form-control @error('cnpj') is-invalid @enderror" 
                                           id="cnpj" 
                                           name="cnpj" 
                                           value="{{ old('cnpj', $config->cnpj ?? '') }}" 
                                           placeholder="00.000.000/0000-00"
                                           maxlength="18"
                                           required>
                                    @error('cnpj')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="ie" class="form-label">Inscrição Estadual *</label>
                                        <input type="text" 
                                               class="form-control @error('ie') is-invalid @enderror" 
                                               id="ie" 
                                               name="ie" 
                                               value="{{ old('ie', $config->ie ?? '') }}" 
                                               required>
                                        @error('ie')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="im" class="form-label">Inscrição Municipal</label>
                                        <input type="text" 
                                               class="form-control @error('im') is-invalid @enderror" 
                                               id="im" 
                                               name="im" 
                                               value="{{ old('im', $config->im ?? '') }}">
                                        @error('im')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address_json" class="form-label">Endereço Completo *</label>
                                    <textarea class="form-control @error('address_json') is-invalid @enderror" 
                                              id="address_json" 
                                              name="address_json" 
                                              rows="4" 
                                              placeholder="Rua, Número, Complemento, Bairro, Cidade - UF, CEP"
                                              required>{{ old('address_json', is_array($config->address_json ?? null) ? implode(', ', $config->address_json) : ($config->address_json ?? '')) }}</textarea>
                                    @error('address_json')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Endereço completo da empresa para emissão da NFCe
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações NFCe -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Configurações NFCe</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Importante:</strong> Estas configurações são essenciais para a emissão de NFCe.
                                </div>

                                <div class="mb-3">
                                    <label for="environment" class="form-label">Ambiente *</label>
                                    <select class="form-select @error('environment') is-invalid @enderror" 
                                            id="environment" 
                                            name="environment" 
                                            required>
                                        <option value="homologacao" {{ old('environment', $config->environment ?? 'homologacao') == 'homologacao' ? 'selected' : '' }}>Homologação</option>
                                        <option value="producao" {{ old('environment', $config->environment ?? '') == 'producao' ? 'selected' : '' }}>Produção</option>
                                    </select>
                                    @error('environment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Use homologação para testes e produção para emissão real
                                    </small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nfce_series" class="form-label">Série NFCe *</label>
                                        <input type="number" 
                                               class="form-control @error('nfce_series') is-invalid @enderror" 
                                               id="nfce_series" 
                                               name="nfce_series" 
                                               value="{{ old('nfce_series', $config->nfce_series ?? '1') }}" 
                                               min="1" 
                                               max="999"
                                               required>
                                        @error('nfce_series')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="nfce_number" class="form-label">Próximo Número *</label>
                                        <input type="number" 
                                               class="form-control @error('nfce_number') is-invalid @enderror" 
                                               id="nfce_number" 
                                               name="nfce_number" 
                                               value="{{ old('nfce_number', $config->nfce_number ?? '1') }}" 
                                               min="1"
                                               required>
                                        @error('nfce_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="csc_id" class="form-label">CSC ID *</label>
                                        <input type="text" 
                                               class="form-control @error('csc_id') is-invalid @enderror" 
                                               id="csc_id" 
                                               name="csc_id" 
                                               value="{{ old('csc_id', $config->csc_id ?? '') }}" 
                                               placeholder="000001"
                                               maxlength="6"
                                               required>
                                        @error('csc_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Código de Segurança do Contribuinte (ID)
                                        </small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="csc_token" class="form-label">CSC Token *</label>
                                        <input type="password" 
                                               class="form-control @error('csc_token') is-invalid @enderror" 
                                               id="csc_token" 
                                               name="csc_token" 
                                               value="{{ old('csc_token', $config->csc_token ?? '') }}" 
                                               placeholder="Token fornecido pela SEFAZ"
                                               required>
                                        @error('csc_token')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Token fornecido pela SEFAZ para assinatura
                                        </small>
                                    </div>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="show_csc_token">
                                    <label class="form-check-label" for="show_csc_token">
                                        Mostrar CSC Token
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Salvar Configurações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Máscara para CNPJ
    document.getElementById('cnpj').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 14) {
            value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
        }
        e.target.value = value;
    });

    // Mostrar/ocultar CSC Token
    document.getElementById('show_csc_token').addEventListener('change', function(e) {
        const cscTokenInput = document.getElementById('csc_token');
        cscTokenInput.type = e.target.checked ? 'text' : 'password';
    });

    // Validação do CSC ID (apenas números)
    document.getElementById('csc_id').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '').padStart(6, '0').substring(0, 6);
    });

    // Validação do ambiente
    document.getElementById('environment').addEventListener('change', function(e) {
        const isProduction = e.target.value === 'producao';
        if (isProduction) {
            if (!confirm('Atenção! Você está alterando para o ambiente de PRODUÇÃO. Isso significa que as NFCe emitidas serão válidas fiscalmente. Tem certeza?')) {
                e.target.value = 'homologacao';
            }
        }
    });
</script>
@endpush

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .alert {
        border-radius: 0.375rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    @media (max-width: 768px) {
        .container-fluid {
            padding: 1rem;
        }
        
        .h3 {
            font-size: 1.5rem;
        }
        
        .btn {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .d-flex.justify-content-between > a {
            margin-top: 1rem;
        }
    }
</style>
@endpush