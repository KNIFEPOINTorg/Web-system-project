<?php
/**
 * Notification Class
 * Handles all notification operations including creation, retrieval, and management
 */

require_once __DIR__ . '/../config/database.php';

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Create a new notification
     * 
     * @param int $userId User ID to send notification to
     * @param string $type Type of notification (welcome, order_update, promotion, system)
     * @param string $title Notification title
     * @param string $message Notification message
     * @param string|null $actionUrl Optional URL for notification action
     * @param array|null $data Optional additional data
     * @param string $priority Priority level (low, medium, high)
     * @param string|null $expiresAt Optional expiration timestamp
     * @return bool|int Returns notification ID on success, false on failure
     */
    public function create($userId, $type, $title, $message, $actionUrl = null, $data = null, $priority = 'medium', $expiresAt = null) {
        try {
            $sql = "INSERT INTO notifications (user_id, type, title, message, action_url, data, priority, expires_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            
            // Convert data array to JSON if provided
            $jsonData = $data ? json_encode($data) : null;
            
            $result = $stmt->execute([
                $userId,
                $type,
                $title,
                $message,
                $actionUrl,
                $jsonData,
                $priority,
                $expiresAt
            ]);
            
            return $result ? $this->db->lastInsertId() : false;
            
        } catch (PDOException $e) {
            error_log("Notification creation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get notifications for a specific user
     * 
     * @param int $userId User ID
     * @param bool $unreadOnly Whether to fetch only unread notifications
     * @param int $limit Maximum number of notifications to fetch
     * @param int $offset Offset for pagination
     * @return array Array of notifications
     */
    public function getUserNotifications($userId, $unreadOnly = false, $limit = 50, $offset = 0) {
        try {
            $sql = "SELECT id, type, title, message, is_read, action_url, data, priority, 
                           read_at, created_at, expires_at
                    FROM notifications 
                    WHERE user_id = ? 
                    AND (expires_at IS NULL OR expires_at > NOW())";
            
            $params = [$userId];
            
            if ($unreadOnly) {
                $sql .= " AND is_read = FALSE";
            }
            
            $sql .= " ORDER BY priority DESC, created_at DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Decode JSON data for each notification
            foreach ($notifications as &$notification) {
                if ($notification['data']) {
                    $notification['data'] = json_decode($notification['data'], true);
                }
            }
            
            return $notifications;
            
        } catch (PDOException $e) {
            error_log("Get notifications error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get unread notification count for a user
     * 
     * @param int $userId User ID
     * @return int Number of unread notifications
     */
    public function getUnreadCount($userId) {
        try {
            $sql = "SELECT COUNT(*) as count 
                    FROM notifications 
                    WHERE user_id = ? 
                    AND is_read = FALSE 
                    AND (expires_at IS NULL OR expires_at > NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $result['count'];
            
        } catch (PDOException $e) {
            error_log("Get unread count error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Mark a notification as read
     * 
     * @param int $notificationId Notification ID
     * @param int $userId User ID (for security)
     * @return bool Success status
     */
    public function markAsRead($notificationId, $userId) {
        try {
            $sql = "UPDATE notifications 
                    SET is_read = TRUE, read_at = NOW() 
                    WHERE id = ? AND user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificationId, $userId]);
            
        } catch (PDOException $e) {
            error_log("Mark as read error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function markAllAsRead($userId) {
        try {
            $sql = "UPDATE notifications 
                    SET is_read = TRUE, read_at = NOW() 
                    WHERE user_id = ? AND is_read = FALSE";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId]);
            
        } catch (PDOException $e) {
            error_log("Mark all as read error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a notification
     * 
     * @param int $notificationId Notification ID
     * @param int $userId User ID (for security)
     * @return bool Success status
     */
    public function delete($notificationId, $userId) {
        try {
            $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$notificationId, $userId]);
            
        } catch (PDOException $e) {
            error_log("Delete notification error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete all read notifications for a user
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteAllRead($userId) {
        try {
            $sql = "DELETE FROM notifications WHERE user_id = ? AND is_read = TRUE";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId]);
            
        } catch (PDOException $e) {
            error_log("Delete all read error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean up expired notifications
     * 
     * @return bool Success status
     */
    public function cleanupExpired() {
        try {
            $sql = "DELETE FROM notifications WHERE expires_at IS NOT NULL AND expires_at <= NOW()";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Cleanup expired notifications error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Create a welcome notification for new users
     * 
     * @param int $userId User ID
     * @param string $userName User's name
     * @return bool|int Returns notification ID on success, false on failure
     */
    public function createWelcomeNotification($userId, $userName) {
        $title = "Welcome to LFLshop!";
        $message = "Hello {$userName}! Welcome to LFLshop, your gateway to authentic Ethiopian products. Start exploring our collections and discover amazing local crafts, coffee, spices, and more!";
        
        return $this->create(
            $userId,
            'welcome',
            $title,
            $message,
            '/dashboard.php',
            ['user_name' => $userName],
            'high'
        );
    }
    
    /**
     * Create an order update notification
     * 
     * @param int $userId User ID
     * @param int $orderId Order ID
     * @param string $status New order status
     * @param string $message Custom message
     * @return bool|int Returns notification ID on success, false on failure
     */
    public function createOrderUpdateNotification($userId, $orderId, $status, $message) {
        $title = "Order Update - #{$orderId}";
        
        return $this->create(
            $userId,
            'order_update',
            $title,
            $message,
            "/orders.php?id={$orderId}",
            ['order_id' => $orderId, 'status' => $status],
            'high'
        );
    }
    
    /**
     * Create a promotion notification
     * 
     * @param int $userId User ID
     * @param string $title Promotion title
     * @param string $message Promotion message
     * @param string|null $actionUrl Optional action URL
     * @return bool|int Returns notification ID on success, false on failure
     */
    public function createPromotionNotification($userId, $title, $message, $actionUrl = null) {
        return $this->create(
            $userId,
            'promotion',
            $title,
            $message,
            $actionUrl,
            null,
            'medium',
            date('Y-m-d H:i:s', strtotime('+30 days')) // Expire in 30 days
        );
    }
    
    /**
     * Get notification statistics for admin
     * 
     * @return array Statistics array
     */
    public function getStatistics() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN is_read = FALSE THEN 1 ELSE 0 END) as unread,
                        SUM(CASE WHEN type = 'welcome' THEN 1 ELSE 0 END) as welcome,
                        SUM(CASE WHEN type = 'order_update' THEN 1 ELSE 0 END) as order_updates,
                        SUM(CASE WHEN type = 'promotion' THEN 1 ELSE 0 END) as promotions,
                        SUM(CASE WHEN type = 'system' THEN 1 ELSE 0 END) as system
                    FROM notifications";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Get notification statistics error: " . $e->getMessage());
            return [
                'total' => 0,
                'unread' => 0,
                'welcome' => 0,
                'order_updates' => 0,
                'promotions' => 0,
                'system' => 0
            ];
        }
    }
}
