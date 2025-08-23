/**
 * Tables Module
 * Handles table functionality, sorting, filtering, and pagination
 */

class TableManager {
    constructor() {
        this.tables = new Map();
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initTables();
    }
    
    refreshData() {
        // Refresh table data
        console.log('Table data refreshed');
        // This method can be extended to reload table data
    }
    
    bindEvents() {
        // Handle table actions
        document.addEventListener('click', (e) => {
            const sortHeader = e.target.closest('[data-sort]');
            if (sortHeader) {
                this.handleSort(e, sortHeader);
            }
            
            const filterButton = e.target.closest('[data-filter]');
            if (filterButton) {
                this.handleFilter(e, filterButton);
            }
            
            const exportButton = e.target.closest('[data-export]');
            if (exportButton) {
                this.handleExport(e, exportButton);
            }
        });
        
        // Handle search inputs
        document.addEventListener('input', (e) => {
            if (e.target.matches('[data-table-search]')) {
                this.handleSearch(e.target);
            }
        });
        
        // Handle pagination
        document.addEventListener('click', (e) => {
            const paginationLink = e.target.closest('[data-page]');
            if (paginationLink) {
                this.handlePagination(e, paginationLink);
            }
        });
    }
    
    initTables() {
        const tables = document.querySelectorAll('.table-enhanced');
        tables.forEach(table => {
            this.initTable(table);
        });
    }
    
    initTable(table) {
        const tableId = table.id || `table_${Date.now()}`;
        table.id = tableId;
        
        const config = {
            element: table,
            sortable: table.hasAttribute('data-sortable'),
            filterable: table.hasAttribute('data-filterable'),
            searchable: table.hasAttribute('data-searchable'),
            paginated: table.hasAttribute('data-paginated'),
            pageSize: parseInt(table.getAttribute('data-page-size')) || 10,
            currentPage: 1,
            totalRows: 0,
            filteredRows: 0,
            sortColumn: null,
            sortDirection: 'asc',
            filters: new Map(),
            searchTerm: ''
        };
        
        this.tables.set(tableId, config);
        
        // Initialize table features
        if (config.sortable) {
            this.initSorting(table);
        }
        
        if (config.searchable) {
            this.initSearch(table);
        }
        
        if (config.paginated) {
            this.initPagination(table);
        }
        
        this.updateTableInfo(tableId);
    }
    
    initSorting(table) {
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.classList.add('sortable');
            header.style.cursor = 'pointer';
            
            // Add sort icon
            if (!header.querySelector('.sort-icon')) {
                const icon = document.createElement('i');
                icon.className = 'fas fa-sort sort-icon ms-2';
                header.appendChild(icon);
            }
        });
    }
    
    initSearch(table) {
        const searchInput = document.querySelector(`[data-table-search="${table.id}"]`);
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                this.performSearch(table.id, searchInput.value);
            });
        }
    }
    
    initPagination(table) {
        const config = this.tables.get(table.id);
        config.totalRows = table.querySelectorAll('tbody tr').length;
        config.filteredRows = config.totalRows;
        
        this.renderPagination(table.id);
        this.showPage(table.id, 1);
    }
    
    handleSort(event, header) {
        event.preventDefault();
        
        const table = header.closest('table');
        const config = this.tables.get(table.id);
        const column = header.getAttribute('data-sort');
        
        // Update sort direction
        if (config.sortColumn === column) {
            config.sortDirection = config.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            config.sortColumn = column;
            config.sortDirection = 'asc';
        }
        
        this.sortTable(table.id, column, config.sortDirection);
        this.updateSortIcons(table, column, config.sortDirection);
    }
    
    sortTable(tableId, column, direction) {
        const config = this.tables.get(tableId);
        const table = config.element;
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        const columnIndex = this.getColumnIndex(table, column);
        
        rows.sort((a, b) => {
            const aValue = this.getCellValue(a, columnIndex);
            const bValue = this.getCellValue(b, columnIndex);
            
            let comparison = 0;
            
            // Try to parse as numbers first
            const aNum = parseFloat(aValue.replace(/[^\d.-]/g, ''));
            const bNum = parseFloat(bValue.replace(/[^\d.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                comparison = aNum - bNum;
            } else {
                // String comparison
                comparison = aValue.localeCompare(bValue, 'pt-BR', { numeric: true });
            }
            
            return direction === 'asc' ? comparison : -comparison;
        });
        
        // Re-append sorted rows
        rows.forEach(row => tbody.appendChild(row));
        
        // Update pagination if enabled
        if (config.paginated) {
            this.showPage(tableId, config.currentPage);
        }
    }
    
    updateSortIcons(table, activeColumn, direction) {
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            const icon = header.querySelector('.sort-icon');
            const column = header.getAttribute('data-sort');
            
            if (column === activeColumn) {
                icon.className = `fas fa-sort-${direction === 'asc' ? 'up' : 'down'} sort-icon ms-2`;
            } else {
                icon.className = 'fas fa-sort sort-icon ms-2';
            }
        });
    }
    
    handleSearch(searchInput) {
        const tableId = searchInput.getAttribute('data-table-search');
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        this.performSearch(tableId, searchTerm);
    }
    
    performSearch(tableId, searchTerm) {
        const config = this.tables.get(tableId);
        const table = config.element;
        const rows = table.querySelectorAll('tbody tr');
        
        config.searchTerm = searchTerm;
        let visibleCount = 0;
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const isVisible = !searchTerm || text.includes(searchTerm);
            
            row.style.display = isVisible ? '' : 'none';
            if (isVisible) visibleCount++;
        });
        
        config.filteredRows = visibleCount;
        
        // Update pagination
        if (config.paginated) {
            config.currentPage = 1;
            this.renderPagination(tableId);
            this.showPage(tableId, 1);
        }
        
        this.updateTableInfo(tableId);
    }
    
    handlePagination(event, link) {
        event.preventDefault();
        
        const tableId = link.getAttribute('data-table');
        const page = parseInt(link.getAttribute('data-page'));
        
        this.showPage(tableId, page);
    }
    
    showPage(tableId, page) {
        const config = this.tables.get(tableId);
        const table = config.element;
        const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => {
            return row.style.display !== 'none';
        });
        
        const startIndex = (page - 1) * config.pageSize;
        const endIndex = startIndex + config.pageSize;
        
        rows.forEach((row, index) => {
            if (index >= startIndex && index < endIndex) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        config.currentPage = page;
        this.updatePaginationState(tableId);
        this.updateTableInfo(tableId);
    }
    
    renderPagination(tableId) {
        const config = this.tables.get(tableId);
        const totalPages = Math.ceil(config.filteredRows / config.pageSize);
        
        let paginationContainer = document.querySelector(`[data-pagination="${tableId}"]`);
        if (!paginationContainer) {
            paginationContainer = document.createElement('nav');
            paginationContainer.setAttribute('data-pagination', tableId);
            config.element.parentNode.appendChild(paginationContainer);
        }
        
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }
        
        let paginationHtml = '<ul class="pagination justify-content-center">';
        
        // Previous button
        paginationHtml += `
            <li class="page-item ${config.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${config.currentPage - 1}" data-table="${tableId}">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        const startPage = Math.max(1, config.currentPage - 2);
        const endPage = Math.min(totalPages, config.currentPage + 2);
        
        if (startPage > 1) {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="1" data-table="${tableId}">1</a>
                </li>
            `;
            if (startPage > 2) {
                paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === config.currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}" data-table="${tableId}">${i}</a>
                </li>
            `;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="#" data-page="${totalPages}" data-table="${tableId}">${totalPages}</a>
                </li>
            `;
        }
        
        // Next button
        paginationHtml += `
            <li class="page-item ${config.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${config.currentPage + 1}" data-table="${tableId}">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
        
        paginationHtml += '</ul>';
        paginationContainer.innerHTML = paginationHtml;
    }
    
    updatePaginationState(tableId) {
        const config = this.tables.get(tableId);
        const paginationContainer = document.querySelector(`[data-pagination="${tableId}"]`);
        
        if (!paginationContainer) return;
        
        const pageLinks = paginationContainer.querySelectorAll('.page-link[data-page]');
        pageLinks.forEach(link => {
            const page = parseInt(link.getAttribute('data-page'));
            const listItem = link.parentNode;
            
            listItem.classList.toggle('active', page === config.currentPage);
        });
    }
    
    updateTableInfo(tableId) {
        const config = this.tables.get(tableId);
        const infoContainer = document.querySelector(`[data-table-info="${tableId}"]`);
        
        if (!infoContainer) return;
        
        const startRecord = config.paginated ? 
            ((config.currentPage - 1) * config.pageSize) + 1 : 1;
        const endRecord = config.paginated ? 
            Math.min(config.currentPage * config.pageSize, config.filteredRows) : config.filteredRows;
        
        infoContainer.textContent = `Mostrando ${startRecord} a ${endRecord} de ${config.filteredRows} registros`;
        
        if (config.filteredRows !== config.totalRows) {
            infoContainer.textContent += ` (filtrados de ${config.totalRows} registros totais)`;
        }
    }
    
    handleExport(event, button) {
        event.preventDefault();
        
        const tableId = button.getAttribute('data-export');
        const format = button.getAttribute('data-format') || 'csv';
        const filename = button.getAttribute('data-filename') || 'export';
        
        this.exportTable(tableId, format, filename);
    }
    
    exportTable(tableId, format, filename) {
        const config = this.tables.get(tableId);
        const table = config.element;
        
        switch (format.toLowerCase()) {
            case 'csv':
                this.exportToCSV(table, filename);
                break;
            case 'excel':
                this.exportToExcel(table, filename);
                break;
            case 'pdf':
                this.exportToPDF(table, filename);
                break;
            default:
                console.warn('Formato de exportação não suportado:', format);
        }
    }
    
    exportToCSV(table, filename) {
        const rows = [];
        
        // Headers
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => 
            th.textContent.trim().replace(/["\r\n]/g, '')
        );
        rows.push(headers.join(','));
        
        // Data rows (only visible ones)
        const dataRows = table.querySelectorAll('tbody tr');
        dataRows.forEach(row => {
            if (row.style.display !== 'none') {
                const cells = Array.from(row.querySelectorAll('td')).map(td => {
                    let text = td.textContent.trim().replace(/["\r\n]/g, ' ');
                    return `"${text}"`;
                });
                rows.push(cells.join(','));
            }
        });
        
        const csvContent = rows.join('\n');
        this.downloadFile(csvContent, `${filename}.csv`, 'text/csv');
    }
    
    exportToExcel(table, filename) {
        // This would require a library like SheetJS
        console.log('Excel export would require additional library');
    }
    
    exportToPDF(table, filename) {
        // This would require a library like jsPDF
        console.log('PDF export would require additional library');
    }
    
    downloadFile(content, filename, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = window.URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        window.URL.revokeObjectURL(url);
    }
    
    // Helper methods
    getColumnIndex(table, columnName) {
        const headers = table.querySelectorAll('th[data-sort]');
        for (let i = 0; i < headers.length; i++) {
            if (headers[i].getAttribute('data-sort') === columnName) {
                return i;
            }
        }
        return 0;
    }
    
    getCellValue(row, columnIndex) {
        const cells = row.querySelectorAll('td');
        return cells[columnIndex] ? cells[columnIndex].textContent.trim() : '';
    }
    
    // Public methods
    refresh(tableId) {
        const config = this.tables.get(tableId);
        if (config) {
            this.updateTableInfo(tableId);
            if (config.paginated) {
                this.renderPagination(tableId);
            }
        }
    }
    
    search(tableId, term) {
        this.performSearch(tableId, term);
    }
    
    sort(tableId, column, direction = 'asc') {
        this.sortTable(tableId, column, direction);
    }
    
    goToPage(tableId, page) {
        this.showPage(tableId, page);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new TableManager();
});

// Export for Node.js environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TableManager;
}

// ES6 export
export { TableManager };