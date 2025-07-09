<?php
/**
 * Payment Processing API for LFLshop
 * Handles demo payment transactions and order completion
 */

session_start();

// Initialize secure API headers and error handling
require_once 'cors_config.php';
require_once 'error_handler.php';
initializeSecureAPI();
header('Access-Control-Allow-Headers: Content-Type');

require_once '../database/config.php';
require_once '../includes/security.php';

class PaymentAPI {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function handleRequest() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            return $this->error('Authentication required', 401);
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';
        
        switch ($method) {
            case 'POST':
                switch ($action) {
                    case 'process':
                        return $this->processPayment();
                    case 'verify':
                        return $this->verifyPayment();
                    default:
                        return $this->error('Invalid action');
                }
            case 'GET':
                switch ($action) {
                    case 'status':
                        return $this->getPaymentStatus();
                    default:
                        return $this->error('Invalid action');
                }
            default:
                return $this->error('Method not allowed');
        }
    }
    
    private function processPayment() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required = ['order_id', 'payment_method', 'amount'];
        foreach ($required as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                return $this->error("$field is required");
            }
        }
        
        $orderId = (int)$input['order_id'];
        $paymentMethod = SecurityHelper::sanitize($input['payment_method']);
        $amount = (float)$input['amount'];
        
        // Verify order belongs to user
        $this->db->query('SELECT * FROM orders WHERE id = :order_id AND user_id = :user_id');
        $this->db->bind(':order_id', $orderId);
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $order = $this->db->single();
        
        if (!$order) {
            return $this->error('Order not found', 404);
        }
        
        if ($order['payment_status'] === 'paid') {
            return $this->error('Order already paid');
        }
        
        // Verify amount matches order total
        if (abs($amount - $order['total_amount']) > 0.01) {
            return $this->error('Payment amount mismatch');
        }
        
        try {
            $this->db->beginTransaction();
            
            // Generate transaction ID
            $transactionId = 'TXN_' . time() . '_' . rand(1000, 9999);
            
            // Process based on payment method
            switch ($paymentMethod) {
                case 'cash_on_delivery':
                    $result = $this->processCashOnDelivery($orderId, $transactionId, $amount);
                    break;
                case 'credit_card':
                case 'debit_card':
                    $result = $this->processDemoCardPayment($orderId, $transactionId, $amount, $input);
                    break;
                case 'mobile_money':
                    $result = $this->processDemoMobilePayment($orderId, $transactionId, $amount, $input);
                    break;
                default:
                    throw new Exception('Unsupported payment method');
            }
            
            if ($result['success']) {
                // Update order status
                $this->db->query('UPDATE orders SET payment_status = :status, status = :order_status WHERE id = :order_id');
                $this->db->bind(':status', $result['payment_status']);
                $this->db->bind(':order_status', $result['order_status']);
                $this->db->bind(':order_id', $orderId);
                $this->db->execute();
                
                // Clear user's cart
                $this->db->query('DELETE FROM cart_items WHERE user_id = :user_id');
                $this->db->bind(':user_id', $_SESSION['user_id']);
                $this->db->execute();
                
                // Update product stock
                $this->updateProductStock($orderId);
                
                // Create notification
                $this->createOrderNotification($orderId, $result['payment_status']);
                
                $this->db->endTransaction();
                
                return $this->success('Payment processed successfully', [
                    'transaction_id' => $transactionId,
                    'payment_status' => $result['payment_status'],
                    'order_status' => $result['order_status'],
                    'redirect_url' => $result['redirect_url'] ?? null
                ]);
            } else {
                throw new Exception($result['message']);
            }
            
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return $this->error('Payment processing failed: ' . $e->getMessage());
        }
    }
    
    private function processCashOnDelivery($orderId, $transactionId, $amount) {
        // Insert payment transaction
        $this->db->query('INSERT INTO payment_transactions (order_id, transaction_id, payment_method, amount, status, processed_at) VALUES (:order_id, :transaction_id, :payment_method, :amount, :status, NOW())');
        $this->db->bind(':order_id', $orderId);
        $this->db->bind(':transaction_id', $transactionId);
        $this->db->bind(':payment_method', 'cash_on_delivery');
        $this->db->bind(':amount', $amount);
        $this->db->bind(':status', 'pending');
        $this->db->execute();
        
        return [
            'success' => true,
            'payment_status' => 'pending',
            'order_status' => 'confirmed'
        ];
    }
    
    private function processDemoCardPayment($orderId, $transactionId, $amount, $input) {
        // Demo card validation
        $cardNumber = preg_replace('/\s+/', '', $input['card_number'] ?? '');
        $expiryMonth = $input['expiry_month'] ?? '';
        $expiryYear = $input['expiry_year'] ?? '';
        $cvv = $input['cvv'] ?? '';
        $cardholderName = SecurityHelper::sanitize($input['cardholder_name'] ?? '');
        
        // Basic validation
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            throw new Exception('Invalid card number');
        }
        
        if (!$expiryMonth || !$expiryYear || !$cvv) {
            throw new Exception('Missing card details');
        }
        
        // Demo card numbers for testing
        $testCards = [
            '4111111111111111' => 'success', // Visa success
            '4000000000000002' => 'declined', // Visa declined
            '5555555555554444' => 'success', // Mastercard success
            '5200000000000007' => 'declined', // Mastercard declined
        ];
        
        $cardType = $this->getCardType($cardNumber);
        $lastFour = substr($cardNumber, -4);
        
        // Simulate payment processing
        $isSuccess = $testCards[$cardNumber] ?? (rand(1, 10) > 2); // 80% success rate for other cards
        
        if ($isSuccess) {
            $status = 'completed';
            $gatewayResponse = [
                'status' => 'success',
                'authorization_code' => 'AUTH_' . rand(100000, 999999),
                'reference' => 'REF_' . time()
            ];
        } else {
            $status = 'failed';
            $gatewayResponse = [
                'status' => 'failed',
                'error_code' => 'DECLINED',
                'error_message' => 'Card declined by issuer'
            ];
        }
        
        // Insert payment transaction
        $this->db->query('INSERT INTO payment_transactions (order_id, transaction_id, payment_method, amount, status, gateway_response, card_last_four, card_type, processed_at) VALUES (:order_id, :transaction_id, :payment_method, :amount, :status, :gateway_response, :card_last_four, :card_type, NOW())');
        $this->db->bind(':order_id', $orderId);
        $this->db->bind(':transaction_id', $transactionId);
        $this->db->bind(':payment_method', 'credit_card');
        $this->db->bind(':amount', $amount);
        $this->db->bind(':status', $status);
        $this->db->bind(':gateway_response', json_encode($gatewayResponse));
        $this->db->bind(':card_last_four', $lastFour);
        $this->db->bind(':card_type', $cardType);
        $this->db->execute();
        
        if (!$isSuccess) {
            throw new Exception('Payment declined: ' . $gatewayResponse['error_message']);
        }
        
        return [
            'success' => true,
            'payment_status' => 'paid',
            'order_status' => 'confirmed'
        ];
    }
    
    private function processDemoMobilePayment($orderId, $transactionId, $amount, $input) {
        $phoneNumber = SecurityHelper::sanitize($input['phone_number'] ?? '');
        $provider = SecurityHelper::sanitize($input['provider'] ?? '');
        
        if (!$phoneNumber || !$provider) {
            throw new Exception('Phone number and provider required');
        }
        
        // Simulate mobile payment processing
        $isSuccess = rand(1, 10) > 1; // 90% success rate
        
        if ($isSuccess) {
            $status = 'completed';
            $gatewayResponse = [
                'status' => 'success',
                'transaction_ref' => 'MOB_' . rand(100000, 999999),
                'provider' => $provider
            ];
        } else {
            $status = 'failed';
            $gatewayResponse = [
                'status' => 'failed',
                'error_code' => 'INSUFFICIENT_FUNDS',
                'error_message' => 'Insufficient balance'
            ];
        }
        
        // Insert payment transaction
        $this->db->query('INSERT INTO payment_transactions (order_id, transaction_id, payment_method, amount, status, gateway_response, processed_at) VALUES (:order_id, :transaction_id, :payment_method, :amount, :status, :gateway_response, NOW())');
        $this->db->bind(':order_id', $orderId);
        $this->db->bind(':transaction_id', $transactionId);
        $this->db->bind(':payment_method', 'mobile_money');
        $this->db->bind(':amount', $amount);
        $this->db->bind(':status', $status);
        $this->db->bind(':gateway_response', json_encode($gatewayResponse));
        $this->db->execute();
        
        if (!$isSuccess) {
            throw new Exception('Mobile payment failed: ' . $gatewayResponse['error_message']);
        }
        
        return [
            'success' => true,
            'payment_status' => 'paid',
            'order_status' => 'confirmed'
        ];
    }
    
    private function getCardType($cardNumber) {
        $cardNumber = preg_replace('/\s+/', '', $cardNumber);
        
        if (preg_match('/^4/', $cardNumber)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'Mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'American Express';
        } else {
            return 'Unknown';
        }
    }
    
    private function updateProductStock($orderId) {
        $this->db->query('SELECT product_id, quantity FROM order_items WHERE order_id = :order_id');
        $this->db->bind(':order_id', $orderId);
        $orderItems = $this->db->resultset();
        
        foreach ($orderItems as $item) {
            $this->db->query('UPDATE products SET stock_quantity = stock_quantity - :quantity, sales_count = sales_count + :quantity WHERE id = :product_id');
            $this->db->bind(':quantity', $item['quantity']);
            $this->db->bind(':product_id', $item['product_id']);
            $this->db->execute();
        }
    }
    
    private function createOrderNotification($orderId, $paymentStatus) {
        $message = $paymentStatus === 'paid' 
            ? 'Your order has been confirmed and payment received.'
            : 'Your order has been confirmed. Payment will be collected on delivery.';
            
        $this->db->query('INSERT INTO notifications (user_id, type, title, message, action_url) VALUES (:user_id, :type, :title, :message, :action_url)');
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $this->db->bind(':type', 'order_confirmed');
        $this->db->bind(':title', 'Order Confirmed');
        $this->db->bind(':message', $message);
        $this->db->bind(':action_url', '/html/customer-dashboard.html?tab=orders');
        $this->db->execute();
    }
    
    private function verifyPayment() {
        $transactionId = $_GET['transaction_id'] ?? '';
        
        if (!$transactionId) {
            return $this->error('Transaction ID required');
        }
        
        $this->db->query('SELECT pt.*, o.order_number FROM payment_transactions pt JOIN orders o ON pt.order_id = o.id WHERE pt.transaction_id = :transaction_id AND o.user_id = :user_id');
        $this->db->bind(':transaction_id', $transactionId);
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $transaction = $this->db->single();
        
        if (!$transaction) {
            return $this->error('Transaction not found', 404);
        }
        
        return $this->success('Transaction found', $transaction);
    }
    
    private function getPaymentStatus() {
        $orderId = $_GET['order_id'] ?? '';
        
        if (!$orderId) {
            return $this->error('Order ID required');
        }
        
        $this->db->query('SELECT payment_status, status FROM orders WHERE id = :order_id AND user_id = :user_id');
        $this->db->bind(':order_id', $orderId);
        $this->db->bind(':user_id', $_SESSION['user_id']);
        $order = $this->db->single();
        
        if (!$order) {
            return $this->error('Order not found', 404);
        }
        
        return $this->success('Payment status retrieved', $order);
    }
    
    private function success($message, $data = null) {
        $response = ['success' => true, 'message' => $message];
        if ($data) $response['data'] = $data;
        echo json_encode($response);
        exit;
    }
    
    private function error($message, $code = 400) {
        http_response_code($code);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}

$api = new PaymentAPI();
$api->handleRequest();
?>