<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?><?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body<?= isset($bodyClass) ? ' class="' . $bodyClass . '"' : '' ?>>
    <nav class="navbar">
        <div class="container">
            <a href="<?= SITE_URL ?>" class="logo">
                <span class="logo-icon">&#9827;</span>
                Jack Nine Tables
            </a>
            <button class="nav-toggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <ul class="nav-menu">
                <li><a href="<?= SITE_URL ?>">Home</a></li>
                <li><a href="<?= SITE_URL ?>/builder.php">Build Your Table</a></li>
                <li><a href="<?= SITE_URL ?>/gallery.php">Gallery</a></li>
                <li><a href="<?= SITE_URL ?>/about.php">About</a></li>
                <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-dropdown">
                        <a href="#" class="dropdown-toggle">
                            <?= sanitize($_SESSION['user_name']) ?>
                            <span class="dropdown-arrow">&#9662;</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a></li>
                            <li><a href="<?= SITE_URL ?>/my-designs.php">My Designs</a></li>
                            <li><a href="<?= SITE_URL ?>/my-orders.php">My Orders</a></li>
                            <li><a href="<?= SITE_URL ?>/profile.php">Profile</a></li>
                            <li class="divider"></li>
                            <li><a href="<?= SITE_URL ?>/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a href="<?= SITE_URL ?>/login.php" class="btn btn-outline">Login</a></li>
                    <li><a href="<?= SITE_URL ?>/register.php" class="btn btn-primary">Get Started</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <?php
    $flashSuccess = getFlash('success');
    $flashError = getFlash('error');
    $flashInfo = getFlash('info');
    if ($flashSuccess || $flashError || $flashInfo):
    ?>
    <div class="flash-messages">
        <div class="container">
            <?php if ($flashSuccess): ?>
                <div class="alert alert-success"><?= sanitize($flashSuccess) ?></div>
            <?php endif; ?>
            <?php if ($flashError): ?>
                <div class="alert alert-error"><?= sanitize($flashError) ?></div>
            <?php endif; ?>
            <?php if ($flashInfo): ?>
                <div class="alert alert-info"><?= sanitize($flashInfo) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <main>
