<?php
/**
 * Notifications Page - LFLshop
 * Display and manage user notifications
 */

session_start();
require_once 'php/middleware/auth_middleware.php';
require_once 'php/classes/Notification.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$notification = new Notification();

// Get user notifications
$notifications = $notification->getUserNotifications($userId, false, 50, 0);
$unreadCount = $notification->getUnreadCount($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - LFLshop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/design-system.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/enhanced-search.css">
    <style>
        .notifications-main {
            padding: var(--space-8) 0;
            min-height: calc(100vh - 80px);
            background: var(--bg-secondary);
        }

        .notifications-header {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: var(--space-6);
            margin-bottom: var(--space-6);
            box-shadow: var(--shadow-sm);
        }

        .notifications-header h1 {
            font-size: var(--text-2xl);
            font-weight: var(--font-bold);
            color: var(--text-primary);
            margin-bottom: var(--space-2);
        }

        .notifications-stats {
            display: flex;
            gap: var(--space-6);
            margin-top: var(--space-4);
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: var(--space-2);
            color: var(--text-secondary);
            font-size: var(--text-sm);
        }

        .stat-number {
            font-weight: var(--font-semibold);
            color: var(--primary-color);
        }

        .notifications-actions {
            display: flex;
            gap: var(--space-3);
            margin-top: var(--space-4);
        }

        .btn-action {
            padding: var(--space-2) var(--space-4);
            border: 1px solid var(--border-light);
            background: var(--white);
            color: var(--text-secondary);
            border-radius: var(--radius-md);
            font-size: var(--text-sm);
            cursor: pointer;
            transition: all var(--transition-fast);
            display: flex;
            align-items: center;
            gap: var(--space-2);
        }

        .btn-action:hover {
            background: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }

        .notifications-container {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .notification-item {
            padding: var(--space-4);
            border-bottom: 1px solid var(--border-light);
            transition: background-color var(--transition-fast);
            cursor: pointer;
            position: relative;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: var(--bg-secondary);
        }

        .notification-item.unread {
            background: rgba(212, 165, 116, 0.05);
            border-left: 4px solid var(--primary-color);
        }

        .notification-content {
            display: flex;
            gap: var(--space-4);
            align-items: flex-start;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: var(--text-lg);
            color: var(--white);
            flex-shrink: 0;
        }

        .notification-icon.welcome {
            background: var(--success);
        }

        .notification-icon.order_update {
            background: var(--info);
        }

        .notification-icon.promotion {
            background: var(--warning);
        }

        .notification-icon.system {
            background: var(--text-secondary);
        }

        .notification-details {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-size: var(--text-base);
            font-weight: var(--font-semibold);
            color: var(--text-primary);
            margin-bottom: var(--space-1);
            line-height: var(--line-height-tight);
        }

        .notification-message {
            font-size: var(--text-sm);
            color: var(--text-secondary);
            line-height: var(--line-height-relaxed);
            margin-bottom: var(--space-2);
        }

        .notification-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: var(--text-xs);
            color: var(--text-muted);
        }

        .notification-time {
            display: flex;
            align-items: center;
            gap: var(--space-1);
        }

        .notification-actions {
            display: flex;
            gap: var(--space-2);
            opacity: 0;
            transition: opacity var(--transition-fast);
        }

        .notification-item:hover .notification-actions {
            opacity: 1;
        }

        .action-btn {
            padding: var(--space-1) var(--space-2);
            border: none;
            background: transparent;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: var(--radius-sm);
            font-size: var(--text-xs);
            transition: all var(--transition-fast);
        }

        .action-btn:hover {
            background: var(--bg-secondary);
            color: var(--text-primary);
        }

        .priority-high {
            position: relative;
        }

        .priority-high::before {
            content: '';
            position: absolute;
            top: var(--space-2);
            right: var(--space-2);
            width: 8px;
            height: 8px;
            background: var(--error);
            border-radius: var(--radius-full);
        }

        .empty-state {
            text-align: center;
            padding: var(--space-16) var(--space-6);
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: var(--text-6xl);
            color: var(--text-muted);
            margin-bottom: var(--space-4);
        }

        .empty-state h3 {
            font-size: var(--text-xl);
            font-weight: var(--font-semibold);
            color: var(--text-primary);
            margin-bottom: var(--space-2);
        }

        .loading {
            text-align: center;
            padding: var(--space-8);
            color: var(--text-secondary);
        }

        .loading i {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .notifications-main {
                padding: var(--space-4) 0;
            }

            .notifications-header {
                padding: var(--space-4);
                margin-bottom: var(--space-4);
            }

            .notifications-stats {
                flex-direction: column;
                gap: var(--space-3);
            }

            .notifications-actions {
                flex-wrap: wrap;
            }

            .notification-content {
                gap: var(--space-3);
            }

            .notification-icon {
                width: 32px;
                height: 32px;
                font-size: var(--text-base);
            }

            .notification-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: var(--space-2);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-left">
                <div class="logo">
                    <img src="Logo/LOCALS.png" alt="LFLshop Logo">
                </div>
                <ul class="nav-links">
                    <li><a href="html/index.html">Home</a></li>
                    <li><a href="html/collections.html">Collections</a></li>
                    <li><a href="html/sale.html">Sale</a></li>
                    <li><a href="html/about.html">About</a></li>
                </ul>
            </div>
            
            <div class="nav-right">
                <!-- Enhanced Search Container -->
                <div class="search-container enhanced-search">
                    <div class="search-input-wrapper">
                        <input type="text" 
                               id="globalSearchInput" 
                               placeholder="Search Ethiopian products..." 
                               class="search-bar"
                               autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                        <button class="search-clear" id="searchClear" style="display: none;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <!-- Search Suggestions Dropdown -->
                    <div class="search-suggestions" id="searchSuggestions" style="display: none;">
                        <div class="suggestions-header">
                            <span>Search Suggestions</span>
                        </div>
                        <div class="suggestions-list" id="suggestionsList">
                            <!-- Dynamic suggestions will be inserted here -->
                        </div>
                        <div class="suggestions-footer">
                            <button class="view-all-results" id="viewAllResults">
                                <i class="fas fa-search"></i>
                                View all results
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Icons (Always visible for authenticated users) -->
                <div class="nav-icons">
                    <a href="wishlist.php" class="nav-icon" title="Wishlist">
                        <i class="fas fa-heart"></i>
                        <span class="notification-count">0</span>
                    </a>
                    <a href="cart.php" class="nav-icon" title="Shopping Cart">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                    <a href="notifications.php" class="nav-icon active" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge"><?php echo $unreadCount; ?></span>
                    </a>
                </div>
                
                <!-- User Menu (Always visible for authenticated users) -->
                <div class="user-menu">
                    <button class="user-menu-toggle">
                        <span><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="user-dropdown">
                        <a href="dashboard.php" class="dropdown-item">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a href="account-settings.php" class="dropdown-item">
                            <i class="fas fa-user-cog"></i>
                            Account Settings
                        </a>
                        <a href="orders.php" class="dropdown-item">
                            <i class="fas fa-shopping-bag"></i>
                            My Orders
                        </a>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item logout-btn" onclick="logout()">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="notifications-main">
        <div class="container">
            <!-- Notifications Header -->
            <div class="notifications-header">
                <h1>Notifications</h1>
                <p>Stay updated with your orders, promotions, and important updates</p>
                
                <div class="notifications-stats">
                    <div class="stat-item">
                        <i class="fas fa-bell"></i>
                        <span>Total: <span class="stat-number" id="totalCount"><?php echo count($notifications); ?></span></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-envelope"></i>
                        <span>Unread: <span class="stat-number" id="unreadCount"><?php echo $unreadCount; ?></span></span>
                    </div>
                </div>
                
                <div class="notifications-actions">
                    <button class="btn-action" onclick="markAllAsRead()" <?php echo $unreadCount === 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-check-double"></i>
                        Mark All as Read
                    </button>
                    <button class="btn-action" onclick="deleteAllRead()">
                        <i class="fas fa-trash"></i>
                        Clear Read
                    </button>
                    <button class="btn-action" onclick="refreshNotifications()">
                        <i class="fas fa-sync-alt"></i>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Notifications Container -->
            <div class="notifications-container">
                <div id="notificationsList">
                    <?php if (empty($notifications)): ?>
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <h3>No notifications yet</h3>
                            <p>You'll see notifications here when you have updates about your orders, new promotions, and other important information.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notification-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?> <?php echo $notif['priority'] === 'high' ? 'priority-high' : ''; ?>" 
                                 data-id="<?php echo $notif['id']; ?>"
                                 onclick="handleNotificationClick(<?php echo $notif['id']; ?>, '<?php echo htmlspecialchars($notif['action_url'] ?? ''); ?>')">
                                <div class="notification-content">
                                    <div class="notification-icon <?php echo $notif['type']; ?>">
                                        <?php
                                        $icons = [
                                            'welcome' => 'fas fa-hand-wave',
                                            'order_update' => 'fas fa-shopping-bag',
                                            'promotion' => 'fas fa-tag',
                                            'system' => 'fas fa-cog'
                                        ];
                                        $iconClass = $icons[$notif['type']] ?? 'fas fa-bell';
                                        ?>
                                        <i class="<?php echo $iconClass; ?>"></i>
                                    </div>
                                    
                                    <div class="notification-details">
                                        <div class="notification-title"><?php echo htmlspecialchars($notif['title']); ?></div>
                                        <div class="notification-message"><?php echo htmlspecialchars($notif['message']); ?></div>
                                        
                                        <div class="notification-meta">
                                            <div class="notification-time">
                                                <i class="fas fa-clock"></i>
                                                <span><?php echo getTimeAgo($notif['created_at']); ?></span>
                                            </div>
                                            
                                            <div class="notification-actions">
                                                <?php if (!$notif['is_read']): ?>
                                                    <button class="action-btn" onclick="event.stopPropagation(); markAsRead(<?php echo $notif['id']; ?>)">
                                                        <i class="fas fa-check"></i> Mark Read
                                                    </button>
                                                <?php endif; ?>
                                                <button class="action-btn" onclick="event.stopPropagation(); deleteNotification(<?php echo $notif['id']; ?>)">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="javascript/auth-state-manager.js"></script>
    <script src="javascript/enhanced-search.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize authentication state manager
            if (window.authStateManager) {
                window.authStateManager.init().then(() => {
                    console.log('Authentication state manager initialized on notifications page');
                });
            }
            
            // Initialize enhanced search
            if (window.enhancedSearch) {
                window.enhancedSearch.init();
                console.log('Enhanced search initialized on notifications page');
            }
            
            // Initialize user menu dropdown
            const userMenuToggle = document.querySelector('.user-menu-toggle');
            const userDropdown = document.querySelector('.user-dropdown');

            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    userDropdown.style.opacity = userDropdown.style.opacity === '1' ? '0' : '1';
                    userDropdown.style.visibility = userDropdown.style.visibility === 'visible' ? 'hidden' : 'visible';
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.style.opacity = '0';
                        userDropdown.style.visibility = 'hidden';
                    }
                });
            }
        });
        
        // Notification management functions
        function handleNotificationClick(notificationId, actionUrl) {
            // Mark as read if unread
            const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
            if (notificationElement && notificationElement.classList.contains('unread')) {
                markAsRead(notificationId);
            }
            
            // Navigate to action URL if provided
            if (actionUrl && actionUrl.trim() !== '') {
                window.location.href = actionUrl;
            }
        }
        
        function markAsRead(notificationId) {
            fetch('php/handlers/notification_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_as_read&notification_id=${notificationId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
                    if (notificationElement) {
                        notificationElement.classList.remove('unread');
                        const markReadBtn = notificationElement.querySelector('.action-btn');
                        if (markReadBtn && markReadBtn.textContent.includes('Mark Read')) {
                            markReadBtn.remove();
                        }
                    }
                    updateCounts();
                } else {
                    showNotification('Failed to mark notification as read', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to mark notification as read', 'error');
            });
        }
        
        function markAllAsRead() {
            fetch('php/handlers/notification_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_as_read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                        const markReadBtn = item.querySelector('.action-btn');
                        if (markReadBtn && markReadBtn.textContent.includes('Mark Read')) {
                            markReadBtn.remove();
                        }
                    });
                    updateCounts();
                    showNotification('All notifications marked as read', 'success');
                } else {
                    showNotification('Failed to mark all notifications as read', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to mark all notifications as read', 'error');
            });
        }
        
        function deleteNotification(notificationId) {
            if (!confirm('Are you sure you want to delete this notification?')) {
                return;
            }
            
            fetch('php/handlers/notification_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_notification&notification_id=${notificationId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const notificationElement = document.querySelector(`[data-id="${notificationId}"]`);
                    if (notificationElement) {
                        notificationElement.remove();
                    }
                    updateCounts();
                    showNotification('Notification deleted', 'success');
                } else {
                    showNotification('Failed to delete notification', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to delete notification', 'error');
            });
        }
        
        function deleteAllRead() {
            if (!confirm('Are you sure you want to delete all read notifications?')) {
                return;
            }
            
            fetch('php/handlers/notification_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=delete_all_read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item:not(.unread)').forEach(item => {
                        item.remove();
                    });
                    updateCounts();
                    showNotification('All read notifications deleted', 'success');
                } else {
                    showNotification('Failed to delete read notifications', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to delete read notifications', 'error');
            });
        }
        
        function refreshNotifications() {
            window.location.reload();
        }
        
        function updateCounts() {
            const totalCount = document.querySelectorAll('.notification-item').length;
            const unreadCount = document.querySelectorAll('.notification-item.unread').length;
            
            document.getElementById('totalCount').textContent = totalCount;
            document.getElementById('unreadCount').textContent = unreadCount;
            
            // Update notification badge in navigation
            const notificationBadge = document.querySelector('.notification-badge');
            if (notificationBadge) {
                notificationBadge.textContent = unreadCount;
                notificationBadge.style.display = unreadCount > 0 ? 'block' : 'none';
            }
            
            // Update mark all as read button
            const markAllBtn = document.querySelector('button[onclick="markAllAsRead()"]');
            if (markAllBtn) {
                markAllBtn.disabled = unreadCount === 0;
            }
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification-toast ${type}`;
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px; padding: 16px; background: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}" style="color: ${type === 'success' ? '#28a745' : '#dc3545'};"></i>
                    <span style="color: #333;">${message}</span>
                </div>
            `;
            
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1100;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Hide notification after 3 seconds
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 3000);
        }
        
        // Logout function
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                fetch('php/handlers/auth_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=logout'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect || 'html/index.html';
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = 'html/index.html';
                });
            }
        }
    </script>
</body>
</html>

<?php
/**
 * Get human-readable time ago string
 */
function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) {
        return 'Just now';
    } elseif ($time < 3600) {
        $minutes = floor($time / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time < 86400) {
        $hours = floor($time / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time < 2592000) {
        $days = floor($time / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($time < 31536000) {
        $months = floor($time / 2592000);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    } else {
        $years = floor($time / 31536000);
        return $years . ' year' . ($years > 1 ? 's' : '') . ' ago';
    }
}
?>
