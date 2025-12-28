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
$userId = intval($input['user_id'] ?? 0);

$db = new Database();
$admin = new Admin($db->connect());

switch ($action) {
    case 'toggle_admin':
        $result = $admin->toggleAdminStatus($userId);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
