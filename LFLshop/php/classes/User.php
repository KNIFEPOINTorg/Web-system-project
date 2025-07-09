<?php
/**
 * User Class for LFLshop
 * Handles user authentication, registration, and profile management
 */

class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $firstName;
    private $lastName;
    private $role;
    private $status;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Register new user
     */
    public function register($userData) {
        try {
            // Validate input
            $errors = $this->validateRegistrationData($userData);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Check if user already exists
            if ($this->userExists($userData['email'], $userData['username'])) {
                return ['success' => false, 'errors' => ['User with this email or username already exists']];
            }
            
            // Hash password
            $passwordHash = Security::hashPassword($userData['password']);
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, first_name, last_name, phone) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $userData['username'],
                $userData['email'],
                $passwordHash,
                $userData['first_name'],
                $userData['last_name'],
                $userData['phone'] ?? null
            ]);
            
            if ($result) {
                $userId = $this->db->lastInsertId();
                
                // Create user profile
                $this->createUserProfile($userId);
                
                Security::logSecurityEvent('user_registered', ['user_id' => $userId, 'email' => $userData['email']]);
                
                return ['success' => true, 'user_id' => $userId];
            }
            
            return ['success' => false, 'errors' => ['Registration failed']];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Registration failed']];
        }
    }
    
    /**
     * Authenticate user login
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Check rate limiting
            if (!Security::checkRateLimit($email)) {
                return ['success' => false, 'errors' => ['Too many login attempts. Please try again later.']];
            }
            
            // Get user by email
            $stmt = $this->db->prepare("
                SELECT id, username, email, password_hash, first_name, last_name, role, status, 
                       login_attempts, locked_until 
                FROM users 
                WHERE email = ? AND status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user) {
                Security::recordFailedAttempt($email);
                return ['success' => false, 'errors' => ['Invalid email or password']];
            }
            
            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                return ['success' => false, 'errors' => ['Account is temporarily locked. Please try again later.']];
            }
            
            // Verify password
            if (!Security::verifyPassword($password, $user['password_hash'])) {
                $this->recordFailedLogin($user['id']);
                Security::recordFailedAttempt($email);
                return ['success' => false, 'errors' => ['Invalid email or password']];
            }
            
            // Reset failed attempts
            $this->resetFailedLogins($user['id']);
            Security::resetRateLimit($email);
            
            // Create session
            $this->createSession($user);
            
            // Update last login
            $this->updateLastLogin($user['id']);
            
            Security::logSecurityEvent('user_login', ['user_id' => $user['id'], 'email' => $email]);
            
            return ['success' => true, 'user' => $user];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Login failed']];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            
            // Remove session from database
            if ($userId) {
                $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE id = ?");
                $stmt->execute([session_id()]);
                
                Security::logSecurityEvent('user_logout', ['user_id' => $userId]);
            }
            
            // Destroy session
            session_destroy();
            
            return true;
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT u.*, up.bio, up.avatar, up.address_line1, up.address_line2, 
                       up.city, up.state, up.postal_code, up.country, up.date_of_birth, up.gender
                FROM users u
                LEFT JOIN user_profiles up ON u.id = up.user_id
                WHERE u.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $profileData) {
        try {
            $this->db->beginTransaction();
            
            // Update users table
            if (isset($profileData['first_name']) || isset($profileData['last_name']) || isset($profileData['phone'])) {
                $userFields = [];
                $userValues = [];
                
                if (isset($profileData['first_name'])) {
                    $userFields[] = 'first_name = ?';
                    $userValues[] = $profileData['first_name'];
                }
                if (isset($profileData['last_name'])) {
                    $userFields[] = 'last_name = ?';
                    $userValues[] = $profileData['last_name'];
                }
                if (isset($profileData['phone'])) {
                    $userFields[] = 'phone = ?';
                    $userValues[] = $profileData['phone'];
                }
                
                $userValues[] = $userId;
                
                $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $userFields) . " WHERE id = ?");
                $stmt->execute($userValues);
            }
            
            // Update user_profiles table
            $profileFields = ['bio', 'address_line1', 'address_line2', 'city', 'state', 'postal_code', 'country', 'date_of_birth', 'gender'];
            $updateFields = [];
            $updateValues = [];
            
            foreach ($profileFields as $field) {
                if (isset($profileData[$field])) {
                    $updateFields[] = "$field = ?";
                    $updateValues[] = $profileData[$field];
                }
            }
            
            if (!empty($updateFields)) {
                $updateValues[] = $userId;
                
                $stmt = $this->db->prepare("
                    UPDATE user_profiles 
                    SET " . implode(', ', $updateFields) . " 
                    WHERE user_id = ?
                ");
                $stmt->execute($updateValues);
            }
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Update profile error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Profile update failed']];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password hash
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'errors' => ['User not found']];
            }
            
            // Verify current password
            if (!Security::verifyPassword($currentPassword, $user['password_hash'])) {
                return ['success' => false, 'errors' => ['Current password is incorrect']];
            }
            
            // Validate new password
            $passwordValidation = Security::validatePassword($newPassword);
            if ($passwordValidation !== true) {
                return ['success' => false, 'errors' => $passwordValidation];
            }
            
            // Update password
            $newPasswordHash = Security::hashPassword($newPassword);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $result = $stmt->execute([$newPasswordHash, $userId]);
            
            if ($result) {
                Security::logSecurityEvent('password_changed', ['user_id' => $userId]);
                return ['success' => true];
            }
            
            return ['success' => false, 'errors' => ['Password change failed']];
            
        } catch (Exception $e) {
            error_log("Change password error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['Password change failed']];
        }
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistrationData($data) {
        $errors = [];
        
        // Required fields
        $required = ['username', 'email', 'password', 'first_name', 'last_name'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }
        
        // Email validation
        if (!empty($data['email']) && !Security::validateEmail($data['email'])) {
            $errors[] = "Invalid email format";
        }
        
        // Password validation
        if (!empty($data['password'])) {
            $passwordValidation = Security::validatePassword($data['password']);
            if ($passwordValidation !== true) {
                $errors = array_merge($errors, $passwordValidation);
            }
        }
        
        // Username validation
        if (!empty($data['username'])) {
            if (strlen($data['username']) < 3) {
                $errors[] = "Username must be at least 3 characters long";
            }
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
                $errors[] = "Username can only contain letters, numbers, and underscores";
            }
        }
        
        return $errors;
    }
    
    /**
     * Check if user exists
     */
    private function userExists($email, $username) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Create user profile
     */
    private function createUserProfile($userId) {
        $stmt = $this->db->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
        $stmt->execute([$userId]);
    }
    
    /**
     * Create user session
     */
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        // Store session in database
        $stmt = $this->db->prepare("
            INSERT INTO user_sessions (id, user_id, ip_address, user_agent) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            last_activity = CURRENT_TIMESTAMP, 
            ip_address = VALUES(ip_address), 
            user_agent = VALUES(user_agent)
        ");
        
        $stmt->execute([
            session_id(),
            $user['id'],
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    /**
     * Record failed login attempt
     */
    private function recordFailedLogin($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET login_attempts = login_attempts + 1,
                locked_until = CASE 
                    WHEN login_attempts + 1 >= ? THEN DATE_ADD(NOW(), INTERVAL ? SECOND)
                    ELSE locked_until 
                END
            WHERE id = ?
        ");
        $stmt->execute([MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME, $userId]);
    }
    
    /**
     * Reset failed login attempts
     */
    private function resetFailedLogins($userId) {
        $stmt = $this->db->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE id = ?");
        $stmt->execute([$userId]);
    }
    
    /**
     * Update last login timestamp
     */
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }
}
?>
