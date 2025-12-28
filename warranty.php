<?php
require_once 'config/config.php';

$pageTitle = 'Warranty';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 3rem 0;">
    <div class="container hero-content">
        <h1>Warranty</h1>
        <p>Our commitment to quality craftsmanship</p>
    </div>
</section>

<section class="section">
    <div class="container container-narrow">
        <div class="legal-content">

            <div class="legal-section">
                <h2>Our Warranty Promise</h2>
                <p>Every Jack Nine Tables poker table is built with care and attention to detail. We stand behind our craftsmanship and want you to enjoy your table for years to come.</p>
                <p>All tables come with a <strong>1-year limited warranty</strong> covering defects in materials and workmanship from the date of delivery or pickup.</p>
            </div>

            <div class="legal-section">
                <h2>What's Covered</h2>
                <p>Our warranty covers:</p>
                <ul>
                    <li>Structural defects in the table frame or base</li>
                    <li>Defects in rail padding or armrest construction</li>
                    <li>Playing surface installation defects</li>
                    <li>Vinyl or fabric defects present at time of delivery</li>
                    <li>Loose or failing seams not caused by misuse</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>What's Not Covered</h2>
                <p>The warranty does not cover:</p>
                <ul>
                    <li>Normal wear and tear</li>
                    <li>Damage caused by accidents, misuse, or abuse</li>
                    <li>Damage from spills, stains, or improper cleaning</li>
                    <li>Fading or discoloration from sunlight or UV exposure</li>
                    <li>Damage caused during customer transport or moving</li>
                    <li>Modifications or repairs made by anyone other than Jack Nine Tables</li>
                    <li>Cosmetic imperfections that don't affect functionality</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>How to Make a Warranty Claim</h2>
                <p>If you believe your table has a defect covered under warranty:</p>
                <ol>
                    <li><strong>Contact us</strong> via email or our contact form with a description of the issue</li>
                    <li><strong>Provide photos</strong> clearly showing the defect</li>
                    <li><strong>Include your order information</strong> (name, order date, etc.)</li>
                </ol>
                <p>We'll review your claim and respond within 3-5 business days. If the defect is covered, we'll work with you to repair or replace the affected components.</p>
            </div>

            <div class="legal-section">
                <h2>Repairs & Replacements</h2>
                <p>For covered warranty claims:</p>
                <ul>
                    <li>We will repair or replace defective components at no charge</li>
                    <li>Repairs may be performed at our workshop in Newark, CA</li>
                    <li>For minor issues, we may provide materials and instructions for self-repair</li>
                    <li>Replacement parts will be matched as closely as possible to the original</li>
                </ul>
                <p><strong>Note:</strong> Transportation of the table to our workshop for warranty repairs is the customer's responsibility unless otherwise arranged.</p>
            </div>

            <div class="legal-section">
                <h2>Beyond the Warranty</h2>
                <p>Even after your warranty period ends, we're here to help. We offer repair services for:</p>
                <ul>
                    <li>Playing surface replacement</li>
                    <li>Rail vinyl replacement</li>
                    <li>Padding repairs</li>
                    <li>General restoration</li>
                </ul>
                <p><a href="<?= SITE_URL ?>/contact.php">Contact us</a> for a repair quote.</p>
            </div>

            <div class="legal-section">
                <h2>Contact Us</h2>
                <p>Questions about our warranty? We're happy to help.</p>
                <div class="legal-contact">
                    <p><strong>Email:</strong> <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
                    <p><strong>Contact Form:</strong> <a href="<?= SITE_URL ?>/contact.php">Contact Us</a></p>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
