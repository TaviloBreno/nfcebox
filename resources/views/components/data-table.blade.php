@props([
    'headers' => [],
    'data' => [],
    'actions' => null,
    'responsive' => true,
    'striped' => true,
    'hover' => true,
    'bordered' => false,
    'small' => false,
    'dark' => false,
    'headerDark' => true,
    'sortable' => false,
    'searchable' => false,
    'pagination' => null,
    'emptyMessage' => 'Nenhum registro encontrado.',
    'tableId' => null,
    'containerClass' => '',
    'tableClass' => ''
])

@php
    $tableClasses = ['table'];
    
    if ($striped) $tableClasses[] = 'table-striped';
    if ($hover) $tableClasses[] = 'table-hover';
    if ($bordered) $tableClasses[] = 'table-bordered';
    if ($small) $tableClasses[] = 'table-sm';
    if ($dark) $tableClasses[] = 'table-dark';
    if ($tableClass) $tableClasses[] = $tableClass;
    
    $tableClassString = implode(' ', $tableClasses);
    
    $headerClasses = [];
    if ($headerDark && !$dark) $headerClasses[] = 'table-dark';
    $headerClassString = implode(' ', $headerClasses);
    
    $uniqueId = $tableId ?: 'table_' . uniqid();
@endphp

<div class="{{ $containerClass }}">
    @if($searchable)
        <div class="mb-3">
            <div class="input-group">
                <input 
                    type="text" 
                    class="form-control" 
                    placeholder="Buscar na tabela..." 
                    data-table-search="{{ $uniqueId }}"
                >
                <button class="btn btn-outline-secondary" type="button" data-table-search-clear="{{ $uniqueId }}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endif
    
    @if($responsive)
        <div class="table-responsive">
    @endif
    
    <table 
        class="{{ $tableClassString }}" 
        id="{{ $uniqueId }}"
        @if($sortable) data-sortable="true" @endif
        {{ $attributes }}
    >
        @if(!empty($headers))
            <thead class="{{ $headerClassString }}">
                <tr>
                    @foreach($headers as $key => $header)
                        @php
                            $headerConfig = is_array($header) ? $header : ['label' => $header];
                            $label = $headerConfig['label'] ?? $header;
                            $sortable = $headerConfig['sortable'] ?? false;
                            $width = $headerConfig['width'] ?? null;
                            $class = $headerConfig['class'] ?? '';
                        @endphp
                        <th 
                            @if($width) style="width: {{ $width }}" @endif
                            @if($class) class="{{ $class }}" @endif
                            @if($sortable) data-sortable="true" data-sort-key="{{ $key }}" @endif
                        >
                            {{ $label }}
                            @if($sortable)
                                <i class="fas fa-sort ms-1 text-muted" data-sort-icon></i>
                            @endif
                        </th>
                    @endforeach
                    @if($actions)
                        <th class="text-center" style="width: 120px;">Ações</th>
                    @endif
                </tr>
            </thead>
        @endif
        
        <tbody>
            @forelse($data as $index => $row)
                <tr data-row-index="{{ $index }}">
                    @if(is_array($row) || is_object($row))
                        @foreach($headers as $key => $header)
                            @php
                                $value = is_array($row) ? ($row[$key] ?? '') : ($row->{$key} ?? '');
                                $headerConfig = is_array($header) ? $header : [];
                                $format = $headerConfig['format'] ?? null;
                                $class = $headerConfig['cellClass'] ?? '';
                                
                                // Apply formatting
                                if ($format === 'currency' && is_numeric($value)) {
                                    $value = 'R$ ' . number_format($value, 2, ',', '.');
                                } elseif ($format === 'date' && $value) {
                                    $value = \Carbon\Carbon::parse($value)->format('d/m/Y');
                                } elseif ($format === 'datetime' && $value) {
                                    $value = \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
                                }
                            @endphp
                            <td @if($class) class="{{ $class }}" @endif>
                                {!! $value !!}
                            </td>
                        @endforeach
                    @else
                        <td colspan="{{ count($headers) + ($actions ? 1 : 0) }}">
                            {{ $row }}
                        </td>
                    @endif
                    
                    @if($actions)
                        <td class="text-center">
                            @if(is_callable($actions))
                                {!! $actions($row, $index) !!}
                            @else
                                {{ $actions }}
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    @php
                        $headerCount = isset($headers) ? substr_count($headers, '<th') : 0;
                        $totalCols = $headerCount + ($actions ? 1 : 0);
                    @endphp
                    <td colspan="{{ $totalCols }}" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        {{ $emptyMessage }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($responsive)
        </div>
    @endif
    
    @if($pagination)
        <div class="d-flex justify-content-center mt-3">
            {{ $pagination }}
        </div>
    @endif
</div>

@if($sortable || $searchable)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableId = '{{ $uniqueId }}';
            const table = document.getElementById(tableId);
            
            @if($searchable)
                // Search functionality
                const searchInput = document.querySelector(`[data-table-search="${tableId}"]`);
                const searchClear = document.querySelector(`[data-table-search-clear="${tableId}"]`);
                
                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        const searchTerm = this.value.toLowerCase();
                        const rows = table.querySelectorAll('tbody tr');
                        
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            row.style.display = text.includes(searchTerm) ? '' : 'none';
                        });
                    });
                }
                
                if (searchClear) {
                    searchClear.addEventListener('click', function() {
                        searchInput.value = '';
                        searchInput.dispatchEvent(new Event('input'));
                    });
                }
            @endif
            
            @if($sortable)
                // Sort functionality
                const sortableHeaders = table.querySelectorAll('th[data-sortable="true"]');
                
                sortableHeaders.forEach(header => {
                    header.style.cursor = 'pointer';
                    header.addEventListener('click', function() {
                        const sortKey = this.dataset.sortKey;
                        const icon = this.querySelector('[data-sort-icon]');
                        const tbody = table.querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        
                        // Reset other icons
                        sortableHeaders.forEach(h => {
                            if (h !== this) {
                                const otherIcon = h.querySelector('[data-sort-icon]');
                                if (otherIcon) {
                                    otherIcon.className = 'fas fa-sort ms-1 text-muted';
                                }
                            }
                        });
                        
                        // Determine sort direction
                        let ascending = true;
                        if (icon.classList.contains('fa-sort-up')) {
                            ascending = false;
                            icon.className = 'fas fa-sort-down ms-1';
                        } else {
                            icon.className = 'fas fa-sort-up ms-1';
                        }
                        
                        // Sort rows
                        rows.sort((a, b) => {
                            const aValue = a.children[Object.keys({{ json_encode($headers) }}).indexOf(sortKey)]?.textContent || '';
                            const bValue = b.children[Object.keys({{ json_encode($headers) }}).indexOf(sortKey)]?.textContent || '';
                            
                            const comparison = aValue.localeCompare(bValue, 'pt-BR', { numeric: true });
                            return ascending ? comparison : -comparison;
                        });
                        
                        // Reorder DOM
                        rows.forEach(row => tbody.appendChild(row));
                    });
                });
            @endif
        });
    </script>
    @endpush
@endif