@props([
    'id' => 'modal',
    'title' => '',
    'size' => 'md',
    'centered' => false,
    'backdrop' => 'true',
    'keyboard' => 'true',
    'footerActions' => null,
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => ''
])

@php
    $modalClass = 'modal-dialog';
    if ($size !== 'md') {
        $modalClass .= ' modal-' . $size;
    }
    if ($centered) {
        $modalClass .= ' modal-dialog-centered';
    }
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true" 
     data-bs-backdrop="{{ $backdrop }}" data-bs-keyboard="{{ $keyboard }}">
    <div class="{{ $modalClass }}">
        <div class="modal-content">
            @if($title)
                <div class="modal-header {{ $headerClass }}">
                    <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="modal-body {{ $bodyClass }}">
                {{ $slot }}
            </div>
            
            @if($footerActions)
                <div class="modal-footer {{ $footerClass }}">
                    {{ $footerActions }}
                </div>
            @endif
        </div>
    </div>
</div>