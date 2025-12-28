<?php
require_once 'config/config.php';

$pageTitle = 'Frequently Asked Questions';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 3rem 0;">
    <div class="container hero-content">
        <h1>Frequently Asked Questions</h1>
        <p>Everything you need to know about our custom poker tables</p>
    </div>
</section>

<section class="section">
    <div class="container container-narrow">

        <div class="faq-category">
            <h2>Ordering & Customization</h2>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>How do I order a custom poker table?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Start by using our <a href="<?= SITE_URL ?>/builder.php">Table Builder</a> to design your ideal table. Choose your preferred size, playing surface material and color, and rail vinyl. Once you're happy with your design, you can save it and request a quote. We'll review your design and get back to you with pricing and timeline.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Can I customize colors and materials not shown in the builder?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Absolutely! Our builder shows the most popular options, but we can work with a wide range of materials and colors. When you request a quote, let us know what you're looking for in the notes field, and we'll discuss options with you.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Can I add custom logos or graphics to my table?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>We are not able to offer this option at this time. We are looking into options to be able to offer this customization in the near future.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>What payment methods do you accept?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>We accept major credit cards, PayPal, and bank transfers. For custom tables, we require a 50% deposit to begin work, with the remaining balance due before shipping.</p>
                </div>
            </div>
        </div>

        <div class="faq-category">
            <h2>Table Specifications</h2>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>What's the difference between Velveteen and Suited Speed Cloth?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p><strong>Velveteen</strong> is a soft, plush fabric with a velvet-like texture. It offers a classic, traditional look and feel that many players prefer for home games.</p>
                    <p><strong>Suited Speed Cloth</strong> is a professional-grade polyester fabric that allows cards to glide smoothly across the surface. It features a subtle, woven-in pattern of card suits (hearts, diamonds, clubs, spades) and offers that authentic casino feel. It's durable, spill-resistant, and easy to clean.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>What type of foam padding do you use?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>We use high-density foam padding under the playing surface for comfortable card handling and chip placement. The rails feature soft but supportive padding that's comfortable for extended play sessions while maintaining its shape over time.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>How much does a poker table weigh?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Weight varies by size and style, but most of our tables range from 150-200 lbs. Keep in mind that poker tables are designed to be sturdy and stable during play. We recommend having two people available for moving and setup.</p>
                </div>
            </div>
        </div>

        <div class="faq-category">
            <h2>Shipping & Delivery</h2>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>How long does it take to build my table?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Custom tables typically take 4-6 weeks to build, depending on complexity and current order volume. We'll provide a specific timeline when you receive your quote.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Do you ship outside the local area?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>We currently only ship to the San Francisco Bay Area and local pickup from Newark, CA. Visit our <a href="<?= SITE_URL ?>/shipping.php">Shipping Info</a> page for more details.</p>
                </div>
            </div>
        </div>

        <div class="faq-category">
            <h2>Care & Warranty</h2>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>How do I clean and maintain my poker table?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>For regular cleaning:</p>
                    <ul>
                        <li>Use a soft brush or lint roller to remove dust and debris from the playing surface</li>
                        <li>Wipe vinyl rails with a damp cloth and mild soap as needed</li>
                        <li>Avoid harsh chemicals or abrasive cleaners</li>
                        <li>For spills, blot immediately with a clean, dry cloth</li>
                    </ul>
                    <p>We recommend using a table cover when not in use to protect against dust and UV fading.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>What warranty do you offer?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>All our tables come with a warranty covering defects in materials and workmanship. We stand behind our craftsmanship and want you to enjoy your table for years to come. Visit our <a href="<?= SITE_URL ?>/warranty.php">Warranty</a> page for complete details.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question" aria-expanded="false">
                    <span>Can the felt or vinyl be replaced if damaged?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Yes! While our materials are durable, accidents happen. We can replace the playing surface or rail vinyl if needed. Contact us and we'll discuss options for restoring your table to like-new condition.</p>
                </div>
            </div>
        </div>

    </div>
</section>

<section class="section" style="background: var(--gray-200);">
    <div class="container container-narrow text-center">
        <h2>Still Have Questions?</h2>
        <p style="margin-bottom: 2rem;">We're here to help! Reach out and we'll get back to you as soon as possible.</p>
        <a href="<?= SITE_URL ?>/contact.php" class="btn btn-primary btn-lg">Contact Us</a>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const faqQuestions = document.querySelectorAll('.faq-question');

        faqQuestions.forEach(question => {
            question.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                const answer = this.nextElementSibling;

                // Close all other open answers
                faqQuestions.forEach(q => {
                    if (q !== this) {
                        q.setAttribute('aria-expanded', 'false');
                        q.nextElementSibling.style.maxHeight = null;
                    }
                });

                // Toggle current answer
                this.setAttribute('aria-expanded', !isExpanded);
                if (!isExpanded) {
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                } else {
                    answer.style.maxHeight = null;
                }
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>