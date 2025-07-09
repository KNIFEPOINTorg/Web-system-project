// Notifications System for LFLshop

// Notification storage
let notifications = [];
let notificationCount = 0;

// Initialize notifications system
document.addEventListener('DOMContentLoaded', function() {
    if (isLoggedIn()) {
        initializeNotifications();
        loadDemoNotifications();
        setupNotificationEventListeners();
        updateNotificationDisplay();
    }
});

// Initialize notification system
function initializeNotifications() {
    // Load notifications from localStorage
    const savedNotifications = localStorage.getItem('lflshop_notifications');
    if (savedNotifications) {
        notifications = JSON.parse(savedNotifications);
    }
    
    // Show notification dropdown for logged-in users
    const notificationDropdown = document.getElementById('notification-dropdown');
    if (notificationDropdown && isLoggedIn()) {
        notificationDropdown.style.display = 'block';
    }
}

// Load demo notifications for testing
function loadDemoNotifications() {
    const currentUser = getCurrentUser();
    if (!currentUser) return;
    
    // Only load demo notifications if none exist
    if (notifications.length === 0) {
        const demoNotifications = [
            {
                id: 1,
                type: 'order',
                title: 'Order Shipped',
                message: 'Your order #LFL-2024-101 has been shipped and is on its way!',
                time: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(), // 2 hours ago
                read: false,
                icon: 'fas fa-shipping-fast'
            },
            {
                id: 2,
                type: 'promotion',
                title: 'Special Offer',
                message: 'Get 25% off on all traditional clothing this week!',
                time: new Date(Date.now() - 6 * 60 * 60 * 1000).toISOString(), // 6 hours ago
                read: false,
                icon: 'fas fa-tag'
            },
            {
                id: 3,
                type: 'message',
                title: 'New Review',
                message: 'Someone left a 5-star review on your Traditional Habesha Dress!',
                time: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(), // 1 day ago
                read: true,
                icon: 'fas fa-star'
            },
            {
                id: 4,
                type: 'system',
                title: 'Account Verified',
                message: 'Your seller account has been successfully verified!',
                time: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000).toISOString(), // 3 days ago
                read: true,
                icon: 'fas fa-check-circle'
            }
        ];
        
        // Filter notifications based on user type
        if (currentUser.userType === 'Shop Only') {
            notifications = demoNotifications.filter(n => n.type !== 'message' || !n.message.includes('review'));
        } else {
            notifications = demoNotifications;
        }
        
        saveNotifications();
    }
}

// Setup event listeners for notification system
function setupNotificationEventListeners() {
    // Mark all as read
    const markAllReadBtn = document.getElementById('mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', markAllAsRead);
    }
    
    // Clear all notifications
    const clearAllBtn = document.getElementById('clear-all');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', clearAllNotifications);
    }
    
    // Notification icon click
    const notificationIcon = document.getElementById('notification-icon');
    if (notificationIcon) {
        notificationIcon.addEventListener('click', function(e) {
            e.preventDefault();
            toggleNotificationPanel();
        });
    }
    
    // Close notification panel when clicking outside
    document.addEventListener('click', function(e) {
        const notificationDropdown = document.getElementById('notification-dropdown');
        if (notificationDropdown && !notificationDropdown.contains(e.target)) {
            closeNotificationPanel();
        }
    });
}

// Update notification display
function updateNotificationDisplay() {
    updateNotificationCount();
    renderNotificationList();
}

// Update notification count badge
function updateNotificationCount() {
    const unreadCount = notifications.filter(n => !n.read).length;
    const countElement = document.getElementById('notification-count');
    
    if (countElement) {
        countElement.textContent = unreadCount;
        countElement.style.display = unreadCount > 0 ? 'flex' : 'none';
    }
    
    notificationCount = unreadCount;
}

// Render notification list
function renderNotificationList() {
    const notificationList = document.getElementById('notification-list');
    if (!notificationList) return;
    
    if (notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="empty-notifications">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications yet</p>
            </div>
        `;
        return;
    }
    
    // Sort notifications by time (newest first)
    const sortedNotifications = [...notifications].sort((a, b) => new Date(b.time) - new Date(a.time));
    
    notificationList.innerHTML = sortedNotifications.map(notification => `
        <div class="notification-item ${notification.read ? '' : 'unread'}" onclick="markAsRead(${notification.id})">
            <div class="notification-icon-type ${notification.type}">
                <i class="${notification.icon}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${formatNotificationTime(notification.time)}</div>
            </div>
        </div>
    `).join('');
}

// Format notification time
function formatNotificationTime(timeString) {
    const time = new Date(timeString);
    const now = new Date();
    const diffMs = now - time;
    const diffMins = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    if (diffMins < 1) {
        return 'Just now';
    } else if (diffMins < 60) {
        return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    } else if (diffHours < 24) {
        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    } else if (diffDays < 7) {
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    } else {
        return time.toLocaleDateString();
    }
}

// Toggle notification panel
function toggleNotificationPanel() {
    const panel = document.getElementById('notification-panel');
    if (panel) {
        const isVisible = panel.style.opacity === '1';
        if (isVisible) {
            closeNotificationPanel();
        } else {
            openNotificationPanel();
        }
    }
}

// Open notification panel
function openNotificationPanel() {
    const panel = document.getElementById('notification-panel');
    if (panel) {
        panel.style.opacity = '1';
        panel.style.visibility = 'visible';
        panel.style.transform = 'translateY(0)';
    }
}

// Close notification panel
function closeNotificationPanel() {
    const panel = document.getElementById('notification-panel');
    if (panel) {
        panel.style.opacity = '0';
        panel.style.visibility = 'hidden';
        panel.style.transform = 'translateY(-10px)';
    }
}

// Mark single notification as read
function markAsRead(notificationId) {
    const notification = notifications.find(n => n.id === notificationId);
    if (notification && !notification.read) {
        notification.read = true;
        saveNotifications();
        updateNotificationDisplay();
    }
}

// Mark all notifications as read
function markAllAsRead() {
    notifications.forEach(notification => {
        notification.read = true;
    });
    saveNotifications();
    updateNotificationDisplay();
    showNotificationToast('All notifications marked as read', 'success');
}

// Clear all notifications
function clearAllNotifications() {
    if (notifications.length === 0) {
        showNotificationToast('No notifications to clear', 'info');
        return;
    }
    
    notifications = [];
    saveNotifications();
    updateNotificationDisplay();
    showNotificationToast('All notifications cleared', 'success');
}

// Add new notification
function addNotification(type, title, message, icon = null) {
    const notification = {
        id: Date.now(),
        type: type,
        title: title,
        message: message,
        time: new Date().toISOString(),
        read: false,
        icon: icon || getDefaultIcon(type)
    };
    
    notifications.unshift(notification);
    
    // Keep only last 50 notifications
    if (notifications.length > 50) {
        notifications = notifications.slice(0, 50);
    }
    
    saveNotifications();
    updateNotificationDisplay();
    
    // Show toast notification
    showNotificationToast(title, 'info');
}

// Get default icon for notification type
function getDefaultIcon(type) {
    const icons = {
        order: 'fas fa-shopping-bag',
        message: 'fas fa-envelope',
        system: 'fas fa-cog',
        promotion: 'fas fa-tag'
    };
    return icons[type] || 'fas fa-bell';
}

// Save notifications to localStorage
function saveNotifications() {
    localStorage.setItem('lflshop_notifications', JSON.stringify(notifications));
}

// Show notification toast
function showNotificationToast(message, type = 'info') {
    // Create toast element if it doesn't exist
    let toast = document.getElementById('notification-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'notification-toast';
        toast.className = 'notification-toast';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            padding: 16px 20px;
            z-index: 2001;
            max-width: 300px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            display: none;
            border-left: 4px solid #C53A2B;
        `;
        
        toast.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="toast-icon fas fa-bell" style="color: #C53A2B;"></i>
                <span class="toast-message" style="font-family: 'Open Sans', sans-serif; color: #2C2C2C;"></span>
                <button class="toast-close" style="background: none; border: none; color: #666; cursor: pointer; font-size: 1.2rem; padding: 0; width: 20px; height: 20px;">&times;</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Close button functionality
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            hideNotificationToast();
        });
    }
    
    const messageElement = toast.querySelector('.toast-message');
    const iconElement = toast.querySelector('.toast-icon');
    
    messageElement.textContent = message;
    
    // Set icon based on type
    if (type === 'success') {
        iconElement.className = 'toast-icon fas fa-check-circle';
        iconElement.style.color = '#28a745';
        toast.style.borderLeftColor = '#28a745';
    } else if (type === 'error') {
        iconElement.className = 'toast-icon fas fa-exclamation-circle';
        iconElement.style.color = '#dc3545';
        toast.style.borderLeftColor = '#dc3545';
    } else {
        iconElement.className = 'toast-icon fas fa-info-circle';
        iconElement.style.color = '#007bff';
        toast.style.borderLeftColor = '#007bff';
    }
    
    // Show toast
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto hide after 4 seconds
    setTimeout(() => {
        hideNotificationToast();
    }, 4000);
}

// Hide notification toast
function hideNotificationToast() {
    const toast = document.getElementById('notification-toast');
    if (toast) {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 300);
    }
}

// Export functions for global use
window.addNotification = addNotification;
window.markAsRead = markAsRead;
window.markAllAsRead = markAllAsRead;
window.clearAllNotifications = clearAllNotifications;
window.showNotificationToast = showNotificationToast;
