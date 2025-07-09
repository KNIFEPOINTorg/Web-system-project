/**
 * Authentication State Manager for LFLshop
 * Handles authentication state across all pages and manages navigation visibility
 */

class AuthStateManager {
    constructor() {
        this.currentUser = null;
        this.isAuthenticated = false;
        this.apiBaseUrl = '../api';
    }

    async init() {
        try {
            await this.checkAuthStatus();
            this.updateNavigationState();
            this.setupEventListeners();
        } catch (error) {
            console.error('Auth state manager initialization failed:', error);
            this.handleUnauthenticated();
        }
    }

    async checkAuthStatus() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/auth.php?action=check`);
            const data = await response.json();

            if (data.success && data.data && data.data.user) {
                this.currentUser = data.data.user;
                this.isAuthenticated = true;
                localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
                console.log('✅ User authenticated:', this.currentUser.name);
            } else {
                this.handleUnauthenticated();
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            this.handleUnauthenticated();
        }
    }

    handleUnauthenticated() {
        this.currentUser = null;
        this.isAuthenticated = false;
        localStorage.removeItem('currentUser');
        console.log('❌ User not authenticated');
    }

    updateNavigationState() {
        // Update authentication-dependent navigation elements
        this.updateAuthLinks();
        this.updateNavIcons();
        this.updateUserMenu();
        this.updateCartCount();
    }

    updateAuthLinks() {
        const authLinks = document.querySelector('.auth-links');
        if (!authLinks) return;

        if (this.isAuthenticated) {
            authLinks.style.display = 'none';
        } else {
            authLinks.style.display = 'flex';
            // Determine correct paths based on current location
            const isInHtmlFolder = window.location.pathname.includes('/html/');
            const loginPath = isInHtmlFolder ? '../login.php' : 'login.php';
            const registerPath = isInHtmlFolder ? '../register.php' : 'register.php';
            
            authLinks.innerHTML = `
                <a href="${loginPath}">Sign In</a>
                <span class="auth-divider">|</span>
                <a href="${registerPath}">Sign Up</a>
            `;
        }
    }

    updateNavIcons() {
        const navIcons = document.querySelector('.nav-icons');
        if (!navIcons) return;

        if (this.isAuthenticated) {
            navIcons.style.display = 'flex';
        } else {
            navIcons.style.display = 'none';
        }
    }

    updateUserMenu() {
        const userMenu = document.querySelector('.user-menu');
        if (!userMenu) return;

        if (this.isAuthenticated && this.currentUser) {
            userMenu.style.display = 'flex';
            
            // Update user name in menu
            const userNameSpan = userMenu.querySelector('.user-menu-toggle span');
            if (userNameSpan) {
                userNameSpan.textContent = this.currentUser.name || 'User';
            }

            // Update dashboard link based on user type
            const dashboardLink = userMenu.querySelector('a[href*="dashboard"]');
            if (dashboardLink) {
                const userType = this.currentUser.user_type || this.currentUser.userType;
                const isInHtmlFolder = window.location.pathname.includes('/html/');
                let dashboardPath = '';
                
                if (userType === 'seller') {
                    dashboardPath = isInHtmlFolder ? 'seller-dashboard.html' : 'html/seller-dashboard.html';
                } else if (userType === 'admin') {
                    dashboardPath = isInHtmlFolder ? '../admin/admin_control_panel.php' : 'admin/admin_control_panel.php';
                } else {
                    dashboardPath = isInHtmlFolder ? 'customer-dashboard.html' : 'html/customer-dashboard.html';
                }
                
                dashboardLink.href = dashboardPath;
            }
        } else {
            userMenu.style.display = 'none';
        }
    }

    async updateCartCount() {
        if (!this.isAuthenticated) {
            // For unauthenticated users, use localStorage cart
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const count = cart.reduce((total, item) => total + (item.quantity || 1), 0);
            this.setCartCount(count);
            return;
        }

        try {
            // For authenticated users, get cart from server
            const response = await fetch(`${this.apiBaseUrl}/cart.php?action=count`);
            const data = await response.json();
            
            if (data.success) {
                this.setCartCount(data.count || 0);
            } else {
                this.setCartCount(0);
            }
        } catch (error) {
            console.error('Failed to update cart count:', error);
            this.setCartCount(0);
        }
    }

    setCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
            element.style.display = count > 0 ? 'flex' : 'none';
        });
    }

    async logout() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/auth.php?action=logout`, {
                method: 'POST'
            });

            // Clear local state regardless of server response
            this.handleUnauthenticated();
            this.updateNavigationState();

            // Redirect to home page
            const isInHtmlFolder = window.location.pathname.includes('/html/');
            const homePath = isInHtmlFolder ? 'index.html' : 'html/index.html';
            window.location.href = homePath;
        } catch (error) {
            console.error('Logout error:', error);
            // Still clear local state and redirect
            this.handleUnauthenticated();
            this.updateNavigationState();
            window.location.href = 'index.html';
        }
    }

    setupEventListeners() {
        // Handle logout clicks
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('logout-btn') || 
                e.target.closest('.logout-btn')) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    this.logout();
                }
            }
        });

        // Handle user menu toggle
        const userMenuToggle = document.querySelector('.user-menu-toggle');
        const userDropdown = document.querySelector('.user-dropdown');

        if (userMenuToggle && userDropdown) {
            userMenuToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const isVisible = userDropdown.style.opacity === '1';
                userDropdown.style.opacity = isVisible ? '0' : '1';
                userDropdown.style.visibility = isVisible ? 'hidden' : 'visible';
                userDropdown.style.transform = isVisible ? 'translateY(-10px)' : 'translateY(0)';
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.style.opacity = '0';
                    userDropdown.style.visibility = 'hidden';
                    userDropdown.style.transform = 'translateY(-10px)';
                }
            });
        }
    }

    // Helper methods for other scripts
    requireAuth() {
        if (!this.isAuthenticated) {
            const isInHtmlFolder = window.location.pathname.includes('/html/');
            const loginPath = isInHtmlFolder ? '../login.php' : 'login.php';
            const currentPath = encodeURIComponent(window.location.pathname);
            window.location.href = `${loginPath}?redirect=${currentPath}`;
            return false;
        }
        return true;
    }

    showAuthRequiredMessage(action = 'perform this action') {
        const message = `Please sign in to ${action}`;
        this.showNotification(message, 'warning');
        
        setTimeout(() => {
            const isInHtmlFolder = window.location.pathname.includes('/html/');
            const loginPath = isInHtmlFolder ? '../login.php' : 'login.php';
            window.location.href = loginPath;
        }, 2000);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `auth-notification ${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'warning' ? '#fff3cd' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
            color: ${type === 'warning' ? '#856404' : type === 'error' ? '#721c24' : '#0c5460'};
            border: 1px solid ${type === 'warning' ? '#ffeaa7' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 3000;
            font-size: 14px;
            font-weight: 500;
            max-width: 300px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-${type === 'warning' ? 'exclamation-triangle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        // Add animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
            style.remove();
        }, 5000);
    }

    getDashboardLink() {
        if (!this.isAuthenticated || !this.currentUser) return '#';
        
        const userType = this.currentUser.user_type || this.currentUser.userType;
        const isInHtmlFolder = window.location.pathname.includes('/html/');
        
        if (userType === 'seller') {
            return isInHtmlFolder ? 'seller-dashboard.html' : 'html/seller-dashboard.html';
        } else if (userType === 'admin') {
            return isInHtmlFolder ? '../admin/admin_control_panel.php' : 'admin/admin_control_panel.php';
        } else {
            return isInHtmlFolder ? 'customer-dashboard.html' : 'html/customer-dashboard.html';
        }
    }
}

// Create global instance
window.authStateManager = new AuthStateManager();

// Backward compatibility
window.authNavigation = {
    get isAuthenticated() { return window.authStateManager.isAuthenticated; },
    get currentUser() { return window.authStateManager.currentUser; },
    showAuthRequiredMessage: (action) => window.authStateManager.showAuthRequiredMessage(action),
    getDashboardLink: () => window.authStateManager.getDashboardLink(),
    loadCartCount: () => window.authStateManager.updateCartCount()
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.authStateManager.init();
    });
} else {
    window.authStateManager.init();
}