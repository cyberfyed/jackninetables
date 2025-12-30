<?php
require_once 'config/config.php';
require_once 'classes/User.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$token = $_GET['token'] ?? '';
$errors = [];
$success = false;

if (empty($token)) {
    setFlash('error', 'Invalid reset link.');
    redirect('forgot-password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($password)) {
            $errors[] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } else {
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'Password must contain at least one uppercase letter.';
            }
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'Password must contain at least one lowercase letter.';
            }
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'Password must contain at least one number.';
            }
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $db = new Database();
            $user = new User($db->connect());
            $result = $user->resetPassword($token, $password);

            if ($result['success']) {
                setFlash('success', 'Your password has been reset. You can now log in.');
                redirect('login.php');
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

$pageTitle = 'Reset Password';
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Reset Password</h1>
            <p>Enter your new password</p>
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

                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div class="form-hint">Minimum 8 characters with uppercase, lowercase, and number</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Reset Password</button>
            </form>
        </div>

        <div class="auth-footer">
            <p>Remember your password? <a href="<?= SITE_URL ?>/login.php">Sign in</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
