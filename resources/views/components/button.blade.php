@props([
    'variant' => 'primary', // primary, secondary, success, danger, warning, info, light, dark, outline-*
    'size' => 'default', // sm, default, lg
    'type' => 'button', // button, submit, reset
    'href' => null, // if provided, renders as link
    'target' => null, // for links
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'loading' => false,
    'loadingText' => 'Carregando...',
    'disabled' => false,
    'block' => false, // full width
    'tooltip' => null,
    'confirmMessage' => null, // shows confirmation dialog
    'badge' => null, // shows badge next to text
    'badgeVariant' => 'secondary'
])

@php
    $classes = ['btn'];
    
    // Variant classes
    $classes[] = 'btn-' . $variant;
    
    // Size classes
    if ($size === 'sm') {
        $classes[] = 'btn-sm';
    } elseif ($size === 'lg') {
        $classes[] = 'btn-lg';
    }
    
    // Block class
    if ($block) {
        $classes[] = 'w-100';
    }
    
    $classString = implode(' ', $classes);
    
    // Determine tag
    $tag = $href ? 'a' : 'button';
    
    // Prepare attributes
    $commonAttributes = [
        'class' => $classString,
    ];
    
    if ($disabled || $loading) {
        $commonAttributes['disabled'] = true;
    }
    
    if ($tooltip) {
        $commonAttributes['title'] = $tooltip;
        $commonAttributes['data-bs-toggle'] = 'tooltip';
    }
    
    if ($confirmMessage) {
        $commonAttributes['onclick'] = "return confirm('" . addslashes($confirmMessage) . "')";
    }
    
    if ($tag === 'a') {
        $commonAttributes['href'] = $href;
        if ($target) {
            $commonAttributes['target'] = $target;
        }
        if ($disabled || $loading) {
            $commonAttributes['aria-disabled'] = 'true';
            $commonAttributes['tabindex'] = '-1';
        }
    } else {
        $commonAttributes['type'] = $type;
    }
@endphp

<{{ $tag }}
    @foreach($commonAttributes as $attr => $value)
        @if(is_bool($value))
            @if($value) {{ $attr }} @endif
        @else
            {{ $attr }}="{{ $value }}"
        @endif
    @endforeach
    {{ $attributes }}
>
    @if($loading)
        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
        {{ $loadingText }}
    @else
        @if($icon && $iconPosition === 'left')
            <i class="{{ $icon }} me-2"></i>
        @endif
        
        {{ $slot }}
        
        @if($badge)
            <span class="badge bg-{{ $badgeVariant }} ms-2">{{ $badge }}</span>
        @endif
        
        @if($icon && $iconPosition === 'right')
            <i class="{{ $icon }} ms-2"></i>
        @endif
    @endif
</{{ $tag }}>