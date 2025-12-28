<?php
require_once 'config/config.php';
require_once 'classes/Order.php';
require_once 'classes/PayPal.php';

requireLogin();

$db = new Database();
$orderModel = new Order($db->connect());

$orders = $orderModel->getByUser($_SESSION['user_id']);

$pageTitle = 'My Orders';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 2rem 0;">
    <div class="container hero-content">
        <h1>My Orders & Quotes</h1>
        <p>Track your quote requests and orders</p>
    </div>
</section>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <?php if (empty($orders)): ?>
            <div class="card">
                <div class="card-body text-center" style="padding: 4rem 2rem;">
                    <div style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1rem;">&#128203;</div>
                    <h3>No Orders Yet</h3>
                    <p style="color: var(--gray-600); margin-bottom: 1.5rem;">Design your custom poker table and request a quote to get started.</p>
                    <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary btn-lg">Build Your Table</a>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body" style="padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: var(--gray-100);">
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--gray-200);">Order #</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--gray-200);">Date</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--gray-200);">Configuration</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid var(--gray-200);">Status</th>
                                <th style="padding: 1rem; text-align: right; border-bottom: 2px solid var(--gray-200);">Price</th>
                                <th style="padding: 1rem; text-align: center; border-bottom: 2px solid var(--gray-200);">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <?php
                                $needsDeposit = $orderModel->needsDeposit($o);
                                $orderPrice = $orderModel->getOrderPrice($o);
                                $depositAmount = PayPal::calculateDeposit($orderPrice);
                                $depositPaid = !empty($o['deposit_paid_at']);
                                ?>
                                <tr>
                                    <td style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">
                                        <strong><?= sanitize($o['order_number']) ?></strong>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">
                                        <?= date('M j, Y', strtotime($o['created_at'])) ?>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">
                                        <div style="font-size: 0.9rem;">
                                            <?php $d = $o['design_data']; ?>
                                            <?= $d['tableStyle'] === 'racetrack' ? 'Racetrack' : 'Standard' ?> |
                                            <?= $d['tableSize'] ?? 'N/A' ?> |
                                            <span style="display: inline-block; width: 14px; height: 14px; background: <?= $d['railColor'] ?? '#000' ?>; border-radius: 3px; vertical-align: middle; border: 1px solid var(--gray-300);"></span>
                                            <span style="display: inline-block; width: 14px; height: 14px; background: <?= $d['surfaceColor'] ?? '#000' ?>; border-radius: 3px; vertical-align: middle; border: 1px solid var(--gray-300);"></span>
                                        </div>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid var(--gray-200);">
                                        <span class="status-badge status-<?= $o['status'] ?>">
                                            <?= ucfirst(str_replace('_', ' ', $o['status'])) ?>
                                        </span>
                                        <?php if ($depositPaid): ?>
                                            <div style="font-size: 0.75rem; color: var(--success); margin-top: 0.25rem;">
                                                Deposit paid: $<?= number_format($o['deposit_amount'], 2) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid var(--gray-200); text-align: right;">
                                        <?php if ($o['final_price']): ?>
                                            <strong>$<?= number_format($o['final_price'], 2) ?></strong>
                                        <?php else: ?>
                                            <span style="color: var(--gray-500);">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 1rem; border-bottom: 1px solid var(--gray-200); text-align: center;">
                                        <?php if ($needsDeposit): ?>
                                            <a href="<?= SITE_URL ?>/pay-deposit.php?id=<?= $o['id'] ?>" class="btn btn-primary btn-sm">
                                                Pay Deposit<br>
                                                <small>$<?= number_format($depositAmount, 2) ?></small>
                                            </a>
                                        <?php elseif ($depositPaid && $o['status'] === 'pending'): ?>
                                            <span style="color: var(--success); font-size: 0.85rem;">Awaiting Start</span>
                                        <?php elseif ($o['status'] === 'in_progress'): ?>
                                            <span style="color: var(--primary); font-size: 0.85rem;">Building</span>
                                        <?php elseif ($o['status'] === 'completed'): ?>
                                            <span style="color: var(--success); font-size: 0.85rem;">Complete</span>
                                        <?php elseif ($o['status'] === 'quote' && !$orderPrice): ?>
                                            <span style="color: var(--gray-500); font-size: 0.85rem;">Awaiting Quote</span>
                                        <?php else: ?>
                                            <span style="color: var(--gray-400);">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div style="margin-top: 2rem;">
            <h3>Order Status Guide</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                <div><span class="status-badge status-quote">Quote</span> - Awaiting price estimate</div>
                <div><span class="status-badge status-pending">Pending</span> - Awaiting confirmation</div>
                <div><span class="status-badge status-in_progress">In Progress</span> - Being built</div>
                <div><span class="status-badge status-completed">Completed</span> - Ready/Delivered</div>
            </div>
        </div>
    </div>
</section>

<style>
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
    table { font-size: 0.85rem; }
    table th, table td { padding: 0.75rem 0.5rem; }
}
</style>

<?php require_once 'includes/footer.php'; ?>
