<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/EmailService.php';

// Must be logged in but not verified
if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SESSION['email_verified'] ?? false) {
    setFlash('info', 'Your email is already verified.');
    redirect('dashboard.php');
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $db = new Database();
        $user = new User($db->connect());
        $result = $user->resendVerificationEmail($_SESSION['user_id']);

        if ($result['success']) {
            $emailService = new EmailService();
            $verifyUrl = SITE_URL . '/verify-email.php?token=' . $result['token'];
            $emailService->sendVerificationEmail($result['email'], $result['name'], $verifyUrl);
            $success = true;
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Resend Verification';
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Verify Your Email</h1>
            <p>A verified email is required to save designs and request quotes.</p>
        </div>

        <div class="auth-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Verification Email Sent!</strong><br>
                    Please check your inbox at <strong><?= sanitize($_SESSION['user_email']) ?></strong> and click the verification link.
                </div>
                <p style="text-align: center; color: var(--gray-600); margin-top: 1rem;">
                    Didn't receive the email? Check your spam folder or wait a few minutes before requesting another.
                </p>
            <?php else: ?>
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= sanitize($error) ?>
                    </div>
                <?php endif; ?>

                <p style="margin-bottom: 1.5rem;">
                    We'll send a verification link to: <strong><?= sanitize($_SESSION['user_email']) ?></strong>
                </p>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Send Verification Email</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="auth-footer">
            <p><a href="<?= SITE_URL ?>/dashboard.php">Back to Dashboard</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
