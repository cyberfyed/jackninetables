<?php
require_once 'config/config.php';

$pageTitle = 'Custom Handcrafted Poker Tables';
require_once 'includes/header.php';
?>

<section class="hero">
    <div class="container hero-content">
        <img src="<?= SITE_URL ?>/assets/images/jacknineb.jpg" alt="Jack Nine Tables" class="hero-logo">
        <div class="hero-info">
            <h1>Custom Poker Tables<br>Built for Your Game</h1>
            <p>Handcrafted with premium materials and precision craftsmanship. Design your perfect poker table with our interactive builder.</p>
            <div class="hero-cta">
                <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary btn-lg">Build Your Table</a>
                <a href="<?= SITE_URL ?>/gallery.php" class="btn btn-outline btn-lg">View Gallery</a>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Why Choose Jack Nine Tables?</h2>
            <p>We combine traditional carpentry skills with modern design to create the perfect centerpiece for your game room.</p>
        </div>

        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 2rem;">
            <div class="card" style="flex: 0 1 320px;">
                <div class="card-body text-center">
                    <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">&#9824;</div>
                    <h3>Custom Design</h3>
                    <p>Use our interactive builder to design your table exactly how you want it. Choose colors, materials, and features.</p>
                </div>
            </div>

            <div class="card" style="flex: 0 1 320px;">
                <div class="card-body text-center">
                    <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">&#9829;</div>
                    <h3>Premium Materials</h3>
                    <p>We use only the finest materials, professional-grade speed cloth, and premium vinyl for lasting quality.</p>
                </div>
            </div>

            <div class="card" style="flex: 0 1 320px;">
                <div class="card-body text-center">
                    <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">&#9830;</div>
                    <h3>Expert Craftsmanship</h3>
                    <p>Each table is handcrafted with attention to detail, ensuring a beautiful and durable product.</p>
                </div>
            </div>

            <div class="card" style="flex: 0 1 320px;">
                <div class="card-body text-center">
                    <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">&#9827;</div>
                    <h3>Built to Last</h3>
                    <p>Our tables are built to withstand years of use. Solid construction you can count on for game nights to come.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-dark">
    <div class="container">
        <div class="section-header">
            <h2>How It Works</h2>
            <p>Getting your custom poker table is easy with our simple 3-step process.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; text-align: center;">
            <div>
                <div style="width: 80px; height: 80px; background: var(--gold); color: var(--dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 1rem;">1</div>
                <h3>Design Your Table</h3>
                <p style="color: var(--gray-400);">Use our interactive builder to customize your table. Pick your size, rail color, playing surface, and more.</p>
            </div>

            <div>
                <div style="width: 80px; height: 80px; background: var(--gold); color: var(--dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 1rem;">2</div>
                <h3>Get a Quote</h3>
                <p style="color: var(--gray-400);">Submit your design and we'll provide a detailed quote. We'll work with you to finalize every detail.</p>
            </div>

            <div>
                <div style="width: 80px; height: 80px; background: var(--gold); color: var(--dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: bold; margin: 0 auto 1rem;">3</div>
                <h3>We Build & Deliver</h3>
                <p style="color: var(--gray-400);">Once approved, we handcraft your table and deliver it ready for your first game night.</p>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary btn-lg">Start Designing</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Table Options</h2>
            <p>We offer a variety of customization options to make your table uniquely yours.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div class="card">
                <div class="card-header" style="background: var(--primary); color: white;">
                    <h3>Oval Tables</h3>
                </div>
                <div class="card-body">
                    <ul style="list-style: none; line-height: 2;">
                        <li>&#10003; Classic casino-style design</li>
                        <li>&#10003; Seats 8-10 players comfortably</li>
                        <li>&#10003; Optional racetrack rail</li>
                        <li>&#10003; Built-in cup holders</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background: var(--secondary); color: white;">
                    <h3>Playing Surfaces</h3>
                </div>
                <div class="card-body">
                    <ul style="list-style: none; line-height: 2;">
                        <li>&#10003; Suited Speed Cloth (pro-grade)</li>
                        <li>&#10003; Velveteen (classic feel)</li>
                        <li>&#10003; Multiple color options</li>
                        <li>&#10003; Water-resistant</li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="background: var(--gold); color: var(--dark);">
                    <h3>Rail Options</h3>
                </div>
                <div class="card-body">
                    <ul style="list-style: none; line-height: 2;">
                        <li>&#10003; Premium padded vinyl</li>
                        <li>&#10003; Multiple color choices</li>
                        <li>&#10003; Racetrack or standard</li>
                        <li>&#10003; Matching or contrasting</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background: var(--gray-200);">
    <div class="container text-center">
        <h2>Ready to Build Your Dream Table?</h2>
        <p style="max-width: 600px; margin: 1rem auto 2rem;">Join poker enthusiasts who trust Jack Nine for their custom poker tables.</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary btn-lg">Start Building Now</a>
            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-secondary btn-lg">Contact Us</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>