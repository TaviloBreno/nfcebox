/**
 * Sidebar Module
 * Handles sidebar navigation functionality
 */

class SidebarManager {
    constructor() {
        this.sidebar = document.querySelector('.sidebar');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.sidebarOverlay = document.querySelector('.sidebar-overlay');
        this.mainContent = document.querySelectorAll('.main-content');
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setActiveNavItem();
        this.handleResponsive();
    }
    
    bindEvents() {
        // Create overlay if it doesn't exist
        this.createOverlay();
        
        // Toggle sidebar
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', (e) => {
                this.handleSidebarToggle(e);
            });
        }
        
        // Close sidebar when clicking overlay
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => {
                this.closeSidebar();
            });
        }
        
        // Handle window resize
        window.addEventListener('resize', () => {
            this.handleResponsive();
        });
        
        // Handle navigation clicks
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                this.handleNavClick(e, link);
            });
        });
        
        // Restore sidebar state from localStorage
        this.restoreSidebarState();
        
        // Setup overlay observer
        this.setupOverlayObserver();
    }
    
    toggleSidebar() {
        if (this.sidebar && this.sidebarOverlay) {
            this.sidebar.classList.toggle('show');
            this.sidebarOverlay.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        }
    }
    
    closeSidebar() {
        if (this.sidebar && this.sidebarOverlay) {
            this.sidebar.classList.remove('show');
            this.sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    }
    
    openSidebar() {
        if (this.sidebar && this.sidebarOverlay) {
            this.sidebar.classList.add('show');
            this.sidebarOverlay.classList.add('show');
            document.body.classList.add('sidebar-open');
        }
    }
    
    setActiveNavItem() {
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            
            const href = link.getAttribute('href');
            if (href && (currentPath === href || currentPath.startsWith(href + '/'))) {
                link.classList.add('active');
            }
        });
    }
    
    handleNavClick(event, link) {
        // Remove active class from all nav links
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(navLink => {
            navLink.classList.remove('active');
        });
        
        // Add active class to clicked link
        link.classList.add('active');
        
        // Close sidebar on mobile after navigation
        if (window.innerWidth <= 768) {
            setTimeout(() => {
                this.closeSidebar();
            }, 150);
        }
    }
    
    handleResponsive() {
        if (window.innerWidth > 768) {
            this.closeSidebar();
            // Restore collapsed state for desktop
            this.restoreSidebarState();
        } else {
            // Remove desktop classes on mobile
            if (this.sidebar) {
                this.sidebar.classList.remove('collapsed');
                this.mainContent.forEach(element => {
                    element.classList.remove('sidebar-collapsed');
                });
            }
        }
    }
    
    createOverlay() {
        if (!this.sidebarOverlay) {
            this.sidebarOverlay = document.createElement('div');
            this.sidebarOverlay.className = 'sidebar-overlay';
            document.body.appendChild(this.sidebarOverlay);
        }
    }
    
    handleSidebarToggle(e) {
        e.preventDefault();
        
        if (window.innerWidth <= 768) {
            // Mobile behavior
            this.toggleSidebar();
        } else {
            // Desktop behavior
            if (this.sidebar) {
                this.sidebar.classList.toggle('collapsed');
                
                // Toggle main content margin
                this.mainContent.forEach(element => {
                    element.classList.toggle('sidebar-collapsed');
                });
                
                // Store state in localStorage
                const isCollapsed = this.sidebar.classList.contains('collapsed');
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            }
        }
    }
    
    restoreSidebarState() {
        if (window.innerWidth > 768 && this.sidebar) {
            const savedState = localStorage.getItem('sidebarCollapsed');
            if (savedState === 'true') {
                this.sidebar.classList.add('collapsed');
                this.mainContent.forEach(element => {
                    element.classList.add('sidebar-collapsed');
                });
            }
        }
    }
    
    setupOverlayObserver() {
        if (this.sidebar && this.sidebarOverlay) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        if (this.sidebar.classList.contains('show')) {
                            this.sidebarOverlay.classList.add('show');
                        } else {
                            this.sidebarOverlay.classList.remove('show');
                        }
                    }
                });
            });
            observer.observe(this.sidebar, { attributes: true });
        }
    }
    
    // Public methods for external use
    collapse() {
        if (this.sidebar) {
            this.sidebar.classList.add('collapsed');
        }
    }
    
    expand() {
        if (this.sidebar) {
            this.sidebar.classList.remove('collapsed');
        }
    }
    
    isCollapsed() {
        return this.sidebar ? this.sidebar.classList.contains('collapsed') : false;
    }
    
    isMobile() {
        return window.innerWidth <= 768;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new SidebarManager();
});

// Export for Node.js environments
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SidebarManager;
}

// ES6 export
export { SidebarManager };