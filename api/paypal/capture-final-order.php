<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/Order.php';
require_once __DIR__ . '/../../classes/PayPal.php';
require_once __DIR__ . '/../../classes/EmailService.php';

header('Content-Type: application/json');

// Check login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Verify CSRF
if (!verifyCSRF($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

$paypalOrderId = $input['paypal_order_id'] ?? '';
$orderId = intval($input['order_id'] ?? 0);

if (!$paypalOrderId || !$orderId) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

$db = new Database();
$conn = $db->connect();
$orderModel = new Order($conn);

// Get order and verify ownership
$order = $orderModel->getById($orderId, $_SESSION['user_id']);

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Order not found']);
    exit;
}

// Must be in invoice_sent status
if ($order['status'] !== 'invoice_sent') {
    echo json_encode(['success' => false, 'error' => 'This order is not ready for final payment']);
    exit;
}

// Capture the PayPal payment
$paypal = new PayPal();
$result = $paypal->captureOrder($paypalOrderId);

if (!$result['success']) {
    echo json_encode($result);
    exit;
}

// Calculate amounts
$orderPrice = $orderModel->getOrderPrice($order);
$depositPaid = $order['deposit_amount'] ?? 0;
$finalAmount = $orderPrice - $depositPaid;

// Record the final payment in our database
$recorded = $orderModel->recordFinalPayment(
    $orderId,
    $result['transaction_id']
);

if (!$recorded) {
    error_log("Failed to record final payment for order $orderId");
    // Payment was captured, so we still return success but log the error
}

// Get user info for emails
$userQuery = "SELECT first_name, last_name, email FROM users WHERE id = :user_id";
$userStmt = $conn->prepare($userQuery);
$userStmt->bindParam(':user_id', $order['user_id']);
$userStmt->execute();
$user = $userStmt->fetch();

if ($user) {
    $emailService = new EmailService();

    // Send confirmation email to customer
    try {
        $emailService->sendFinalPaymentConfirmation(
            $user['email'],
            $user['first_name'],
            $order['order_number'],
            $finalAmount,
            $orderPrice
        );
    } catch (Exception $e) {
        error_log("Failed to send final payment confirmation email: " . $e->getMessage());
    }

    // Send notification email to admin
    try {
        $emailService->sendFinalPaymentNotificationToAdmin(
            $user['first_name'] . ' ' . $user['last_name'],
            $user['email'],
            $order['order_number'],
            $finalAmount,
            $orderPrice
        );
    } catch (Exception $e) {
        error_log("Failed to send final payment notification email: " . $e->getMessage());
    }
}

echo json_encode([
    'success' => true,
    'transaction_id' => $result['transaction_id'],
    'amount' => $finalAmount
]);
