<?php
$pageTitle = 'Settings';
require_once 'includes/admin-header.php';
require_once __DIR__ . '/../classes/Settings.php';

$settings = new Settings($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid request.');
    } else {
        $updates = [];

        // Process each setting from POST
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token' && strpos($key, 'setting_') === 0) {
                $settingKey = substr($key, 8); // Remove 'setting_' prefix
                $updates[$settingKey] = trim($value);
            }
        }

        if (!empty($updates)) {
            if ($settings->updateBulk($updates)) {
                setFlash('success', 'Settings updated successfully.');
            } else {
                setFlash('error', 'Some settings could not be updated.');
            }
        }

        // Redirect to prevent form resubmission
        header('Location: ' . SITE_URL . '/admin/settings.php');
        exit;
    }
}

// Get all settings grouped
$groupedSettings = $settings->getAllGrouped();

// Define friendly group names
$groupLabels = [
    'general' => 'General Settings',
    'contact' => 'Contact Information',
    'email' => 'Email Settings',
    'business' => 'Business Details'
];

// Define field labels and descriptions
$fieldInfo = [
    'site_name' => ['label' => 'Site Name', 'description' => 'The name of your website'],
    'admin_email' => ['label' => 'Admin Email', 'description' => 'Primary email for admin notifications'],
    'business_phone' => ['label' => 'Phone Number', 'description' => 'Business phone for customer contact'],
    'business_address' => ['label' => 'Business Address', 'description' => 'Physical address for shipping and contact'],
    'contact_email' => ['label' => 'Contact Email', 'description' => 'Email shown on contact page'],
    'support_email' => ['label' => 'Support Email', 'description' => 'Email for customer support inquiries']
];
?>

<div class="settings-container">
    <form method="POST" class="settings-form">
        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

        <?php if (empty($groupedSettings)): ?>
            <div class="admin-table-container">
                <div class="empty-state">
                    <div class="empty-state-icon">&#9881;</div>
                    <div class="empty-state-title">No settings configured</div>
                    <p>Settings will appear here once they're added to the database.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($groupedSettings as $group => $items): ?>
                <div class="settings-group">
                    <div class="settings-group-header">
                        <h3 class="settings-group-title"><?= sanitize($groupLabels[$group] ?? ucfirst($group)) ?></h3>
                    </div>
                    <div class="settings-group-body">
                        <?php foreach ($items as $setting): ?>
                            <?php
                            $key = $setting['setting_key'];
                            $value = $setting['setting_value'];
                            $type = $setting['setting_type'];
                            $info = $fieldInfo[$key] ?? ['label' => ucwords(str_replace('_', ' ', $key)), 'description' => ''];
                            ?>
                            <div class="setting-row">
                                <div class="setting-label">
                                    <label for="setting_<?= sanitize($key) ?>"><?= sanitize($info['label']) ?></label>
                                    <?php if (!empty($info['description'])): ?>
                                        <span class="setting-description"><?= sanitize($info['description']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="setting-input">
                                    <?php if ($type === 'boolean'): ?>
                                        <label class="toggle">
                                            <input type="hidden" name="setting_<?= sanitize($key) ?>" value="0">
                                            <input type="checkbox" name="setting_<?= sanitize($key) ?>" value="1"
                                                   <?= $value ? 'checked' : '' ?>>
                                            <span class="toggle-slider"></span>
                                        </label>
                                    <?php elseif ($type === 'number'): ?>
                                        <input type="number" id="setting_<?= sanitize($key) ?>"
                                               name="setting_<?= sanitize($key) ?>"
                                               value="<?= sanitize($value) ?>"
                                               class="form-control">
                                    <?php elseif ($type === 'email'): ?>
                                        <input type="email" id="setting_<?= sanitize($key) ?>"
                                               name="setting_<?= sanitize($key) ?>"
                                               value="<?= sanitize($value) ?>"
                                               class="form-control">
                                    <?php elseif (strlen($value) > 100): ?>
                                        <textarea id="setting_<?= sanitize($key) ?>"
                                                  name="setting_<?= sanitize($key) ?>"
                                                  class="form-control"
                                                  rows="3"><?= sanitize($value) ?></textarea>
                                    <?php else: ?>
                                        <input type="text" id="setting_<?= sanitize($key) ?>"
                                               name="setting_<?= sanitize($key) ?>"
                                               value="<?= sanitize($value) ?>"
                                               class="form-control">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="settings-actions">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<style>
.settings-container {
    max-width: 800px;
}

.settings-group {
    background: var(--gray-800);
    border-radius: 8px;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.settings-group-header {
    padding: 1rem 1.5rem;
    background: var(--gray-750);
    border-bottom: 1px solid var(--gray-700);
}

.settings-group-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-100);
}

.settings-group-body {
    padding: 1rem 1.5rem;
}

.setting-row {
    display: flex;
    align-items: flex-start;
    gap: 2rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--gray-700);
}

.setting-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.setting-row:first-child {
    padding-top: 0;
}

.setting-label {
    flex: 0 0 200px;
}

.setting-label label {
    display: block;
    font-weight: 500;
    color: var(--gray-200);
    margin-bottom: 0.25rem;
}

.setting-description {
    font-size: 0.8125rem;
    color: var(--gray-500);
}

.setting-input {
    flex: 1;
}

.setting-input .form-control {
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: var(--gray-900);
    border: 1px solid var(--gray-700);
    border-radius: 6px;
    color: var(--gray-100);
    font-size: 0.9375rem;
}

.setting-input .form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(218, 165, 32, 0.1);
}

.setting-input textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

/* Toggle switch */
.toggle {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
}

.toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: var(--gray-600);
    transition: 0.3s;
    border-radius: 26px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.toggle input:checked + .toggle-slider {
    background-color: var(--primary);
}

.toggle input:checked + .toggle-slider:before {
    transform: translateX(22px);
}

.settings-actions {
    padding-top: 1rem;
}

.settings-actions .btn {
    padding: 0.75rem 2rem;
}

@media (max-width: 768px) {
    .setting-row {
        flex-direction: column;
        gap: 0.5rem;
    }

    .setting-label {
        flex: none;
    }
}
</style>

<?php require_once 'includes/admin-footer.php'; ?>
