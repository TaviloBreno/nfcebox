@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Gerenciamento de Certificados A1</h1>
                <a href="{{ route('configurations.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Voltar
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Upload de Certificado -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Upload de Certificado A1</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Formatos aceitos:</strong> .pfx, .p12<br>
                                <strong>Tamanho máximo:</strong> 5MB<br>
                                <strong>Segurança:</strong> Arquivos são armazenados de forma criptografada
                            </div>

                            <form action="{{ route('configurations.certificates.upload') }}" method="POST" enctype="multipart/form-data" id="certificateForm">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="certificate_file" class="form-label">Arquivo do Certificado *</label>
                                    <input type="file" 
                                           class="form-control @error('certificate_file') is-invalid @enderror" 
                                           id="certificate_file" 
                                           name="certificate_file" 
                                           accept=".pfx,.p12"
                                           required>
                                    @error('certificate_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Selecione o arquivo do certificado digital (.pfx ou .p12)
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="certificate_password" class="form-label">Senha do Certificado *</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control @error('certificate_password') is-invalid @enderror" 
                                               id="certificate_password" 
                                               name="certificate_password" 
                                               placeholder="Digite a senha do certificado"
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                        </button>
                                        @error('certificate_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">
                                        A senha será armazenada de forma criptografada
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="certificate_alias" class="form-label">Nome/Alias do Certificado *</label>
                                    <input type="text" 
                                           class="form-control @error('certificate_alias') is-invalid @enderror" 
                                           id="certificate_alias" 
                                           name="certificate_alias" 
                                           placeholder="Ex: Certificado Principal, Certificado Backup"
                                           maxlength="100"
                                           required>
                                    @error('certificate_alias')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Nome para identificar este certificado no sistema
                                    </small>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="set_as_default" name="set_as_default" value="1">
                                    <label class="form-check-label" for="set_as_default">
                                        Definir como certificado padrão
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Este certificado será usado por padrão para emissão de NFCe
                                    </small>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                                        <i class="fas fa-upload me-2"></i>Fazer Upload do Certificado
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Lista de Certificados -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>Certificados Instalados</h5>
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshCertificates()">
                                <i class="fas fa-sync-alt me-1"></i>Atualizar
                            </button>
                        </div>
                        <div class="card-body">
                            @if(isset($certificates) && $certificates->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Alias</th>
                                                <th>Status</th>
                                                <th>Validade</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($certificates as $certificate)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            @if($certificate->is_default)
                                                                <i class="fas fa-star text-warning me-2" title="Certificado Padrão"></i>
                                                            @endif
                                                            <div>
                                                                <strong>{{ $certificate->alias }}</strong>
                                                                @if($certificate->subject)
                                                                    <br><small class="text-muted">{{ $certificate->subject }}</small>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($certificate->is_valid)
                                                            <span class="badge bg-success">Válido</span>
                                                        @else
                                                            <span class="badge bg-danger">Inválido</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($certificate->expires_at)
                                                            <small class="{{ $certificate->expires_at->isPast() ? 'text-danger' : ($certificate->expires_at->diffInDays() < 30 ? 'text-warning' : 'text-success') }}">
                                                                {{ $certificate->expires_at->format('d/m/Y') }}
                                                                <br>({{ $certificate->expires_at->diffForHumans() }})
                                                            </small>
                                                        @else
                                                            <small class="text-muted">N/A</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            @if(!$certificate->is_default)
                                                                <button type="button" 
                                                                        class="btn btn-outline-warning" 
                                                                        onclick="setAsDefault({{ $certificate->id }})"
                                                                        title="Definir como Padrão">
                                                                    <i class="fas fa-star"></i>
                                                                </button>
                                                            @endif
                                                            <button type="button" 
                                                                    class="btn btn-outline-info" 
                                                                    onclick="viewCertificateDetails({{ $certificate->id }})"
                                                                    title="Ver Detalhes">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger" 
                                                                    onclick="deleteCertificate({{ $certificate->id }})"
                                                                    title="Excluir">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Nenhum certificado instalado</h5>
                                    <p class="text-muted">Faça o upload do seu primeiro certificado A1 para começar a emitir NFCe.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informações de Segurança -->
            <div class="row">
                <div class="col-12">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Informações de Segurança</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6><i class="fas fa-lock me-2"></i>Armazenamento Seguro</h6>
                                    <p class="small text-muted">
                                        Os certificados são armazenados em diretório protegido com permissões restritas.
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <h6><i class="fas fa-key me-2"></i>Criptografia</h6>
                                    <p class="small text-muted">
                                        As senhas dos certificados são criptografadas usando algoritmos seguros.
                                    </p>
                                </div>
                                <div class="col-md-4">
                                    <h6><i class="fas fa-user-shield me-2"></i>Acesso Restrito</h6>
                                    <p class="small text-muted">
                                        Apenas administradores podem gerenciar certificados digitais.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalhes do certificado -->
<div class="modal fade" id="certificateDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Certificado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="certificateDetailsContent">
                <!-- Conteúdo será carregado via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('certificate_password');
        const icon = document.getElementById('togglePasswordIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Validação do arquivo
    document.getElementById('certificate_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const allowedTypes = ['.pfx', '.p12'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(fileExtension)) {
                alert('Formato de arquivo não suportado. Use apenas arquivos .pfx ou .p12');
                e.target.value = '';
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) { // 5MB
                alert('Arquivo muito grande. O tamanho máximo é 5MB.');
                e.target.value = '';
                return;
            }
        }
    });

    // Submissão do formulário com loading
    document.getElementById('certificateForm').addEventListener('submit', function(e) {
        const uploadBtn = document.getElementById('uploadBtn');
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Fazendo Upload...';
        uploadBtn.disabled = true;
    });

    // Função para definir certificado como padrão
    function setAsDefault(certificateId) {
        if (confirm('Deseja definir este certificado como padrão?')) {
            fetch(`/configurations/certificates/${certificateId}/set-default`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao definir certificado como padrão: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar solicitação.');
            });
        }
    }

    // Função para excluir certificado
    function deleteCertificate(certificateId) {
        if (confirm('Tem certeza que deseja excluir este certificado? Esta ação não pode ser desfeita.')) {
            fetch(`/configurations/certificates/${certificateId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao excluir certificado: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao processar solicitação.');
            });
        }
    }

    // Função para ver detalhes do certificado
    function viewCertificateDetails(certificateId) {
        fetch(`/configurations/certificates/${certificateId}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('certificateDetailsContent').innerHTML = data.html;
                    new bootstrap.Modal(document.getElementById('certificateDetailsModal')).show();
                } else {
                    alert('Erro ao carregar detalhes do certificado: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao carregar detalhes.');
            });
    }

    // Função para atualizar lista de certificados
    function refreshCertificates() {
        location.reload();
    }
</script>
@endpush

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
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.025);
    }
    
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .alert {
        border-radius: 0.375rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .text-warning {
        color: #f0ad4e !important;
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
        
        .card-body {
            padding: 1rem;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start !important;
        }
        
        .d-flex.justify-content-between > a {
            margin-top: 1rem;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .btn-group-sm > .btn {
            padding: 0.125rem 0.25rem;
            font-size: 0.625rem;
        }
    }
</style>
@endpush