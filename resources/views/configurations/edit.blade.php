@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Editar Configurações</h1>
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
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Informações da Empresa</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="company_name" class="form-label">Nome da Empresa *</label>
                                    <input type="text" 
                                           class="form-control @error('company_name') is-invalid @enderror" 
                                           id="company_name" 
                                           name="company_name" 
                                           value="{{ old('company_name', $config->company_name ?? '') }}" 
                                           required>
                                    @error('company_name')
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
                                           required>
                                    @error('cnpj')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Endereço *</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3" 
                                              required>{{ old('address', $config->address ?? '') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Telefone</label>
                                        <input type="text" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" 
                                               name="phone" 
                                               value="{{ old('phone', $config->phone ?? '') }}" 
                                               placeholder="(11) 99999-9999">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">E-mail</label>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email', $config->email ?? '') }}" 
                                               placeholder="contato@empresa.com">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificado Digital</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Importante:</strong> O certificado digital é necessário para emissão de NFCe. 
                                    Certifique-se de que o arquivo está acessível no servidor.
                                </div>

                                <div class="mb-3">
                                    <label for="certificate_path" class="form-label">Caminho do Certificado</label>
                                    <input type="text" 
                                           class="form-control @error('certificate_path') is-invalid @enderror" 
                                           id="certificate_path" 
                                           name="certificate_path" 
                                           value="{{ old('certificate_path', $config->certificate_path ?? '') }}" 
                                           placeholder="C:\certificados\certificado.pfx">
                                    @error('certificate_path')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Caminho completo para o arquivo do certificado (.pfx ou .p12)
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="certificate_password" class="form-label">Senha do Certificado</label>
                                    <input type="password" 
                                           class="form-control @error('certificate_password') is-invalid @enderror" 
                                           id="certificate_password" 
                                           name="certificate_password" 
                                           value="{{ old('certificate_password', $config->certificate_password ?? '') }}" 
                                           placeholder="Digite a senha do certificado">
                                    @error('certificate_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Senha utilizada para proteger o certificado digital
                                    </small>
                                </div>

                                @if($config && $config->certificate_path)
                                    <div class="alert alert-{{ file_exists($config->certificate_path) ? 'success' : 'danger' }}" role="alert">
                                        <i class="fas fa-{{ file_exists($config->certificate_path) ? 'check-circle' : 'exclamation-triangle' }} me-2"></i>
                                        @if(file_exists($config->certificate_path))
                                            <strong>Certificado encontrado!</strong> O arquivo está acessível no caminho especificado.
                                        @else
                                            <strong>Certificado não encontrado!</strong> Verifique se o caminho está correto e se o arquivo existe.
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configurações Avançadas</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Atenção:</strong> Estas configurações afetam o funcionamento do sistema. 
                                    Altere apenas se souber o que está fazendo.
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="environment" class="form-label">Ambiente</label>
                                        <select class="form-select" id="environment" name="environment">
                                            <option value="homologacao" {{ old('environment', $config->environment ?? 'homologacao') == 'homologacao' ? 'selected' : '' }}>Homologação</option>
                                            <option value="producao" {{ old('environment', $config->environment ?? '') == 'producao' ? 'selected' : '' }}>Produção</option>
                                        </select>
                                        <small class="form-text text-muted">
                                            Ambiente para emissão de NFCe (use homologação para testes)
                                        </small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="serie_nfce" class="form-label">Série NFCe</label>
                                        <input type="number" 
                                               class="form-control" 
                                               id="serie_nfce" 
                                               name="serie_nfce" 
                                               value="{{ old('serie_nfce', $config->serie_nfce ?? '1') }}" 
                                               min="1" 
                                               max="999">
                                        <small class="form-text text-muted">
                                            Série utilizada para numeração das NFCe
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
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

    // Máscara para telefone
    document.getElementById('phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length >= 11) {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 7) {
            value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
        }
        e.target.value = value;
    });

    // Validação do caminho do certificado
    document.getElementById('certificate_path').addEventListener('blur', function(e) {
        const path = e.target.value;
        if (path && !path.match(/\.(pfx|p12)$/i)) {
            alert('Atenção: O certificado deve ter extensão .pfx ou .p12');
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
    }
</style>
@endpush