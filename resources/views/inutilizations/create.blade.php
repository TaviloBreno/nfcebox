@extends('layouts.app')

@section('title', 'Nova Inutilização')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-ban mr-2"></i>
                        Nova Inutilização de NFC-e
                    </h3>
                    <a href="{{ route('inutilizations.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Voltar
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Alertas de erro -->
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6><i class="fas fa-exclamation-triangle mr-1"></i> Erro na validação:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif
                    
                    <!-- Informações importantes -->
                    <div class="alert alert-info" role="alert">
                        <h6><i class="fas fa-info-circle mr-1"></i> Informações Importantes:</h6>
                        <ul class="mb-0">
                            <li>A inutilização é <strong>irreversível</strong> e impede o uso dos números da faixa especificada.</li>
                            <li>A justificativa deve ter pelo menos <strong>15 caracteres</strong> e ser clara e objetiva.</li>
                            <li>Verifique cuidadosamente a faixa de números antes de confirmar.</li>
                            <li>O processo será enviado automaticamente para a SEFAZ após a criação.</li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="{{ route('inutilizations.store') }}" id="inutilizationForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Série -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="series" class="required">
                                        <i class="fas fa-hashtag mr-1"></i>
                                        Série
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('series') is-invalid @enderror" 
                                           id="series" 
                                           name="series" 
                                           value="{{ old('series', '001') }}" 
                                           maxlength="3" 
                                           placeholder="Ex: 001"
                                           required>
                                    @error('series')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Série da NFC-e (máximo 3 caracteres)
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Número Inicial -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="numero_inicial" class="required">
                                        <i class="fas fa-play mr-1"></i>
                                        Número Inicial
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('numero_inicial') is-invalid @enderror" 
                                           id="numero_inicial" 
                                           name="numero_inicial" 
                                           value="{{ old('numero_inicial') }}" 
                                           min="1" 
                                           max="999999999" 
                                           placeholder="Ex: 1"
                                           required>
                                    @error('numero_inicial')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Primeiro número da faixa a ser inutilizada
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Número Final -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="numero_final" class="required">
                                        <i class="fas fa-stop mr-1"></i>
                                        Número Final
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('numero_final') is-invalid @enderror" 
                                           id="numero_final" 
                                           name="numero_final" 
                                           value="{{ old('numero_final') }}" 
                                           min="1" 
                                           max="999999999" 
                                           placeholder="Ex: 100"
                                           required>
                                    @error('numero_final')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Último número da faixa a ser inutilizada
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Resumo da Faixa -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card bg-light" id="rangePreview" style="display: none;">
                                    <div class="card-body py-2">
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <strong>Série:</strong>
                                                <span id="previewSeries" class="text-primary">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Faixa:</strong>
                                                <span id="previewRange" class="text-info">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Quantidade:</strong>
                                                <span id="previewQuantity" class="text-success">-</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Status:</strong>
                                                <span class="badge badge-warning">Será inutilizada</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Justificativa -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="justificativa" class="required">
                                        <i class="fas fa-comment-alt mr-1"></i>
                                        Justificativa
                                    </label>
                                    <textarea class="form-control @error('justificativa') is-invalid @enderror" 
                                              id="justificativa" 
                                              name="justificativa" 
                                              rows="4" 
                                              minlength="15" 
                                              maxlength="255" 
                                              placeholder="Descreva o motivo da inutilização desta faixa de números..."
                                              required>{{ old('justificativa') }}</textarea>
                                    @error('justificativa')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="d-flex justify-content-between">
                                        <small class="form-text text-muted">
                                            Mínimo 15 caracteres. Seja claro e objetivo sobre o motivo da inutilização.
                                        </small>
                                        <small class="form-text text-muted">
                                            <span id="charCount">0</span>/255 caracteres
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Confirmação -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="confirmacao" required>
                                    <label class="form-check-label" for="confirmacao">
                                        <strong>Confirmo que desejo inutilizar esta faixa de números</strong> e estou ciente de que esta ação é irreversível.
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botões -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('inutilizations.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times mr-1"></i>
                                        Cancelar
                                    </a>
                                    
                                    <button type="submit" class="btn btn-danger" id="submitBtn" disabled>
                                        <i class="fas fa-ban mr-1"></i>
                                        <span class="btn-text">Inutilizar Faixa</span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: red;
}

#rangePreview {
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn:disabled {
    cursor: not-allowed;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    const $series = $('#series');
    const $numeroInicial = $('#numero_inicial');
    const $numeroFinal = $('#numero_final');
    const $justificativa = $('#justificativa');
    const $confirmacao = $('#confirmacao');
    const $submitBtn = $('#submitBtn');
    const $rangePreview = $('#rangePreview');
    const $charCount = $('#charCount');
    
    // Atualiza contador de caracteres
    function updateCharCount() {
        const count = $justificativa.val().length;
        $charCount.text(count);
        
        if (count < 15) {
            $charCount.parent().removeClass('text-success').addClass('text-danger');
        } else if (count > 240) {
            $charCount.parent().removeClass('text-success').addClass('text-warning');
        } else {
            $charCount.parent().removeClass('text-danger text-warning').addClass('text-success');
        }
    }
    
    // Atualiza preview da faixa
    function updateRangePreview() {
        const series = $series.val();
        const inicial = parseInt($numeroInicial.val());
        const final = parseInt($numeroFinal.val());
        
        if (series && inicial && final && inicial <= final) {
            const quantidade = final - inicial + 1;
            const faixa = inicial === final ? inicial.toString() : `${inicial} - ${final}`;
            
            $('#previewSeries').text(series);
            $('#previewRange').text(faixa);
            $('#previewQuantity').text(quantidade);
            
            $rangePreview.slideDown();
        } else {
            $rangePreview.slideUp();
        }
    }
    
    // Valida se o formulário pode ser enviado
    function validateForm() {
        const series = $series.val().trim();
        const inicial = parseInt($numeroInicial.val());
        const final = parseInt($numeroFinal.val());
        const justificativa = $justificativa.val().trim();
        const confirmado = $confirmacao.is(':checked');
        
        const isValid = series.length > 0 && 
                       inicial > 0 && 
                       final > 0 && 
                       inicial <= final && 
                       justificativa.length >= 15 && 
                       confirmado;
        
        $submitBtn.prop('disabled', !isValid);
    }
    
    // Event listeners
    $series.on('input', function() {
        // Converte para maiúsculo e remove caracteres não numéricos
        let value = $(this).val().toUpperCase().replace(/[^0-9]/g, '');
        if (value.length > 3) value = value.substring(0, 3);
        $(this).val(value.padStart(3, '0'));
        
        updateRangePreview();
        validateForm();
    });
    
    $numeroInicial.on('input', function() {
        const value = parseInt($(this).val());
        if (value && $numeroFinal.val() && value > parseInt($numeroFinal.val())) {
            $numeroFinal.val(value);
        }
        
        updateRangePreview();
        validateForm();
    });
    
    $numeroFinal.on('input', function() {
        const value = parseInt($(this).val());
        if (value && $numeroInicial.val() && value < parseInt($numeroInicial.val())) {
            $numeroInicial.val(value);
        }
        
        updateRangePreview();
        validateForm();
    });
    
    $justificativa.on('input', function() {
        updateCharCount();
        validateForm();
    });
    
    $confirmacao.on('change', validateForm);
    
    // Submissão do formulário
    $('#inutilizationForm').on('submit', function(e) {
        const $spinner = $submitBtn.find('.spinner-border');
        const $btnText = $submitBtn.find('.btn-text');
        
        $submitBtn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $btnText.text('Processando...');
        
        // Confirmação final
        const series = $series.val();
        const inicial = $numeroInicial.val();
        const final = $numeroFinal.val();
        const quantidade = parseInt(final) - parseInt(inicial) + 1;
        
        if (!confirm(`Confirma a inutilização da série ${series}, números ${inicial} a ${final} (${quantidade} número(s))?\n\nEsta ação é IRREVERSÍVEL!`)) {
            e.preventDefault();
            $submitBtn.prop('disabled', false);
            $spinner.addClass('d-none');
            $btnText.text('Inutilizar Faixa');
            validateForm();
        }
    });
    
    // Inicialização
    updateCharCount();
    updateRangePreview();
    validateForm();
    
    // Auto-focus no primeiro campo
    $series.focus();
});
</script>
@endpush