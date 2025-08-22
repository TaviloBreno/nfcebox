@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'help' => null,
    'prepend' => null,
    'append' => null,
    'rows' => null, // for textarea
    'options' => [], // for select
    'multiple' => false, // for select
    'size' => 'default', // sm, default, lg
    'containerClass' => '',
    'labelClass' => '',
    'inputClass' => ''
])

@php
    $inputId = $name . '_' . uniqid();
    $hasError = $errors->has($name);
    $oldValue = old($name, $value);
    
    $inputClasses = [];
    
    // Base classes based on input type
    if ($type === 'select') {
        $inputClasses[] = 'form-select';
    } else {
        $inputClasses[] = 'form-control';
    }
    
    // Size classes
    if ($size === 'sm') {
        $inputClasses[] = $type === 'select' ? 'form-select-sm' : 'form-control-sm';
    } elseif ($size === 'lg') {
        $inputClasses[] = $type === 'select' ? 'form-select-lg' : 'form-control-lg';
    }
    
    // Error state
    if ($hasError) {
        $inputClasses[] = 'is-invalid';
    }
    
    // Custom classes
    if ($inputClass) {
        $inputClasses[] = $inputClass;
    }
    
    $inputClassString = implode(' ', $inputClasses);
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
    
    @if($prepend || $append)
        <div class="input-group">
            @if($prepend)
                <span class="input-group-text">{{ $prepend }}</span>
            @endif
    @endif
    
    @if($type === 'textarea')
        <textarea
            id="{{ $inputId }}"
            name="{{ $name }}"
            class="{{ $inputClassString }}"
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            @if($rows) rows="{{ $rows }}" @endif
            {{ $attributes }}
        >{{ $oldValue }}</textarea>
    @elseif($type === 'select')
        <select
            id="{{ $inputId }}"
            name="{{ $name }}{{ $multiple ? '[]' : '' }}"
            class="{{ $inputClassString }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($multiple) multiple @endif
            {{ $attributes }}
        >
            @if(!$multiple && !$required)
                <option value="">Selecione...</option>
            @endif
            @foreach($options as $optionValue => $optionLabel)
                <option 
                    value="{{ $optionValue }}"
                    @if(
                        ($multiple && is_array($oldValue) && in_array($optionValue, $oldValue)) ||
                        (!$multiple && $oldValue == $optionValue)
                    ) selected @endif
                >
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @else
        <input
            id="{{ $inputId }}"
            name="{{ $name }}"
            type="{{ $type }}"
            class="{{ $inputClassString }}"
            @if($oldValue !== null) value="{{ $oldValue }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes }}
        >
    @endif
    
    @if($prepend || $append)
            @if($append)
                <span class="input-group-text">{{ $append }}</span>
            @endif
        </div>
    @endif
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>