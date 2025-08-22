/**
 * Modals Module
 * Handles modal dialogs and interactions
 */

class ModalManager {
    constructor() {
        this.modals = new Map();
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initBootstrapModals();
    }
    
    bindEvents() {
        // Handle modal triggers
        document.addEventListener('click', (e) => {
            const trigger = e.target.closest('[data-bs-toggle="modal"]');
            if (trigger) {
                this.handleModalTrigger(e, trigger);
            }
            
            // Handle confirmation dialogs
            const confirmTrigger = e.target.closest('[data-confirm]');
            if (confirmTrigger) {
                this.handleConfirmDialog(e, confirmTrigger);
            }
        });
        
        // Handle modal events
        document.addEventListener('shown.bs.modal', (e) => {
            this.onModalShown(e.target);
        });
        
        document.addEventListener('hidden.bs.modal', (e) => {
            this.onModalHidden(e.target);
        });
    }
    
    initBootstrapModals() {
        const modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(modalElement => {
            const modal = new bootstrap.Modal(modalElement);
            this.modals.set(modalElement.id, modal);
        });
    }
    
    handleModalTrigger(event, trigger) {
        const targetSelector = trigger.getAttribute('data-bs-target');
        const modalElement = document.querySelector(targetSelector);
        
        if (!modalElement) return;
        
        // Load content if URL is provided
        const url = trigger.getAttribute('data-url');
        if (url) {
            this.loadModalContent(modalElement, url);
        }
        
        // Set modal title if provided
        const title = trigger.getAttribute('data-title');
        if (title) {
            const titleElement = modalElement.querySelector('.modal-title');
            if (titleElement) {
                titleElement.textContent = title;
            }
        }
        
        // Pass data attributes to modal
        const dataAttributes = trigger.dataset;
        Object.keys(dataAttributes).forEach(key => {
            if (key.startsWith('modal')) {
                const modalKey = key.replace('modal', '').toLowerCase();
                modalElement.setAttribute(`data-${modalKey}`, dataAttributes[key]);
            }
        });
    }
    
    async loadModalContent(modalElement, url) {
        const modalBody = modalElement.querySelector('.modal-body');
        if (!modalBody) return;
        
        // Show loading state
        modalBody.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2 text-muted">Carregando conteúdo...</p>
            </div>
        `;
        
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (response.ok) {
                const content = await response.text();
                modalBody.innerHTML = content;
                
                // Initialize any components in the loaded content
                this.initModalContent(modalElement);
            } else {
                throw new Error('Falha ao carregar conteúdo');
            }
        } catch (error) {
            console.error('Error loading modal content:', error);
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erro ao carregar conteúdo. Tente novamente.
                </div>
            `;
        }
    }
    
    initModalContent(modalElement) {
        // Initialize forms in modal
        const forms = modalElement.querySelectorAll('form');
        forms.forEach(form => {
            if (window.formManager) {
                window.formManager.initForm(form);
            }
        });
        
        // Initialize other components as needed
        this.initModalComponents(modalElement);
    }
    
    initModalComponents(modalElement) {
        // Initialize tooltips
        const tooltips = modalElement.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
        
        // Initialize popovers
        const popovers = modalElement.querySelectorAll('[data-bs-toggle="popover"]');
        popovers.forEach(popover => {
            new bootstrap.Popover(popover);
        });
    }
    
    handleConfirmDialog(event, trigger) {
        event.preventDefault();
        
        const message = trigger.getAttribute('data-confirm');
        const title = trigger.getAttribute('data-confirm-title') || 'Confirmação';
        const confirmText = trigger.getAttribute('data-confirm-text') || 'Confirmar';
        const cancelText = trigger.getAttribute('data-cancel-text') || 'Cancelar';
        const variant = trigger.getAttribute('data-confirm-variant') || 'danger';
        
        this.showConfirmDialog({
            title,
            message,
            confirmText,
            cancelText,
            variant,
            onConfirm: () => {
                // Execute the original action
                if (trigger.tagName === 'A') {
                    window.location.href = trigger.href;
                } else if (trigger.tagName === 'BUTTON' && trigger.form) {
                    trigger.form.submit();
                } else if (trigger.onclick) {
                    trigger.onclick();
                }
            }
        });
    }
    
    showConfirmDialog(options) {
        const {
            title = 'Confirmação',
            message = 'Tem certeza?',
            confirmText = 'Confirmar',
            cancelText = 'Cancelar',
            variant = 'danger',
            onConfirm = () => {},
            onCancel = () => {}
        } = options;
        
        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="confirmModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                ${title}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                ${cancelText}
                            </button>
                            <button type="button" class="btn btn-${variant}" id="confirmButton">
                                ${confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing confirm modal
        const existingModal = document.getElementById('confirmModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        const modalElement = document.getElementById('confirmModal');
        const modal = new bootstrap.Modal(modalElement);
        
        // Handle confirm button click
        const confirmButton = modalElement.querySelector('#confirmButton');
        confirmButton.addEventListener('click', () => {
            modal.hide();
            onConfirm();
        });
        
        // Handle cancel
        modalElement.addEventListener('hidden.bs.modal', () => {
            modalElement.remove();
            onCancel();
        });
        
        modal.show();
    }
    
    showAlert(options) {
        const {
            title = 'Aviso',
            message = '',
            variant = 'info',
            confirmText = 'OK',
            onConfirm = () => {}
        } = options;
        
        const iconClass = {
            'success': 'fas fa-check-circle text-success',
            'danger': 'fas fa-exclamation-circle text-danger',
            'warning': 'fas fa-exclamation-triangle text-warning',
            'info': 'fas fa-info-circle text-info'
        }[variant] || 'fas fa-info-circle text-info';
        
        const modalHtml = `
            <div class="modal fade" id="alertModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="${iconClass} me-2"></i>
                                ${title}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-${variant}" data-bs-dismiss="modal">
                                ${confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing alert modal
        const existingModal = document.getElementById('alertModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        const modalElement = document.getElementById('alertModal');
        const modal = new bootstrap.Modal(modalElement);
        
        // Handle modal close
        modalElement.addEventListener('hidden.bs.modal', () => {
            modalElement.remove();
            onConfirm();
        });
        
        modal.show();
    }
    
    onModalShown(modalElement) {
        // Focus first input in modal
        const firstInput = modalElement.querySelector('input, select, textarea');
        if (firstInput && !firstInput.disabled) {
            firstInput.focus();
        }
        
        // Add body class to prevent scrolling
        document.body.classList.add('modal-open');
    }
    
    onModalHidden(modalElement) {
        // Clean up any temporary data
        const tempAttributes = ['data-url', 'data-title'];
        tempAttributes.forEach(attr => {
            modalElement.removeAttribute(attr);
        });
        
        // Remove body class if no other modals are open
        const openModals = document.querySelectorAll('.modal.show');
        if (openModals.length === 0) {
            document.body.classList.remove('modal-open');
        }
    }
    
    // Public methods
    show(modalId) {
        const modal = this.modals.get(modalId);
        if (modal) {
            modal.show();
        }
    }
    
    hide(modalId) {
        const modal = this.modals.get(modalId);
        if (modal) {
            modal.hide();
        }
    }
    
    toggle(modalId) {
        const modal = this.modals.get(modalId);
        if (modal) {
            modal.toggle();
        }
    }
    
    isOpen(modalId) {
        const modalElement = document.getElementById(modalId);
        return modalElement ? modalElement.classList.contains('show') : false;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ModalManager();
});

// Export for Node.js environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ModalManager;
}

// ES6 export
export { ModalManager };