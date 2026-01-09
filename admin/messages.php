<?php
$pageTitle = 'Messages';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';

$admin = new Admin($conn);

// Filters
$filters = [
    'is_read' => isset($_GET['is_read']) && $_GET['is_read'] !== '' ? intval($_GET['is_read']) : '',
    'search' => $_GET['search'] ?? '',
    'archived' => $_GET['archived'] ?? ''
];
$viewingArchived = $filters['archived'] === 'only';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalMessages = $admin->countMessages($filters);
$totalPages = ceil($totalMessages / $perPage);
$messages = $admin->getAllMessages($filters, $perPage, $offset);

// Build query string for pagination
$queryParams = [];
if (isset($_GET['is_read']) && $_GET['is_read'] !== '') {
    $queryParams['is_read'] = $_GET['is_read'];
}
if (!empty($filters['search'])) {
    $queryParams['search'] = $filters['search'];
}
$queryString = http_build_query($queryParams);
?>

<div class="admin-table-container">
    <div class="admin-table-header">
        <h2 class="admin-table-title"><?= $viewingArchived ? 'Archived Messages' : 'Contact Messages' ?> (<?= $totalMessages ?>)</h2>
        <div style="margin-left: auto;">
            <?php if ($viewingArchived): ?>
                <a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-sm btn-secondary">&larr; Back to Active</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/admin/messages.php?archived=only" class="btn btn-sm btn-secondary">View Archived</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="filters-bar">
        <div class="filter-group">
            <label for="is_read">Status:</label>
            <select name="is_read" id="is_read" class="filter-select" onchange="this.form.submit()">
                <option value="">All Messages</option>
                <option value="0" <?= $filters['is_read'] === 0 ? 'selected' : '' ?>>Unread</option>
                <option value="1" <?= $filters['is_read'] === 1 ? 'selected' : '' ?>>Read</option>
            </select>
        </div>

        <div class="filter-search">
            <input type="text" name="search" placeholder="Search by name, email, or subject..."
                   value="<?= sanitize($filters['search']) ?>">
        </div>

        <button type="submit" class="btn btn-sm">Search</button>
        <?php if (!empty($queryString)): ?>
            <a href="<?= SITE_URL ?>/admin/messages.php" class="btn btn-sm btn-secondary">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($messages)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#9993;</div>
            <div class="empty-state-title">No messages found</div>
            <p>Try adjusting your filters or search terms.</p>
        </div>
    <?php else: ?>
        <table class="admin-table mobile-cards">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr style="<?= !$message['is_read'] ? 'background: rgba(239, 68, 68, 0.05);' : '' ?>">
                        <td data-label="From">
                            <div style="<?= !$message['is_read'] ? 'font-weight: 600;' : '' ?>">
                                <?= sanitize($message['name']) ?>
                            </div>
                            <small style="color: var(--gray-500);"><?= sanitize($message['email']) ?></small>
                        </td>
                        <td data-label="Subject">
                            <a href="<?= SITE_URL ?>/admin/message-detail.php?id=<?= $message['id'] ?>" style="<?= !$message['is_read'] ? 'font-weight: 600;' : '' ?>">
                                <?= sanitize($message['subject'] ?: 'No subject') ?>
                            </a>
                        </td>
                        <td data-label="Message">
                            <span style="color: var(--gray-600);">
                                <?= sanitize(substr($message['message'], 0, 50)) ?><?= strlen($message['message']) > 50 ? '...' : '' ?>
                            </span>
                        </td>
                        <td data-label="Status">
                            <span class="status-badge <?= $message['is_read'] ? 'read' : 'unread' ?>">
                                <?= $message['is_read'] ? 'Read' : 'Unread' ?>
                            </span>
                        </td>
                        <td data-label="Date"><?= date('M j, Y', strtotime($message['created_at'])) ?></td>
                        <td data-label="">
                            <div class="table-actions">
                                <a href="<?= SITE_URL ?>/admin/message-detail.php?id=<?= $message['id'] ?>" class="table-action view">View</a>
                                <?php if ($viewingArchived): ?>
                                    <button type="button" class="table-action edit" onclick="unarchiveMessage(<?= $message['id'] ?>)">Restore</button>
                                    <button type="button" class="table-action delete" onclick="deleteMessage(<?= $message['id'] ?>)">Delete</button>
                                <?php else: ?>
                                    <button type="button" class="table-action archive" onclick="archiveMessage(<?= $message['id'] ?>)">Archive</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <div class="pagination-info">
                    Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalMessages) ?> of <?= $totalMessages ?> messages
                </div>
                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                        <a href="?<?= $queryString ? $queryString . '&' : '' ?>page=<?= $page - 1 ?>" class="pagination-link">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?<?= $queryString ? $queryString . '&' : '' ?>page=<?= $i ?>"
                           class="pagination-link <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?= $queryString ? $queryString . '&' : '' ?>page=<?= $page + 1 ?>" class="pagination-link">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$extraJS = <<<'JS'
<script>
function showModal(message, isDanger, onConfirm) {
    confirmModal.message.textContent = message;
    confirmModal.confirmBtn.className = isDanger ? 'btn btn-danger confirm-modal-confirm' : 'btn btn-primary confirm-modal-confirm';
    confirmModal.modal.classList.add('active');

    const newConfirmBtn = confirmModal.confirmBtn.cloneNode(true);
    confirmModal.confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmModal.confirmBtn);
    confirmModal.confirmBtn = newConfirmBtn;

    confirmModal.confirmBtn.addEventListener('click', async () => {
        confirmModal.confirmBtn.disabled = true;
        confirmModal.confirmBtn.textContent = 'Processing...';
        try {
            await onConfirm();
        } catch (e) {
            confirmModal.confirmBtn.disabled = false;
            confirmModal.confirmBtn.textContent = 'Confirm';
        }
    });
}

function archiveMessage(messageId) {
    showModal('Archive this message? It will be moved to the archived view.', false, async () => {
        const response = await fetch(`${SITE_URL}/admin/api/messages.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'archive', message_id: messageId, csrf_token: CSRF_TOKEN })
        });
        const data = await response.json();
        if (data.success) {
            confirmModal.hide();
            location.reload();
        } else {
            alert(data.error || 'Failed to archive');
            confirmModal.confirmBtn.disabled = false;
            confirmModal.confirmBtn.textContent = 'Confirm';
        }
    });
}

function unarchiveMessage(messageId) {
    showModal('Restore this message? It will be moved back to the active view.', false, async () => {
        const response = await fetch(`${SITE_URL}/admin/api/messages.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'unarchive', message_id: messageId, csrf_token: CSRF_TOKEN })
        });
        const data = await response.json();
        if (data.success) {
            confirmModal.hide();
            location.reload();
        } else {
            alert(data.error || 'Failed to restore');
            confirmModal.confirmBtn.disabled = false;
            confirmModal.confirmBtn.textContent = 'Confirm';
        }
    });
}

function deleteMessage(messageId) {
    showModal('Permanently delete this message? This cannot be undone.', true, async () => {
        const response = await fetch(`${SITE_URL}/admin/api/messages.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'delete', message_id: messageId, csrf_token: CSRF_TOKEN })
        });
        const data = await response.json();
        if (data.success) {
            confirmModal.hide();
            location.reload();
        } else {
            alert(data.error || 'Failed to delete');
            confirmModal.confirmBtn.disabled = false;
            confirmModal.confirmBtn.textContent = 'Confirm';
        }
    });
}
</script>
JS;
require_once 'includes/admin-footer.php'; ?>
