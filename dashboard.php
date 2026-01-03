<?php
require_once 'config/config.php';
require_once 'classes/TableDesign.php';
require_once 'classes/Order.php';

requireLogin();

$db = new Database();
$conn = $db->connect();

$design = new TableDesign($conn);
$order = new Order($conn);

// Get stats
$designCount = $design->countByUser($_SESSION['user_id']);
$orderCount = $order->countByUser($_SESSION['user_id']);
$recentDesigns = $design->getByUser($_SESSION['user_id'], 3);
$recentOrders = $order->getByUser($_SESSION['user_id'], 3);

// Get pending quotes that need customer action
$stmt = $conn->prepare("SELECT id, order_number, status, final_price FROM orders WHERE user_id = ? AND status IN ('price_sent', 'invoice_sent') ORDER BY updated_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$pendingQuotes = $stmt->fetchAll();

$pageTitle = 'Dashboard';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 2rem 0;">
    <div class="container hero-content">
        <h1>Welcome back, <?= sanitize(explode(' ', $_SESSION['user_name'])[0]) ?>!</h1>
        <p>Manage your table designs and orders</p>
    </div>
</section>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <?php if (!isEmailVerified()): ?>
        <!-- Email Verification Reminder -->
        <div class="verification-alert">
            <div class="verification-alert-icon">&#9888;</div>
            <div class="verification-alert-content">
                <strong>Verify Your Email</strong>
                <p>Please verify your email address to save designs and request quotes.</p>
            </div>
            <a href="<?= SITE_URL ?>/resend-verification.php" class="btn btn-primary">Resend Verification Email</a>
        </div>
        <?php endif; ?>

        <?php if (!empty($pendingQuotes)): ?>
        <!-- Pending Quote Notification -->
        <div class="quote-notification-alert" id="quoteNotification">
            <button type="button" class="notification-dismiss" onclick="dismissQuoteNotification()" title="Dismiss">&times;</button>
            <div class="quote-notification-icon">&#128172;</div>
            <div class="quote-notification-content">
                <strong>You have <?= count($pendingQuotes) ?> quote<?= count($pendingQuotes) > 1 ? 's' : '' ?> waiting for your response!</strong>
                <ul class="pending-quotes-list">
                    <?php foreach ($pendingQuotes as $pq): ?>
                    <li>
                        <span class="quote-ref"><?= sanitize($pq['order_number']) ?></span>
                        <?php if ($pq['status'] === 'price_sent'): ?>
                            <span class="quote-action">Quote ready - $<?= number_format($pq['final_price'], 2) ?></span>
                        <?php else: ?>
                            <span class="quote-action">Final payment due</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="quote-notification-buttons">
                <a href="<?= SITE_URL ?>/my-orders.php" class="btn btn-primary">View All Orders</a>
                <?php
                $firstQuote = $pendingQuotes[0];
                if ($firstQuote['status'] === 'price_sent'): ?>
                    <a href="<?= SITE_URL ?>/pay-deposit.php?id=<?= $firstQuote['id'] ?>" class="btn btn-primary">Pay Deposit</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/pay-final.php?id=<?= $firstQuote['id'] ?>" class="btn btn-primary">Pay Balance</a>
                <?php endif; ?>
            </div>
        </div>
        <script>
        // Check if notification was dismissed this session
        if (sessionStorage.getItem('quoteNotificationDismissed')) {
            document.getElementById('quoteNotification').style.display = 'none';
        }
        function dismissQuoteNotification() {
            document.getElementById('quoteNotification').style.display = 'none';
            sessionStorage.setItem('quoteNotificationDismissed', 'true');
        }
        </script>
        <?php endif; ?>

        <!-- Quick Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div class="card">
                <div class="card-body text-center">
                    <div style="font-size: 2.5rem; font-weight: bold; color: var(--primary);"><?= $designCount ?></div>
                    <div style="color: var(--gray-600);">Saved Designs</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <div style="font-size: 2.5rem; font-weight: bold; color: var(--gold);"><?= $orderCount ?></div>
                    <div style="color: var(--gray-600);">Quote Requests</div>
                </div>
            </div>
            <div class="card">
                <div class="card-body text-center">
                    <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary btn-lg" style="margin-top: 0.5rem;">
                        + New Design
                    </a>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Recent Designs -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0;">Recent Designs</h3>
                    <a href="<?= SITE_URL ?>/my-designs.php">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentDesigns)): ?>
                        <p style="color: var(--gray-500); text-align: center; padding: 2rem 0;">
                            No designs yet. <a href="<?= SITE_URL ?>/builder.php">Create your first table!</a>
                        </p>
                    <?php else: ?>
                        <ul style="list-style: none;">
                            <?php foreach ($recentDesigns as $d): ?>
                                <li style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--gray-200);">
                                    <div>
                                        <strong><?= sanitize($d['name']) ?></strong>
                                        <div style="font-size: 0.85rem; color: var(--gray-500);">
                                            <?= date('M j, Y', strtotime($d['created_at'])) ?>
                                        </div>
                                    </div>
                                    <a href="<?= SITE_URL ?>/builder.php?load=<?= $d['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Orders/Quotes -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0;">Recent Quotes</h3>
                    <a href="<?= SITE_URL ?>/my-orders.php">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <p style="color: var(--gray-500); text-align: center; padding: 2rem 0;">
                            No quote requests yet. <a href="<?= SITE_URL ?>/builder.php">Design a table and request a quote!</a>
                        </p>
                    <?php else: ?>
                        <ul style="list-style: none;">
                            <?php foreach ($recentOrders as $o): ?>
                                <li style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--gray-200);">
                                    <div>
                                        <strong><?= sanitize($o['order_number']) ?></strong>
                                        <div style="font-size: 0.85rem; color: var(--gray-500);">
                                            <?= date('M j, Y', strtotime($o['created_at'])) ?>
                                        </div>
                                    </div>
                                    <span class="status-badge status-<?= $o['status'] ?>">
                                        <?= ucfirst($o['status']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.verification-alert {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    border: 2px solid #ff9800;
    border-radius: var(--radius-lg);
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
}

.verification-alert-icon {
    font-size: 2rem;
    color: #e65100;
    flex-shrink: 0;
}

.verification-alert-content {
    flex: 1;
}

.verification-alert-content strong {
    color: #e65100;
    font-size: 1.1rem;
}

.verification-alert-content p {
    color: var(--gray-700);
    margin: 0.25rem 0 0;
    font-size: 0.9rem;
}

/* Quote Notification Alert */
.quote-notification-alert {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
    border: 2px solid var(--primary);
    border-radius: var(--radius-lg);
    padding: 1.25rem 1.5rem;
    padding-right: 2.5rem;
    margin-bottom: 2rem;
    animation: slideIn 0.3s ease-out;
}

.notification-dismiss {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--gray-500);
    cursor: pointer;
    line-height: 1;
    padding: 0.25rem;
    transition: color 0.2s;
}

.notification-dismiss:hover {
    color: var(--gray-700);
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.quote-notification-icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.quote-notification-content {
    flex: 1;
}

.quote-notification-content strong {
    color: var(--primary-dark);
    font-size: 1.1rem;
    display: block;
    margin-bottom: 0.75rem;
}

.pending-quotes-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.pending-quotes-list li {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.pending-quotes-list li:last-child {
    border-bottom: none;
}

.quote-ref {
    font-weight: 600;
    color: var(--primary);
    font-family: monospace;
}

.quote-action {
    color: var(--gray-600);
    font-size: 0.9rem;
    flex: 1;
}

.quote-notification-buttons {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    flex-shrink: 0;
    align-self: center;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
.status-quote { background: #e3f2fd; color: #1565c0; }
.status-pending { background: #fff3e0; color: #ef6c00; }
.status-in_progress { background: #e8f5e9; color: #2e7d32; }
.status-completed { background: #e8f5e9; color: #2e7d32; }
.status-cancelled { background: #ffebee; color: #c62828; }

@media (max-width: 768px) {
    .section > .container > div:last-child {
        grid-template-columns: 1fr;
    }
    .verification-alert,
    .quote-notification-alert {
        flex-direction: column;
        text-align: center;
    }
    .pending-quotes-list li {
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }
    .quote-notification-buttons {
        width: 100%;
        margin-top: 1rem;
    }
    .quote-notification-buttons .btn {
        width: 100%;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
