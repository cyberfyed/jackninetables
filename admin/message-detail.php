<?php
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';

$admin = new Admin($conn);

$id = intval($_GET['id'] ?? 0);
$message = $admin->getMessageById($id);

if (!$message) {
    setFlash('error', 'Message not found.');
    header('Location: ' . SITE_URL . '/admin/messages.php');
    exit;
}

// Mark as read when viewed
if (!$message['is_read']) {
    $admin->markMessageRead($id, 1);
    $message['is_read'] = 1;
}

$pageTitle = 'Message from ' . $message['name'];

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        if ($_POST['action'] === 'delete') {
            $admin->deleteMessage($id);
            setFlash('success', 'Message deleted.');
            header('Location: ' . SITE_URL . '/admin/messages.php');
            exit;
        } elseif ($_POST['action'] === 'toggle_read') {
            $newStatus = $message['is_read'] ? 0 : 1;
            $admin->markMessageRead($id, $newStatus);
            $message['is_read'] = $newStatus;
            setFlash('success', $newStatus ? 'Marked as read.' : 'Marked as unread.');
        }
    }
}
?>

<div class="detail-header">
    <a href="<?= SITE_URL ?>/admin/messages.php" class="detail-back">
        &larr; Back to Messages
    </a>
    <span class="status-badge <?= $message['is_read'] ? 'read' : 'unread' ?>">
        <?= $message['is_read'] ? 'Read' : 'Unread' ?>
    </span>
</div>

<div class="detail-grid">
    <!-- Main Content -->
    <div>
        <!-- Message Content -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3 class="detail-card-title"><?= sanitize($message['subject'] ?: 'No subject') ?></h3>
            </div>
            <div class="detail-card-body">
                <p style="color: var(--gray-700); line-height: 1.8; white-space: pre-wrap;">
<?= sanitize($message['message']) ?>
                </p>
            </div>
            <div class="quick-actions">
                <a href="mailto:<?= sanitize($message['email']) ?>?subject=Re: <?= urlencode($message['subject'] ?: 'Your message') ?>" class="btn btn-primary">
                    Reply via Email
                </a>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="action" value="toggle_read">
                    <button type="submit" class="btn btn-secondary">
                        Mark as <?= $message['is_read'] ? 'Unread' : 'Read' ?>
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-danger" data-confirm="Are you sure you want to delete this message?">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Sender Info -->
        <div class="detail-card" style="margin-bottom: 1.5rem;">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Sender</h3>
            </div>
            <div class="detail-card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?= sanitize($message['name']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value">
                            <a href="mailto:<?= sanitize($message['email']) ?>"><?= sanitize($message['email']) ?></a>
                        </span>
                    </div>
                    <?php if (!empty($message['phone'])): ?>
                    <div class="info-item">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?= sanitize($message['phone']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($message['user_id']): ?>
                    <div style="margin-top: 1rem;">
                        <a href="<?= SITE_URL ?>/admin/user-detail.php?id=<?= $message['user_id'] ?>" class="btn btn-sm btn-secondary btn-block">
                            View User Profile
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Message Info -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3 class="detail-card-title">Details</h3>
            </div>
            <div class="detail-card-body">
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Received</span>
                        <span class="info-value"><?= date('M j, Y g:i A', strtotime($message['created_at'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Subject</span>
                        <span class="info-value"><?= sanitize($message['subject'] ?: 'None') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Registered User</span>
                        <span class="info-value"><?= $message['user_id'] ? 'Yes' : 'No' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
