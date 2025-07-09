/**
 * Authentication-Aware Navigation System
 * Properly handles cart and notifications visibility based on authentication status
 */

class AuthAwareNavigation {
    constructor() {
        this.isAuthenticated = false;
        this.currentUser = null;
        this.init();
    }

    async init() {
        await this.checkAuthStatus();
        this.setupNavigation();
        this.setupEventListeners();
        this.updateNavigationState();
    }

    async checkAuthStatus() {
        try {
            const response = await fetch('../api/auth.php?action=check');
            const data = await response.json();
            
            if (data.success && data.data && data.data.user) {
                this.isAuthenticated = true;
                this.currentUser = data.data.user;
                
                // Store user data in localStorage for quick access
                localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
            } else {
                this.isAuthenticated = false;
                this.currentUser = null;
                
                // Clear any stale user data
                localStorage.removeItem('currentUser');
            }
        } catch (error) {
            console.error('Auth check failed:', error);
            this.isAuthenticated = false;
            this.currentUser = null;
            localStorage.removeItem('currentUser');
        }
    }

    setupNavigation() {
        // Create the navigation structure
        this.createNavigationElements();
        this.setupMobileMenu();
        this.setupSearchFunctionality();
    }

    createNavigationElements() {
        const navRight = document.querySelector('.nav-right');
        if (!navRight) return;

        // Clear existing nav-right content
        navRight.innerHTML = '';

        // Search container (always visible)
        const searchContainer = document.createElement('div');
        searchContainer.className = 'search-container';
        searchContainer.innerHTML = `
            <input type="text" placeholder="Search Ethiopian products..." class="search-bar">
            <i class="fas fa-search search-icon"></i>
        `;
        navRight.appendChild(searchContainer);

        // Navigation icons (only for authenticated users)
        if (this.isAuthenticated) {
            const navIcons = document.createElement('div');
            navIcons.className = 'nav-icons';
            navIcons.innerHTML = `
                <a href="#" class="nav-icon notification-icon" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">0</span>
                </a>
                <a href="cart.html" class="nav-icon cart-icon" title="Shopping Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>
            `;
            navRight.appendChild(navIcons);
        }

        // Auth links
        const authLinks = document.createElement('div');
        authLinks.className = 'auth-links';
        navRight.appendChild(authLinks);

        // Mobile menu toggle
        const mobileToggle = document.createElement('button');
        mobileToggle.className = 'mobile-menu-toggle';
        mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
        navRight.appendChild(mobileToggle);

        this.updateAuthLinks();
    }

    updateAuthLinks() {
        const authLinks = document.querySelector('.auth-links');
        if (!authLinks) return;

        if (this.isAuthenticated && this.currentUser) {
            const dashboardLink = this.getDashboardLink();
            authLinks.innerHTML = `
                <div class="user-menu">
                    <button class="user-menu-toggle">
                        <i class="fas fa-user-circle"></i>
                        <span>${this.currentUser.name}</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown">
                        <a href="${dashboardLink}" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a href="profile.html" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            Profile
                        </a>
                        <a href="orders.html" class="dropdown-item">
                            <i class="fas fa-shopping-bag"></i>
                            Orders
                        </a>
                        <div class="dropdown-divider"></div>
                        <button onclick="authNavigation.logout()" class="dropdown-item logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </div>
                </div>
            `;
            
            this.setupUserMenu();
        } else {
            authLinks.innerHTML = `
                <a href="signin.html" class="auth-link">Sign In</a>
                <span class="auth-divider">|</span>
                <a href="signup.html" class="auth-link">Sign Up</a>
            `;
        }
    }

    getDashboardLink() {
        if (!this.currentUser) return 'customer-dashboard.html';
        
        switch (this.currentUser.user_type) {
            case 'seller':
                return 'seller-dashboard.html';
            case 'admin':
                return 'admin-dashboard.html';
            default:
                return 'customer-dashboard.html';
        }
    }

    setupUserMenu() {
        const userMenuToggle = document.querySelector('.user-menu-toggle');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (userMenuToggle && userDropdown) {
            userMenuToggle.addEventListener('click', (e) => {
                e.preventDefault();
                userDropdown.classList.toggle('show');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                    userDropdown.classList.remove('show');
                }
            });
        }
    }

    setupEventListeners() {
        // Cart icon click handler
        document.addEventListener('click', (e) => {
            if (e.target.closest('.cart-icon')) {
                e.preventDefault();
                if (!this.isAuthenticated) {
                    this.showAuthRequiredMessage('cart');
                    return;
                }
                window.location.href = 'cart.html';
            }
        });

        // Notification icon click handler
        document.addEventListener('click', (e) => {
            if (e.target.closest('.notification-icon')) {
                e.preventDefault();
                if (!this.isAuthenticated) {
                    this.showAuthRequiredMessage('notifications');
                    return;
                }
                this.toggleNotificationPanel();
            }
        });

        // Add to cart button handlers
        document.addEventListener('click', (e) => {
            if (e.target.matches('.add-to-cart-btn') || e.target.closest('.add-to-cart-btn')) {
                if (!this.isAuthenticated) {
                    e.preventDefault();
                    this.showAuthRequiredMessage('add to cart');
                    return;
                }
            }
        });

        // Search functionality
        const searchBar = document.querySelector('.search-bar');
        const searchIcon = document.querySelector('.search-icon');
        
        if (searchBar) {
            searchBar.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch(searchBar.value);
                }
            });
        }

        if (searchIcon) {
            searchIcon.addEventListener('click', () => {
                if (searchBar) {
                    this.performSearch(searchBar.value);
                }
            });
        }
    }

    showAuthRequiredMessage(action) {
        const modal = document.createElement('div');
        modal.className = 'auth-required-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        `;

        modal.innerHTML = `
            <div style="
                background: white;
                padding: 2rem;
                border-radius: 12px;
                max-width: 400px;
                width: 90%;
                text-align: center;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            ">
                <div style="
                    width: 60px;
                    height: 60px;
                    background: #f8f9fa;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1rem;
                ">
                    <i class="fas fa-lock" style="font-size: 24px; color: #6c757d;"></i>
                </div>
                <h3 style="margin: 0 0 1rem 0; color: #333;">Sign In Required</h3>
                <p style="margin: 0 0 2rem 0; color: #666;">
                    You need to sign in to ${action}. Join thousands of customers supporting Ethiopian creators!
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <a href="signin.html" style="
                        background: #007bff;
                        color: white;
                        padding: 12px 24px;
                        border-radius: 6px;
                        text-decoration: none;
                        font-weight: 500;
                    ">Sign In</a>
                    <a href="signup.html" style="
                        background: #28a745;
                        color: white;
                        padding: 12px 24px;
                        border-radius: 6px;
                        text-decoration: none;
                        font-weight: 500;
                    ">Sign Up</a>
                    <button onclick="this.closest('.auth-required-modal').remove()" style="
                        background: #6c757d;
                        color: white;
                        padding: 12px 24px;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-weight: 500;
                    ">Cancel</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    async updateNavigationState() {
        if (this.isAuthenticated) {
            await this.loadCartCount();
            await this.loadNotificationCount();
        }
    }

    async loadCartCount() {
        if (!this.isAuthenticated) return;

        try {
            const response = await fetch('../api/cart.php?action=count');
            const data = await response.json();

            const cartCountElement = document.querySelector('.cart-count');
            if (cartCountElement) {
                const count = data.success ? (data.data.count || 0) : 0;
                cartCountElement.textContent = count;
                cartCountElement.style.display = count > 0 ? 'flex' : 'none';
            }
        } catch (error) {
            console.error('Failed to load cart count:', error);
        }
    }

    async loadNotificationCount() {
        if (!this.isAuthenticated) return;

        try {
            const response = await fetch('../api/notifications.php?action=count');
            const data = await response.json();

            const notificationCountElement = document.querySelector('.notification-count');
            if (notificationCountElement) {
                const count = data.success ? (data.data.unread_count || 0) : 0;
                notificationCountElement.textContent = count;
                notificationCountElement.style.display = count > 0 ? 'flex' : 'none';
            }
        } catch (error) {
            console.error('Failed to load notification count:', error);
            // Use mock data for demo
            const notificationCountElement = document.querySelector('.notification-count');
            if (notificationCountElement) {
                notificationCountElement.textContent = '2';
                notificationCountElement.style.display = 'flex';
            }
        }
    }

    toggleNotificationPanel() {
        let panel = document.querySelector('.notification-panel');
        
        if (panel) {
            panel.remove();
            return;
        }

        // Mock notifications for demo
        const notifications = [
            {
                id: 1,
                type: 'order',
                title: 'Order Confirmed',
                message: 'Your order #ORD-001 has been confirmed',
                time: '2 hours ago',
                read: false
            },
            {
                id: 2,
                type: 'promotion',
                title: 'Special Offer',
                message: '20% off on traditional textiles',
                time: '1 day ago',
                read: false
            }
        ];

        panel = document.createElement('div');
        panel.className = 'notification-panel';
        panel.style.cssText = `
            position: fixed;
            top: 70px;
            right: 20px;
            width: 350px;
            max-height: 500px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            overflow: hidden;
        `;

        panel.innerHTML = `
            <div style="padding: 1rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0;">Notifications</h4>
                <button onclick="this.closest('.notification-panel').remove()" style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">&times;</button>
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
                ${notifications.map(notification => `
                    <div style="
                        padding: 1rem;
                        border-bottom: 1px solid #eee;
                        ${!notification.read ? 'background: #f8f9fa;' : ''}
                    ">
                        <div style="display: flex; gap: 0.75rem;">
                            <div style="
                                width: 40px;
                                height: 40px;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                background: ${notification.type === 'order' ? '#28a745' : '#dc3545'};
                                color: white;
                                flex-shrink: 0;
                            ">
                                <i class="fas fa-${notification.type === 'order' ? 'shopping-bag' : 'tag'}"></i>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 0.25rem;">${notification.title}</div>
                                <div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">${notification.message}</div>
                                <div style="font-size: 0.75rem; color: #999;">${notification.time}</div>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;

        document.body.appendChild(panel);

        // Close panel when clicking outside
        setTimeout(() => {
            document.addEventListener('click', (e) => {
                if (!panel.contains(e.target) && !document.querySelector('.notification-icon').contains(e.target)) {
                    panel.remove();
                }
            }, { once: true });
        }, 100);
    }

    setupMobileMenu() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        
        if (mobileToggle && navLinks) {
            mobileToggle.addEventListener('click', () => {
                navLinks.classList.toggle('mobile-open');
                
                const icon = mobileToggle.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-bars');
                    icon.classList.toggle('fa-times');
                }
            });
        }
    }

    performSearch(query) {
        if (!query.trim()) return;
        
        const searchParams = new URLSearchParams();
        searchParams.set('search', query.trim());
        window.location.href = `collections.html?${searchParams.toString()}`;
    }

    async logout() {
        try {
            await fetch('../api/auth.php?action=logout', { method: 'POST' });
            
            // Clear local data
            localStorage.removeItem('currentUser');
            this.isAuthenticated = false;
            this.currentUser = null;
            
            // Redirect to home page
            window.location.href = 'index.html';
        } catch (error) {
            console.error('Logout failed:', error);
            // Force redirect anyway
            window.location.href = 'index.html';
        }
    }

    // Public method to refresh auth state
    async refreshAuthState() {
        await this.checkAuthStatus();
        this.createNavigationElements();
        await this.updateNavigationState();
    }
}

// Initialize the auth-aware navigation
const authNavigation = new AuthAwareNavigation();

// Global functions for backward compatibility
async function updateCartCount() {
    await authNavigation.loadCartCount();
}

async function updateAuthState() {
    await authNavigation.refreshAuthState();
}

function logout() {
    authNavigation.logout();
}

// Export for use in other scripts
window.authNavigation = authNavigation;