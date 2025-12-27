    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="<?= SITE_URL ?>" class="logo">
                        <span class="logo-icon">&#9827;</span>
                        Jack Nine Tables
                    </a>
                    <p>Handcrafted custom poker tables built with precision and passion. From casual game nights to professional tournaments, we build the perfect table for your needs.</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="<?= SITE_URL ?>/builder.php">Build Your Table</a></li>
                        <li><a href="<?= SITE_URL ?>/gallery.php">Gallery</a></li>
                        <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="<?= SITE_URL ?>/faq.php">FAQ</a></li>
                        <li><a href="<?= SITE_URL ?>/shipping.php">Shipping Info</a></li>
                        <li><a href="<?= SITE_URL ?>/care.php">Table Care</a></li>
                        <li><a href="<?= SITE_URL ?>/warranty.php">Warranty</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Get In Touch</h4>
                    <p><strong>Email:</strong> <?= SITE_EMAIL ?></p>
                    <p><strong>Phone:</strong> (555) 123-4567</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook">&#128101;</a>
                        <a href="#" aria-label="Instagram">&#128247;</a>
                        <a href="#" aria-label="YouTube">&#9654;</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <script src="<?= SITE_URL ?>/assets/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
