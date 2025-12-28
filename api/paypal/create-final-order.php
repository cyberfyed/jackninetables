<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/Order.php';
require_once __DIR__ . '/../../classes/PayPal.php';

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

$orderId = intval($input['order_id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    exit;
}

$db = new Database();
$orderModel = new Order($db->connect());

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

// Calculate remaining balance
$orderPrice = $orderModel->getOrderPrice($order);
$depositPaid = $order['deposit_amount'] ?? 0;
$remainingBalance = $orderPrice - $depositPaid;

if ($remainingBalance <= 0) {
    echo json_encode(['success' => false, 'error' => 'No balance remaining']);
    exit;
}

// Create PayPal order
$paypal = new PayPal();
$description = SITE_NAME . ' - Final Payment for Order ' . $order['order_number'];
$result = $paypal->createOrder($orderId, $remainingBalance, $description);

echo json_encode($result);
