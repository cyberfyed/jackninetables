<?php
require_once 'config/config.php';
require_once 'classes/TableDesign.php';

requireLogin();

$db = new Database();
$design = new TableDesign($db->connect());

$designs = $design->getByUser($_SESSION['user_id']);

$pageTitle = 'My Designs';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 2rem 0;">
    <div class="container hero-content">
        <h1>My Designs</h1>
        <p>View and manage your saved table designs</p>
    </div>
</section>

<section class="section" style="padding-top: 2rem;">
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <p style="margin: 0; color: var(--gray-600);"><?= count($designs) ?> saved design<?= count($designs) !== 1 ? 's' : '' ?></p>
            <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary">+ New Design</a>
        </div>

        <?php if (empty($designs)): ?>
            <div class="card">
                <div class="card-body text-center" style="padding: 4rem 2rem;">
                    <div style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1rem;">&#127922;</div>
                    <h3>No Designs Yet</h3>
                    <p style="color: var(--gray-600); margin-bottom: 1.5rem;">Start creating your custom poker table with our interactive builder.</p>
                    <a href="<?= SITE_URL ?>/builder.php" class="btn btn-primary btn-lg">Build Your First Table</a>
                </div>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                <?php foreach ($designs as $d): ?>
                    <div class="card design-card" data-id="<?= $d['id'] ?>">
                        <div class="card-body">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.25rem 0;"><?= sanitize($d['name']) ?></h3>
                                    <div style="font-size: 0.85rem; color: var(--gray-500);">
                                        Created <?= date('M j, Y', strtotime($d['created_at'])) ?>
                                    </div>
                                </div>
                                <button class="btn-icon favorite-btn <?= $d['is_favorite'] ? 'active' : '' ?>" data-id="<?= $d['id'] ?>" title="Toggle Favorite">
                                    <?= $d['is_favorite'] ? '&#9733;' : '&#9734;' ?>
                                </button>
                            </div>

                            <div class="design-preview" style="background: var(--gray-100); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1rem;">
                                <ul style="list-style: none; font-size: 0.9rem;">
                                    <li style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <span style="color: var(--gray-600);">Style:</span>
                                        <span><?= $d['design_data']['tableStyle'] === 'racetrack' ? 'With Racetrack' : 'Standard Rail' ?></span>
                                    </li>
                                    <li style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <span style="color: var(--gray-600);">Size:</span>
                                        <span><?= sanitize($d['design_data']['tableSize'] ?? 'N/A') ?></span>
                                    </li>
                                    <li style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="color: var(--gray-600);">Colors:</span>
                                        <span>
                                            <span style="display: inline-block; width: 20px; height: 20px; background: <?= $d['design_data']['railColor'] ?? '#000' ?>; border-radius: 4px; vertical-align: middle; border: 1px solid var(--gray-300);"></span>
                                            <span style="display: inline-block; width: 20px; height: 20px; background: <?= $d['design_data']['surfaceColor'] ?? '#000' ?>; border-radius: 4px; vertical-align: middle; border: 1px solid var(--gray-300); margin-left: 4px;"></span>
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <div style="display: flex; gap: 0.5rem;">
                                <a href="<?= SITE_URL ?>/builder.php?load=<?= $d['id'] ?>" class="btn btn-primary btn-sm" style="flex: 1;">Edit</a>
                                <button class="btn btn-outline btn-sm delete-btn" data-id="<?= $d['id'] ?>">Delete</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Confirm Delete Modal -->
<div class="modal" id="confirmModal">
    <div class="modal-backdrop"></div>
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Delete Design</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body" style="text-align: center;">
            <p style="font-size: 1.1rem; color: var(--gray-700);">Are you sure you want to delete this design?</p>
            <p style="color: var(--gray-500); font-size: 0.9rem;">This action cannot be undone.</p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-outline" id="cancelDelete">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmDelete" style="background: var(--error); border-color: var(--error);">Delete</button>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div class="modal" id="notificationModal">
    <div class="modal-backdrop"></div>
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="notificationTitle">Notice</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body" style="text-align: center;">
            <p id="notificationMessage" style="font-size: 1.1rem; color: var(--gray-700);"></p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-primary" id="notificationOk">OK</button>
        </div>
    </div>
</div>

<style>
.btn-icon {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-400);
    padding: 0;
    line-height: 1;
    transition: color var(--transition-fast);
}
.btn-icon:hover, .btn-icon.active {
    color: var(--gold);
}
.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 2000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal.active {
    display: flex;
}
.modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
}
.modal-content {
    position: relative;
    background: var(--white);
    border-radius: var(--radius-lg);
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-xl);
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid var(--gray-200);
}
.modal-header h3 {
    margin: 0;
}
.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray-500);
    padding: 0;
    line-height: 1;
}
.modal-body {
    padding: 1.5rem;
}
.modal-footer {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid var(--gray-200);
    background: var(--gray-100);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let deleteId = null;
    const confirmModal = document.getElementById('confirmModal');
    const notificationModal = document.getElementById('notificationModal');

    function showConfirmModal(id) {
        deleteId = id;
        confirmModal.classList.add('active');
    }

    function hideConfirmModal() {
        confirmModal.classList.remove('active');
        deleteId = null;
    }

    function showNotification(message, title = 'Notice', type = 'info') {
        const titleEl = document.getElementById('notificationTitle');
        const messageEl = document.getElementById('notificationMessage');
        const okBtn = document.getElementById('notificationOk');

        titleEl.textContent = title;
        messageEl.textContent = message;

        if (type === 'success') {
            titleEl.style.color = 'var(--success)';
        } else if (type === 'error') {
            titleEl.style.color = 'var(--error)';
        } else {
            titleEl.style.color = 'var(--dark)';
        }

        notificationModal.classList.add('active');
    }

    function hideNotification() {
        notificationModal.classList.remove('active');
    }

    // Modal close handlers
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            hideConfirmModal();
            hideNotification();
        });
    });

    document.getElementById('cancelDelete').addEventListener('click', hideConfirmModal);
    document.getElementById('notificationOk').addEventListener('click', hideNotification);

    // Confirm delete handler
    document.getElementById('confirmDelete').addEventListener('click', async function() {
        if (!deleteId) return;

        try {
            const response = await fetch('<?= SITE_URL ?>/api/designs.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    action: 'delete',
                    id: deleteId,
                    csrf_token: '<?= getCSRFToken() ?>'
                })
            });

            const result = await response.json();
            hideConfirmModal();

            if (result.success) {
                const card = document.querySelector(`.design-card[data-id="${deleteId}"]`);
                if (card) card.remove();

                // Update count
                const remaining = document.querySelectorAll('.design-card').length;
                const countText = document.querySelector('.container > div > p');
                if (countText) {
                    countText.textContent = `${remaining} saved design${remaining !== 1 ? 's' : ''}`;
                }

                // Show empty state if no designs left
                if (remaining === 0) {
                    location.reload();
                }
            } else {
                showNotification(result.error || 'Failed to delete design', 'Error', 'error');
            }
        } catch (error) {
            console.error(error);
            hideConfirmModal();
            showNotification('An error occurred', 'Error', 'error');
        }
    });

    // Delete design - show confirm modal
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            showConfirmModal(this.dataset.id);
        });
    });

    // Toggle favorite
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.id;
            try {
                const response = await fetch('<?= SITE_URL ?>/api/designs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        action: 'toggle_favorite',
                        id: id,
                        csrf_token: '<?= getCSRFToken() ?>'
                    })
                });

                const result = await response.json();
                if (result.success) {
                    this.classList.toggle('active');
                    this.innerHTML = this.classList.contains('active') ? '&#9733;' : '&#9734;';
                }
            } catch (error) {
                console.error(error);
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
