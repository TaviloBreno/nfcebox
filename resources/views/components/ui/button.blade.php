@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'href' => null,
    'target' => null,
    'disabled' => false,
    'loading' => false,
    'class' => ''
])

@php
    $baseClasses = 'btn';
    $variantClass = 'btn-' . $variant;
    $sizeClass = $size !== 'md' ? 'btn-' . $size : '';
    $classes = trim($baseClasses . ' ' . $variantClass . ' ' . $sizeClass . ' ' . $class);
    
    $tag = $href ? 'a' : 'button';
    $attributes = $href ? ['href' => $href] : ['type' => $type];
    
    if ($target && $href) {
        $attributes['target'] = $target;
    }
    
    if ($disabled) {
        $attributes['disabled'] = true;
        $classes .= ' disabled';
    }
@endphp

<{{ $tag }} 
    {{ $attributes->merge($attributes) }}
    class="{{ $classes }}"
    @if($loading) data-loading="true" @endif
>
    @if($loading)
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
    @elseif($icon && $iconPosition === 'left')
        <i class="{{ $icon }} me-2"></i>
    @endif
    
    {{ $slot }}
    
    @if($icon && $iconPosition === 'right')
        <i class="{{ $icon }} ms-2"></i>
    @endif
</{{ $tag }}>