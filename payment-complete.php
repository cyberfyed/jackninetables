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

$orderPrice = $order['final_price'] ?? 0;

$pageTitle = 'Payment Complete';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 2rem 0;">
    <div class="container hero-content">
        <h1>Payment Complete!</h1>
        <p>Your order is fully paid</p>
    </div>
</section>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <div class="card">
                <div class="card-body" style="padding: 3rem;">
                    <div style="font-size: 5rem; color: var(--success); margin-bottom: 1rem;">&#127881;</div>
                    <h2 style="color: var(--success); margin-bottom: 1rem;">Paid in Full!</h2>
                    <p style="font-size: 1.1rem; color: var(--gray-600); margin-bottom: 2rem;">
                        Your order <strong><?= sanitize($order['order_number']) ?></strong> has been fully paid.
                        <br>Total: <strong>$<?= number_format($orderPrice, 2) ?></strong>
                    </p>

                    <div style="background: #f0fdf4; border: 1px solid #22c55e; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; text-align: left;">
                        <h4 style="margin-top: 0; margin-bottom: 1rem; color: #166534;">Delivery Information</h4>
                        <p style="margin: 0; color: #15803d;">
                            Your custom poker table is ready! We'll contact you shortly to coordinate delivery.
                            If you have specific delivery requirements or questions, please reach out to us.
                        </p>
                    </div>

                    <p style="color: var(--gray-600); margin-bottom: 2rem;">
                        A confirmation email has been sent to your email address.
                    </p>

                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="<?= SITE_URL ?>/my-orders.php" class="btn btn-primary btn-lg">View My Orders</a>
                        <a href="<?= SITE_URL ?>/contact.php" class="btn btn-secondary btn-lg">Contact Us</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
