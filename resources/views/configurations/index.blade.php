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
                                        <strong>Razão Social:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->corporate_name ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Nome Fantasia:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->trade_name ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>CNPJ:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->cnpj ? preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $config->cnpj) : 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>IE:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->ie ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>IM:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->im ?? 'Não configurado' }}
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
                            <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Configurações NFCe</h5>
                        </div>
                        <div class="card-body">
                            @if($config)
                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Ambiente:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        @if($config->environment === 'producao')
                                            <span class="badge bg-success">Produção</span>
                                        @else
                                            <span class="badge bg-warning">Homologação</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Série NFCe:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->nfce_series ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>Próximo Número:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->nfce_number ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-4">
                                        <strong>CSC ID:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        {{ $config->csc_id ?? 'Não configurado' }}
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-4">
                                        <strong>CSC Token:</strong>
                                    </div>
                                    <div class="col-sm-8">
                                        @if($config->csc_token)
                                            <span class="badge bg-success">Configurado</span>
                                        @else
                                            <span class="badge bg-warning">Não configurado</span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                    <h5>Configurações NFCe não encontradas</h5>
                                    <p class="text-muted">Configure os parâmetros da NFCe para começar.</p>
                                    <a href="{{ route('configurations.edit') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Configurar Agora
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificados A1</h5>
                            <a href="{{ route('configurations.certificates') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Gerenciar Certificados
                            </a>
                        </div>
                        <div class="card-body">
                            @if($config && $config->certificates && $config->certificates->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Alias</th>
                                                <th>Arquivo</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($config->certificates as $certificate)
                                                <tr>
                                                    <td>{{ $certificate->alias }}</td>
                                                    <td><code>{{ basename($certificate->path) }}</code></td>
                                                    <td>
                                                        @if(file_exists(storage_path('app/secure/certs/' . basename($certificate->path))))
                                                            <span class="badge bg-success">Ativo</span>
                                                        @else
                                                            <span class="badge bg-danger">Arquivo não encontrado</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('configurations.certificates.show', $certificate) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                                    <h5>Nenhum certificado configurado</h5>
                                    <p class="text-muted">Faça upload de certificados A1 (.pfx/.p12) para emissão de NFCe.</p>
                                    <a href="{{ route('configurations.certificates') }}" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i>Fazer Upload de Certificado
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