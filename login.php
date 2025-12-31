<?php
require_once 'config/config.php';
require_once 'classes/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/');
    }
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email)) {
            $errors[] = 'Email is required.';
        }
        if (empty($password)) {
            $errors[] = 'Password is required.';
        }

        if (empty($errors)) {
            $db = new Database();
            $user = new User($db->connect());
            $result = $user->login($email, $password);

            if ($result['success']) {
                // Handle Remember Me cookie
                if (!empty($_POST['remember'])) {
                    setcookie('remembered_email', $email, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                } else {
                    setcookie('remembered_email', '', time() - 3600, '/', '', false, true);
                }

                setFlash('success', 'Welcome back, ' . $_SESSION['user_name'] . '!');

                // Check if there's a saved redirect destination
                if (!empty($_SESSION['redirect_after_login'])) {
                    $redirectTo = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . $redirectTo);
                    exit;
                }

                if (isAdmin()) {
                    redirect('admin/');
                }
                redirect('dashboard.php');
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Sign in to access your account</p>
        </div>

        <div class="auth-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= sanitize($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" data-validate novalidate>
                <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                <?php $rememberedEmail = $_COOKIE['remembered_email'] ?? ''; ?>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= sanitize($_POST['email'] ?? $rememberedEmail) ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal; cursor: pointer;">
                        <input type="checkbox" name="remember" <?= $rememberedEmail ? 'checked' : '' ?>> Remember me
                    </label>
                    <a href="<?= SITE_URL ?>/forgot-password.php">Forgot password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
            </form>
        </div>

        <div class="auth-footer">
            <p>Don't have an account? <a href="<?= SITE_URL ?>/register.php">Create one</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
