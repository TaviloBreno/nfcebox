@props([
    'id' => null,
    'title' => null,
    'size' => 'default', // sm, default, lg, xl, fullscreen
    'centered' => false,
    'scrollable' => false,
    'backdrop' => 'true', // true, false, static
    'keyboard' => true,
    'focus' => true,
    'show' => false,
    'fade' => true,
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => '',
    'closeButton' => true,
    'footer' => null,
    'actions' => null
])

@php
    $modalId = $id ?: 'modal_' . uniqid();
    
    $modalClasses = ['modal'];
    if ($fade) $modalClasses[] = 'fade';
    if ($show) $modalClasses[] = 'show';
    
    $dialogClasses = ['modal-dialog'];
    
    // Size classes
    switch ($size) {
        case 'sm':
            $dialogClasses[] = 'modal-sm';
            break;
        case 'lg':
            $dialogClasses[] = 'modal-lg';
            break;
        case 'xl':
            $dialogClasses[] = 'modal-xl';
            break;
        case 'fullscreen':
            $dialogClasses[] = 'modal-fullscreen';
            break;
    }
    
    if ($centered) $dialogClasses[] = 'modal-dialog-centered';
    if ($scrollable) $dialogClasses[] = 'modal-dialog-scrollable';
    
    $modalClassString = implode(' ', $modalClasses);
    $dialogClassString = implode(' ', $dialogClasses);
@endphp

<div 
    class="{{ $modalClassString }}" 
    id="{{ $modalId }}"
    tabindex="-1"
    aria-labelledby="{{ $modalId }}Label"
    aria-hidden="true"
    data-bs-backdrop="{{ $backdrop }}"
    @if(!$keyboard) data-bs-keyboard="false" @endif
    @if(!$focus) data-bs-focus="false" @endif
    {{ $attributes }}
>
    <div class="{{ $dialogClassString }}">
        <div class="modal-content">
            @if($title || $closeButton)
                <div class="modal-header {{ $headerClass }}">
                    @if($title)
                        <h5 class="modal-title" id="{{ $modalId }}Label">
                            {{ $title }}
                        </h5>
                    @endif
                    
                    @if($closeButton)
                        <button 
                            type="button" 
                            class="btn-close" 
                            data-bs-dismiss="modal" 
                            aria-label="Fechar"
                        ></button>
                    @endif
                </div>
            @endif
            
            <div class="modal-body {{ $bodyClass }}">
                {{ $slot }}
            </div>
            
            @if($footer || $actions)
                <div class="modal-footer {{ $footerClass }}">
                    @if($footer)
                        {{ $footer }}
                    @endif
                    
                    @if($actions)
                        {{ $actions }}
                    @else
                        <button 
                            type="button" 
                            class="btn btn-secondary" 
                            data-bs-dismiss="modal"
                        >
                            Fechar
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('{{ $modalId }}');
        if (modal) {
            // Initialize modal
            const bsModal = new bootstrap.Modal(modal);
            
            // Store modal instance for external access
            modal.bsModal = bsModal;
            
            @if($show)
                // Show modal if show prop is true
                bsModal.show();
            @endif
            
            // Custom events
            modal.addEventListener('show.bs.modal', function(event) {
                // Trigger custom event
                modal.dispatchEvent(new CustomEvent('modal:show', {
                    detail: { modal: bsModal, element: modal }
                }));
            });
            
            modal.addEventListener('shown.bs.modal', function(event) {
                // Focus first input if available
                const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
                if (firstInput) {
                    firstInput.focus();
                }
                
                // Trigger custom event
                modal.dispatchEvent(new CustomEvent('modal:shown', {
                    detail: { modal: bsModal, element: modal }
                }));
            });
            
            modal.addEventListener('hide.bs.modal', function(event) {
                // Trigger custom event
                modal.dispatchEvent(new CustomEvent('modal:hide', {
                    detail: { modal: bsModal, element: modal }
                }));
            });
            
            modal.addEventListener('hidden.bs.modal', function(event) {
                // Clear form data if present
                const forms = modal.querySelectorAll('form');
                forms.forEach(form => {
                    if (form.dataset.clearOnHide !== 'false') {
                        form.reset();
                        // Clear validation states
                        const invalidInputs = form.querySelectorAll('.is-invalid');
                        invalidInputs.forEach(input => input.classList.remove('is-invalid'));
                        const validInputs = form.querySelectorAll('.is-valid');
                        validInputs.forEach(input => input.classList.remove('is-valid'));
                    }
                });
                
                // Trigger custom event
                modal.dispatchEvent(new CustomEvent('modal:hidden', {
                    detail: { modal: bsModal, element: modal }
                }));
            });
        }
    });
</script>
@endpush