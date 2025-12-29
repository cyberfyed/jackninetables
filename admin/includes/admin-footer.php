            </div><!-- /.admin-content -->
        </main><!-- /.admin-main -->
    </div><!-- /.admin-layout -->

    <!-- Custom Confirmation Modal -->
    <div id="confirmModal" class="confirm-modal">
        <div class="confirm-modal-backdrop"></div>
        <div class="confirm-modal-dialog">
            <button type="button" class="confirm-modal-close" aria-label="Close">&times;</button>
            <div class="confirm-modal-icon">&#9888;</div>
            <h3 class="confirm-modal-title">Confirm Action</h3>
            <p class="confirm-modal-message"></p>
            <div class="confirm-modal-actions">
                <button type="button" class="btn btn-secondary confirm-modal-cancel">Cancel</button>
                <button type="button" class="btn btn-danger confirm-modal-confirm">Confirm</button>
            </div>
        </div>
    </div>

    <style>
    .confirm-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .confirm-modal.active {
        display: flex;
    }

    .confirm-modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(4px);
    }

    .confirm-modal-dialog {
        position: relative;
        background: var(--gray-800);
        border: 1px solid var(--gray-700);
        border-radius: 12px;
        padding: 2rem;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        animation: modalSlideIn 0.2s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.95) translateY(-10px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .confirm-modal-close {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        background: none;
        border: none;
        color: var(--gray-500);
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        line-height: 1;
        transition: color 0.2s;
    }

    .confirm-modal-close:hover {
        color: var(--gray-300);
    }

    .confirm-modal-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #f59e0b;
    }

    .confirm-modal-title {
        margin: 0 0 0.75rem 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--gray-100);
    }

    .confirm-modal-message {
        margin: 0 0 1.5rem 0;
        color: var(--gray-400);
        line-height: 1.5;
    }

    .confirm-modal-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: center;
    }

    .confirm-modal-actions .btn {
        min-width: 100px;
        padding: 0.625rem 1.25rem;
    }
    </style>

    <script src="<?= SITE_URL ?>/assets/js/main.js"></script>
    <script>
        // Auto-dismiss flash messages
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.3s';
                setTimeout(() => alert.remove(), 300);
            }, 15000);
        });

        // Custom confirmation modal
        const confirmModal = {
            modal: document.getElementById('confirmModal'),
            message: document.querySelector('.confirm-modal-message'),
            confirmBtn: document.querySelector('.confirm-modal-confirm'),
            cancelBtn: document.querySelector('.confirm-modal-cancel'),
            closeBtn: document.querySelector('.confirm-modal-close'),
            backdrop: document.querySelector('.confirm-modal-backdrop'),
            pendingElement: null,
            pendingForm: null,

            show(messageText, element) {
                this.message.textContent = messageText;
                this.pendingElement = element;

                // Find the parent form if element is inside one
                this.pendingForm = element.closest('form');

                // Update confirm button style based on action type
                const isDanger = element.classList.contains('btn-danger') ||
                                 messageText.toLowerCase().includes('delete') ||
                                 messageText.toLowerCase().includes('remove');
                this.confirmBtn.className = isDanger ? 'btn btn-danger confirm-modal-confirm' : 'btn btn-primary confirm-modal-confirm';

                this.modal.classList.add('active');
                this.confirmBtn.focus();
            },

            hide() {
                this.modal.classList.remove('active');
                this.pendingElement = null;
                this.pendingForm = null;
            },

            confirm() {
                if (this.pendingForm) {
                    // Submit the form directly
                    this.pendingForm.submit();
                } else if (this.pendingElement) {
                    // For links or other elements
                    this.pendingElement.removeAttribute('data-confirm');
                    this.pendingElement.click();
                }
                this.hide();
            },

            init() {
                // Cancel button
                this.cancelBtn.addEventListener('click', () => this.hide());

                // Close button (X)
                this.closeBtn.addEventListener('click', () => this.hide());

                // Backdrop click does NOT close (per user request - only cancel, x, or successful submission)
                // this.backdrop.addEventListener('click', () => this.hide());

                // Confirm button
                this.confirmBtn.addEventListener('click', () => this.confirm());

                // Escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                        this.hide();
                    }
                });

                // Intercept clicks on elements with data-confirm
                document.querySelectorAll('[data-confirm]').forEach(el => {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        this.show(el.dataset.confirm, el);
                    });
                });
            }
        };

        confirmModal.init();

        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const adminSidebar = document.querySelector('.admin-sidebar');

        // Create overlay element
        const overlay = document.createElement('div');
        overlay.className = 'mobile-overlay';
        document.body.appendChild(overlay);

        if (mobileMenuToggle && adminSidebar) {
            mobileMenuToggle.addEventListener('click', () => {
                mobileMenuToggle.classList.toggle('active');
                adminSidebar.classList.toggle('open');
                overlay.classList.toggle('active');
            });

            // Close sidebar when clicking overlay
            overlay.addEventListener('click', () => {
                mobileMenuToggle.classList.remove('active');
                adminSidebar.classList.remove('open');
                overlay.classList.remove('active');
            });

            // Close sidebar when clicking a nav item (on mobile)
            adminSidebar.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', () => {
                    if (window.innerWidth <= 1024) {
                        mobileMenuToggle.classList.remove('active');
                        adminSidebar.classList.remove('open');
                        overlay.classList.remove('active');
                    }
                });
            });
        }
    </script>
    <?php if (isset($extraJS)): ?>
        <?= $extraJS ?>
    <?php endif; ?>
</body>
</html>
