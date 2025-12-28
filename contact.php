<?php
require_once 'config/config.php';
require_once 'classes/EmailService.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name)) {
            $errors[] = 'Name is required.';
        }
        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (empty($message)) {
            $errors[] = 'Message is required.';
        }

        if (empty($errors)) {
            // Save to database
            $db = new Database();
            $conn = $db->connect();

            $query = "INSERT INTO contact_messages (user_id, name, email, phone, subject, message)
                      VALUES (:user_id, :name, :email, :phone, :subject, :message)";

            $stmt = $conn->prepare($query);
            $userId = isLoggedIn() ? $_SESSION['user_id'] : null;
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);

            if ($stmt->execute()) {
                $success = true;

                // Send emails
                $emailService = new EmailService();

                // Send notification to admin
                $emailService->sendContactMessage($name, $email, $phone, $subject, $message);

                // Send confirmation to user
                $emailService->sendContactConfirmation($email, $name);
            } else {
                $errors[] = 'Failed to send message. Please try again.';
            }
        }
    }
}

$pageTitle = 'Contact Us';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 3rem 0;">
    <div class="container hero-content">
        <h1>Contact Us</h1>
        <p>Have questions? We'd love to hear from you.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
            <div>
                <h2>Get In Touch</h2>
                <p style="margin-bottom: 2rem;">Whether you have questions about our tables, or just want to chat about poker, we're here to help.</p>

                <div style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.5rem;">Email</h4>
                    <p style="color: var(--gray-600);"><?= SITE_EMAIL ?></p>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h4 style="margin-bottom: 0.5rem;">Location</h4>
                    <p style="color: var(--gray-600);">We build and deliver locally.<br>Contact us for service area details.</p>
                </div>

            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <strong>Message Sent!</strong><br>
                            Thank you for reaching out. We'll get back to you as soon as possible.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <?php foreach ($errors as $error): ?>
                                <p><?= sanitize($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" data-validate>
                        <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                        <div class="form-group">
                            <label class="form-label" for="name">Your Name</label>
                            <input type="text" id="name" name="name" class="form-control"
                                value="<?= sanitize($success ? (isLoggedIn() ? $_SESSION['user_name'] : '') : ($_POST['name'] ?? (isLoggedIn() ? $_SESSION['user_name'] : ''))) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control"
                                value="<?= sanitize($success ? (isLoggedIn() ? $_SESSION['user_email'] : '') : ($_POST['email'] ?? (isLoggedIn() ? $_SESSION['user_email'] : ''))) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number <span style="color: var(--gray-500);">(optional)</span></label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                value="<?= sanitize($success ? '' : ($_POST['phone'] ?? '')) ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="subject">Subject</label>
                            <select id="subject" name="subject" class="form-control">
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Existing Order">Existing Order</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="message">Message</label>
                            <textarea id="message" name="message" class="form-control" rows="5" required><?= sanitize($success ? '' : ($_POST['message'] ?? '')) ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    @media (max-width: 768px) {
        .section>.container>div {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<?php require_once 'includes/footer.php'; ?>