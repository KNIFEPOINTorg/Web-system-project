<?php
/**
 * Profile Handler for LFLshop
 * Handles user profile updates and account management
 */

require_once __DIR__ . '/../config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'errors' => ['Authentication required']]);
    exit;
}

// Set JSON response header
header('Content-Type: application/json');

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'update_profile':
        handleUpdateProfile();
        break;
    case 'change_password':
        handleChangePassword();
        break;
    case 'upload_avatar':
        handleUploadAvatar();
        break;
    case 'get_profile':
        handleGetProfile();
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'errors' => ['Invalid action']]);
        break;
}

/**
 * Handle profile update
 */
function handleUpdateProfile() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        
        // Sanitize input
        $profileData = [
            'first_name' => Security::sanitizeInput($_POST['first_name'] ?? ''),
            'last_name' => Security::sanitizeInput($_POST['last_name'] ?? ''),
            'phone' => Security::sanitizeInput($_POST['phone'] ?? ''),
            'bio' => Security::sanitizeInput($_POST['bio'] ?? ''),
            'address_line1' => Security::sanitizeInput($_POST['address_line1'] ?? ''),
            'address_line2' => Security::sanitizeInput($_POST['address_line2'] ?? ''),
            'city' => Security::sanitizeInput($_POST['city'] ?? ''),
            'state' => Security::sanitizeInput($_POST['state'] ?? ''),
            'postal_code' => Security::sanitizeInput($_POST['postal_code'] ?? ''),
            'country' => Security::sanitizeInput($_POST['country'] ?? ''),
            'date_of_birth' => Security::sanitizeInput($_POST['date_of_birth'] ?? ''),
            'gender' => Security::sanitizeInput($_POST['gender'] ?? '')
        ];
        
        // Remove empty values
        $profileData = array_filter($profileData, function($value) {
            return $value !== '';
        });
        
        // Validate required fields
        if (empty($profileData['first_name']) || empty($profileData['last_name'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['First name and last name are required']]);
            return;
        }
        
        // Check for security threats
        foreach ($profileData as $field => $value) {
            if (Security::detectSQLInjection($value) || Security::detectXSS($value)) {
                Security::logSecurityEvent('profile_update_security_threat', ['field' => $field, 'user_id' => $userId]);
                http_response_code(400);
                echo json_encode(['success' => false, 'errors' => ['Invalid input detected']]);
                return;
            }
        }
        
        // Update profile
        $user = new User();
        $result = $user->updateProfile($userId, $profileData);
        
        if ($result['success']) {
            // Update session data
            $_SESSION['first_name'] = $profileData['first_name'];
            $_SESSION['last_name'] = $profileData['last_name'];
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Update profile error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Profile update failed']]);
    }
}

/**
 * Handle password change
 */
function handleChangePassword() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate input
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['All password fields are required']]);
            return;
        }
        
        if ($newPassword !== $confirmPassword) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['New passwords do not match']]);
            return;
        }
        
        // Change password
        $user = new User();
        $result = $user->changePassword($userId, $currentPassword, $newPassword);
        
        if ($result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
        
    } catch (Exception $e) {
        error_log("Change password error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Password change failed']]);
    }
}

/**
 * Handle avatar upload
 */
function handleUploadAvatar() {
    try {
        // Validate CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'errors' => ['Invalid CSRF token']]);
            return;
        }
        
        $userId = getCurrentUserId();
        
        // Validate file upload
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => ['No file uploaded or upload error']]);
            return;
        }
        
        $file = $_FILES['avatar'];
        $errors = Security::validateFileUpload($file, ['jpg', 'jpeg', 'png']);
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            return;
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = UPLOAD_PATH . 'avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'avatar_' . $userId . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Update user profile with avatar path
            $user = new User();
            $result = $user->updateProfile($userId, ['avatar' => 'uploads/avatars/' . $fileName]);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Avatar uploaded successfully',
                    'avatar_url' => BASE_URL . 'uploads/avatars/' . $fileName
                ]);
            } else {
                // Delete uploaded file if database update failed
                unlink($filePath);
                http_response_code(500);
                echo json_encode(['success' => false, 'errors' => ['Failed to update avatar in database']]);
            }
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'errors' => ['Failed to upload file']]);
        }
        
    } catch (Exception $e) {
        error_log("Upload avatar error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Avatar upload failed']]);
    }
}

/**
 * Get user profile data
 */
function handleGetProfile() {
    try {
        $userId = getCurrentUserId();
        
        $user = new User();
        $profile = $user->getUserById($userId);
        
        if ($profile) {
            // Remove sensitive data
            unset($profile['password_hash']);
            unset($profile['login_attempts']);
            unset($profile['locked_until']);
            
            echo json_encode([
                'success' => true,
                'profile' => $profile
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'errors' => ['Profile not found']]);
        }
        
    } catch (Exception $e) {
        error_log("Get profile error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'errors' => ['Failed to get profile']]);
    }
}
?>
