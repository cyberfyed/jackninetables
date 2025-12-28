<?php
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';

$admin = new Admin($conn);

$id = intval($_GET['id'] ?? 0);
$user = $admin->getUserById($id);

if (!$user) {
    setFlash('error', 'User not found.');
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

$pageTitle = $user['first_name'] . ' ' . $user['last_name'];

// Handle toggle admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        if ($_POST['action'] === 'toggle_admin') {
            $result = $admin->toggleAdminStatus($id);
            if ($result['success']) {
                $user['is_admin'] = $result['is_admin'];
                setFlash('success', $result['is_admin'] ? 'User promoted to admin.' : 'Admin privileges removed.');
            } else {
                setFlash('error', $result['error']);
            }
        }
    }
}

// Get user's orders and designs
$orders = $admin->getUserOrders($id);
$designs = $admin->getUserDesigns($id);
?>

<div class="detail-header">
    <a href="<?= SITE_URL ?>/admin/users.php" class="detail-back">
        &larr; Back to Users
    </a>
    <div>
        <?php if ($user['is_admin']): ?>
            <span class="status-badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">Admin</span>
        <?php endif; ?>
        <span class="status-badge <?= $user['email_verified'] ? 'completed' : 'pending' ?>">
            <?= $user['email_verified'] ? 'Verified' : 'Unverified' ?>
        </span>
    </div>
</div>

<div class="detail-grid">
    <!-- Main Content -->
    <div>
        <!-- User's Orders -->
        <div class="admin-table-container" style="margin-bottom: 1.5rem;">
            <div class="admin-table-header">
                <h3 class="admin-table-title">Orders (<?= count($orders) ?>)</h3>
            </div>
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">&#128203;</div>
                    <div class="empty-state-title">No orders yet</div>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Table</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php $design = $order['design_data']; ?>
                            <tr>
                                <td>
                                    <a href="<?= SITE_URL ?>/admin/quote-detail.php?id=<?= $order['id'] ?>">
                                        <?= sanitize($order['order_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?= ($design['tableStyle'] ?? '') === 'racetrack' ? 'Racetrack' : 'Standard' ?>
                                </td>
                                <td>
                                    <span class="status-badge <?= $order['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($order['final_price']): ?>
                                        $<?= number_format($order['final_price'], 2) ?>
                                    <?php elseif ($order['estimated_price']): ?>
                                        ~$<?= number_format($order['estimated_price'], 2) ?>
                                    <?php else: ?>
                                        --
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- User's Designs -->
        <div class="admin-table-container">
            <div class="admin-table-header">
                <h3 class="admin-table-title">Saved Designs (<?= count($designs) ?>)</h3>
            </div>
            <?php if (empty($designs)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">&#128196;</div>
                    <div class="empty-state-title">No saved designs</div>
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Style</th>
                            <th>Colors</th>
                            <th>Favorite</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($designs as $design): ?>
                            <?php $data = $design['design_data']; ?>
                            <tr>
                                <td><?= sanitize($design['name']) ?></td>
                                <td><?= ($data['tableStyle'] ?? '') === 'racetrack' ? 'Racetrack' : 'Standard' ?></td>
                                <td>
                                    <div style="display: flex; gap: 4px;">
                                        <span class="color-swatch" style="width: 20px; height: 20px; background: <?= sanitize($data['railColor'] ?? '#000') ?>;"></span>
                                        <span class="color-swatch" style="width: 20px; height: 20px; background: <?= sanitize($data['surfaceColor'] ?? '#000') ?>;"></span>
                                    </div>
                                </td>
                                <td><?= $design['is_favorite'] ? '&#9733;' : '' ?></td>
                                <td><?= date('M j, Y', strtotime($design['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- User Info -->
        <div class="detail-card" style="margin-bottom: 1.5rem;">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Profile</h3>
            </div>
            <div class="detail-card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value">
                            <a href="mailto:<?= sanitize($user['email']) ?>"><?= sanitize($user['email']) ?></a>
                        </span>
                    </div>
                    <?php if (!empty($user['phone'])): ?>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?= sanitize($user['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($user['address'])): ?>
                    <div class="info-item">
                        <span class="info-label">Address</span>
                        <span class="info-value" style="text-align: right;">
                            <?= sanitize($user['address']) ?><br>
                            <?= sanitize($user['city']) ?>, <?= sanitize($user['state']) ?> <?= sanitize($user['zip']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Account Info -->
        <div class="detail-card" style="margin-bottom: 1.5rem;">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Account</h3>
            </div>
            <div class="detail-card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Joined</span>
                        <span class="info-value"><?= date('M j, Y', strtotime($user['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Status</span>
                        <span class="info-value"><?= $user['email_verified'] ? 'Verified' : 'Unverified' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Role</span>
                        <span class="info-value"><?= $user['is_admin'] ? 'Administrator' : 'User' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Total Orders</span>
                        <span class="info-value"><?= $user['order_count'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Saved Designs</span>
                        <span class="info-value"><?= $user['design_count'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Actions</h3>
            </div>
            <div class="detail-card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="action" value="toggle_admin">

                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                        <p style="color: var(--gray-500); font-size: 0.875rem;">You cannot modify your own admin status.</p>
                    <?php else: ?>
                        <button type="submit" class="btn <?= $user['is_admin'] ? 'btn-danger' : 'btn-primary' ?> btn-block"
                                data-confirm="<?= $user['is_admin'] ? 'Remove admin privileges from this user?' : 'Make this user an administrator?' ?>">
                            <?= $user['is_admin'] ? 'Remove Admin' : 'Make Admin' ?>
                        </button>
                    <?php endif; ?>
                </form>

                <a href="mailto:<?= sanitize($user['email']) ?>" class="btn btn-secondary btn-block" style="margin-top: 0.5rem;">
                    Send Email
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
