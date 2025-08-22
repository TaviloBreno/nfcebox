@props([
    'title',
    'value',
    'icon' => null,
    'variant' => 'primary', // primary, success, info, warning, danger, secondary
    'textColor' => 'white',
    'change' => null, // percentage change
    'changeType' => null, // increase, decrease
    'subtitle' => null,
    'footer' => null,
    'loading' => false,
    'animate' => true,
    'size' => 'default', // sm, default, lg
    'containerClass' => ''
])

@php
    $cardClasses = ['card', 'h-100'];
    
    // Variant classes
    $cardClasses[] = 'bg-' . $variant;
    if ($textColor === 'white') {
        $cardClasses[] = 'text-white';
    } else {
        $cardClasses[] = 'text-' . $textColor;
    }
    
    // Size classes
    if ($size === 'sm') {
        $cardClasses[] = 'card-sm';
    } elseif ($size === 'lg') {
        $cardClasses[] = 'card-lg';
    }
    
    $cardClassString = implode(' ', $cardClasses);
    
    // Format value if numeric
    $formattedValue = $value;
    if (is_numeric($value)) {
        if ($value >= 1000000) {
            $formattedValue = number_format($value / 1000000, 1, ',', '.') . 'M';
        } elseif ($value >= 1000) {
            $formattedValue = number_format($value / 1000, 1, ',', '.') . 'K';
        } else {
            $formattedValue = number_format($value, 0, ',', '.');
        }
    }
    
    $uniqueId = 'stat_' . uniqid();
@endphp

<div class="{{ $containerClass }}">
    <div class="{{ $cardClassString }}" id="{{ $uniqueId }}">
        <div class="card-body">
            @if($loading)
                <div class="d-flex justify-content-center align-items-center" style="min-height: 100px;">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            @else
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        @if($subtitle)
                            <div class="small opacity-75 mb-1">{{ $subtitle }}</div>
                        @endif
                        
                        <h6 class="card-title mb-2 opacity-90">{{ $title }}</h6>
                        
                        <div class="d-flex align-items-baseline">
                            <h2 class="mb-0 me-2" @if($animate) data-counter="{{ $value }}" @endif>
                                @if($animate && is_numeric($value))
                                    0
                                @else
                                    {{ $formattedValue }}
                                @endif
                            </h2>
                            
                            @if($change !== null)
                                @php
                                    $changeClass = '';
                                    $changeIcon = '';
                                    
                                    if ($changeType === 'increase' || ($changeType === null && $change > 0)) {
                                        $changeClass = 'text-success';
                                        $changeIcon = 'fas fa-arrow-up';
                                    } elseif ($changeType === 'decrease' || ($changeType === null && $change < 0)) {
                                        $changeClass = 'text-danger';
                                        $changeIcon = 'fas fa-arrow-down';
                                    } else {
                                        $changeClass = 'text-muted';
                                        $changeIcon = 'fas fa-minus';
                                    }
                                @endphp
                                
                                <small class="{{ $changeClass }} d-flex align-items-center">
                                    <i class="{{ $changeIcon }} me-1" style="font-size: 0.7em;"></i>
                                    {{ abs($change) }}%
                                </small>
                            @endif
                        </div>
                    </div>
                    
                    @if($icon)
                        <div class="ms-3">
                            <i class="{{ $icon }} fa-2x opacity-75"></i>
                        </div>
                    @endif
                </div>
                
                @if($footer)
                    <div class="mt-3 pt-2 border-top border-light border-opacity-25">
                        <small class="opacity-75">{{ $footer }}</small>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

@if($animate && is_numeric($value))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const element = document.querySelector('#{{ $uniqueId }} [data-counter]');
            if (element) {
                const target = parseInt(element.dataset.counter);
                const duration = 2000; // 2 seconds
                const increment = target / (duration / 16); // 60fps
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    // Format the number
                    let displayValue = Math.floor(current);
                    if (displayValue >= 1000000) {
                        element.textContent = (displayValue / 1000000).toFixed(1) + 'M';
                    } else if (displayValue >= 1000) {
                        element.textContent = (displayValue / 1000).toFixed(1) + 'K';
                    } else {
                        element.textContent = displayValue.toLocaleString('pt-BR');
                    }
                }, 16);
            }
        });
    </script>
    @endpush
@endif