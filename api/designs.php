<?php
require_once '../config/config.php';
require_once '../classes/TableDesign.php';

header('Content-Type: application/json');

// Check if AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Verify CSRF
if (!verifyCSRF($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

// Require login for design operations
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please log in to save designs']);
    exit;
}

$db = new Database();
$design = new TableDesign($db->connect());

$action = $input['action'] ?? '';

switch ($action) {
    case 'save':
        // Require email verification for saving designs
        if (!isEmailVerified()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Please verify your email to save designs', 'require_verification' => true]);
            exit;
        }

        $name = trim($input['name'] ?? '');
        $designData = json_decode($input['design_data'] ?? '{}', true);

        if (empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Design name is required']);
            exit;
        }

        $result = $design->create($_SESSION['user_id'], $name, $designData);
        echo json_encode($result);
        break;

    case 'update':
        // Require email verification for updating designs
        if (!isEmailVerified()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Please verify your email to update designs', 'require_verification' => true]);
            exit;
        }

        $id = intval($input['id'] ?? 0);
        $data = [
            'name' => $input['name'] ?? null,
            'design_data' => isset($input['design_data']) ? json_decode($input['design_data'], true) : null
        ];

        $result = $design->update($id, $_SESSION['user_id'], array_filter($data));
        echo json_encode($result);
        break;

    case 'delete':
        $id = intval($input['id'] ?? 0);
        $result = $design->delete($id, $_SESSION['user_id']);
        echo json_encode($result);
        break;

    case 'get':
        $id = intval($input['id'] ?? 0);
        $result = $design->getById($id, $_SESSION['user_id']);

        if ($result) {
            echo json_encode(['success' => true, 'design' => $result]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Design not found']);
        }
        break;

    case 'list':
        $designs = $design->getByUser($_SESSION['user_id']);
        echo json_encode(['success' => true, 'designs' => $designs]);
        break;

    case 'toggle_favorite':
        $id = intval($input['id'] ?? 0);
        $result = $design->toggleFavorite($id, $_SESSION['user_id']);
        echo json_encode($result);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
