<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/RateLimiter.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/');
    }
    redirect('dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting - 10 login attempts per minute
    $rateLimiter = new RateLimiter();
    if (!$rateLimiter->check('login', 10)) {
        $errors[] = getFlash('error');
    }
    // Verify CSRF
    elseif (!verifyCSRF($_POST['csrf_token'] ?? '')) {
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

                    // Validate redirect URL to prevent open redirect attacks
                    // Must start with / and not // (protocol-relative) or contain ://
                    if (preg_match('/^\/[^\/]/', $redirectTo) && strpos($redirectTo, '://') === false) {
                        header('Location: ' . $redirectTo);
                        exit;
                    }
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
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <svg class="eye-open" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="eye-closed" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
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
