<?php
require_once 'config/config.php';

$pageTitle = 'Shipping Info';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 3rem 0;">
    <div class="container hero-content">
        <h1>Shipping Information</h1>
        <p>Delivery options and what to expect</p>
    </div>
</section>

<section class="section">
    <div class="container container-narrow">
        <div class="legal-content">

            <div class="legal-section">
                <h2>Delivery Area</h2>
                <p>We currently offer delivery to the <strong>San Francisco Bay Area</strong> only. This includes:</p>
                <ul>
                    <li>Alameda County</li>
                    <li>Contra Costa County</li>
                    <li>San Francisco County</li>
                    <li>San Mateo County</li>
                    <li>Santa Clara County</li>
                </ul>
                <p>If you're located outside these areas but still interested in a table, please <a href="<?= SITE_URL ?>/contact.php">contact us</a> to discuss options.</p>
            </div>

            <div class="legal-section">
                <h2>Local Pickup</h2>
                <p>Local pickup is available from our workshop in <strong>Newark, CA</strong> at no additional charge.</p>
                <p>When your table is ready, we'll contact you to schedule a pickup time that works for you. Please bring a vehicle large enough to transport your table safely - a truck, SUV, or van is recommended.</p>
                <p><strong>Table dimensions:</strong> Our tables are approximately 96" long x 48" wide. Please ensure your vehicle can accommodate these dimensions.</p>
            </div>

            <div class="legal-section">
                <h2>Delivery Service</h2>
                <p>For customers in the Bay Area, we offer delivery service for an additional fee based on your location.</p>
                <p>Our delivery service includes:</p>
                <ul>
                    <li>Careful transport of your table</li>
                    <li>Delivery to your door or garage</li>
                    <li>Basic setup assistance if needed</li>
                </ul>
                <p>Delivery fees will be calculated and included in your quote based on your address.</p>
            </div>

            <div class="legal-section">
                <h2>Build Time</h2>
                <p>Each table is custom-built to order. Typical build time is <strong>4-6 weeks</strong> depending on current order volume and complexity.</p>
                <p>We'll provide a more specific timeline when you receive your quote, and keep you updated on progress throughout the build.</p>
            </div>

            <div class="legal-section">
                <h2>Packaging & Handling</h2>
                <p>Your table will be carefully wrapped and protected for transport, whether you're picking up or having it delivered.</p>
                <p><strong>Important:</strong> Poker tables are heavy (approximately 150-200 lbs). We recommend having at least two people available to move and set up your table.</p>
            </div>

            <div class="legal-section">
                <h2>Questions?</h2>
                <p>If you have questions about shipping, delivery, or pickup options, please don't hesitate to reach out.</p>
                <div class="legal-contact">
                    <p><strong>Email:</strong> <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
                    <p><strong>Contact Form:</strong> <a href="<?= SITE_URL ?>/contact.php">Contact Us</a></p>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>