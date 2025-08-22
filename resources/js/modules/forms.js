/**
 * Forms Module
 * Handles form validation, submission and interactions
 */

class FormManager {
    constructor() {
        this.forms = document.querySelectorAll('form');
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initValidation();
        this.initFileUploads();
        this.initSelectComponents();
    }
    
    bindEvents() {
        this.forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                this.handleFormSubmit(e, form);
            });
        });
        
        // Handle input changes for real-time validation
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', (e) => {
                this.validateField(e.target);
            });
            
            input.addEventListener('input', (e) => {
                this.clearFieldError(e.target);
            });
        });
    }
    
    handleFormSubmit(event, form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        
        // Add loading state
        if (submitBtn) {
            this.setButtonLoading(submitBtn, true);
        }
        
        // Validate form before submission
        if (!this.validateForm(form)) {
            event.preventDefault();
            if (submitBtn) {
                this.setButtonLoading(submitBtn, false);
            }
            return false;
        }
        
        // If form has data-ajax attribute, handle with AJAX
        if (form.hasAttribute('data-ajax')) {
            event.preventDefault();
            this.submitFormAjax(form);
        }
    }
    
    validateForm(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            if (!this.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(field) {
        const value = field.value.trim();
        const fieldName = field.name || field.id;
        let isValid = true;
        let errorMessage = '';
        
        // Clear previous errors
        this.clearFieldError(field);
        
        // Required validation
        if (field.hasAttribute('required') && !value) {
            errorMessage = `${this.getFieldLabel(field)} é obrigatório.`;
            isValid = false;
        }
        
        // Email validation
        if (field.type === 'email' && value && !this.isValidEmail(value)) {
            errorMessage = 'Por favor, insira um email válido.';
            isValid = false;
        }
        
        // CNPJ validation
        if (field.hasAttribute('data-cnpj') && value && !this.isValidCNPJ(value)) {
            errorMessage = 'Por favor, insira um CNPJ válido.';
            isValid = false;
        }
        
        // CPF validation
        if (field.hasAttribute('data-cpf') && value && !this.isValidCPF(value)) {
            errorMessage = 'Por favor, insira um CPF válido.';
            isValid = false;
        }
        
        // Phone validation
        if (field.hasAttribute('data-phone') && value && !this.isValidPhone(value)) {
            errorMessage = 'Por favor, insira um telefone válido.';
            isValid = false;
        }
        
        // Password confirmation
        if (field.hasAttribute('data-confirm')) {
            const confirmField = document.querySelector(`[name="${field.getAttribute('data-confirm')}"]`);
            if (confirmField && value !== confirmField.value) {
                errorMessage = 'As senhas não coincidem.';
                isValid = false;
            }
        }
        
        // Min length validation
        const minLength = field.getAttribute('minlength');
        if (minLength && value && value.length < parseInt(minLength)) {
            errorMessage = `${this.getFieldLabel(field)} deve ter pelo menos ${minLength} caracteres.`;
            isValid = false;
        }
        
        // Max length validation
        const maxLength = field.getAttribute('maxlength');
        if (maxLength && value && value.length > parseInt(maxLength)) {
            errorMessage = `${this.getFieldLabel(field)} deve ter no máximo ${maxLength} caracteres.`;
            isValid = false;
        }
        
        if (!isValid) {
            this.showFieldError(field, errorMessage);
        }
        
        return isValid;
    }
    
    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        let errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'invalid-feedback';
            field.parentNode.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
    
    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorElement = field.parentNode.querySelector('.invalid-feedback');
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
    
    getFieldLabel(field) {
        const label = document.querySelector(`label[for="${field.id}"]`);
        return label ? label.textContent.replace('*', '').trim() : field.name || 'Campo';
    }
    
    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.classList.add('btn-loading');
            button.setAttribute('data-original-text', button.textContent);
            button.textContent = 'Carregando...';
        } else {
            button.disabled = false;
            button.classList.remove('btn-loading');
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.textContent = originalText;
                button.removeAttribute('data-original-text');
            }
        }
    }
    
    async submitFormAjax(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        try {
            const response = await fetch(form.action, {
                method: form.method || 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                this.showSuccess(result.message || 'Operação realizada com sucesso!');
                
                // Reset form if specified
                if (form.hasAttribute('data-reset-on-success')) {
                    form.reset();
                }
                
                // Redirect if specified
                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                }
            } else {
                this.showError(result.message || 'Ocorreu um erro ao processar a solicitação.');
                
                // Show field errors if available
                if (result.errors) {
                    this.showFieldErrors(form, result.errors);
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            this.showError('Erro de conexão. Tente novamente.');
        } finally {
            if (submitBtn) {
                this.setButtonLoading(submitBtn, false);
            }
        }
    }
    
    showFieldErrors(form, errors) {
        Object.keys(errors).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field && errors[fieldName].length > 0) {
                this.showFieldError(field, errors[fieldName][0]);
            }
        });
    }
    
    showSuccess(message) {
        this.showAlert(message, 'success');
    }
    
    showError(message) {
        this.showAlert(message, 'danger');
    }
    
    showAlert(message, type = 'info') {
        const alertContainer = document.querySelector('.alert-container') || document.body;
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        alertContainer.insertBefore(alert, alertContainer.firstChild);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    initFileUploads() {
        const fileInputs = document.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                this.handleFileUpload(e.target);
            });
        });
    }
    
    handleFileUpload(input) {
        const files = input.files;
        const preview = input.parentNode.querySelector('.file-preview');
        
        if (preview && files.length > 0) {
            preview.innerHTML = '';
            
            Array.from(files).forEach(file => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <i class="fas fa-file"></i>
                    <span>${file.name}</span>
                    <small>(${this.formatFileSize(file.size)})</small>
                `;
                preview.appendChild(fileItem);
            });
        }
    }
    
    initSelectComponents() {
        // Initialize custom select components if needed
        const customSelects = document.querySelectorAll('.custom-select');
        customSelects.forEach(select => {
            this.initCustomSelect(select);
        });
    }
    
    initCustomSelect(select) {
        // Custom select implementation
        // This would be expanded based on specific needs
    }
    
    // Validation helper methods
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    isValidCNPJ(cnpj) {
        cnpj = cnpj.replace(/[^\d]/g, '');
        
        if (cnpj.length !== 14) return false;
        
        // CNPJ validation algorithm
        let sum = 0;
        let weight = 2;
        
        for (let i = 11; i >= 0; i--) {
            sum += parseInt(cnpj.charAt(i)) * weight;
            weight = weight === 9 ? 2 : weight + 1;
        }
        
        let digit1 = sum % 11 < 2 ? 0 : 11 - (sum % 11);
        
        sum = 0;
        weight = 2;
        
        for (let i = 12; i >= 0; i--) {
            sum += parseInt(cnpj.charAt(i)) * weight;
            weight = weight === 9 ? 2 : weight + 1;
        }
        
        let digit2 = sum % 11 < 2 ? 0 : 11 - (sum % 11);
        
        return parseInt(cnpj.charAt(12)) === digit1 && parseInt(cnpj.charAt(13)) === digit2;
    }
    
    isValidCPF(cpf) {
        cpf = cpf.replace(/[^\d]/g, '');
        
        if (cpf.length !== 11) return false;
        
        // CPF validation algorithm
        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += parseInt(cpf.charAt(i)) * (10 - i);
        }
        
        let digit1 = sum % 11 < 2 ? 0 : 11 - (sum % 11);
        
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(cpf.charAt(i)) * (11 - i);
        }
        
        let digit2 = sum % 11 < 2 ? 0 : 11 - (sum % 11);
        
        return parseInt(cpf.charAt(9)) === digit1 && parseInt(cpf.charAt(10)) === digit2;
    }
    
    isValidPhone(phone) {
        const phoneRegex = /^\(?\d{2}\)?[\s-]?\d{4,5}[\s-]?\d{4}$/;
        return phoneRegex.test(phone.replace(/[^\d]/g, ''));
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new FormManager();
});

// Export for Node.js environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FormManager;
}

// ES6 export
export { FormManager };