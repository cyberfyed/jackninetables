<?php
$pageTitle = 'Quotes';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';

$admin = new Admin($conn);

// Color name mappings
$colorNames = [
    // Rail colors
    '#1a1a1a' => 'Black',
    '#3d2314' => 'Brown',
    '#1a2744' => 'Blue',
    '#f5f5f5' => 'White',
    '#c4a77d' => 'Tan',
    '#8b0000' => 'Red',
    // Speed cloth surface colors
    '#1a472a' => 'Green',
    '#1a3a5c' => 'Blue',
    '#6b1c1c' => 'Red',
    '#3d1a4d' => 'Purple',
    // Velveteen surface colors
    '#2d5a3d' => 'Green',
    '#2a4a6d' => 'Blue',
    '#8b2c2c' => 'Red',
    '#252525' => 'Black',
];

// Status display mapping
$statusLabels = [
    'quote_started' => 'Quote Started',
    'price_sent' => 'Price Sent',
    'deposit_paid' => 'Deposit Paid',
    'invoice_sent' => 'Invoice Sent',
    'paid_in_full' => 'Paid in Full',
    'cancelled' => 'Cancelled'
];

// Filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? '',
    'archived' => $_GET['archived'] ?? ''
];
$viewingArchived = $filters['archived'] === 'only';

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalOrders = $admin->countOrders($filters);
$totalPages = ceil($totalOrders / $perPage);
$orders = $admin->getAllOrders($filters, $perPage, $offset);

// Build query string for pagination
$queryParams = array_filter($filters);
$queryString = http_build_query($queryParams);
?>

<div class="admin-table-container">
    <div class="admin-table-header">
        <h2 class="admin-table-title"><?= $viewingArchived ? 'Archived Quotes' : 'All Quotes' ?> (<?= $totalOrders ?>)</h2>
        <div style="margin-left: auto;">
            <?php if ($viewingArchived): ?>
                <a href="<?= SITE_URL ?>/admin/quotes.php" class="btn btn-sm btn-secondary">&larr; Back to Active</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/admin/quotes.php?archived=only" class="btn btn-sm btn-secondary">View Archived</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="filters-bar">
        <div class="filter-group">
            <label for="status">Status:</label>
            <select name="status" id="status" class="filter-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="quote_started" <?= $filters['status'] === 'quote_started' ? 'selected' : '' ?>>Quote Started</option>
                <option value="price_sent" <?= $filters['status'] === 'price_sent' ? 'selected' : '' ?>>Price Sent</option>
                <option value="deposit_paid" <?= $filters['status'] === 'deposit_paid' ? 'selected' : '' ?>>Deposit Paid</option>
                <option value="invoice_sent" <?= $filters['status'] === 'invoice_sent' ? 'selected' : '' ?>>Invoice Sent</option>
                <option value="paid_in_full" <?= $filters['status'] === 'paid_in_full' ? 'selected' : '' ?>>Paid in Full</option>
                <option value="cancelled" <?= $filters['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <div class="filter-search">
            <input type="text" name="search" placeholder="Search by order #, name, or email..."
                   value="<?= sanitize($filters['search']) ?>">
        </div>

        <button type="submit" class="btn btn-sm">Search</button>
        <?php if (!empty($queryString)): ?>
            <a href="<?= SITE_URL ?>/admin/quotes.php" class="btn btn-sm btn-secondary">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($orders)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128203;</div>
            <div class="empty-state-title">No quotes found</div>
            <p>Try adjusting your filters or search terms.</p>
        </div>
    <?php else: ?>
        <table class="admin-table mobile-cards">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Table</th>
                    <th>Status</th>
                    <th>Price</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td data-label="Order #">
                            <a href="<?= SITE_URL ?>/admin/quote-detail.php?id=<?= $order['id'] ?>">
                                <strong><?= sanitize($order['order_number']) ?></strong>
                            </a>
                        </td>
                        <td data-label="Customer">
                            <div><?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></div>
                            <small style="color: var(--gray-500);"><?= sanitize($order['email']) ?></small>
                        </td>
                        <td data-label="Table">
                            <?php
                            $design = $order['design_data'];
                            $style = ($design['tableStyle'] ?? '') === 'racetrack' ? 'Racetrack' : 'Standard';
                            $railColor = strtolower($design['railColor'] ?? '#000');
                            $surfaceColor = strtolower($design['surfaceColor'] ?? '#000');
                            $railName = $colorNames[$railColor] ?? 'Custom';
                            $surfaceName = $colorNames[$surfaceColor] ?? 'Custom';
                            ?>
                            <div><?= $style ?></div>
                            <div style="display: flex; gap: 6px; margin-top: 4px; flex-wrap: wrap;">
                                <span style="display: flex; align-items: center; gap: 3px;" title="Rail: <?= sanitize($railName) ?>">
                                    <span class="color-swatch" style="width: 14px; height: 14px; background: <?= sanitize($railColor) ?>;"></span>
                                    <small style="color: var(--gray-500); font-size: 0.7rem;"><?= sanitize($railName) ?></small>
                                </span>
                                <span style="display: flex; align-items: center; gap: 3px;" title="Surface: <?= sanitize($surfaceName) ?>">
                                    <span class="color-swatch" style="width: 14px; height: 14px; background: <?= sanitize($surfaceColor) ?>;"></span>
                                    <small style="color: var(--gray-500); font-size: 0.7rem;"><?= sanitize($surfaceName) ?></small>
                                </span>
                            </div>
                        </td>
                        <td data-label="Status">
                            <span class="status-badge <?= $order['status'] ?>">
                                <?= $statusLabels[$order['status']] ?? ucfirst(str_replace('_', ' ', $order['status'])) ?>
                            </span>
                        </td>
                        <td data-label="Price">
                            <?php if ($order['final_price']): ?>
                                <strong>$<?= number_format($order['final_price'], 2) ?></strong>
                            <?php else: ?>
                                <span style="color: var(--gray-400);">--</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Date"><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td data-label="">
                            <div class="table-actions">
                                <a href="<?= SITE_URL ?>/admin/quote-detail.php?id=<?= $order['id'] ?>" class="table-action view">View</a>
                                <?php if ($viewingArchived): ?>
                                    <button type="button" class="table-action edit" onclick="unarchiveQuote(<?= $order['id'] ?>)">Restore</button>
                                    <button type="button" class="table-action delete" onclick="deleteQuote(<?= $order['id'] ?>, '<?= sanitize($order['order_number']) ?>')">Delete</button>
                                <?php else: ?>
                                    <button type="button" class="table-action archive" onclick="archiveQuote(<?= $order['id'] ?>)">Archive</button>
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
                    Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalOrders) ?> of <?= $totalOrders ?> quotes
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

function archiveQuote(orderId) {
    showModal('Archive this quote? It will be moved to the archived view.', false, async () => {
        const response = await fetch(`${SITE_URL}/admin/api/quotes.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'archive', order_id: orderId, csrf_token: CSRF_TOKEN })
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

function unarchiveQuote(orderId) {
    showModal('Restore this quote? It will be moved back to the active view.', false, async () => {
        const response = await fetch(`${SITE_URL}/admin/api/quotes.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'unarchive', order_id: orderId, csrf_token: CSRF_TOKEN })
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

function deleteQuote(orderId, orderNumber) {
    showModal(`Permanently delete quote ${orderNumber}? This cannot be undone.`, true, async () => {
        const response = await fetch(`${SITE_URL}/admin/api/quotes.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'delete', order_id: orderId, csrf_token: CSRF_TOKEN })
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
