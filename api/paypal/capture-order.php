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

if (!$orderModel->needsDeposit($order)) {
    echo json_encode(['success' => false, 'error' => 'This order does not require a deposit']);
    exit;
}

// Capture the PayPal payment
$paypal = new PayPal();
$result = $paypal->captureOrder($paypalOrderId);

if (!$result['success']) {
    echo json_encode($result);
    exit;
}

// Calculate deposit amount for recording
$orderPrice = $orderModel->getOrderPrice($order);
$depositAmount = PayPal::calculateDeposit($orderPrice);

// Record the deposit in our database
$recorded = $orderModel->recordDeposit(
    $orderId,
    $depositAmount,
    $paypalOrderId,
    $result['transaction_id']
);

if (!$recorded) {
    error_log("Failed to record deposit for order $orderId");
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
        $emailService->sendDepositConfirmation(
            $user['email'],
            $user['first_name'],
            $order['order_number'],
            $depositAmount,
            $orderPrice
        );
    } catch (Exception $e) {
        error_log("Failed to send deposit confirmation email: " . $e->getMessage());
    }

    // Send notification email to admin
    try {
        $emailService->sendDepositNotificationToAdmin(
            $user['first_name'] . ' ' . $user['last_name'],
            $user['email'],
            $order['order_number'],
            $depositAmount,
            $orderPrice
        );
    } catch (Exception $e) {
        error_log("Failed to send deposit notification email: " . $e->getMessage());
    }
}

echo json_encode([
    'success' => true,
    'transaction_id' => $result['transaction_id'],
    'amount' => $depositAmount
]);
