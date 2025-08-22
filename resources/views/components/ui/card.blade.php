@props([
    'title' => null,
    'icon' => null,
    'headerActions' => null,
    'class' => '',
    'bodyClass' => '',
    'headerClass' => ''
])

<div class="card {{ $class }}">
    @if($title || $icon || $headerActions)
        <div class="card-header {{ $headerClass }}">
            <div class="d-flex justify-content-between align-items-center">
                @if($title || $icon)
                    <h3 class="card-title mb-0">
                        @if($icon)
                            <i class="{{ $icon }} me-2"></i>
                        @endif
                        {{ $title }}
                    </h3>
                @endif
                
                @if($headerActions)
                    <div class="d-flex gap-2">
                        {{ $headerActions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="card-body {{ $bodyClass }}">
        {{ $slot }}
    </div>
</div>