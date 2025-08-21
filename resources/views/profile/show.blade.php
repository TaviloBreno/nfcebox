@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Meu Perfil</h1>
                <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Editar Perfil
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" 
                                         alt="Avatar" 
                                         class="rounded-circle img-fluid" 
                                         style="width: 120px; height: 120px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded-circle d-inline-flex align-items-center justify-content-center" 
                                         style="width: 120px; height: 120px;">
                                        <i class="fas fa-user fa-3x text-white"></i>
                                    </div>
                                @endif
                            </div>
                            <h5 class="card-title">{{ $user->name }}</h5>
                            <p class="text-muted">{{ $user->email }}</p>
                            @if($user->is_admin)
                                <span class="badge bg-danger">Administrador</span>
                            @else
                                <span class="badge bg-primary">Usuário</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-md-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Informações Pessoais</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <strong>Nome:</strong>
                                </div>
                                <div class="col-sm-9">
                                    {{ $user->name }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <strong>E-mail:</strong>
                                </div>
                                <div class="col-sm-9">
                                    {{ $user->email }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <strong>Telefone:</strong>
                                </div>
                                <div class="col-sm-9">
                                    {{ $user->phone ?? 'Não informado' }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <strong>Biografia:</strong>
                                </div>
                                <div class="col-sm-9">
                                    {{ $user->bio ?? 'Não informado' }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-3">
                                    <strong>Membro desde:</strong>
                                </div>
                                <div class="col-sm-9">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-3">
                                    <strong>Tipo de usuário:</strong>
                                </div>
                                <div class="col-sm-9">
                                    @if($user->is_admin)
                                        <span class="badge bg-danger">Administrador</span>
                                    @else
                                        <span class="badge bg-primary">Usuário</span>
                                    @endif
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
        }
    }
</style>
@endpush