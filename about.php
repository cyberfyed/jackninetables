<?php
require_once 'config/config.php';

$pageTitle = 'About Us';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 3rem 0;">
    <div class="container hero-content">
        <h1>About Jack Nine Tables</h1>
        <p>Crafting premium poker tables with passion and precision</p>
    </div>
</section>

<section class="section">
    <div class="container container-narrow">
        <h2>Our Story</h2>
        <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 1.5rem;">
            Jack Nine Tables was born from a love of woodworking and a passion for poker. What started as building a table for my own game room quickly turned into something more when friends and fellow players started asking for their own custom tables.
        </p>
        <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 1.5rem;">
            Every table we build combines traditional carpentry techniques with modern materials and design. We understand that a poker table isn't just furniture—it's the centerpiece of countless memorable nights with friends and family.
        </p>
        <p style="font-size: 1.1rem; line-height: 1.8; margin-bottom: 1.5rem;">
            The name "Jack Nine" comes from one of our favorite starting hands in Texas Hold'em. It's not the flashiest hand, but in the right situation, it can surprise everyone at the table. That's how we approach our craft—solid, reliable, and capable of exceeding expectations.
        </p>
    </div>
</section>

<section class="section" style="background: var(--gray-200);">
    <div class="container">
        <div class="section-header">
            <h2>Our Commitment</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
            <div class="card">
                <div class="card-body">
                    <h3 style="color: var(--primary);">Quality Materials</h3>
                    <p>We source premium materials, professional-grade speed cloth, and high-quality vinyl. Every material is chosen for durability and appearance.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 style="color: var(--primary);">Attention to Detail</h3>
                    <p>From perfectly aligned seams to smooth, comfortable rails, we obsess over the details that make a table feel premium.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 style="color: var(--primary);">Customer Satisfaction</h3>
                    <p>We work closely with each customer to ensure their vision becomes reality. Your satisfaction is our top priority.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container container-narrow text-center">
        <h2>Let's Build Something Great</h2>
        <p style="margin-bottom: 2rem;">Ready to create your custom poker table? We'd love to hear from you.</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary btn-lg">Design Your Table</a>
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-secondary btn-lg">Get in Touch</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>