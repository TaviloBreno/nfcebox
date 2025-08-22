@extends('layouts.app')

@section('title', 'Inutilizações de NFC-e')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-ban mr-2"></i>
                        Inutilizações de NFC-e
                    </h3>
                    <a href="{{ route('inutilizations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i>
                        Nova Inutilização
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <form method="GET" action="{{ route('inutilizations.index') }}" class="form-inline">
                                <div class="form-group mr-3">
                                    <label for="status" class="mr-2">Status:</label>
                                    <select name="status" id="status" class="form-control form-control-sm">
                                        <option value="">Todos</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                                        <option value="authorized" {{ request('status') === 'authorized' ? 'selected' : '' }}>Autorizada</option>
                                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeitada</option>
                                        <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Erro</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mr-3">
                                    <label for="series" class="mr-2">Série:</label>
                                    <input type="text" name="series" id="series" class="form-control form-control-sm" 
                                           value="{{ request('series') }}" placeholder="Ex: 001" maxlength="3">
                                </div>
                                
                                <div class="form-group mr-3">
                                    <label for="date_from" class="mr-2">De:</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" 
                                           value="{{ request('date_from') }}">
                                </div>
                                
                                <div class="form-group mr-3">
                                    <label for="date_to" class="mr-2">Até:</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" 
                                           value="{{ request('date_to') }}">
                                </div>
                                
                                <button type="submit" class="btn btn-sm btn-secondary mr-2">
                                    <i class="fas fa-search mr-1"></i>
                                    Filtrar
                                </button>
                                
                                <a href="{{ route('inutilizations.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times mr-1"></i>
                                    Limpar
                                </a>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Tabela -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Série</th>
                                    <th>Faixa</th>
                                    <th>Quantidade</th>
                                    <th>Justificativa</th>
                                    <th>Status</th>
                                    <th>Usuário</th>
                                    <th>Data/Hora</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inutilizations as $inutilization)
                                    <tr>
                                        <td>{{ $inutilization->id }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $inutilization->series }}</span>
                                        </td>
                                        <td>{{ $inutilization->faixa }}</td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $inutilization->quantidade }}</span>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                                  title="{{ $inutilization->justificativa }}">
                                                {{ $inutilization->justificativa }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $inutilization->status_class }}">
                                                {{ $inutilization->status_label }}
                                            </span>
                                            @if($inutilization->retry_count > 0)
                                                <small class="text-muted d-block">
                                                    {{ $inutilization->retry_count }} tentativa(s)
                                                </small>
                                            @endif
                                        </td>
                                        <td>{{ $inutilization->user->name ?? 'N/A' }}</td>
                                        <td>
                                            <small>
                                                {{ $inutilization->created_at->format('d/m/Y H:i') }}
                                                @if($inutilization->authorized_at)
                                                    <br>
                                                    <span class="text-success">
                                                        Autorizada: {{ $inutilization->authorized_at->format('d/m/Y H:i') }}
                                                    </span>
                                                @endif
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('inutilizations.show', $inutilization) }}" 
                                                   class="btn btn-sm btn-outline-info" title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($inutilization->xml_path)
                                                    <a href="{{ route('inutilizations.download-xml', $inutilization) }}" 
                                                       class="btn btn-sm btn-outline-success" title="Baixar XML">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                                
                                                @if(($inutilization->hasError() || $inutilization->isPending()) && $inutilization->retry_count < 5)
                                                    <form method="POST" action="{{ route('inutilizations.retry', $inutilization) }}" 
                                                          class="d-inline" onsubmit="return confirm('Deseja reprocessar esta inutilização?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Tentar Novamente">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Nenhuma inutilização encontrada.</p>
                                            <a href="{{ route('inutilizations.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus mr-1"></i>
                                                Criar primeira inutilização
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    @if($inutilizations->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $inutilizations->links() }}
                        </div>
                    @endif
                    
                    <!-- Estatísticas -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Resumo</h6>
                                    <div class="row text-center">
                                        <div class="col-md-3">
                                            <div class="text-primary">
                                                <i class="fas fa-list-ol fa-2x"></i>
                                                <h4 class="mt-2">{{ $inutilizations->total() }}</h4>
                                                <small>Total</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-success">
                                                <i class="fas fa-check-circle fa-2x"></i>
                                                <h4 class="mt-2">{{ $inutilizations->where('status', 'authorized')->count() }}</h4>
                                                <small>Autorizadas</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-warning">
                                                <i class="fas fa-clock fa-2x"></i>
                                                <h4 class="mt-2">{{ $inutilizations->where('status', 'pending')->count() }}</h4>
                                                <small>Pendentes</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-danger">
                                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                                                <h4 class="mt-2">{{ $inutilizations->whereIn('status', ['rejected', 'error'])->count() }}</h4>
                                                <small>Com Erro</small>
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
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh para inutilizações pendentes
    @if($inutilizations->where('status', 'pending')->count() > 0)
        setTimeout(function() {
            location.reload();
        }, 30000); // Refresh a cada 30 segundos
    @endif
    
    // Tooltip para justificativas truncadas
    $('[title]').tooltip();
});
</script>
@endpush