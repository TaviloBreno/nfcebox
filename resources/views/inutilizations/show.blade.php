@extends('layouts.app')

@section('title', 'Detalhes da Inutilização')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-eye mr-2"></i>
                        Detalhes da Inutilização #{{ $inutilization->id }}
                    </h3>
                    <div>
                        <a href="{{ route('inutilizations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Voltar
                        </a>
                        @if($inutilization->xml_path && file_exists(storage_path('app/' . $inutilization->xml_path)))
                            <a href="{{ route('inutilizations.download', $inutilization) }}" class="btn btn-info">
                                <i class="fas fa-download mr-1"></i>
                                Baixar XML
                            </a>
                        @endif
                        @if($inutilization->canBeReprocessed())
                            <form method="POST" action="{{ route('inutilizations.reprocess', $inutilization) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Deseja reprocessar esta inutilização?')">
                                    <i class="fas fa-redo mr-1"></i>
                                    Reprocessar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Status Alert -->
                    <div class="row mb-4">
                        <div class="col-12">
                            @if($inutilization->isAuthorized())
                                <div class="alert alert-success" role="alert">
                                    <h5><i class="fas fa-check-circle mr-2"></i>Inutilização Autorizada</h5>
                                    <p class="mb-0">A faixa de números foi inutilizada com sucesso na SEFAZ.</p>
                                    @if($inutilization->authorized_at)
                                        <small class="text-muted">Autorizada em: {{ $inutilization->authorized_at->format('d/m/Y H:i:s') }}</small>
                                    @endif
                                </div>
                            @elseif($inutilization->isRejected())
                                <div class="alert alert-danger" role="alert">
                                    <h5><i class="fas fa-times-circle mr-2"></i>Inutilização Rejeitada</h5>
                                    @if($inutilization->sefaz_error_message)
                                        <p class="mb-1"><strong>Erro:</strong> {{ $inutilization->sefaz_error_message }}</p>
                                    @endif
                                    @if($inutilization->sefaz_error_code)
                                        <small class="text-muted">Código: {{ $inutilization->sefaz_error_code }}</small>
                                    @endif
                                </div>
                            @elseif($inutilization->hasError())
                                <div class="alert alert-warning" role="alert">
                                    <h5><i class="fas fa-exclamation-triangle mr-2"></i>Erro no Processamento</h5>
                                    @if($inutilization->sefaz_error_message)
                                        <p class="mb-1">{{ $inutilization->sefaz_error_message }}</p>
                                    @endif
                                    <small class="text-muted">Tentativas: {{ $inutilization->retry_count }}/5</small>
                                </div>
                            @else
                                <div class="alert alert-info" role="alert">
                                    <h5><i class="fas fa-clock mr-2"></i>Processamento Pendente</h5>
                                    <p class="mb-0">A inutilização está sendo processada pela SEFAZ.</p>
                                    @if($inutilization->next_retry_at)
                                        <small class="text-muted">Próxima tentativa: {{ $inutilization->next_retry_at->format('d/m/Y H:i:s') }}</small>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Informações Principais -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        Informações da Inutilização
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>ID:</strong></td>
                                            <td>{{ $inutilization->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Série:</strong></td>
                                            <td><span class="badge badge-primary">{{ $inutilization->series }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Faixa:</strong></td>
                                            <td>
                                                @if($inutilization->numero_inicial == $inutilization->numero_final)
                                                    <span class="badge badge-info">{{ $inutilization->numero_inicial }}</span>
                                                @else
                                                    <span class="badge badge-info">{{ $inutilization->numero_inicial }} - {{ $inutilization->numero_final }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Quantidade:</strong></td>
                                            <td><span class="badge badge-success">{{ $inutilization->getQuantidadeAttribute() }}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>{!! $inutilization->getStatusFormattedAttribute() !!}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Usuário:</strong></td>
                                            <td>
                                                <i class="fas fa-user mr-1"></i>
                                                {{ $inutilization->user->name ?? 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Criada em:</strong></td>
                                            <td>
                                                <i class="fas fa-calendar mr-1"></i>
                                                {{ $inutilization->created_at->format('d/m/Y H:i:s') }}
                                            </td>
                                        </tr>
                                        @if($inutilization->authorized_at)
                                        <tr>
                                            <td><strong>Autorizada em:</strong></td>
                                            <td>
                                                <i class="fas fa-check mr-1"></i>
                                                {{ $inutilization->authorized_at->format('d/m/Y H:i:s') }}
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-server mr-2"></i>
                                        Informações da SEFAZ
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td><strong>Protocolo:</strong></td>
                                            <td>
                                                @if($inutilization->protocol)
                                                    <code>{{ $inutilization->protocol }}</code>
                                                @else
                                                    <span class="text-muted">Não disponível</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tentativas:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $inutilization->retry_count > 3 ? 'danger' : ($inutilization->retry_count > 1 ? 'warning' : 'success') }}">
                                                    {{ $inutilization->retry_count }}/5
                                                </span>
                                            </td>
                                        </tr>
                                        @if($inutilization->next_retry_at)
                                        <tr>
                                            <td><strong>Próxima tentativa:</strong></td>
                                            <td>
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ $inutilization->next_retry_at->format('d/m/Y H:i:s') }}
                                            </td>
                                        </tr>
                                        @endif
                                        @if($inutilization->sefaz_error_code)
                                        <tr>
                                            <td><strong>Código do Erro:</strong></td>
                                            <td><code>{{ $inutilization->sefaz_error_code }}</code></td>
                                        </tr>
                                        @endif
                                        @if($inutilization->xml_path)
                                        <tr>
                                            <td><strong>XML:</strong></td>
                                            <td>
                                                @if(file_exists(storage_path('app/' . $inutilization->xml_path)))
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-file-code mr-1"></i>
                                                        Disponível
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-file-times mr-1"></i>
                                                        Não encontrado
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Justificativa -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-comment-alt mr-2"></i>
                                        Justificativa
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">{{ $inutilization->justificativa }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resposta da SEFAZ -->
                    @if($inutilization->sefaz_response)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-server mr-2"></i>
                                        Resposta da SEFAZ
                                    </h5>
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-toggle="collapse" data-target="#sefazResponse">
                                        <i class="fas fa-eye mr-1"></i>
                                        Ver/Ocultar
                                    </button>
                                </div>
                                <div class="collapse" id="sefazResponse">
                                    <div class="card-body">
                                        <pre class="bg-light p-3 rounded"><code>{{ $inutilization->sefaz_response }}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Mensagem de Erro Detalhada -->
                    @if($inutilization->sefaz_error_message && $inutilization->hasError())
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        Detalhes do Erro
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Mensagem:</strong> {{ $inutilization->sefaz_error_message }}</p>
                                    @if($inutilization->sefaz_error_code)
                                        <p><strong>Código:</strong> <code>{{ $inutilization->sefaz_error_code }}</code></p>
                                    @endif
                                    <p class="mb-0"><strong>Tentativas realizadas:</strong> {{ $inutilization->retry_count }}/5</p>
                                    
                                    @if($inutilization->canBeReprocessed())
                                        <div class="mt-3">
                                            <form method="POST" action="{{ route('inutilizations.reprocess', $inutilization) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-warning" onclick="return confirm('Deseja tentar reprocessar esta inutilização?')">
                                                    <i class="fas fa-redo mr-1"></i>
                                                    Tentar Novamente
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
pre {
    max-height: 400px;
    overflow-y: auto;
    font-size: 0.875rem;
}

.table td {
    padding: 0.5rem 0.75rem;
}

.badge {
    font-size: 0.875em;
}

code {
    color: #e83e8c;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh para inutilizações pendentes
    @if($inutilization->isPending())
        setTimeout(function() {
            location.reload();
        }, 30000); // Refresh a cada 30 segundos
    @endif
    
    // Tooltip para badges
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
@endpush