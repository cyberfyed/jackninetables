<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/EmailService.php';
require_once 'classes/RateLimiter.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$fieldErrors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting - 5 password reset attempts per minute
    $rateLimiter = new RateLimiter();
    if (!$rateLimiter->check('password_reset', 5)) {
        $errors[] = getFlash('error');
    } elseif (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $fieldErrors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fieldErrors['email'] = 'Please enter a valid email address.';
        }

        if (empty($errors) && empty($fieldErrors)) {
            $db = new Database();
            $user = new User($db->connect());
            $result = $user->createResetToken($email);

            // Always show success to prevent email enumeration
            $success = true;

            // Send password reset email if token was created
            if (!empty($result['token'])) {
                $resetLink = SITE_URL . "/reset-password.php?token=" . $result['token'];
                $userName = $result['name'] ?? 'Customer';

                $emailService = new EmailService();
                $emailService->sendPasswordReset($email, $userName, $resetLink);
            }
        }
    }
}

$pageTitle = 'Forgot Password';
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Forgot Password</h1>
            <p>Enter your email to reset your password</p>
        </div>

        <div class="auth-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p>If an account exists with this email, you will receive a password reset link shortly.</p>
                </div>
                <div class="text-center mt-3">
                    <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline">Back to Login</a>
                </div>
            <?php else: ?>
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
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control<?= isset($fieldErrors['email']) ? ' is-invalid' : '' ?>"
                               value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        <?php if (isset($fieldErrors['email'])): ?>
                            <div class="form-error"><?= sanitize($fieldErrors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">Send Reset Link</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="auth-footer">
            <p>Remember your password? <a href="<?= SITE_URL ?>/login.php">Sign in</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
