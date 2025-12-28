<?php
require_once '../config/config.php';
require_once '../classes/Order.php';
require_once '../classes/EmailService.php';

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

$action = $input['action'] ?? '';

if ($action !== 'quote') {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

// Require login for quote requests
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please create an account to request quotes']);
    exit;
}

// Require email verification
if (!isEmailVerified()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Please verify your email to request quotes', 'require_verification' => true]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $order = new Order($conn);

    // Check if user already has a pending quote
    if ($order->hasPendingQuote($_SESSION['user_id'])) {
        http_response_code(429);
        echo json_encode(['success' => false, 'error' => 'You already have a pending quote request. Please wait for a response before submitting another.']);
        exit;
    }

    $designData = json_decode($input['design_data'] ?? '{}', true);
    $notes = trim($input['notes'] ?? '');

    $emailService = new EmailService();

    $result = $order->create($_SESSION['user_id'], $designData, null, $notes);

    if ($result['success']) {
        // Send confirmation email to customer (don't fail quote if email fails)
        try {
            $emailService->sendQuoteConfirmation(
                $_SESSION['user_email'],
                $_SESSION['user_name'],
                $designData,
                $result['order_number']
            );
        } catch (Exception $e) {
            error_log("Quote confirmation email failed: " . $e->getMessage());
        }

        // Send notification to admin (don't fail quote if email fails)
        try {
            $emailService->sendQuoteNotificationToAdmin(
                $_SESSION['user_name'],
                $_SESSION['user_email'],
                '', // Phone not stored in session
                $designData,
                $notes,
                $result['order_number']
            );
        } catch (Exception $e) {
            error_log("Quote admin notification email failed: " . $e->getMessage());
        }

        echo json_encode([
            'success' => true,
            'order_number' => $result['order_number'],
            'message' => 'Quote request submitted successfully'
        ]);
    } else {
        echo json_encode($result);
    }
} catch (Exception $e) {
    error_log("Quote API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred: ' . $e->getMessage()]);
}

function formatDesignSummary($data) {
    $sizeLabels = [
        '84x42' => '84" x 42" (8 Players)',
        '96x42' => '96" x 42" (10 Players)',
        '108x48' => '108" x 48" (10+ Players)'
    ];

    $summary = "Table Configuration:\n";
    $summary .= "- Style: " . ($data['tableStyle'] === 'racetrack' ? 'With Racetrack' : 'Standard Rail') . "\n";
    $summary .= "- Size: " . ($sizeLabels[$data['tableSize']] ?? $data['tableSize']) . "\n";
    $summary .= "- Rail Color: " . $data['railColor'] . "\n";
    $summary .= "- Surface: " . ($data['surfaceMaterial'] === 'speedcloth' ? 'Suited Speed Cloth' : 'Velveteen') . "\n";
    $summary .= "- Surface Color: " . $data['surfaceColor'] . "\n";
    $summary .= "- Cup Holders: " . ($data['cupHolders'] ? $data['cupHolderCount'] : 'None') . "\n";
    $summary .= "- Dealer Cutout: " . ($data['dealerCutout'] ? 'Yes' : 'No') . "\n";

    return $summary;
}
