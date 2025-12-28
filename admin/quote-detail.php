<?php
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';

$admin = new Admin($conn);

// Color name mappings
$colorNames = [
    // Rail colors
    '#1a1a1a' => 'Black',
    '#3d2314' => 'Dark Brown',
    '#1a2744' => 'Navy Blue',
    '#f5f5f5' => 'White',
    '#c4a77d' => 'Tan',
    '#8b0000' => 'Red',
    // Speed cloth surface colors
    '#1a472a' => 'Casino Green',
    '#1a3a5c' => 'Blue',
    '#6b1c1c' => 'Red',
    '#3d1a4d' => 'Purple',
    // Velveteen surface colors
    '#2d5a3d' => 'Green',
    '#2a4a6d' => 'Blue',
    '#8b2c2c' => 'Red',
    '#252525' => 'Black',
];

$id = intval($_GET['id'] ?? 0);
$order = $admin->getOrderById($id);

if (!$order) {
    setFlash('error', 'Quote not found.');
    header('Location: ' . SITE_URL . '/admin/quotes.php');
    exit;
}

$pageTitle = 'Quote ' . $order['order_number'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        $result = $admin->updateOrder($id, [
            'status' => $_POST['status'] ?? $order['status'],
            'estimated_price' => $_POST['estimated_price'] ?? null,
            'final_price' => $_POST['final_price'] ?? null,
            'admin_notes' => $_POST['admin_notes'] ?? ''
        ]);

        if ($result['success']) {
            setFlash('success', 'Quote updated successfully.');
            header('Location: ' . SITE_URL . '/admin/quote-detail.php?id=' . $id);
            exit;
        } else {
            setFlash('error', $result['error']);
        }
    }

    // Refresh order data
    $order = $admin->getOrderById($id);
}

$design = $order['design_data'];
$style = ($design['tableStyle'] ?? '') === 'racetrack' ? 'With Racetrack' : 'Standard Rail';
$surface = ($design['surfaceMaterial'] ?? '') === 'speedcloth' ? 'Suited Speed Cloth' : 'Velveteen';
$cupHolders = !empty($design['cupHolders']) ? ($design['cupHolderCount'] ?? 0) . ' cup holders' : 'None';
?>

<div class="detail-header">
    <a href="<?= SITE_URL ?>/admin/quotes.php" class="detail-back">
        &larr; Back to Quotes
    </a>
    <span class="status-badge <?= $order['status'] ?>">
        <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
    </span>
</div>

<div class="detail-grid">
    <!-- Main Content -->
    <div>
        <!-- Table Design -->
        <div class="detail-card" style="margin-bottom: 1.5rem;">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Table Configuration</h3>
            </div>
            <div class="detail-card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Table Style</span>
                        <span class="info-value"><?= sanitize($style) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Size</span>
                        <span class="info-value">96" x 48" (8ft x 4ft)</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Rail Color</span>
                        <span class="info-value">
                            <?php
                            $railColor = strtolower($design['railColor'] ?? '#000');
                            $railColorName = $colorNames[$railColor] ?? 'Custom';
                            ?>
                            <span class="color-swatch" style="background: <?= sanitize($railColor) ?>;"></span>
                            <?= sanitize($railColorName) ?>
                        </span>
                    </div>
                    <?php if (($design['tableStyle'] ?? '') === 'racetrack' && !empty($design['racetrackColor'])): ?>
                    <div class="info-item">
                        <span class="info-label">Racetrack Wood</span>
                        <span class="info-value"><?= ucfirst(sanitize($design['racetrackColor'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-label">Surface Material</span>
                        <span class="info-value"><?= sanitize($surface) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Surface Color</span>
                        <span class="info-value">
                            <?php
                            $surfaceColor = strtolower($design['surfaceColor'] ?? '#000');
                            $surfaceColorName = $colorNames[$surfaceColor] ?? 'Custom';
                            ?>
                            <span class="color-swatch" style="background: <?= sanitize($surfaceColor) ?>;"></span>
                            <?= sanitize($surfaceColorName) ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Cup Holders</span>
                        <span class="info-value"><?= sanitize($cupHolders) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Notes -->
        <?php if (!empty($order['notes'])): ?>
        <div class="detail-card" style="margin-bottom: 1.5rem;">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Customer Notes</h3>
            </div>
            <div class="detail-card-body">
                <p style="color: var(--gray-700); line-height: 1.6;">
                    <?= nl2br(sanitize($order['notes'])) ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Update Form -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Update Quote</h3>
            </div>
            <div class="detail-card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                    <div class="admin-form-group">
                        <label class="admin-form-label" for="status">Status</label>
                        <select name="status" id="status" class="admin-form-control select">
                            <option value="quote" <?= $order['status'] === 'quote' ? 'selected' : '' ?>>Quote</option>
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="in_progress" <?= $order['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="estimated_price">Estimated Price ($)</label>
                            <input type="number" name="estimated_price" id="estimated_price"
                                   class="admin-form-control" step="0.01" min="0"
                                   value="<?= $order['estimated_price'] ? sanitize($order['estimated_price']) : '' ?>"
                                   placeholder="0.00">
                        </div>

                        <div class="admin-form-group">
                            <label class="admin-form-label" for="final_price">Final Price ($)</label>
                            <input type="number" name="final_price" id="final_price"
                                   class="admin-form-control" step="0.01" min="0"
                                   value="<?= $order['final_price'] ? sanitize($order['final_price']) : '' ?>"
                                   placeholder="0.00">
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-form-label" for="admin_notes">Admin Notes (Internal)</label>
                        <textarea name="admin_notes" id="admin_notes" class="admin-form-control" rows="4"
                                  placeholder="Internal notes about this quote..."><?= sanitize($order['admin_notes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Customer Info -->
        <div class="detail-card" style="margin-bottom: 1.5rem;">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Customer</h3>
            </div>
            <div class="detail-card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value">
                            <a href="mailto:<?= sanitize($order['email']) ?>"><?= sanitize($order['email']) ?></a>
                        </span>
                    </div>
                    <?php if (!empty($order['phone'])): ?>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?= sanitize($order['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="margin-top: 1rem;">
                    <a href="<?= SITE_URL ?>/admin/user-detail.php?id=<?= $order['user_id'] ?>" class="btn btn-sm btn-secondary btn-block">
                        View Customer Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Order Info -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Order Details</h3>
            </div>
            <div class="detail-card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Order #</span>
                        <span class="info-value"><?= sanitize($order['order_number']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Submitted</span>
                        <span class="info-value"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Updated</span>
                        <span class="info-value"><?= date('M j, Y g:i A', strtotime($order['updated_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
