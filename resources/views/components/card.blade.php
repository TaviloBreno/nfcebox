@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => '',
    'variant' => 'default', // default, primary, success, info, warning, danger
    'shadow' => true,
    'border' => true,
    'actions' => null,
    'footer' => null
])

@php
    $cardClasses = ['card'];
    
    if ($shadow) {
        $cardClasses[] = 'shadow-sm';
    }
    
    if (!$border) {
        $cardClasses[] = 'border-0';
    }
    
    switch ($variant) {
        case 'primary':
            $cardClasses[] = 'border-primary';
            break;
        case 'success':
            $cardClasses[] = 'border-success';
            break;
        case 'info':
            $cardClasses[] = 'border-info';
            break;
        case 'warning':
            $cardClasses[] = 'border-warning';
            break;
        case 'danger':
            $cardClasses[] = 'border-danger';
            break;
    }
    
    $cardClass = implode(' ', $cardClasses);
@endphp

<div {{ $attributes->merge(['class' => $cardClass]) }}>
    @if($title || $subtitle || $icon || $actions)
        <div class="card-header {{ $headerClass }}">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    @if($icon)
                        <i class="{{ $icon }} me-2"></i>
                    @endif
                    <div>
                        @if($title)
                            <h5 class="card-title mb-0">{{ $title }}</h5>
                        @endif
                        @if($subtitle)
                            <small class="text-muted">{{ $subtitle }}</small>
                        @endif
                    </div>
                </div>
                @if($actions)
                    <div class="card-actions">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="card-footer {{ $footerClass }}">
            {{ $footer }}
        </div>
    @endif
</div>