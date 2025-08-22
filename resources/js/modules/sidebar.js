/**
 * Sidebar Module
 * Handles sidebar navigation functionality
 */

class SidebarManager {
    constructor() {
        this.sidebar = document.querySelector('.sidebar');
        this.sidebarToggle = document.querySelector('.sidebar-toggle');
        this.sidebarOverlay = document.querySelector('.sidebar-overlay');
        this.mainContent = document.querySelector('.main-content');
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setActiveNavItem();
        this.handleResponsive();
    }
    
    bindEvents() {
        // Toggle sidebar on mobile
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', () => {
                this.toggleSidebar();
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