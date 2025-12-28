<?php
require_once 'config/config.php';
require_once 'classes/Order.php';
require_once 'classes/PayPal.php';

requireLogin();

$db = new Database();
$orderModel = new Order($db->connect());

$orderId = intval($_GET['id'] ?? 0);
$order = $orderModel->getById($orderId, $_SESSION['user_id']);

if (!$order) {
    setFlash('error', 'Order not found.');
    redirect('my-orders.php');
}

if (!$orderModel->needsDeposit($order)) {
    setFlash('error', 'This order does not require a deposit payment.');
    redirect('my-orders.php');
}

$orderPrice = $orderModel->getOrderPrice($order);
$depositAmount = PayPal::calculateDeposit($orderPrice);
$design = $order['design_data'];

$pageTitle = 'Pay Deposit - ' . $order['order_number'];
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 2rem 0;">
    <div class="container hero-content">
        <h1>Pay Deposit</h1>
        <p>Complete your deposit to begin production</p>
    </div>
</section>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto;">
            <!-- Order Summary -->
            <div class="card" style="margin-bottom: 2rem;">
                <div class="card-header">
                    <h3>Order Summary</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                        <span style="color: var(--gray-600);">Order Number</span>
                        <strong><?= sanitize($order['order_number']) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                        <span style="color: var(--gray-600);">Table Style</span>
                        <span><?= $design['tableStyle'] === 'racetrack' ? 'With Racetrack' : 'Standard Rail' ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                        <span style="color: var(--gray-600);">Colors</span>
                        <span>
                            <span style="display: inline-block; width: 20px; height: 20px; background: <?= sanitize($design['railColor'] ?? '#000') ?>; border-radius: 4px; vertical-align: middle; border: 1px solid var(--gray-300);"></span>
                            <span style="display: inline-block; width: 20px; height: 20px; background: <?= sanitize($design['surfaceColor'] ?? '#000') ?>; border-radius: 4px; vertical-align: middle; border: 1px solid var(--gray-300); margin-left: 4px;"></span>
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">
                        <span style="color: var(--gray-600);">Total Price</span>
                        <strong>$<?= number_format($orderPrice, 2) ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.25rem;">
                        <span><strong>Deposit Due (<?= DEPOSIT_PERCENTAGE ?>%)</strong></span>
                        <strong style="color: var(--primary);">$<?= number_format($depositAmount, 2) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Deposit Terms -->
            <div class="card" style="margin-bottom: 2rem; background: #fffbeb; border-color: #f59e0b;">
                <div class="card-body">
                    <h4 style="color: #b45309; margin-bottom: 0.5rem;">Important: Non-Refundable Deposit</h4>
                    <p style="color: #92400e; margin: 0; font-size: 0.9rem;">
                        This deposit is <strong>non-refundable</strong> once production begins. Custom tables are made to your specifications and materials are purchased upon deposit.
                    </p>
                </div>
            </div>

            <!-- PayPal Button -->
            <div class="card">
                <div class="card-body">
                    <div id="paypal-button-container"></div>
                    <div id="payment-status" style="display: none; text-align: center; padding: 2rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">&#9203;</div>
                        <p>Processing your payment...</p>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="<?= SITE_URL ?>/my-orders.php" style="color: var(--gray-600);">&larr; Back to My Orders</a>
            </div>
        </div>
    </div>
</section>

<!-- PayPal SDK -->
<script src="https://www.paypal.com/sdk/js?client-id=<?= PAYPAL_CLIENT_ID ?>&currency=USD"></script>
<script>
    paypal.Buttons({
        style: {
            layout: 'vertical',
            color: 'gold',
            shape: 'rect',
            label: 'pay'
        },

        createOrder: function(data, actions) {
            return fetch('<?= SITE_URL ?>/api/paypal/create-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: <?= $orderId ?>,
                    csrf_token: '<?= getCSRFToken() ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    return data.paypal_order_id;
                } else {
                    throw new Error(data.error || 'Failed to create order');
                }
            });
        },

        onApprove: function(data, actions) {
            document.getElementById('paypal-button-container').style.display = 'none';
            document.getElementById('payment-status').style.display = 'block';

            return fetch('<?= SITE_URL ?>/api/paypal/capture-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    paypal_order_id: data.orderID,
                    order_id: <?= $orderId ?>,
                    csrf_token: '<?= getCSRFToken() ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?= SITE_URL ?>/payment-success.php?order_id=<?= $orderId ?>';
                } else {
                    alert('Payment failed: ' + (data.error || 'Unknown error'));
                    window.location.reload();
                }
            });
        },

        onError: function(err) {
            console.error('PayPal error:', err);
            alert('An error occurred with PayPal. Please try again.');
        },

        onCancel: function(data) {
            // User cancelled - do nothing, they stay on the page
        }
    }).render('#paypal-button-container');
</script>

<?php require_once 'includes/footer.php'; ?>
