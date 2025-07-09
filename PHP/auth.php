<?php

require_once 'config.php';
startSecureSession();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
switch ($method) {
    case 'POST':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'signup':
                    handleSignup($input);
                    break;
                case 'signin':
                    handleSignin($input);
                    break;
                case 'logout':
                    handleLogout();
                    break;
                default:
                    sendErrorResponse('Invalid action', 400);
            }
        } else {
            sendErrorResponse('Action parameter required', 400);
        }
        break;
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'check') {
            checkAuthStatus();
        } else {
            sendErrorResponse('Invalid request', 400);
        }
        break;
    default:
        sendErrorResponse('Method not allowed', 405);
}

function handleSignup($data) {
    try {
        $requiredFields = ['firstName', 'lastName', 'userType', 'password', 'contactMethod'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                sendErrorResponse("Field '$field' is required", 400);
            }
        }

        $firstName = sanitizeInput($data['firstName']);
        $lastName = sanitizeInput($data['lastName']);
        $userType = sanitizeInput($data['userType']);
        $password = $data['password'];
        $contactMethod = sanitizeInput($data['contactMethod']);

        $email = null;
        $phone = null;

        if ($contactMethod === 'email') {
            if (!isset($data['email']) || empty(trim($data['email']))) {
                sendErrorResponse('Email is required', 400);
            }
            $email = sanitizeInput($data['email']);
            if (!validateEmail($email)) {
                sendErrorResponse('Invalid email format', 400);
            }
        } else if ($contactMethod === 'phone') {
            if (!isset($data['phone']) || empty(trim($data['phone']))) {
                sendErrorResponse('Phone number is required', 400);
            }
            $countryCode = sanitizeInput($data['countryCode'] ?? '+251');
            $phoneNumber = sanitizeInput($data['phone']);
            $phone = $countryCode . $phoneNumber;

            if (!validatePhone($phone)) {
                sendErrorResponse('Invalid phone number format', 400);
            }
        } else {
            sendErrorResponse('Invalid contact method', 400);
        }

        if (!validatePassword($password)) {
            sendErrorResponse('Password must be at least 8 characters with uppercase, lowercase, number, and special character', 400);
        }

        $db = Database::getInstance()->getConnection();

        $checkQuery = "SELECT id FROM users WHERE email = ? OR phone = ?";
        $checkStmt = $db->prepare($checkQuery);
        $checkStmt->execute([$email, $phone]);

        if ($checkStmt->fetch()) {
            sendErrorResponse('User already exists with this email or phone number', 409);
        }

        $hashedPassword = hashPassword($password);

        $insertQuery = "INSERT INTO users (first_name, last_name, email, phone, password_hash, user_type, contact_method, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $insertStmt = $db->prepare($insertQuery);
        $insertStmt->execute([
            $firstName,
            $lastName,
            $email,
            $phone,
            $hashedPassword,
            $userType,
            $contactMethod
        ]);

        $userId = $db->lastInsertId();

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_type'] = $userType;
        $_SESSION['logged_in'] = true;

        sendSuccessResponse([
            'user' => [
                'id' => $userId,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'fullName' => $firstName . ' ' . $lastName,
                'email' => $email,
                'phone' => $phone,
                'userType' => $userType,
                'contactMethod' => $contactMethod
            ]
        ], 'Account created successfully');

    } catch (PDOException $e) {
        sendErrorResponse('Database error: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        sendErrorResponse('Server error: ' . $e->getMessage(), 500);
    }
}

function handleSignin($data) {
    try {
        if (!isset($data['loginId']) || empty(trim($data['loginId']))) {
            sendErrorResponse('Login ID is required', 400);
        }
        if (!isset($data['password']) || empty($data['password'])) {
            sendErrorResponse('Password is required', 400);
        }

        $loginId = sanitizeInput($data['loginId']);
        $password = $data['password'];

        $loginType = 'username';
        if (validateEmail($loginId)) {
            $loginType = 'email';
        } else if (validatePhone($loginId)) {
            $loginType = 'phone';
        }

        $db = Database::getInstance()->getConnection();

        $query = "SELECT id, first_name, last_name, email, phone, password_hash, user_type, contact_method FROM users WHERE ";

        if ($loginType === 'email') {
            $query .= "email = ?";
        } else if ($loginType === 'phone') {
            $query .= "phone = ?";
        } else {
            $query .= "(email = ? OR phone = ?)";
        }

        $stmt = $db->prepare($query);

        if ($loginType === 'username') {
            $stmt->execute([$loginId, $loginId]);
        } else {
            $stmt->execute([$loginId]);
        }

        $user = $stmt->fetch();

        if (!$user) {
            sendErrorResponse('Invalid credentials', 401);
        }

        if (!verifyPassword($password, $user['password_hash'])) {
            sendErrorResponse('Invalid credentials', 401);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['logged_in'] = true;

        $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $updateStmt = $db->prepare($updateQuery);
        $updateStmt->execute([$user['id']]);

        sendSuccessResponse([
            'user' => [
                'id' => $user['id'],
                'firstName' => $user['first_name'],
                'lastName' => $user['last_name'],
                'fullName' => $user['first_name'] . ' ' . $user['last_name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
                'userType' => $user['user_type'],
                'contactMethod' => $user['contact_method']
            ]
        ], 'Login successful');

    } catch (PDOException $e) {
        sendErrorResponse('Database error: ' . $e->getMessage(), 500);
    } catch (Exception $e) {
        sendErrorResponse('Server error: ' . $e->getMessage(), 500);
    }
}

function handleLogout() {
    try {
        destroySession();
        sendSuccessResponse([], 'Logout successful');
    } catch (Exception $e) {
        sendErrorResponse('Logout failed: ' . $e->getMessage(), 500);
    }
}

function checkAuthStatus() {
    try {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            $db = Database::getInstance()->getConnection();
            $query = "SELECT id, first_name, last_name, email, phone, user_type, contact_method FROM users WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user) {
                sendSuccessResponse([
                    'authenticated' => true,
                    'user' => [
                        'id' => $user['id'],
                        'firstName' => $user['first_name'],
                        'lastName' => $user['last_name'],
                        'fullName' => $user['first_name'] . ' ' . $user['last_name'],
                        'email' => $user['email'],
                        'phone' => $user['phone'],
                        'userType' => $user['user_type'],
                        'contactMethod' => $user['contact_method']
                    ]
                ]);
            } else {
                destroySession();
                sendSuccessResponse(['authenticated' => false]);
            }
        } else {
            sendSuccessResponse(['authenticated' => false]);
        }
    } catch (Exception $e) {
        sendErrorResponse('Error checking authentication: ' . $e->getMessage(), 500);
    }
}
?>
