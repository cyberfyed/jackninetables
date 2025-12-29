<?php
$pageTitle = 'Dashboard';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';

$admin = new Admin($conn);
$stats = $admin->getDashboardStats();
$recentMessages = $admin->getRecentMessages(5);
?>

<!-- Order Status Cards -->
<div class="stats-grid" style="grid-template-columns: repeat(5, 1fr);">
    <a href="<?= SITE_URL ?>/admin/quotes.php?status=quote_started" class="stat-card" style="text-decoration: none; transition: transform 0.2s;">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['needs_quote'] ?></div>
                <div class="stat-card-label">Need Quote</div>
            </div>
            <div class="stat-card-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">&#128203;</div>
        </div>
    </a>

    <a href="<?= SITE_URL ?>/admin/quotes.php?status=price_sent" class="stat-card" style="text-decoration: none;">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['awaiting_deposit'] ?></div>
                <div class="stat-card-label">Awaiting Deposit</div>
            </div>
            <div class="stat-card-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">&#128176;</div>
        </div>
    </a>

    <a href="<?= SITE_URL ?>/admin/quotes.php?status=deposit_paid" class="stat-card" style="text-decoration: none;">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['in_production'] ?></div>
                <div class="stat-card-label">In Production</div>
            </div>
            <div class="stat-card-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">&#128296;</div>
        </div>
    </a>

    <a href="<?= SITE_URL ?>/admin/quotes.php?status=invoice_sent" class="stat-card" style="text-decoration: none;">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['awaiting_final'] ?></div>
                <div class="stat-card-label">Awaiting Final</div>
            </div>
            <div class="stat-card-icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">&#128230;</div>
        </div>
    </a>

    <a href="<?= SITE_URL ?>/admin/quotes.php?status=paid_in_full" class="stat-card" style="text-decoration: none;">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['completed_orders'] ?></div>
                <div class="stat-card-label">Completed</div>
            </div>
            <div class="stat-card-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">&#10003;</div>
        </div>
    </a>
</div>

<!-- Secondary Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
    <a href="<?= SITE_URL ?>/admin/messages.php?status=unread" class="stat-card" style="text-decoration: none;">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['unread_messages'] ?></div>
                <div class="stat-card-label">Unread Messages</div>
            </div>
            <div class="stat-card-icon messages">&#9993;</div>
        </div>
    </a>

    <a href="<?= SITE_URL ?>/admin/users.php" class="stat-card" style="text-decoration: none;">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['total_users'] ?></div>
                <div class="stat-card-label">Total Users</div>
            </div>
            <div class="stat-card-icon users">&#128100;</div>
        </div>
    </a>

    <a href="<?= SITE_URL ?>/admin/quotes.php" class="stat-card" style="text-decoration: none;">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['total_quotes'] ?></div>
                <div class="stat-card-label">Total Orders</div>
            </div>
            <div class="stat-card-icon orders">&#128203;</div>
        </div>
    </a>
</div>

<!-- Action Needed Section -->
<?php
$needsQuote = $admin->getOrdersNeedingAction('quote_started', 5);
$inProduction = $admin->getOrdersNeedingAction('deposit_paid', 5);
?>

<div class="dashboard-grid">
    <!-- Orders Needing Quotes -->
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h2 class="admin-table-title">&#128203; Need to Send Quote</h2>
            <a href="<?= SITE_URL ?>/admin/quotes.php?status=quote_started" class="btn btn-sm">View All</a>
        </div>
        <?php if (empty($needsQuote)): ?>
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">&#10003;</div>
                <div class="empty-state-title">All caught up!</div>
            </div>
        <?php else: ?>
            <table class="admin-table mobile-cards">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($needsQuote as $order): ?>
                        <tr>
                            <td data-label="Order #"><strong><?= sanitize($order['order_number']) ?></strong></td>
                            <td data-label="Customer"><?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></td>
                            <td data-label="Date"><?= date('M j', strtotime($order['created_at'])) ?></td>
                            <td data-label="">
                                <a href="<?= SITE_URL ?>/admin/quote-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                    Set Price
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Orders In Production -->
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h2 class="admin-table-title">&#128296; In Production</h2>
            <a href="<?= SITE_URL ?>/admin/quotes.php?status=deposit_paid" class="btn btn-sm">View All</a>
        </div>
        <?php if (empty($inProduction)): ?>
            <div class="empty-state" style="padding: 2rem;">
                <div class="empty-state-icon">&#128203;</div>
                <div class="empty-state-title">No tables in production</div>
            </div>
        <?php else: ?>
            <table class="admin-table mobile-cards">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Price</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inProduction as $order): ?>
                        <tr>
                            <td data-label="Order #"><strong><?= sanitize($order['order_number']) ?></strong></td>
                            <td data-label="Customer"><?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></td>
                            <td data-label="Price">$<?= number_format($order['final_price'], 2) ?></td>
                            <td data-label="">
                                <a href="<?= SITE_URL ?>/admin/quote-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Messages -->
<div class="admin-table-container" style="margin-top: 1.5rem;">
    <div class="admin-table-header">
        <h2 class="admin-table-title">&#9993; Recent Messages</h2>
        <a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-sm">View All</a>
    </div>
    <?php if (empty($recentMessages)): ?>
        <div class="empty-state" style="padding: 2rem;">
            <div class="empty-state-icon">&#9993;</div>
            <div class="empty-state-title">No messages yet</div>
        </div>
    <?php else: ?>
        <table class="admin-table mobile-cards">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentMessages as $message): ?>
                    <tr>
                        <td data-label="From">
                            <a href="<?= SITE_URL ?>/admin/message-detail.php?id=<?= $message['id'] ?>">
                                <?= sanitize($message['name']) ?>
                            </a>
                        </td>
                        <td data-label="Subject"><?= sanitize($message['subject'] ?: 'No subject') ?></td>
                        <td data-label="Status">
                            <span class="status-badge <?= $message['is_read'] ? 'read' : 'unread' ?>">
                                <?= $message['is_read'] ? 'Read' : 'Unread' ?>
                            </span>
                        </td>
                        <td data-label="Date"><?= date('M j, Y', strtotime($message['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>

<?php require_once 'includes/admin-footer.php'; ?>
