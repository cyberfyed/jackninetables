<?php
$pageTitle = 'Users';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Admin.php';

$admin = new Admin($conn);

// Filters
$filters = [
    'is_admin' => isset($_GET['is_admin']) && $_GET['is_admin'] !== '' ? intval($_GET['is_admin']) : '',
    'email_verified' => isset($_GET['email_verified']) && $_GET['email_verified'] !== '' ? intval($_GET['email_verified']) : '',
    'search' => $_GET['search'] ?? ''
];

// Pagination
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalUsers = $admin->countUsers($filters);
$totalPages = ceil($totalUsers / $perPage);
$users = $admin->getAllUsers($filters, $perPage, $offset);

// Build query string for pagination
$queryParams = [];
if (isset($_GET['is_admin']) && $_GET['is_admin'] !== '') {
    $queryParams['is_admin'] = $_GET['is_admin'];
}
if (isset($_GET['email_verified']) && $_GET['email_verified'] !== '') {
    $queryParams['email_verified'] = $_GET['email_verified'];
}
if (!empty($filters['search'])) {
    $queryParams['search'] = $filters['search'];
}
$queryString = http_build_query($queryParams);
?>

<div class="admin-table-container">
    <div class="admin-table-header">
        <h2 class="admin-table-title">Users (<?= $totalUsers ?>)</h2>
    </div>

    <!-- Filters -->
    <form method="GET" class="filters-bar">
        <div class="filter-group">
            <label for="is_admin">Role:</label>
            <select name="is_admin" id="is_admin" class="filter-select" onchange="this.form.submit()">
                <option value="">All Users</option>
                <option value="1" <?= $filters['is_admin'] === 1 ? 'selected' : '' ?>>Admins Only</option>
                <option value="0" <?= $filters['is_admin'] === 0 ? 'selected' : '' ?>>Non-Admins</option>
            </select>
        </div>

        <div class="filter-group">
            <label for="email_verified">Email:</label>
            <select name="email_verified" id="email_verified" class="filter-select" onchange="this.form.submit()">
                <option value="">All</option>
                <option value="1" <?= $filters['email_verified'] === 1 ? 'selected' : '' ?>>Verified</option>
                <option value="0" <?= $filters['email_verified'] === 0 ? 'selected' : '' ?>>Unverified</option>
            </select>
        </div>

        <div class="filter-search">
            <input type="text" name="search" placeholder="Search by name or email..."
                   value="<?= sanitize($filters['search']) ?>">
        </div>

        <button type="submit" class="btn btn-sm">Search</button>
        <?php if (!empty($queryString)): ?>
            <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-sm btn-secondary">Clear</a>
        <?php endif; ?>
    </form>

    <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">&#128100;</div>
            <div class="empty-state-title">No users found</div>
            <p>Try adjusting your filters or search terms.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Orders</th>
                    <th>Designs</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <a href="<?= SITE_URL ?>/admin/user-detail.php?id=<?= $user['id'] ?>">
                                <strong><?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                            </a>
                            <?php if ($user['is_admin']): ?>
                                <span class="status-badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; margin-left: 0.5rem;">Admin</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div><?= sanitize($user['email']) ?></div>
                            <?php if ($user['phone']): ?>
                                <small style="color: var(--gray-500);"><?= sanitize($user['phone']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $user['email_verified'] ? 'completed' : 'pending' ?>">
                                <?= $user['email_verified'] ? 'Verified' : 'Unverified' ?>
                            </span>
                        </td>
                        <td><?= $user['order_count'] ?></td>
                        <td><?= $user['design_count'] ?></td>
                        <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="<?= SITE_URL ?>/admin/user-detail.php?id=<?= $user['id'] ?>" class="table-action view">View</a>
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
                    Showing <?= $offset + 1 ?> to <?= min($offset + $perPage, $totalUsers) ?> of <?= $totalUsers ?> users
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
