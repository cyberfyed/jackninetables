<?php
$pageTitle = 'Dashboard';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';

$admin = new Admin($conn);
$stats = $admin->getDashboardStats();
$recentOrders = $admin->getRecentOrders(5);
$recentMessages = $admin->getRecentMessages(5);
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['pending_quotes'] ?></div>
                <div class="stat-card-label">Pending Quotes</div>
            </div>
            <div class="stat-card-icon quotes">&#128203;</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['unread_messages'] ?></div>
                <div class="stat-card-label">Unread Messages</div>
            </div>
            <div class="stat-card-icon messages">&#9993;</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['total_users'] ?></div>
                <div class="stat-card-label">Total Users</div>
            </div>
            <div class="stat-card-icon users">&#128100;</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value"><?= $stats['completed_orders'] ?></div>
                <div class="stat-card-label">Completed Orders</div>
            </div>
            <div class="stat-card-icon orders">&#10003;</div>
        </div>
    </div>
</div>

<!-- Recent Activity Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <!-- Recent Quotes -->
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h2 class="admin-table-title">Recent Quotes</h2>
            <a href="<?= SITE_URL ?>/admin/quotes.php" class="btn btn-sm">View All</a>
        </div>
        <?php if (empty($recentOrders)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#128203;</div>
                <div class="empty-state-title">No quotes yet</div>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td>
                                <a href="<?= SITE_URL ?>/admin/quote-detail.php?id=<?= $order['id'] ?>">
                                    <?= sanitize($order['order_number']) ?>
                                </a>
                            </td>
                            <td><?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></td>
                            <td>
                                <span class="status-badge <?= $order['status'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Recent Messages -->
    <div class="admin-table-container">
        <div class="admin-table-header">
            <h2 class="admin-table-title">Recent Messages</h2>
            <a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-sm">View All</a>
        </div>
        <?php if (empty($recentMessages)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">&#9993;</div>
                <div class="empty-state-title">No messages yet</div>
            </div>
        <?php else: ?>
            <table class="admin-table">
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
                            <td>
                                <a href="<?= SITE_URL ?>/admin/message-detail.php?id=<?= $message['id'] ?>">
                                    <?= sanitize($message['name']) ?>
                                </a>
                            </td>
                            <td><?= sanitize($message['subject'] ?: 'No subject') ?></td>
                            <td>
                                <span class="status-badge <?= $message['is_read'] ? 'read' : 'unread' ?>">
                                    <?= $message['is_read'] ? 'Read' : 'Unread' ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($message['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
