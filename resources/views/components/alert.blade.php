@props([
    'type' => 'info', // success, danger, warning, info, primary, secondary, light, dark
    'title' => null,
    'dismissible' => true,
    'icon' => null,
    'autoHide' => false,
    'delay' => 5000, // milliseconds
    'position' => null, // fixed-top, fixed-bottom, sticky-top
    'containerClass' => ''
])

@php
    $alertClasses = ['alert', 'alert-' . $type];
    
    if ($dismissible) {
        $alertClasses[] = 'alert-dismissible';
        $alertClasses[] = 'fade';
        $alertClasses[] = 'show';
    }
    
    if ($position) {
        $alertClasses[] = $position;
    }
    
    $alertClassString = implode(' ', $alertClasses);
    
    // Default icons for each type
    $defaultIcons = [
        'success' => 'fas fa-check-circle',
        'danger' => 'fas fa-exclamation-triangle',
        'warning' => 'fas fa-exclamation-circle',
        'info' => 'fas fa-info-circle',
        'primary' => 'fas fa-info-circle',
        'secondary' => 'fas fa-info-circle',
        'light' => 'fas fa-info-circle',
        'dark' => 'fas fa-info-circle'
    ];
    
    $displayIcon = $icon ?: ($defaultIcons[$type] ?? null);
    $uniqueId = 'alert_' . uniqid();
@endphp

<div class="{{ $containerClass }}">
    <div 
        class="{{ $alertClassString }}" 
        id="{{ $uniqueId }}"
        role="alert"
        @if($autoHide) data-auto-hide="{{ $delay }}" @endif
        {{ $attributes }}
    >
        <div class="d-flex align-items-start">
            @if($displayIcon)
                <div class="me-3 mt-1">
                    <i class="{{ $displayIcon }}"></i>
                </div>
            @endif
            
            <div class="flex-grow-1">
                @if($title)
                    <h6 class="alert-heading mb-1">{{ $title }}</h6>
                @endif
                
                <div class="alert-content">
                    {{ $slot }}
                </div>
            </div>
            
            @if($dismissible)
                <button 
                    type="button" 
                    class="btn-close" 
                    data-bs-dismiss="alert" 
                    aria-label="Fechar"
                ></button>
            @endif
        </div>
    </div>
</div>

@if($autoHide)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('{{ $uniqueId }}');
            if (alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, {{ $delay }});
            }
        });
    </script>
    @endpush
@endif