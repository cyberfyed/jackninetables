<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/EmailService.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $formData = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ];

        // Validation
        if (empty($formData['first_name'])) {
            $errors[] = 'First name is required.';
        }
        if (empty($formData['last_name'])) {
            $errors[] = 'Last name is required.';
        }
        if (empty($formData['email'])) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (empty($formData['password'])) {
            $errors[] = 'Password is required.';
        } elseif (strlen($formData['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } else {
            if (!preg_match('/[A-Z]/', $formData['password'])) {
                $errors[] = 'Password must contain at least one uppercase letter.';
            }
            if (!preg_match('/[a-z]/', $formData['password'])) {
                $errors[] = 'Password must contain at least one lowercase letter.';
            }
            if (!preg_match('/[0-9]/', $formData['password'])) {
                $errors[] = 'Password must contain at least one number.';
            }
        }
        if ($formData['password'] !== $formData['confirm_password']) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            $db = new Database();
            $user = new User($db->connect());
            $result = $user->register($formData);

            if ($result['success']) {
                // Send verification email
                $emailService = new EmailService();
                $verifyUrl = SITE_URL . '/verify-email.php?token=' . $result['token'];
                $emailService->sendVerificationEmail($formData['email'], $formData['first_name'], $verifyUrl);

                // Auto-login after registration (but not verified yet)
                $user->login($formData['email'], $formData['password']);
                setFlash('info', 'Account created! Please check your email to verify your account and unlock all features.');
                redirect('dashboard.php');
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

$pageTitle = 'Create Account';
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Start designing your custom poker table</p>
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

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label" for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" class="form-control"
                               value="<?= sanitize($formData['first_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control"
                               value="<?= sanitize($formData['last_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control"
                           value="<?= sanitize($formData['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: var(--gray-500);">(optional)</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?= sanitize($formData['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div class="form-hint">Minimum 8 characters with uppercase, lowercase, and number</div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
            </form>
        </div>

        <div class="auth-footer">
            <p>Already have an account? <a href="<?= SITE_URL ?>/login.php">Sign in</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
