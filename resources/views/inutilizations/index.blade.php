@extends('layouts.app')

@section('title', 'Inutilizações de NFC-e')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">
            <i class="fas fa-ban mr-2"></i>
            Inutilizações de NFC-e
        </h1>
        <x-button href="{{ route('inutilizations.create') }}" variant="primary">
            <i class="fas fa-plus mr-1"></i>
            Nova Inutilização
        </x-button>
    </div>
                
    <x-card>
        <x-slot name="header">
            <h5 class="card-title mb-0">Filtros</h5>
        </x-slot>
        
        <form method="GET" action="{{ route('inutilizations.index') }}" class="row g-3">
            <div class="col-md-3">
                <x-form-input 
                    type="select" 
                    name="status" 
                    label="Status" 
                    value="{{ request('status') }}" 
                    size="sm">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="authorized" {{ request('status') === 'authorized' ? 'selected' : '' }}>Autorizada</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeitada</option>
                    <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Erro</option>
                </x-form-input>
            </div>
            
            <div class="col-md-2">
                <x-form-input 
                    type="text" 
                    name="series" 
                    label="Série" 
                    value="{{ request('series') }}" 
                    placeholder="Ex: 001" 
                    maxlength="3" 
                    size="sm" />
            </div>
            
            <div class="col-md-2">
                <x-form-input 
                    type="date" 
                    name="date_from" 
                    label="De" 
                    value="{{ request('date_from') }}" 
                    size="sm" />
            </div>
            
            <div class="col-md-2">
                <x-form-input 
                    type="date" 
                    name="date_to" 
                    label="Até" 
                    value="{{ request('date_to') }}" 
                    size="sm" />
            </div>
            
            <div class="col-md-3 d-flex align-items-end gap-2">
                <x-button type="submit" variant="secondary" size="sm">
                    <i class="fas fa-search mr-1"></i>
                    Filtrar
                </x-button>
                
                <x-button href="{{ route('inutilizations.index') }}" variant="outline-secondary" size="sm">
                    <i class="fas fa-times mr-1"></i>
                    Limpar
                </x-button>
            </div>
        </form>
    </x-card>
                    
    <!-- Tabela -->
    <x-data-table>
        <x-slot name="headers">
            <th>ID</th>
            <th>Série</th>
            <th>Faixa</th>
            <th>Quantidade</th>
            <th>Justificativa</th>
            <th>Status</th>
            <th>Usuário</th>
            <th>Data/Hora</th>
            <th>Ações</th>
        </x-slot>
        
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
                        <x-button 
                            href="{{ route('inutilizations.show', $inutilization) }}" 
                            variant="outline-info" 
                            size="sm" 
                            title="Visualizar">
                            <i class="fas fa-eye"></i>
                        </x-button>
                        
                        @if($inutilization->xml_path)
                            <x-button 
                                href="{{ route('inutilizations.download-xml', $inutilization) }}" 
                                variant="outline-success" 
                                size="sm" 
                                title="Baixar XML">
                                <i class="fas fa-download"></i>
                            </x-button>
                        @endif
                        
                        @if(($inutilization->hasError() || $inutilization->isPending()) && $inutilization->retry_count < 5)
                            <form method="POST" action="{{ route('inutilizations.retry', $inutilization) }}" 
                                  class="d-inline" onsubmit="return confirm('Deseja reprocessar esta inutilização?')">
                                @csrf
                                <x-button 
                                    type="submit" 
                                    variant="outline-warning" 
                                    size="sm" 
                                    title="Tentar Novamente">
                                    <i class="fas fa-redo"></i>
                                </x-button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
        @endforelse
        
        <x-slot name="empty">
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Nenhuma inutilização encontrada.</p>
                <x-button href="{{ route('inutilizations.create') }}" variant="primary">
                    <i class="fas fa-plus mr-1"></i>
                    Criar primeira inutilização
                </x-button>
            </div>
        </x-slot>
        
        <x-slot name="footer">
            @if($inutilizations->hasPages())
                <div class="d-flex justify-content-center">
                    {{ $inutilizations->links() }}
                </div>
            @endif
        </x-slot>
    </x-data-table>
                    
    <!-- Estatísticas -->
    <div class="row mt-4">
        <div class="col-12">
            <x-card variant="light">
                <x-slot name="header">
                    <h6 class="card-title mb-0">Resumo</h6>
                </x-slot>
                
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
            </x-card>
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