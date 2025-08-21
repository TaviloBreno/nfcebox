@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Configurações do Sistema</h1>
                <div>
                    <a href="{{ route('configurations.edit') }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-2"></i>Editar Configurações
                    </a>
                    <a href="{{ route('configurations.users') }}" class="btn btn-secondary">
                        <i class="fas fa-users me-2"></i>Gerenciar Usuários
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Informações da Empresa</h5>
                        </div>
                        <div class="card-body">
                            @if($config)
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nome:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->company_name ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>CNPJ:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->cnpj ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Endereço:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->address ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Telefone:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->phone ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>E-mail:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->email ?? 'Não configurado' }}
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                    <h5>Configurações não encontradas</h5>
                                    <p class="text-muted">Configure as informações da empresa para começar.</p>
                                    <a href="{{ route('configurations.edit') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Configurar Agora
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificado Digital</h5>
                        </div>
                        <div class="card-body">
                            @if($config && $config->certificate_path)
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Caminho:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        <code>{{ $config->certificate_path }}</code>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Status:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        @if(file_exists($config->certificate_path))
                                            <span class="badge bg-success">Arquivo encontrado</span>
                                        @else
                                            <span class="badge bg-danger">Arquivo não encontrado</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>Senha:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        @if($config->certificate_password)
                                            <span class="badge bg-success">Configurada</span>
                                        @else
                                            <span class="badge bg-warning">Não configurada</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                                    <h5>Certificado não configurado</h5>
                                    <p class="text-muted">Configure o certificado digital para emissão de NFCe.</p>
                                    <a href="{{ route('configurations.edit') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Configurar Certificado
                                    </a>
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
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informações do Sistema</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <i class="fas fa-code fa-2x text-primary mb-2"></i>
                                        <h6>Versão do Laravel</h6>
                                        <span class="badge bg-primary">{{ app()->version() }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <i class="fab fa-php fa-2x text-info mb-2"></i>
                                        <h6>Versão do PHP</h6>
                                        <span class="badge bg-info">{{ PHP_VERSION }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <i class="fas fa-users fa-2x text-success mb-2"></i>
                                        <h6>Total de Usuários</h6>
                                        <span class="badge bg-success">{{ \App\Models\User::count() }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <i class="fas fa-shopping-cart fa-2x text-warning mb-2"></i>
                                        <h6>Total de Vendas</h6>
                                        <span class="badge bg-warning">{{ \App\Models\Sale::count() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
            margin-bottom: 0.5rem;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .d-flex.justify-content-between > div {
            margin-top: 1rem;
        }
    }
</style>
@endpush