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
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-receipt"></i> Emissão de NFCe
                </h5>
                <p class="card-text">Emita suas notas fiscais de consumidor eletrônica de forma rápida e segura, seguindo todas as normas da SEFAZ.</p>
                <a href="#" class="btn btn-primary">Saiba Mais</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-graph-up"></i> Relatórios
                </h5>
                <p class="card-text">Acompanhe suas vendas com relatórios detalhados e gráficos intuitivos para melhor gestão do seu negócio.</p>
                <a href="#" class="btn btn-primary">Saiba Mais</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-shield-check"></i> Segurança
                </h5>
                <p class="card-text">Seus dados estão protegidos com criptografia de ponta e backup automático em nuvem.</p>
                <a href="#" class="btn btn-primary">Saiba Mais</a>
            </div>
        </div>
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