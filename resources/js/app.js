/**
 * Main Application JavaScript
 * 
 * This file imports all JavaScript modules and initializes the application
 * following the DRY principle and maintaining separation of concerns
 */

// Import Bootstrap
import './bootstrap';
import 'bootstrap';

// Import custom modules
import { SidebarManager } from './modules/sidebar.js';
import { FormManager } from './modules/forms.js';
import { ModalManager } from './modules/modals.js';
import { TableManager } from './modules/tables.js';

/**
 * Application Class
 * 
 * Follows Single Responsibility Principle (SRP) - SOLID
 * Responsible only for application initialization and coordination
 */
class Application {
    constructor() {
        this.sidebar = null;
        this.forms = null;
        this.modals = null;
        this.tables = null;
        this.initialized = false;
    }

    /**
     * Initialize the application
     */
    init() {
        if (this.initialized) {
            console.warn('Application already initialized');
            return;
        }

        try {
            // Initialize managers
            this.sidebar = new SidebarManager();
            this.forms = new FormManager();
            this.modals = new ModalManager();
            this.tables = new TableManager();

            // Initialize all modules
            this.sidebar.init();
            this.forms.init();
            this.modals.init();
            this.tables.init();

            // Set up global event listeners
            this.setupGlobalEvents();

            // Mark as initialized
            this.initialized = true;

            console.log('NFCeBox Application initialized successfully');
        } catch (error) {
            console.error('Error initializing application:', error);
        }
    }

    /**
     * Set up global event listeners
     */
    setupGlobalEvents() {
        // Handle page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.onPageHidden();
            } else {
                this.onPageVisible();
            }
        });

        // Handle window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.onWindowResize();
            }, 250);
        });

        // Handle beforeunload for unsaved changes
        window.addEventListener('beforeunload', (event) => {
            if (this.hasUnsavedChanges()) {
                event.preventDefault();
                event.returnValue = 'Você tem alterações não salvas. Deseja realmente sair?';
                return event.returnValue;
            }
        });

        // Handle global keyboard shortcuts
        document.addEventListener('keydown', (event) => {
            this.handleGlobalKeyboard(event);
        });

        // Handle AJAX errors globally
        document.addEventListener('ajaxError', (event) => {
            this.handleAjaxError(event.detail);
        });
    }

    /**
     * Handle page hidden event
     */
    onPageHidden() {
        // Save any pending data
        this.savePendingData();
    }

    /**
     * Handle page visible event
     */
    onPageVisible() {
        // Refresh data if needed
        this.refreshDataIfNeeded();
    }

    /**
     * Handle window resize event
     */
    onWindowResize() {
        // Update sidebar responsiveness
        if (this.sidebar) {
            this.sidebar.handleResize();
        }

        // Update table responsiveness
        if (this.tables) {
            this.tables.handleResize();
        }
    }

    /**
     * Handle global keyboard shortcuts
     */
    handleGlobalKeyboard(event) {
        // Ctrl/Cmd + S: Save
        if ((event.ctrlKey || event.metaKey) && event.key === 's') {
            event.preventDefault();
            this.saveCurrentForm();
        }

        // Escape: Close modals/dropdowns
        if (event.key === 'Escape') {
            this.closeActiveOverlays();
        }

        // Ctrl/Cmd + F: Focus search
        if ((event.ctrlKey || event.metaKey) && event.key === 'f') {
            const searchInput = document.querySelector('[data-search]');
            if (searchInput) {
                event.preventDefault();
                searchInput.focus();
            }
        }
    }

    /**
     * Handle AJAX errors globally
     */
    handleAjaxError(error) {
        console.error('AJAX Error:', error);
        
        // Show user-friendly error message
        this.showNotification({
            type: 'error',
            title: 'Erro de Conexão',
            message: 'Ocorreu um erro ao comunicar com o servidor. Tente novamente.',
            duration: 5000
        });
    }

    /**
     * Check if there are unsaved changes
     */
    hasUnsavedChanges() {
        return this.forms ? this.forms.hasUnsavedChanges() : false;
    }

    /**
     * Save pending data
     */
    savePendingData() {
        if (this.forms) {
            this.forms.savePendingData();
        }
    }

    /**
     * Refresh data if needed
     */
    refreshDataIfNeeded() {
        const lastRefresh = localStorage.getItem('lastDataRefresh');
        const now = Date.now();
        const refreshInterval = 5 * 60 * 1000; // 5 minutes

        if (!lastRefresh || (now - parseInt(lastRefresh)) > refreshInterval) {
            this.refreshData();
            localStorage.setItem('lastDataRefresh', now.toString());
        }
    }

    /**
     * Refresh application data
     */
    refreshData() {
        if (this.tables) {
            this.tables.refreshData();
        }
    }

    /**
     * Save current form
     */
    saveCurrentForm() {
        if (this.forms) {
            this.forms.saveCurrentForm();
        }
    }

    /**
     * Close active overlays
     */
    closeActiveOverlays() {
        if (this.modals) {
            this.modals.closeAll();
        }

        // Close dropdowns
        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('show');
        });
    }

    /**
     * Show notification
     */
    showNotification(options) {
        const { type = 'info', title, message, duration = 3000 } = options;
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        notification.innerHTML = `
            ${title ? `<strong>${title}</strong><br>` : ''}
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, duration);
        }
    }

    /**
     * Get manager instance
     */
    getManager(name) {
        const managers = {
            sidebar: this.sidebar,
            forms: this.forms,
            modals: this.modals,
            tables: this.tables
        };
        
        return managers[name] || null;
    }

    /**
     * Destroy the application
     */
    destroy() {
        if (!this.initialized) {
            return;
        }

        try {
            // Destroy all managers
            if (this.sidebar) this.sidebar.destroy();
            if (this.forms) this.forms.destroy();
            if (this.modals) this.modals.destroy();
            if (this.tables) this.tables.destroy();

            // Reset properties
            this.sidebar = null;
            this.forms = null;
            this.modals = null;
            this.tables = null;
            this.initialized = false;

            console.log('NFCeBox Application destroyed');
        } catch (error) {
            console.error('Error destroying application:', error);
        }
    }
}

// Create global application instance
const app = new Application();

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        app.init();
    });
} else {
    app.init();
}

// Make app globally available
window.NFCeBoxApp = app;
window.bootstrap = bootstrap;

// Export for module usage
export default app;