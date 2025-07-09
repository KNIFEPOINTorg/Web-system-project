// Navigation functionality for LFLshop

class NavigationManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupMobileMenu();
        this.setupSearchFunctionality();
        this.setupNotifications();
        this.highlightActiveNavItem();
    }

    setupMobileMenu() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        
        if (mobileToggle && navLinks) {
            mobileToggle.addEventListener('click', () => {
                navLinks.classList.toggle('mobile-open');
                
                // Toggle icon
                const icon = mobileToggle.querySelector('i');
                if (icon) {
                    icon.classList.toggle('fa-bars');
                    icon.classList.toggle('fa-times');
                }
            });

            // Close mobile menu when clicking on a link
            navLinks.addEventListener('click', (e) => {
                if (e.target.tagName === 'A') {
                    navLinks.classList.remove('mobile-open');
                    const icon = mobileToggle.querySelector('i');
                    if (icon) {
                        icon.classList.add('fa-bars');
                        icon.classList.remove('fa-times');
                    }
                }
            });

            // Close mobile menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!mobileToggle.contains(e.target) && !navLinks.contains(e.target)) {
                    navLinks.classList.remove('mobile-open');
                    const icon = mobileToggle.querySelector('i');
                    if (icon) {
                        icon.classList.add('fa-bars');
                        icon.classList.remove('fa-times');
                    }
                }
            });
        }
    }

    setupSearchFunctionality() {
        const searchBar = document.querySelector('.search-bar');
        const searchIcon = document.querySelector('.search-icon');
        
        if (searchBar) {
            searchBar.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.performSearch(searchBar.value);
                }
            });

            // Search suggestions (basic implementation)
            searchBar.addEventListener('input', (e) => {
                this.showSearchSuggestions(e.target.value);
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

    performSearch(query) {
        if (!query.trim()) return;
        
        // Redirect to collections page with search query
        const searchParams = new URLSearchParams();
        searchParams.set('search', query.trim());
        window.location.href = `collections.html?${searchParams.toString()}`;
    }

    showSearchSuggestions(query) {
        if (!query || query.length < 2) {
            this.hideSearchSuggestions();
            return;
        }

        // Mock search suggestions
        const suggestions = [
            'Traditional Habesha Dress',
            'Ethiopian Coffee',
            'Berbere Spice',
            'Silver Cross',
            'Handwoven Scarf',
            'Traditional Jebena',
            'Ethiopian Art',
            'Pottery'
        ].filter(item => 
            item.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 5);

        if (suggestions.length === 0) {
            this.hideSearchSuggestions();
            return;
        }

        let suggestionsContainer = document.querySelector('.search-suggestions');
        if (!suggestionsContainer) {
            suggestionsContainer = document.createElement('div');
            suggestionsContainer.className = 'search-suggestions';
            suggestionsContainer.style.cssText = `
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 0.5rem 0.5rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                max-height: 200px;
                overflow-y: auto;
            `;
            
            const searchContainer = document.querySelector('.search-container');
            if (searchContainer) {
                searchContainer.style.position = 'relative';
                searchContainer.appendChild(suggestionsContainer);
            }
        }

        suggestionsContainer.innerHTML = suggestions.map(suggestion => `
            <div class="search-suggestion" style="
                padding: 0.75rem 1rem;
                cursor: pointer;
                border-bottom: 1px solid #eee;
                transition: background-color 0.2s;
            " onmouseover="this.style.backgroundColor='#f8f9fa'" 
               onmouseout="this.style.backgroundColor='white'"
               onclick="navigationManager.selectSuggestion('${suggestion}')">
                <i class="fas fa-search" style="color: #666; margin-right: 0.5rem;"></i>
                ${suggestion}
            </div>
        `).join('');
    }

    selectSuggestion(suggestion) {
        const searchBar = document.querySelector('.search-bar');
        if (searchBar) {
            searchBar.value = suggestion;
            this.performSearch(suggestion);
        }
    }

    hideSearchSuggestions() {
        const suggestionsContainer = document.querySelector('.search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.remove();
        }
    }

    setupNotifications() {
        const notificationIcon = document.querySelector('.notification-icon');
        const notificationCount = document.querySelector('.notification-count');
        
        if (notificationIcon) {
            notificationIcon.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleNotificationPanel();
            });
        }

        // Mock notification data
        this.notifications = [
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
            },
            {
                id: 3,
                type: 'system',
                title: 'Welcome to LFLshop',
                message: 'Thank you for joining our marketplace',
                time: '3 days ago',
                read: true
            }
        ];

        this.updateNotificationCount();
    }

    updateNotificationCount() {
        const notificationCount = document.querySelector('.notification-count');
        if (notificationCount) {
            const unreadCount = this.notifications.filter(n => !n.read).length;
            notificationCount.textContent = unreadCount;
            notificationCount.style.display = unreadCount > 0 ? 'flex' : 'none';
        }
    }

    toggleNotificationPanel() {
        let panel = document.querySelector('.notification-panel');
        
        if (panel) {
            panel.remove();
            return;
        }

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
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            overflow: hidden;
        `;

        panel.innerHTML = `
            <div style="padding: 1rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <h4 style="margin: 0;">Notifications</h4>
                <button onclick="this.closest('.notification-panel').remove()" style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">&times;</button>
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
                ${this.notifications.length === 0 ? 
                    '<div style="padding: 2rem; text-align: center; color: #666;">No notifications</div>' :
                    this.notifications.map(notification => `
                        <div class="notification-item" style="
                            padding: 1rem;
                            border-bottom: 1px solid #eee;
                            cursor: pointer;
                            ${!notification.read ? 'background: #f8f9fa;' : ''}
                        " onclick="navigationManager.markAsRead(${notification.id})">
                            <div style="display: flex; gap: 0.75rem;">
                                <div style="
                                    width: 40px;
                                    height: 40px;
                                    border-radius: 50%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    background: ${this.getNotificationColor(notification.type)};
                                    color: white;
                                    flex-shrink: 0;
                                ">
                                    <i class="fas fa-${this.getNotificationIcon(notification.type)}"></i>
                                </div>
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; margin-bottom: 0.25rem;">${notification.title}</div>
                                    <div style="font-size: 0.875rem; color: #666; margin-bottom: 0.25rem;">${notification.message}</div>
                                    <div style="font-size: 0.75rem; color: #999;">${notification.time}</div>
                                </div>
                                ${!notification.read ? '<div style="width: 8px; height: 8px; background: #007bff; border-radius: 50%; margin-top: 0.5rem;"></div>' : ''}
                            </div>
                        </div>
                    `).join('')
                }
            </div>
            ${this.notifications.length > 0 ? `
                <div style="padding: 1rem; border-top: 1px solid #eee; text-align: center;">
                    <button onclick="navigationManager.markAllAsRead()" style="
                        background: none;
                        border: none;
                        color: #007bff;
                        cursor: pointer;
                        font-size: 0.875rem;
                    ">Mark all as read</button>
                </div>
            ` : ''}
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

    getNotificationIcon(type) {
        switch (type) {
            case 'order': return 'shopping-bag';
            case 'promotion': return 'tag';
            case 'system': return 'info-circle';
            default: return 'bell';
        }
    }

    getNotificationColor(type) {
        switch (type) {
            case 'order': return '#28a745';
            case 'promotion': return '#dc3545';
            case 'system': return '#17a2b8';
            default: return '#6c757d';
        }
    }

    markAsRead(notificationId) {
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification) {
            notification.read = true;
            this.updateNotificationCount();
            
            // Update the panel if it's open
            const panel = document.querySelector('.notification-panel');
            if (panel) {
                panel.remove();
                this.toggleNotificationPanel();
            }
        }
    }

    markAllAsRead() {
        this.notifications.forEach(n => n.read = true);
        this.updateNotificationCount();
        
        // Update the panel
        const panel = document.querySelector('.notification-panel');
        if (panel) {
            panel.remove();
            this.toggleNotificationPanel();
        }
    }

    highlightActiveNavItem() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const navLinks = document.querySelectorAll('.nav-links a');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            const linkPage = link.getAttribute('href');
            
            if (linkPage === currentPage || 
                (currentPage === '' && linkPage === 'index.html') ||
                (currentPage === 'index.html' && linkPage === 'index.html')) {
                link.classList.add('active');
            }
        });
    }

    // Breadcrumb functionality
    generateBreadcrumbs() {
        const breadcrumbContainer = document.querySelector('.breadcrumb-nav');
        if (!breadcrumbContainer) return;

        const currentPage = window.location.pathname.split('/').pop() || 'index.html';
        const breadcrumbs = this.getBreadcrumbsForPage(currentPage);
        
        breadcrumbContainer.innerHTML = breadcrumbs.map((crumb, index) => {
            const isLast = index === breadcrumbs.length - 1;
            return `
                ${index > 0 ? '<span class="breadcrumb-separator">/</span>' : ''}
                ${isLast ? 
                    `<span class="breadcrumb-current">${crumb.title}</span>` :
                    `<a href="${crumb.url}" class="breadcrumb-link">${crumb.title}</a>`
                }
            `;
        }).join('');
    }

    getBreadcrumbsForPage(page) {
        const breadcrumbMap = {
            'index.html': [{ title: 'Home', url: 'index.html' }],
            'collections.html': [
                { title: 'Home', url: 'index.html' },
                { title: 'Collections', url: 'collections.html' }
            ],
            'cart.html': [
                { title: 'Home', url: 'index.html' },
                { title: 'Shopping Cart', url: 'cart.html' }
            ],
            'customer-dashboard.html': [
                { title: 'Home', url: 'index.html' },
                { title: 'Dashboard', url: 'customer-dashboard.html' }
            ],
            'seller-dashboard.html': [
                { title: 'Home', url: 'index.html' },
                { title: 'Seller Dashboard', url: 'seller-dashboard.html' }
            ]
        };

        return breadcrumbMap[page] || [{ title: 'Home', url: 'index.html' }];
    }
}

// Initialize navigation manager
const navigationManager = new NavigationManager();

// Global functions for backward compatibility
function toggleMobileMenu() {
    const navLinks = document.querySelector('.nav-links');
    if (navLinks) {
        navLinks.classList.toggle('mobile-open');
    }
}

function performSearch(query) {
    navigationManager.performSearch(query);
}