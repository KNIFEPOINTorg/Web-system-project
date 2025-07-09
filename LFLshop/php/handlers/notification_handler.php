<?php
/**
 * Notification Handler
 * Handles all notification-related API requests
 */

session_start();
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../middleware/auth_middleware.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is authenticated
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'errors' => ['Authentication required']]);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$notification = new Notification();
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'get_notifications':
        handleGetNotifications($notification, $userId);
        break;
    case 'get_unread_count':
        handleGetUnreadCount($notification, $userId);
        break;
    case 'mark_as_read':
        handleMarkAsRead($notification, $userId);
        break;
    case 'mark_all_as_read':
        handleMarkAllAsRead($notification, $userId);
        break;
    case 'delete_notification':
        handleDeleteNotification($notification, $userId);
        break;
    case 'delete_all_read':
        handleDeleteAllRead($notification, $userId);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Invalid action']]);
        break;
}

/**
 * Get notifications for the current user
 */
function handleGetNotifications($notification, $userId) {
    try {
        $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
        $limit = min((int)($_GET['limit'] ?? 20), 100); // Max 100 notifications
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        
        $notifications = $notification->getUserNotifications($userId, $unreadOnly, $limit, $offset);
        
        // Format notifications for frontend
        $formattedNotifications = array_map(function($notif) {
            return [
                'id' => (int)$notif['id'],
                'type' => $notif['type'],
                'title' => $notif['title'],
                'message' => $notif['message'],
                'is_read' => (bool)$notif['is_read'],
                'action_url' => $notif['action_url'],
                'data' => $notif['data'],
                'priority' => $notif['priority'],
                'created_at' => $notif['created_at'],
                'read_at' => $notif['read_at'],
                'time_ago' => getTimeAgo($notif['created_at'])
            ];
        }, $notifications);
        
        echo json_encode([
            'success' => true,
            'notifications' => $formattedNotifications,
            'count' => count($formattedNotifications),
            'has_more' => count($formattedNotifications) === $limit
        ]);
        
    } catch (Exception $e) {
        error_log("Get notifications error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to fetch notifications']]);
    }
}

/**
 * Get unread notification count
 */
function handleGetUnreadCount($notification, $userId) {
    try {
        $count = $notification->getUnreadCount($userId);
        
        echo json_encode([
            'success' => true,
            'count' => $count
        ]);
        
    } catch (Exception $e) {
        error_log("Get unread count error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get notification count']]);
    }
}

/**
 * Mark a notification as read
 */
function handleMarkAsRead($notification, $userId) {
    try {
        $notificationId = (int)($_POST['notification_id'] ?? 0);
        
        if (!$notificationId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Notification ID is required']]);
            return;
        }
        
        $result = $notification->markAsRead($notificationId, $userId);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Failed to mark notification as read']]);
        }
        
    } catch (Exception $e) {
        error_log("Mark as read error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to mark notification as read']]);
    }
}

/**
 * Mark all notifications as read
 */
function handleMarkAllAsRead($notification, $userId) {
    try {
        $result = $notification->markAllAsRead($userId);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'All notifications marked as read']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Failed to mark all notifications as read']]);
        }
        
    } catch (Exception $e) {
        error_log("Mark all as read error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to mark all notifications as read']]);
    }
}

/**
 * Delete a notification
 */
function handleDeleteNotification($notification, $userId) {
    try {
        $notificationId = (int)($_POST['notification_id'] ?? 0);
        
        if (!$notificationId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Notification ID is required']]);
            return;
        }
        
        $result = $notification->delete($notificationId, $userId);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Notification deleted']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Failed to delete notification']]);
        }
        
    } catch (Exception $e) {
        error_log("Delete notification error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to delete notification']]);
    }
}

/**
 * Delete all read notifications
 */
function handleDeleteAllRead($notification, $userId) {
    try {
        $result = $notification->deleteAllRead($userId);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'All read notifications deleted']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['Failed to delete read notifications']]);
        }
        
    } catch (Exception $e) {
        error_log("Delete all read error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to delete read notifications']]);
    }
}

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
