@extends('layouts.app')

@section('title', 'NFC-e - Gerenciamento')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        Gerenciamento de NFC-e
                    </h3>
                    <div class="d-flex gap-2">
                        <a href="{{ asset('docs/DANFE_DOCUMENTATION.md') }}" class="btn btn-outline-info btn-sm" target="_blank">
                            <i class="fas fa-info-circle me-1"></i>Documentação DANFE
                        </a>
                        <a href="{{ asset('docs/SYSTEM_FLOWS.md#emissão-de-nfce') }}" class="btn btn-outline-primary btn-sm" target="_blank">
                            <i class="fas fa-book me-1"></i>Guia de Uso
                        </a>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="card-body border-bottom">
                    <form method="GET" action="{{ route('nfce.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Data Inicial</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ request('start_date') }}">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">Data Final</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ request('end_date') }}">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="authorized" {{ request('status') == 'authorized' ? 'selected' : '' }}>Autorizada</option>
                                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Cancelada</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeitada</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="customer_id" class="form-label">Cliente</label>
                            <select class="form-select" id="customer_id" name="customer_id">
                                <option value="">Todos</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="sale_number" class="form-label">Número da Venda</label>
                            <input type="text" class="form-control" id="sale_number" name="sale_number" 
                                   value="{{ request('sale_number') }}" placeholder="Ex: 123">
                        </div>
                        
                        <div class="col-md-2">
                            <label for="access_key" class="form-label">Chave de Acesso</label>
                            <input type="text" class="form-control" id="access_key" name="access_key" 
                                   value="{{ request('access_key') }}" placeholder="Chave parcial">
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> Filtrar
                            </button>
                            <a href="{{ route('nfce.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Lista de NFC-e -->
                <div class="card-body p-0">
                    @if($sales->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Número</th>
                                        <th>Data/Hora</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Chave de Acesso</th>
                                        <th width="200">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sales as $sale)
                                        <tr>
                                            <td>
                                                <strong>{{ $sale->number ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">#{{ $sale->id }}</small>
                                            </td>
                                            <td>
                                                {{ $sale->created_at->format('d/m/Y H:i') }}
                                                @if($sale->authorized_at)
                                                    <br>
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle"></i>
                                                        {{ $sale->authorized_at->format('d/m/Y H:i') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($sale->customer)
                                                    {{ $sale->customer->name }}
                                                    @if($sale->customer->document)
                                                        <br>
                                                        <small class="text-muted">{{ $sale->customer->document }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Consumidor Final</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>R$ {{ number_format($sale->total, 2, ',', '.') }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $sale->items->count() }} item(s)</small>
                                            </td>
                                            <td>
                                                @switch($sale->status)
                                                    @case('authorized')
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle"></i> Autorizada
                                                        </span>
                                                        @break
                                                    @case('canceled')
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times-circle"></i> Cancelada
                                                        </span>
                                                        @if($sale->canceled_at)
                                                            <br>
                                                            <small class="text-muted">{{ $sale->canceled_at->format('d/m/Y H:i') }}</small>
                                                        @endif
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-exclamation-triangle"></i> Rejeitada
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($sale->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($sale->nfce_key)
                                                    <small class="font-monospace">{{ substr($sale->nfce_key, 0, 8) }}...{{ substr($sale->nfce_key, -8) }}</small>
                                                    <br>
                                                    <button class="btn btn-sm btn-outline-info" 
                                                            onclick="copyToClipboard('{{ $sale->nfce_key }}')"
                                                            title="Copiar chave completa">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($sale->status === 'authorized')
                                                        <!-- Visualizar XML -->
                                                        <a href="{{ route('nfce.xml', $sale) }}" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           target="_blank" title="Visualizar XML">
                                                            <i class="fas fa-code"></i>
                                                        </a>
                                                        
                                                        <!-- Baixar PDF -->
                                                        <a href="{{ route('nfce.download-pdf', $sale) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="Baixar PDF">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        
                                                        <!-- Reimprimir -->
                                                        <a href="{{ route('nfce.reprint', $sale) }}" 
                                                           class="btn btn-sm btn-outline-success" 
                                                           target="_blank" title="Reimprimir">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                        
                                                        <!-- Cancelar (se dentro do prazo) -->
                                                        @php
                                                            $canCancel = $sale->authorized_at && 
                                                                        $sale->authorized_at->diffInMinutes(now()) <= 30;
                                                        @endphp
                                                        
                                                        @if($canCancel)
                                                            <button class="btn btn-sm btn-outline-danger" 
                                                                    onclick="showCancelModal({{ $sale->id }})" 
                                                                    title="Cancelar NFC-e">
                                                                <i class="fas fa-ban"></i>
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação -->
                        <div class="card-footer">
                            {{ $sales->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Nenhuma NFC-e encontrada</h5>
                            <p class="text-muted">Não há notas fiscais que correspondam aos filtros aplicados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Cancelamento -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-ban text-danger me-2"></i>
                    Cancelar NFC-e
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Atenção!</strong> Esta ação não pode ser desfeita. A NFC-e será cancelada definitivamente.
                    </div>
                    
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Motivo do Cancelamento *</label>
                        <textarea class="form-control" id="cancellation_reason" name="reason" 
                                  rows="3" required minlength="15" maxlength="255"
                                  placeholder="Informe o motivo do cancelamento (mínimo 15 caracteres)"></textarea>
                        <div class="form-text">Mínimo 15 caracteres, máximo 255 caracteres.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i> Confirmar Cancelamento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentSaleId = null;

function showCancelModal(saleId) {
    currentSaleId = saleId;
    document.getElementById('cancellation_reason').value = '';
    new bootstrap.Modal(document.getElementById('cancelModal')).show();
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Você pode adicionar uma notificação aqui
        alert('Chave de acesso copiada para a área de transferência!');
    });
}

document.getElementById('cancelForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!currentSaleId) return;
    
    const reason = document.getElementById('cancellation_reason').value;
    
    if (reason.length < 15) {
        alert('O motivo deve ter pelo menos 15 caracteres.');
        return;
    }
    
    // Desabilita o botão para evitar duplo clique
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Cancelando...';
    
    fetch(`/nfce/${currentSaleId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('NFC-e cancelada com sucesso!');
            location.reload();
        } else {
            alert('Erro ao cancelar NFC-e: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro ao cancelar NFC-e. Tente novamente.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        bootstrap.Modal.getInstance(document.getElementById('cancelModal')).hide();
    });
});
</script>
@endpush