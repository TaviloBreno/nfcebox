@props([
    'type' => 'text',
    'name' => '',
    'label' => null,
    'placeholder' => '',
    'value' => '',
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'error' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'class' => '',
    'containerClass' => '',
    'labelClass' => '',
    'inputClass' => ''
])

@php
    $inputId = $name ? $name : 'input_' . uniqid();
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?: ($errors->has($name) ? $errors->first($name) : null);
    
    $inputClasses = 'form-control';
    if ($hasError) {
        $inputClasses .= ' is-invalid';
    }
    if ($inputClass) {
        $inputClasses .= ' ' . $inputClass;
    }
@endphp

<div class="mb-3 {{ $containerClass }}">
    @if($label)
        <label for="{{ $inputId }}" class="form-label {{ $labelClass }}">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    @if($icon)
        <div class="input-group">
            @if($iconPosition === 'left')
                <span class="input-group-text">
                    <i class="{{ $icon }}"></i>
                </span>
            @endif
            
            <input 
                type="{{ $type }}"
                id="{{ $inputId }}"
                name="{{ $name }}"
                class="{{ $inputClasses }}"
                placeholder="{{ $placeholder }}"
                value="{{ old($name, $value) }}"
                @if($required) required @endif
                @if($disabled) disabled @endif
                @if($readonly) readonly @endif
                {{ $attributes }}
            >
            
            @if($iconPosition === 'right')
                <span class="input-group-text">
                    <i class="{{ $icon }}"></i>
                </span>
            @endif
        </div>
    @else
        <input 
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            class="{{ $inputClasses }}"
            placeholder="{{ $placeholder }}"
            value="{{ old($name, $value) }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes }}
        >
    @endif
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    
    @if($errorMessage)
        <div class="invalid-feedback d-block">
            {{ $errorMessage }}
        </div>
    @endif
</div>