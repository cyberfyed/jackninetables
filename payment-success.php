<?php
require_once 'config/config.php';
require_once 'classes/Order.php';

requireLogin();

$db = new Database();
$orderModel = new Order($db->connect());

$orderId = intval($_GET['order_id'] ?? 0);
$order = $orderModel->getById($orderId, $_SESSION['user_id']);

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect('my-orders.php');
}

$pageTitle = 'Payment Successful';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 2rem 0;">
    <div class="container hero-content">
        <h1>Payment Successful!</h1>
        <p>Thank you for your deposit</p>
    </div>
</section>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div class="card">
                <div class="card-body" style="padding: 3rem;">
                    <div style="font-size: 5rem; color: var(--success); margin-bottom: 1rem;">&#10003;</div>
                    <h2 style="color: var(--success); margin-bottom: 1rem;">Deposit Received!</h2>
                    <p style="font-size: 1.1rem; color: var(--gray-600); margin-bottom: 2rem;">
                        Your deposit of <strong>$<?= number_format($order['deposit_amount'], 2) ?></strong> for order
                        <strong><?= sanitize($order['order_number']) ?></strong> has been processed successfully.
                    </p>

                    <div style="background: var(--gray-100); border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; text-align: left;">
                        <h4 style="margin-top: 0; margin-bottom: 1rem;">What Happens Next?</h4>
                        <ul style="margin: 0; padding-left: 1.25rem; color: var(--gray-700);">
                            <li style="margin-bottom: 0.5rem;">We'll begin sourcing premium materials for your table</li>
                            <li style="margin-bottom: 0.5rem;">Production typically takes 4-6 weeks</li>
                            <li style="margin-bottom: 0.5rem;">You'll receive email updates on your order progress</li>
                            <li>The remaining balance will be due before shipping</li>
                        </ul>
                    </div>

                    <p style="color: var(--gray-600); margin-bottom: 2rem;">
                        A confirmation email has been sent to your email address.
                    </p>

                    <a href="<?= SITE_URL ?>/my-orders.php" class="btn btn-primary btn-lg">View My Orders</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
