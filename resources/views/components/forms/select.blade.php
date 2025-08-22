@props([
    'name' => '',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'help' => null,
    'error' => null,
    'class' => '',
    'containerClass' => '',
    'labelClass' => '',
    'selectClass' => ''
])

@php
    $selectId = $name ? $name : 'select_' . uniqid();
    $hasError = $error || $errors->has($name);
    $errorMessage = $error ?: ($errors->has($name) ? $errors->first($name) : null);
    
    $selectClasses = 'form-select';
    if ($hasError) {
        $selectClasses .= ' is-invalid';
    }
    if ($selectClass) {
        $selectClasses .= ' ' . $selectClass;
    }
    
    $selectedValue = old($name, $selected);
@endphp

<div class="mb-3 {{ $containerClass }}">
    @if($label)
        <label for="{{ $selectId }}" class="form-label {{ $labelClass }}">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    <select 
        id="{{ $selectId }}"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        class="{{ $selectClasses }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        @if($multiple) multiple @endif
        {{ $attributes }}
    >
        @if($placeholder && !$multiple)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $value => $text)
            @php
                $isSelected = false;
                if ($multiple && is_array($selectedValue)) {
                    $isSelected = in_array($value, $selectedValue);
                } else {
                    $isSelected = (string) $value === (string) $selectedValue;
                }
            @endphp
            <option value="{{ $value }}" @if($isSelected) selected @endif>
                {{ $text }}
            </option>
        @endforeach
    </select>
    
    @if($help)
        <div class="form-text">{{ $help }}</div>
    @endif
    
    @if($errorMessage)
        <div class="invalid-feedback d-block">
            {{ $errorMessage }}
        </div>
    @endif
</div>