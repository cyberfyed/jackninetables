<?php
require_once 'config/config.php';

$pageTitle = 'Gallery';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 2rem 0;">
    <div class="container hero-content">
        <h1>Our Work</h1>
        <p>Browse our collection of custom poker tables</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Featured Tables</h2>
            <p>Each table is handcrafted with attention to detail and premium materials.</p>
        </div>

        <!-- Placeholder gallery - in production, these would be actual photos -->
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 2rem;">
            <div class="card" style="flex: 0 1 350px; max-width: 400px;">
                <div style="background: linear-gradient(135deg, #1a472a 0%, #2d5a3d 100%); height: 250px; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem;">
                    &#9824;
                </div>
                <div class="card-body">
                    <h3>Classic Casino Green</h3>
                    <p style="color: var(--gray-600);">96" stadium with racetrack, suited speed cloth, black vinyl rail with 10 cup holders.</p>
                </div>
            </div>

            <div class="card" style="flex: 0 1 350px; max-width: 400px;">
                <div style="background: linear-gradient(135deg, #1a3a5c 0%, #2a4a6d 100%); height: 250px; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem;">
                    &#9829;
                </div>
                <div class="card-body">
                    <h3>Midnight Blue</h3>
                    <p style="color: var(--gray-600);">96" stadium with racetrack, navy speed cloth, matching navy rail with 8 cup holders.</p>
                </div>
            </div>

            <div class="card" style="flex: 0 1 350px; max-width: 400px;">
                <div style="background: linear-gradient(135deg, #6b1c1c 0%, #8b2c2c 100%); height: 250px; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem;">
                    &#9830;
                </div>
                <div class="card-body">
                    <h3>Ruby Red</h3>
                    <p style="color: var(--gray-600);">96" stadium, standard rail, red velveteen surface, burgundy vinyl rail, perfect for home games.</p>
                </div>
            </div>

            <div class="card" style="flex: 0 1 350px; max-width: 400px;">
                <div style="background: linear-gradient(135deg, #3d1a4d 0%, #5c2a6d 100%); height: 250px; display: flex; align-items: center; justify-content: center; color: white; font-size: 4rem;">
                    &#9827;
                </div>
                <div class="card-body">
                    <h3>Royal Purple</h3>
                    <p style="color: var(--gray-600);">96" stadium with racetrack, purple speed cloth, black rail with gold accents and 10 cup holders.</p>
                </div>
            </div>

            <div class="card" style="flex: 0 1 350px; max-width: 400px;">
                <div style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); height: 250px; display: flex; align-items: center; justify-content: center; color: var(--gold); font-size: 4rem;">
                    &#9824;
                </div>
                <div class="card-body">
                    <h3>Blackout Edition</h3>
                    <p style="color: var(--gray-600);">96" stadium with racetrack, black suited speed cloth, black rail for a sleek, modern look.</p>
                </div>
            </div>

        </div>

        <div class="text-center mt-4">
            <p style="color: var(--gray-600); margin-bottom: 1.5rem;">Ready to create your own custom table?</p>
            <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary btn-lg">Start Building</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>