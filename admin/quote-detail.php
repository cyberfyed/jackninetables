<?php
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';
require_once __DIR__ . '/../classes/EmailService.php';

$admin = new Admin($conn);

// Color name mappings
$colorNames = [
    '#1a1a1a' => 'Black', '#3d2314' => 'Brown', '#1a2744' => 'Blue',
    '#f5f5f5' => 'White', '#c4a77d' => 'Tan', '#8b0000' => 'Red',
    '#1a472a' => 'Green', '#1a3a5c' => 'Blue', '#6b1c1c' => 'Red',
    '#3d1a4d' => 'Purple', '#2d5a3d' => 'Green', '#2a4a6d' => 'Blue',
    '#8b2c2c' => 'Red', '#252525' => 'Black',
];

// Status labels and colors
$statusLabels = [
    'quote_started' => ['label' => 'Quote Started', 'color' => '#6b7280'],
    'price_sent' => ['label' => 'Price Sent', 'color' => '#3b82f6'],
    'deposit_paid' => ['label' => 'Deposit Paid', 'color' => '#10b981'],
    'invoice_sent' => ['label' => 'Invoice Sent', 'color' => '#f59e0b'],
    'paid_in_full' => ['label' => 'Paid In Full', 'color' => '#059669'],
    'cancelled' => ['label' => 'Cancelled', 'color' => '#ef4444'],
];

$id = intval($_GET['id'] ?? 0);
$order = $admin->getOrderById($id);

if (!$order) {
    setFlash('error', 'Quote not found.');
    header('Location: ' . SITE_URL . '/admin/quotes.php');
    exit;
}

$pageTitle = 'Order ' . $order['order_number'];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'send_quote':
                $price = floatval($_POST['price'] ?? $order['final_price'] ?? 0);
                if ($price <= 0) {
                    setFlash('error', 'Please set a price before sending the quote.');
                } else {
                    // Save price and update status
                    $admin->updateOrder($id, ['final_price' => $price, 'status' => 'price_sent']);

                    // Send email to customer
                    $emailService = new EmailService();
                    $depositAmount = $price * (DEPOSIT_PERCENTAGE / 100);
                    $sent = $emailService->sendQuotePriceEmail(
                        $order['email'],
                        $order['first_name'],
                        $order['order_number'],
                        $price,
                        $depositAmount,
                        $id
                    );

                    if ($sent) {
                        setFlash('success', 'Quote sent to customer!');
                    } else {
                        setFlash('error', 'Price saved but email failed to send.');
                    }
                }
                break;

            case 'send_invoice':
                $admin->updateOrder($id, ['status' => 'invoice_sent']);

                // Send final invoice email
                $emailService = new EmailService();
                $remaining = $order['final_price'] - ($order['deposit_amount'] ?? 0);
                $sent = $emailService->sendFinalInvoiceEmail(
                    $order['email'],
                    $order['first_name'],
                    $order['order_number'],
                    $remaining,
                    $order['final_price'],
                    $id
                );

                if ($sent) {
                    setFlash('success', 'Invoice sent to customer!');
                } else {
                    setFlash('error', 'Status updated but email failed to send.');
                }
                break;

            case 'mark_paid':
                $admin->updateOrder($id, ['status' => 'paid_in_full']);
                setFlash('success', 'Order marked as paid in full!');
                break;

            case 'cancel':
                $admin->updateOrder($id, ['status' => 'cancelled']);
                setFlash('success', 'Order cancelled.');
                break;

            case 'save_notes':
                $admin->updateOrder($id, ['admin_notes' => $_POST['admin_notes'] ?? '']);
                setFlash('success', 'Notes saved.');
                break;
        }

        header('Location: ' . SITE_URL . '/admin/quote-detail.php?id=' . $id);
        exit;
    }
}

// Refresh order data
$order = $admin->getOrderById($id);
$design = $order['design_data'];
$style = ($design['tableStyle'] ?? '') === 'racetrack' ? 'With Racetrack' : 'Standard Rail';
$surface = ($design['surfaceMaterial'] ?? '') === 'speedcloth' ? 'Suited Speed Cloth' : 'Velveteen';
$cupHolders = !empty($design['cupHolders']) ? ($design['cupHolderCount'] ?? 0) . ' cup holders' : 'None';

$currentStatus = $order['status'];
$statusInfo = $statusLabels[$currentStatus] ?? ['label' => ucfirst($currentStatus), 'color' => '#6b7280'];
?>

<div class="detail-header">
    <a href="<?= SITE_URL ?>/admin/quotes.php" class="detail-back">
        &larr; Back to Quotes
    </a>
    <span class="status-badge" style="background: <?= $statusInfo['color'] ?>20; color: <?= $statusInfo['color'] ?>;">
        <?= $statusInfo['label'] ?>
    </span>
</div>

<!-- Status Progress Bar -->
<div class="status-progress" style="margin-bottom: 2rem;">
    <?php
    $stages = ['quote_started', 'price_sent', 'deposit_paid', 'invoice_sent', 'paid_in_full'];
    $currentIndex = array_search($currentStatus, $stages);
    if ($currentStatus === 'cancelled') $currentIndex = -1;
    ?>
    <div style="display: flex; justify-content: space-between; position: relative; padding: 0 1rem;">
        <div style="position: absolute; top: 12px; left: 2rem; right: 2rem; height: 4px; background: var(--gray-700); z-index: 0;"></div>
        <?php foreach ($stages as $i => $stage): ?>
            <?php
            $isComplete = $currentIndex !== false && $i <= $currentIndex;
            $isCurrent = $stage === $currentStatus;
            $stageInfo = $statusLabels[$stage];
            ?>
            <div style="text-align: center; position: relative; z-index: 1;">
                <div style="width: 28px; height: 28px; border-radius: 50%; margin: 0 auto 0.5rem;
                            background: <?= $isComplete ? $stageInfo['color'] : 'var(--gray-700)' ?>;
                            border: 3px solid <?= $isCurrent ? '#fff' : 'transparent' ?>;
                            display: flex; align-items: center; justify-content: center;">
                    <?php if ($isComplete && !$isCurrent): ?>
                        <span style="color: #fff; font-size: 14px;">&#10003;</span>
                    <?php endif; ?>
                </div>
                <small style="color: var(--gray-500); font-size: 0.7rem;">
                    <?= $stageInfo['label'] ?>
                </small>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="detail-grid">
    <!-- Main Content -->
    <div>
        <!-- Action Card - Changes based on status -->
        <div class="detail-card" style="margin-bottom: 1.5rem; border: 2px solid <?= $statusInfo['color'] ?>40;">
            <div class="detail-card-header" style="background: <?= $statusInfo['color'] ?>10;">
                <h3 class="detail-card-title">
                    <?php if ($currentStatus === 'quote_started'): ?>
                        Set Price & Send Quote
                    <?php elseif ($currentStatus === 'price_sent'): ?>
                        Awaiting Customer Deposit
                    <?php elseif ($currentStatus === 'deposit_paid'): ?>
                        Build Table & Send Invoice
                    <?php elseif ($currentStatus === 'invoice_sent'): ?>
                        Awaiting Final Payment
                    <?php elseif ($currentStatus === 'paid_in_full'): ?>
                        Order Complete
                    <?php elseif ($currentStatus === 'cancelled'): ?>
                        Order Cancelled
                    <?php endif; ?>
                </h3>
            </div>
            <div class="detail-card-body">
                <?php if ($currentStatus === 'quote_started'): ?>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                        <div class="admin-form-group">
                            <label class="admin-form-label" for="price">Set Price ($)</label>
                            <input type="number" name="price" id="price" class="admin-form-control"
                                   step="0.01" min="0" value="<?= $order['final_price'] ? sanitize($order['final_price']) : '' ?>"
                                   placeholder="0.00" required style="font-size: 1.25rem; padding: 0.75rem;">
                            <small style="color: var(--gray-500);">
                                Customer will pay <?= DEPOSIT_PERCENTAGE ?>% deposit ($<span id="depositPreview">0.00</span>) to start
                            </small>
                        </div>
                        <div style="margin-top: 1rem;">
                            <button type="submit" name="action" value="send_quote" class="btn btn-primary btn-block">
                                Send Quote to Customer
                            </button>
                        </div>
                    </form>
                    <script>
                        document.getElementById('price').addEventListener('input', function() {
                            const price = parseFloat(this.value) || 0;
                            const deposit = price * <?= DEPOSIT_PERCENTAGE / 100 ?>;
                            document.getElementById('depositPreview').textContent = deposit.toFixed(2);
                        });
                        // Trigger on load
                        document.getElementById('price').dispatchEvent(new Event('input'));
                    </script>

                <?php elseif ($currentStatus === 'price_sent'): ?>
                    <div style="text-align: center; padding: 1rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">&#128231;</div>
                        <p style="margin-bottom: 1rem;">Quote for <strong>$<?= number_format($order['final_price'], 2) ?></strong> sent to customer.</p>
                        <p style="color: var(--gray-500);">Waiting for customer to pay <?= DEPOSIT_PERCENTAGE ?>% deposit
                            (<strong>$<?= number_format($order['final_price'] * DEPOSIT_PERCENTAGE / 100, 2) ?></strong>)</p>
                    </div>

                <?php elseif ($currentStatus === 'deposit_paid'): ?>
                    <div style="margin-bottom: 1.5rem; padding: 1rem; background: var(--gray-750); border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Total Price:</span>
                            <strong>$<?= number_format($order['final_price'], 2) ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: var(--success);">
                            <span>Deposit Paid:</span>
                            <strong>-$<?= number_format($order['deposit_amount'], 2) ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--gray-600); padding-top: 0.5rem; font-size: 1.1rem;">
                            <span>Remaining Balance:</span>
                            <strong>$<?= number_format($order['final_price'] - $order['deposit_amount'], 2) ?></strong>
                        </div>
                    </div>
                    <p style="margin-bottom: 1rem; color: var(--gray-400);">When the table is complete, send the final invoice for the remaining balance.</p>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                        <button type="submit" name="action" value="send_invoice" class="btn btn-primary btn-block">
                            Send Final Invoice
                        </button>
                    </form>

                <?php elseif ($currentStatus === 'invoice_sent'): ?>
                    <div style="text-align: center; padding: 1rem;">
                        <div style="font-size: 2rem; margin-bottom: 0.5rem;">&#128176;</div>
                        <p style="margin-bottom: 1rem;">Invoice sent for remaining balance of
                            <strong>$<?= number_format($order['final_price'] - ($order['deposit_amount'] ?? 0), 2) ?></strong></p>
                        <p style="color: var(--gray-500); margin-bottom: 1.5rem;">When payment is received, mark the order as paid.</p>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                            <button type="submit" name="action" value="mark_paid" class="btn btn-primary">
                                Mark as Paid in Full
                            </button>
                        </form>
                    </div>

                <?php elseif ($currentStatus === 'paid_in_full'): ?>
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem; color: var(--success);">&#10003;</div>
                        <h3 style="color: var(--success); margin-bottom: 0.5rem;">Order Complete!</h3>
                        <p style="color: var(--gray-400);">Total collected: <strong>$<?= number_format($order['final_price'], 2) ?></strong></p>
                    </div>

                <?php elseif ($currentStatus === 'cancelled'): ?>
                    <div style="text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem; color: var(--gray-500);">&#10005;</div>
                        <h3 style="color: var(--gray-400);">Order Cancelled</h3>
                        <?php if ($order['deposit_amount']): ?>
                            <p style="color: var(--gray-500);">Deposit of $<?= number_format($order['deposit_amount'], 2) ?> was non-refundable.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

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
                            <?php $railColor = strtolower($design['railColor'] ?? '#000'); ?>
                            <span class="color-swatch" style="background: <?= sanitize($railColor) ?>;"></span>
                            <?= sanitize($colorNames[$railColor] ?? 'Custom') ?>
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
                            <?php $surfaceColor = strtolower($design['surfaceColor'] ?? '#000'); ?>
                            <span class="color-swatch" style="background: <?= sanitize($surfaceColor) ?>;"></span>
                            <?= sanitize($colorNames[$surfaceColor] ?? 'Custom') ?>
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
                <p style="color: var(--gray-300); line-height: 1.6;">
                    <?= nl2br(sanitize($order['notes'])) ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Admin Notes -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Admin Notes (Internal)</h3>
            </div>
            <div class="detail-card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <textarea name="admin_notes" class="admin-form-control" rows="3"
                              placeholder="Internal notes..."><?= sanitize($order['admin_notes'] ?? '') ?></textarea>
                    <button type="submit" name="action" value="save_notes" class="btn btn-secondary btn-sm" style="margin-top: 0.5rem;">
                        Save Notes
                    </button>
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
        <div class="detail-card" style="margin-bottom: 1.5rem;">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Order Details</h3>
            </div>
            <div class="detail-card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Order #</span>
                        <span class="info-value"><?= sanitize($order['order_number']) ?></span>
                    </div>
                    <?php if ($order['final_price']): ?>
                    <div class="info-item">
                        <span class="info-label">Price</span>
                        <span class="info-value">$<?= number_format($order['final_price'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($order['deposit_amount']): ?>
                    <div class="info-item">
                        <span class="info-label">Deposit Paid</span>
                        <span class="info-value" style="color: var(--success);">$<?= number_format($order['deposit_amount'], 2) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="info-item">
                        <span class="info-label">Submitted</span>
                        <span class="info-value"><?= date('M j, Y', strtotime($order['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Order -->
        <?php if (!in_array($currentStatus, ['paid_in_full', 'cancelled'])): ?>
        <div class="detail-card">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Danger Zone</h3>
            </div>
            <div class="detail-card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <button type="submit" name="action" value="cancel" class="btn btn-danger btn-block"
                            data-confirm="Are you sure you want to cancel this order?">
                        Cancel Order
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
