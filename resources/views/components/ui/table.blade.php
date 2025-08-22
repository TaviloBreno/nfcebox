@props([
    'headers' => [],
    'data' => [],
    'actions' => null,
    'striped' => true,
    'hover' => true,
    'bordered' => false,
    'responsive' => true,
    'class' => '',
    'tableClass' => '',
    'emptyMessage' => 'Nenhum registro encontrado.',
    'showActions' => true
])

@php
    $tableClasses = 'table';
    if ($striped) $tableClasses .= ' table-striped';
    if ($hover) $tableClasses .= ' table-hover';
    if ($bordered) $tableClasses .= ' table-bordered';
    if ($tableClass) $tableClasses .= ' ' . $tableClass;
@endphp

<div class="{{ $responsive ? 'table-responsive' : '' }} {{ $class }}">
    <table class="{{ $tableClasses }}">
        @if(count($headers) > 0)
            <thead>
                <tr>
                    @foreach($headers as $header)
                        @if(is_array($header))
                            <th 
                                @if(isset($header['class'])) class="{{ $header['class'] }}" @endif
                                @if(isset($header['width'])) style="width: {{ $header['width'] }}" @endif
                                @if(isset($header['sortable']) && $header['sortable']) 
                                    data-sortable="true" 
                                    style="cursor: pointer;"
                                @endif
                            >
                                {{ $header['label'] ?? $header['text'] ?? '' }}
                                @if(isset($header['sortable']) && $header['sortable'])
                                    <i class="fas fa-sort ms-1"></i>
                                @endif
                            </th>
                        @else
                            <th>{{ $header }}</th>
                        @endif
                    @endforeach
                    @if($showActions && $actions)
                        <th class="text-center" style="width: 120px;">Ações</th>
                    @endif
                </tr>
            </thead>
        @endif
        
        <tbody>
            @if(count($data) > 0)
                @foreach($data as $index => $row)
                    <tr>
                        @if(isset($slot) && $slot->isNotEmpty())
                            {{ $slot }}
                        @else
                            @foreach($headers as $headerKey => $header)
                                @php
                                    $key = is_array($header) ? ($header['key'] ?? $headerKey) : $headerKey;
                                    $value = is_object($row) ? $row->$key : $row[$key] ?? '';
                                @endphp
                                <td>
                                    @if(is_array($header) && isset($header['format']))
                                        {!! $header['format']($value, $row) !!}
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach
                        @endif
                        
                        @if($showActions && $actions)
                            <td class="text-center">
                                @if(is_callable($actions))
                                    {!! $actions($row, $index) !!}
                                @else
                                    {!! $actions !!}
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="{{ count($headers) + ($showActions && $actions ? 1 : 0) }}" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>