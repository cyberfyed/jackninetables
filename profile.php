<?php
require_once 'config/config.php';
require_once 'classes/User.php';

requireLogin();

$db = new Database();
$userModel = new User($db->connect());
$user = $userModel->getById($_SESSION['user_id']);

$errors = [];
$success = false;
$passwordSuccess = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => trim($_POST['state'] ?? ''),
            'zip' => trim($_POST['zip'] ?? '')
        ];

        if (empty($data['first_name']) || empty($data['last_name'])) {
            $errors[] = 'First and last name are required.';
        }

        if (empty($errors)) {
            $result = $userModel->update($_SESSION['user_id'], $data);
            if ($result['success']) {
                $success = true;
                $user = $userModel->getById($_SESSION['user_id']);
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $errors[] = 'All password fields are required.';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } else {
            if (!preg_match('/[A-Z]/', $newPassword)) {
                $errors[] = 'New password must contain at least one uppercase letter.';
            }
            if (!preg_match('/[a-z]/', $newPassword)) {
                $errors[] = 'New password must contain at least one lowercase letter.';
            }
            if (!preg_match('/[0-9]/', $newPassword)) {
                $errors[] = 'New password must contain at least one number.';
            }
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            $result = $userModel->updatePassword($_SESSION['user_id'], $currentPassword, $newPassword);
            if ($result['success']) {
                $passwordSuccess = true;
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

$pageTitle = 'My Profile';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 2rem 0;">
    <div class="container hero-content">
        <h1>My Profile</h1>
        <p>Manage your account settings</p>
    </div>
</section>

<section class="section" style="padding-top: 2rem;">
    <div class="container container-narrow">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= sanitize($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">Profile updated successfully!</div>
        <?php endif; ?>

        <?php if ($passwordSuccess): ?>
            <div class="alert alert-success">Password changed successfully!</div>
        <?php endif; ?>

        <!-- Profile Information -->
        <div class="card" style="margin-bottom: 2rem;">
            <div class="card-header">
                <h3 style="margin: 0;">Profile Information</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="update_profile" value="1">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control"
                                   value="<?= sanitize($user['first_name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control"
                                   value="<?= sanitize($user['last_name']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled>
                        <div class="form-hint">Email cannot be changed</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               value="<?= sanitize($user['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address">Street Address</label>
                        <input type="text" id="address" name="address" class="form-control"
                               value="<?= sanitize($user['address'] ?? '') ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label" for="city">City</label>
                            <input type="text" id="city" name="city" class="form-control"
                                   value="<?= sanitize($user['city'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="state">State</label>
                            <input type="text" id="state" name="state" class="form-control"
                                   value="<?= sanitize($user['state'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="zip">ZIP Code</label>
                            <input type="text" id="zip" name="zip" class="form-control"
                                   value="<?= sanitize($user['zip'] ?? '') ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <h3 style="margin: 0;">Change Password</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">
                    <input type="hidden" name="change_password" value="1">

                    <div class="form-group">
                        <label class="form-label" for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required>
                        <div class="form-hint">Minimum 8 characters with uppercase, lowercase, and number</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>

        <!-- Account Info -->
        <div style="margin-top: 2rem; text-align: center; color: var(--gray-500);">
            <p>Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
        </div>
    </div>
</section>

<style>
@media (max-width: 600px) {
    .card-body form > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
