            </div><!-- /.admin-content -->
        </main><!-- /.admin-main -->
    </div><!-- /.admin-layout -->

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <script>
        // Auto-dismiss flash messages
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });

        // Confirm dialogs for dangerous actions
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', (e) => {
                if (!confirm(el.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });
    </script>
    <?php if (isset($extraJS)): ?>
        <?= $extraJS ?>
    <?php endif; ?>
</body>
</html>
