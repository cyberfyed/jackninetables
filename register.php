<?php
require_once 'config/config.php';
require_once 'classes/User.php';
require_once 'classes/EmailService.php';
require_once 'classes/RateLimiter.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$fieldErrors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limiting - 5 registration attempts per minute
    $rateLimiter = new RateLimiter();
    if (!$rateLimiter->check('register', 5)) {
        $errors[] = getFlash('error');
    }
    // Verify CSRF
    elseif (!verifyCSRF($_POST['csrf_token'] ?? '')) {
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
            $fieldErrors['first_name'] = 'First name is required.';
        }
        if (empty($formData['last_name'])) {
            $fieldErrors['last_name'] = 'Last name is required.';
        }
        if (empty($formData['email'])) {
            $fieldErrors['email'] = 'Email is required.';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $fieldErrors['email'] = 'Please enter a valid email address.';
        }
        if (empty($formData['password'])) {
            $fieldErrors['password'] = 'Password is required.';
        } elseif (strlen($formData['password']) < 8) {
            $fieldErrors['password'] = 'Password must be at least 8 characters.';
        } else {
            $passwordErrors = [];
            if (!preg_match('/[A-Z]/', $formData['password'])) {
                $passwordErrors[] = 'one uppercase letter';
            }
            if (!preg_match('/[a-z]/', $formData['password'])) {
                $passwordErrors[] = 'one lowercase letter';
            }
            if (!preg_match('/[0-9]/', $formData['password'])) {
                $passwordErrors[] = 'one number';
            }
            if (!empty($passwordErrors)) {
                $fieldErrors['password'] = 'Password must contain at least ' . implode(', ', $passwordErrors) . '.';
            }
        }
        if ($formData['password'] !== $formData['confirm_password']) {
            $fieldErrors['confirm_password'] = 'Passwords do not match.';
        }

        if (empty($errors) && empty($fieldErrors)) {
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
                        <input type="text" id="first_name" name="first_name" class="form-control<?= isset($fieldErrors['first_name']) ? ' is-invalid' : '' ?>"
                               value="<?= sanitize($formData['first_name'] ?? '') ?>" required>
                        <?php if (isset($fieldErrors['first_name'])): ?>
                            <div class="form-error"><?= sanitize($fieldErrors['first_name']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="form-control<?= isset($fieldErrors['last_name']) ? ' is-invalid' : '' ?>"
                               value="<?= sanitize($formData['last_name'] ?? '') ?>" required>
                        <?php if (isset($fieldErrors['last_name'])): ?>
                            <div class="form-error"><?= sanitize($fieldErrors['last_name']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control<?= isset($fieldErrors['email']) ? ' is-invalid' : '' ?>"
                           value="<?= sanitize($formData['email'] ?? '') ?>" required>
                    <?php if (isset($fieldErrors['email'])): ?>
                        <div class="form-error"><?= sanitize($fieldErrors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: var(--gray-500);">(optional)</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="<?= sanitize($formData['phone'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control<?= isset($fieldErrors['password']) ? ' is-invalid' : '' ?>" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <svg class="eye-open" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="eye-closed" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <?php if (isset($fieldErrors['password'])): ?>
                        <div class="form-error"><?= sanitize($fieldErrors['password']) ?></div>
                    <?php else: ?>
                        <div class="form-hint">Minimum 8 characters with uppercase, lowercase, and number</div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control<?= isset($fieldErrors['confirm_password']) ? ' is-invalid' : '' ?>" required>
                        <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                            <svg class="eye-open" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg class="eye-closed" width="20" height="20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    <?php if (isset($fieldErrors['confirm_password'])): ?>
                        <div class="form-error"><?= sanitize($fieldErrors['confirm_password']) ?></div>
                    <?php endif; ?>
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
