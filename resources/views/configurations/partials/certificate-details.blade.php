<div class="certificate-details">
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-primary mb-3"><i class="fas fa-certificate me-2"></i>Informações do Certificado</h6>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Alias:</label>
                <p class="mb-1">{{ $details['alias'] }}</p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Titular (Subject):</label>
                <p class="mb-1">{{ $details['subject'] ?: 'N/A' }}</p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Emissor (Issuer):</label>
                <p class="mb-1">{{ $details['issuer'] ?: 'N/A' }}</p>
            </div>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Status e Informações</h6>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Validade:</label>
                <p class="mb-1">
                    {{ $details['expires_at'] }}
                    @if($details['is_valid'])
                        <span class="badge bg-success ms-2">Válido</span>
                    @else
                        <span class="badge bg-danger ms-2">Expirado</span>
                    @endif
                </p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Status:</label>
                <p class="mb-1">
                    @if($details['is_default'])
                        <span class="badge bg-primary">Padrão</span>
                    @else
                        <span class="badge bg-secondary">Secundário</span>
                    @endif
                </p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Tamanho do Arquivo:</label>
                <p class="mb-1">{{ $details['file_size'] }}</p>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Data de Upload:</label>
                <p class="mb-1">{{ $details['created_at'] }}</p>
            </div>
        </div>
    </div>
    
    <hr>
    
    <div class="alert alert-info mb-0">
        <i class="fas fa-shield-alt me-2"></i>
        <strong>Segurança:</strong> A senha do certificado é armazenada de forma criptografada no banco de dados. 
        O arquivo do certificado é armazenado em diretório seguro com permissões restritas.
    </div>
</div>