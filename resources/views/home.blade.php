@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="jumbotron bg-primary text-white p-5 rounded mb-4">
            <h1 class="display-4">Bem-vindo ao NFCeBox!</h1>
            <p class="lead">Uma solução completa para gerenciamento de NFCe (Nota Fiscal de Consumidor Eletrônica).</p>
            <hr class="my-4">
            <p>Gerencie suas notas fiscais de forma simples e eficiente com nossa plataforma moderna.</p>
            <a class="btn btn-light btn-lg" href="#" role="button">Começar Agora</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-4">
        <x-card class="h-100">
            <x-slot name="header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice text-primary"></i> Emissão de NFCe
                </h5>
            </x-slot>
            
            <p class="card-text">Emita suas notas fiscais de consumidor eletrônica de forma rápida e segura, seguindo todas as normas da SEFAZ. Sistema integrado com certificado A1 e validação em tempo real.</p>
            
            <x-slot name="footer">
                <div class="d-flex gap-2">
                    <x-button href="{{ route('nfce.index') }}" variant="primary">
                        <i class="fas fa-arrow-right me-1"></i>Acessar NFCe
                    </x-button>
                    <x-button href="{{ asset('docs/DANFE_DOCUMENTATION.md') }}" variant="outline-primary" target="_blank">
                        <i class="fas fa-info-circle me-1"></i>Saiba Mais
                    </x-button>
                </div>
            </x-slot>
        </x-card>
    </div>
    
    <div class="col-md-4 mb-4">
        <x-card class="h-100">
            <x-slot name="header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-bar text-success"></i> Relatórios
                </h5>
            </x-slot>
            
            <p class="card-text">Acompanhe o desempenho do seu negócio com relatórios detalhados de vendas, produtos e clientes. Exportação em PDF e CSV com gráficos interativos.</p>
            
            <x-slot name="footer">
                <div class="d-flex gap-2">
                    <x-button href="{{ route('reports.index') }}" variant="success">
                        <i class="fas fa-arrow-right me-1"></i>Ver Relatórios
                    </x-button>
                    <x-button href="{{ asset('docs/SYSTEM_FLOWS.md') }}" variant="outline-success" target="_blank">
                        <i class="fas fa-info-circle me-1"></i>Saiba Mais
                    </x-button>
                </div>
            </x-slot>
        </x-card>
    </div>
    
    <div class="col-md-4 mb-4">
        <x-card class="h-100">
            <x-slot name="header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shield-alt text-danger"></i> Segurança
                </h5>
            </x-slot>
            
            <p class="card-text">Seus dados estão protegidos com criptografia de ponta, controle de acesso por perfis, auditoria completa e backup automático em nuvem.</p>
            
            <x-slot name="footer">
                <div class="d-flex gap-2">
                    <x-button href="{{ route('profile.show') }}" variant="danger">
                        <i class="fas fa-arrow-right me-1"></i>Meu Perfil
                    </x-button>
                    <x-button href="{{ asset('docs/PERMISSIONS_GUIDE.md') }}" variant="outline-danger" target="_blank">
                        <i class="fas fa-info-circle me-1"></i>Saiba Mais
                    </x-button>
                </div>
            </x-slot>
        </x-card>
    </div>
</div>

<div class="row mt-5">
    <div class="col-12">
        <div class="alert alert-info" role="alert">
            <h4 class="alert-heading">Recursos Principais</h4>
            <ul class="mb-0">
                <li>Emissão de NFCe em conformidade com a legislação</li>
                <li>Integração com equipamentos SAT</li>
                <li>Controle de estoque integrado</li>
                <li>Relatórios fiscais e gerenciais</li>
                <li>Backup automático na nuvem</li>
                <li>Suporte técnico especializado</li>
            </ul>
        </div>
    </div>
</div>
@endsection