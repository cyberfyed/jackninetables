<?php
require_once __DIR__ . '/../../config/config.php';
requireAdmin();

// Get unread message count for badge
$db = new Database();
$conn = $db->connect();
$stmt = $conn->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = 0");
$unreadMessages = $stmt->fetch()['count'];

// Get pending quotes count
$stmt = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'quote_started'");
$pendingQuotes = $stmt->fetch()['count'];

// Determine current page for active state
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - Admin' : 'Admin' ?> | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="<?= SITE_URL ?>/admin/" class="sidebar-brand">
                    <span class="brand-icon">&#9827;</span>
                    <span>Admin Panel</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <a href="<?= SITE_URL ?>/admin/" class="nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
                    <span class="nav-icon">&#8962;</span>
                    <span>Dashboard</span>
                </a>

                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                </div>

                <a href="<?= SITE_URL ?>/admin/quotes.php" class="nav-item <?= $currentPage === 'quotes' || $currentPage === 'quote-detail' ? 'active' : '' ?>">
                    <span class="nav-icon">&#128203;</span>
                    <span>Quotes</span>
                    <?php if ($pendingQuotes > 0): ?>
                        <span class="nav-badge"><?= $pendingQuotes ?></span>
                    <?php endif; ?>
                </a>

                <a href="<?= SITE_URL ?>/admin/messages.php" class="nav-item <?= $currentPage === 'messages' || $currentPage === 'message-detail' ? 'active' : '' ?>">
                    <span class="nav-icon">&#9993;</span>
                    <span>Messages</span>
                    <?php if ($unreadMessages > 0): ?>
                        <span class="nav-badge"><?= $unreadMessages ?></span>
                    <?php endif; ?>
                </a>

                <a href="<?= SITE_URL ?>/admin/users.php" class="nav-item <?= $currentPage === 'users' || $currentPage === 'user-detail' ? 'active' : '' ?>">
                    <span class="nav-icon">&#128100;</span>
                    <span>Users</span>
                </a>

                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                </div>

                <a href="<?= SITE_URL ?>/admin/settings.php" class="nav-item <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <span class="nav-icon">&#9881;</span>
                    <span>Site Settings</span>
                </a>

                <div class="nav-section">
                    <div class="nav-section-title">Other</div>
                </div>

                <a href="<?= SITE_URL ?>/" class="nav-item" target="_blank">
                    <span class="nav-icon">&#127760;</span>
                    <span>View Site</span>
                </a>

                <a href="<?= SITE_URL ?>/logout.php" class="nav-item">
                    <span class="nav-icon">&#10140;</span>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-topbar">
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">&#9776;</button>
                <h1 class="topbar-title"><?= isset($pageTitle) ? sanitize($pageTitle) : 'Dashboard' ?></h1>
                <div class="topbar-actions">
                    <div class="topbar-user">
                        <span>&#128100;</span>
                        <span><?= sanitize($_SESSION['user_name']) ?></span>
                    </div>
                </div>
            </header>

            <div class="admin-content">
                <?php if ($flash = getFlash('success')): ?>
                    <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?= sanitize($flash) ?></div>
                <?php endif; ?>

                <?php if ($flash = getFlash('error')): ?>
                    <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?= sanitize($flash) ?></div>
                <?php endif; ?>
