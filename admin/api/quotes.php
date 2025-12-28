<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../classes/Admin.php';

header('Content-Type: application/json');

// Check admin access
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    exit;
}

// Check AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Verify CSRF
if (!verifyCSRF($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

$action = $input['action'] ?? '';
$orderId = intval($input['order_id'] ?? 0);

$db = new Database();
$admin = new Admin($db->connect());

switch ($action) {
    case 'update_status':
        $status = $input['status'] ?? '';
        $allowedStatuses = ['quote', 'pending', 'in_progress', 'completed', 'cancelled'];

        if (!in_array($status, $allowedStatuses)) {
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
            exit;
        }

        $result = $admin->updateOrder($orderId, ['status' => $status]);
        echo json_encode($result);
        break;

    case 'update_pricing':
        $result = $admin->updateOrder($orderId, [
            'estimated_price' => $input['estimated_price'] ?? null,
            'final_price' => $input['final_price'] ?? null
        ]);
        echo json_encode($result);
        break;

    case 'update_notes':
        $result = $admin->updateOrder($orderId, [
            'admin_notes' => $input['admin_notes'] ?? ''
        ]);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
