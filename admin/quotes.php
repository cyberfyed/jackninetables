<?php
$pageTitle = 'Quotes';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';

$admin = new Admin($conn);

// Filters
$filters = [
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? ''
];

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
        <h2 class="admin-table-title">All Quotes (<?= $totalOrders ?>)</h2>
    </div>

    <!-- Filters -->
    <form method="GET" class="filters-bar">
        <div class="filter-group">
            <label for="status">Status:</label>
            <select name="status" id="status" class="filter-select" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="quote" <?= $filters['status'] === 'quote' ? 'selected' : '' ?>>Quote</option>
                <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="completed" <?= $filters['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
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
        <table class="admin-table">
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
                        <td>
                            <a href="<?= SITE_URL ?>/admin/quote-detail.php?id=<?= $order['id'] ?>">
                                <strong><?= sanitize($order['order_number']) ?></strong>
                            </a>
                        </td>
                        <td>
                            <div><?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></div>
                            <small style="color: var(--gray-500);"><?= sanitize($order['email']) ?></small>
                        </td>
                        <td>
                            <?php
                            $design = $order['design_data'];
                            $style = ($design['tableStyle'] ?? '') === 'racetrack' ? 'Racetrack' : 'Standard';
                            ?>
                            <div><?= $style ?></div>
                            <div style="display: flex; gap: 4px; margin-top: 4px;">
                                <span class="color-swatch" style="width: 16px; height: 16px; background: <?= sanitize($design['railColor'] ?? '#000') ?>;"></span>
                                <span class="color-swatch" style="width: 16px; height: 16px; background: <?= sanitize($design['surfaceColor'] ?? '#000') ?>;"></span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge <?= $order['status'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($order['final_price']): ?>
                                <strong>$<?= number_format($order['final_price'], 2) ?></strong>
                            <?php elseif ($order['estimated_price']): ?>
                                ~$<?= number_format($order['estimated_price'], 2) ?>
                            <?php else: ?>
                                <span style="color: var(--gray-400);">--</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M j, Y', strtotime($order['created_at'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="<?= SITE_URL ?>/admin/quote-detail.php?id=<?= $order['id'] ?>" class="table-action view">View</a>
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

<?php require_once 'includes/admin-footer.php'; ?>
