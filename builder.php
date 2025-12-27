<?php
require_once 'config/config.php';
require_once 'classes/TableDesign.php';

// Load saved design if ID provided
$loadedDesign = null;
if (isset($_GET['load']) && isLoggedIn()) {
    $db = new Database();
    $designModel = new TableDesign($db->connect());
    $loadedDesign = $designModel->getById(intval($_GET['load']), $_SESSION['user_id']);
}

$pageTitle = 'Build Your Table';
$extraCSS = ['builder.css'];
$extraJS = ['builder.js'];
$bodyClass = 'page-builder';
require_once 'includes/header.php';
?>

<!-- Fixed Full-Width Hero Banner -->
<div class="builder-hero">
    <h1>Build Your Custom Table</h1>
    <p>Design your perfect poker table with our interactive builder</p>
</div>

<div class="builder-page">
    <!-- Fixed Left Side - Table Preview -->
    <div class="builder-left">
        <div class="builder-preview">
            <div class="preview-card">
                <div class="preview-container">
                    <svg id="tablePreview" viewBox="0 0 800 500" class="table-svg">
                        <!-- Table will be rendered here by JavaScript -->
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Scrollable Right Side -->
    <div class="builder-right">
        <div class="builder-options">
                <form id="tableBuilderForm">
                    <input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

                    <!-- Table Style -->
                    <div class="option-group">
                        <h3 class="option-title">Table Style</h3>
                        <div class="option-cards">
                            <label class="option-card">
                                <input type="radio" name="table_style" value="racetrack" checked>
                                <div class="option-card-content">
                                    <div class="option-icon">&#127922;</div>
                                    <span class="option-name">With Racetrack</span>
                                    <span class="option-desc">Classic casino style with drink rail</span>
                                </div>
                            </label>
                            <label class="option-card">
                                <input type="radio" name="table_style" value="standard">
                                <div class="option-card-content">
                                    <div class="option-icon">&#127183;</div>
                                    <span class="option-name">Standard Rail</span>
                                    <span class="option-desc">Traditional padded rail design</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Table Size - Commented out for future multiple sizes -->
                    <!--
                    <div class="option-group">
                        <h3 class="option-title">Table Size</h3>
                        <div class="option-select">
                            <select name="table_size" id="tableSize" class="form-control">
                                <option value="84x42">84" x 42" (8 Players)</option>
                                <option value="96x42" selected>96" x 42" (10 Players)</option>
                                <option value="108x48">108" x 48" (10+ Players)</option>
                            </select>
                        </div>
                    </div>
                    -->
                    <!-- Default size: 96x48 (8ft x 4ft) -->
                    <input type="hidden" name="table_size" id="tableSize" value="96x48">

                    <!-- Rail Color -->
                    <div class="option-group">
                        <h3 class="option-title">Rail Color</h3>
                        <div class="color-picker" id="railColors">
                            <label class="color-swatch" title="Black">
                                <input type="radio" name="rail_color" value="#1a1a1a" checked>
                                <span class="swatch" style="background: #1a1a1a;"></span>
                            </label>
                            <label class="color-swatch" title="Dark Brown">
                                <input type="radio" name="rail_color" value="#3d2314">
                                <span class="swatch" style="background: #3d2314;"></span>
                            </label>
                            <label class="color-swatch" title="Burgundy">
                                <input type="radio" name="rail_color" value="#722F37">
                                <span class="swatch" style="background: #722F37;"></span>
                            </label>
                            <label class="color-swatch" title="Navy Blue">
                                <input type="radio" name="rail_color" value="#1a2744">
                                <span class="swatch" style="background: #1a2744;"></span>
                            </label>
                            <label class="color-swatch" title="Forest Green">
                                <input type="radio" name="rail_color" value="#1a3d2e">
                                <span class="swatch" style="background: #1a3d2e;"></span>
                            </label>
                            <label class="color-swatch" title="White">
                                <input type="radio" name="rail_color" value="#f5f5f5">
                                <span class="swatch" style="background: #f5f5f5; border: 2px solid #ddd;"></span>
                            </label>
                            <label class="color-swatch" title="Tan">
                                <input type="radio" name="rail_color" value="#c4a77d">
                                <span class="swatch" style="background: #c4a77d;"></span>
                            </label>
                            <label class="color-swatch" title="Red">
                                <input type="radio" name="rail_color" value="#8b0000">
                                <span class="swatch" style="background: #8b0000;"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Playing Surface Material -->
                    <div class="option-group">
                        <h3 class="option-title">Playing Surface</h3>
                        <div class="option-cards">
                            <label class="option-card">
                                <input type="radio" name="surface_material" value="speedcloth" checked>
                                <div class="option-card-content">
                                    <span class="option-name">Suited Speed Cloth</span>
                                    <span class="option-desc">Pro-grade, fast card sliding</span>
                                </div>
                            </label>
                            <label class="option-card">
                                <input type="radio" name="surface_material" value="velveteen">
                                <div class="option-card-content">
                                    <span class="option-name">Velveteen</span>
                                    <span class="option-desc">Classic soft feel</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Surface Color -->
                    <div class="option-group">
                        <h3 class="option-title">Surface Color</h3>
                        <div class="color-picker" id="surfaceColors">
                            <!-- Speed Cloth Colors -->
                            <div class="color-set speedcloth-colors">
                                <label class="color-swatch" title="Casino Green">
                                    <input type="radio" name="surface_color" value="#1a472a" checked>
                                    <span class="swatch" style="background: #1a472a;"></span>
                                </label>
                                <label class="color-swatch" title="Blue">
                                    <input type="radio" name="surface_color" value="#1a3a5c">
                                    <span class="swatch" style="background: #1a3a5c;"></span>
                                </label>
                                <label class="color-swatch" title="Red">
                                    <input type="radio" name="surface_color" value="#6b1c1c">
                                    <span class="swatch" style="background: #6b1c1c;"></span>
                                </label>
                                <label class="color-swatch" title="Black">
                                    <input type="radio" name="surface_color" value="#1a1a1a">
                                    <span class="swatch" style="background: #1a1a1a;"></span>
                                </label>
                                <label class="color-swatch" title="Purple">
                                    <input type="radio" name="surface_color" value="#3d1a4d">
                                    <span class="swatch" style="background: #3d1a4d;"></span>
                                </label>
                                <label class="color-swatch" title="Burgundy">
                                    <input type="radio" name="surface_color" value="#4a1c2e">
                                    <span class="swatch" style="background: #4a1c2e;"></span>
                                </label>
                            </div>
                            <!-- Velveteen Colors (hidden by default) -->
                            <div class="color-set velveteen-colors" style="display: none;">
                                <label class="color-swatch" title="Green">
                                    <input type="radio" name="surface_color_velvet" value="#2d5a3d">
                                    <span class="swatch" style="background: #2d5a3d;"></span>
                                </label>
                                <label class="color-swatch" title="Blue">
                                    <input type="radio" name="surface_color_velvet" value="#2a4a6d">
                                    <span class="swatch" style="background: #2a4a6d;"></span>
                                </label>
                                <label class="color-swatch" title="Red">
                                    <input type="radio" name="surface_color_velvet" value="#8b2c2c">
                                    <span class="swatch" style="background: #8b2c2c;"></span>
                                </label>
                                <label class="color-swatch" title="Black">
                                    <input type="radio" name="surface_color_velvet" value="#252525">
                                    <span class="swatch" style="background: #252525;"></span>
                                </label>
                                <label class="color-swatch" title="Navy">
                                    <input type="radio" name="surface_color_velvet" value="#1a2744">
                                    <span class="swatch" style="background: #1a2744;"></span>
                                </label>
                                <label class="color-swatch" title="Wine">
                                    <input type="radio" name="surface_color_velvet" value="#5c1a35">
                                    <span class="swatch" style="background: #5c1a35;"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Cup Holders -->
                    <div class="option-group">
                        <h3 class="option-title">Cup Holders</h3>
                        <div class="option-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="cup_holders" value="1" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span>Include stainless steel cup holders</span>
                        </div>
                        <div class="cup-holder-count" id="cupHolderCount">
                            <label>Number of cup holders:</label>
                            <select name="cup_holder_count" class="form-control" style="width: auto; display: inline-block;">
                                <option value="8">8</option>
                                <option value="10" selected>10</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dealer Position - Removed (targeting players, not casinos)
                    <div class="option-group">
                        <h3 class="option-title">Dealer Cutout</h3>
                        <div class="option-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="dealer_cutout" value="1">
                                <span class="toggle-slider"></span>
                            </label>
                            <span>Include dealer position cutout</span>
                        </div>
                    </div>
                    -->

                    <!-- Saved Design Banner (hidden by default) -->
                    <div class="saved-design-banner" id="savedDesignBanner" style="display: none;">
                        <div class="saved-tag">
                            <span class="saved-icon">&#10003;</span>
                            <span>Design Saved</span>
                        </div>
                        <p class="saved-name" id="savedDesignName"></p>
                        <button type="button" class="btn btn-primary btn-block" id="quoteFromSaved">
                            Request Quote for This Design
                        </button>
                    </div>

                    <!-- Verification Warning (shown if logged in but not verified) -->
                    <?php if (isLoggedIn() && !isEmailVerified()): ?>
                    <div class="verification-warning" id="verificationWarning">
                        <div class="warning-icon">&#9888;</div>
                        <div class="warning-text">
                            <strong>Email Not Verified</strong>
                            <p>Please verify your email to save designs and request quotes.</p>
                        </div>
                        <a href="<?= SITE_URL ?>/resend-verification.php" class="btn btn-sm btn-outline">Verify Now</a>
                    </div>
                    <?php endif; ?>

                    <!-- Actions -->
                    <div class="builder-actions" id="builderActions">
                        <?php if (isLoggedIn()): ?>
                            <?php if (isEmailVerified()): ?>
                                <button type="button" class="btn btn-secondary btn-block" id="saveDesign">
                                    Save Design
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary btn-block btn-disabled" id="saveDesignDisabled" disabled>
                                    Save Design
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?= SITE_URL ?>/login.php" class="btn btn-secondary btn-block">
                                Login to Save Design
                            </a>
                        <?php endif; ?>
                        <?php if (!isLoggedIn()): ?>
                            <a href="<?= SITE_URL ?>/register.php" class="btn btn-primary btn-block btn-lg">
                                Create Account to Get Quote
                            </a>
                        <?php elseif (isEmailVerified()): ?>
                            <button type="button" class="btn btn-primary btn-block btn-lg" id="requestQuote">
                                Request Quote
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary btn-block btn-lg btn-disabled" id="requestQuoteDisabled" disabled>
                                Request Quote
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Save Design Modal -->
<div class="modal" id="saveModal">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Save Your Design</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label" for="designName">Design Name</label>
                <input type="text" id="designName" class="form-control" placeholder="e.g., Game Room Table">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmSave">Save Design</button>
        </div>
    </div>
</div>

<!-- Quote Modal (only for logged-in verified users) -->
<div class="modal" id="quoteModal">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h3>Request a Quote</h3>
            <button type="button" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label" for="quoteNotes">Additional Notes (optional)</label>
                <textarea id="quoteNotes" class="form-control" rows="3" placeholder="Any special requests or questions..."></textarea>
            </div>
            <div class="design-summary" id="designSummary">
                <!-- Populated by JavaScript -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
            <button type="button" class="btn btn-primary" id="submitQuote">Submit Quote Request</button>
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
        <div class="modal-body">
            <p id="notificationMessage"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="notificationOk">OK</button>
        </div>
    </div>
</div>

<script>
    const isLoggedIn = <?= isLoggedIn() ? 'true' : 'false' ?>;
    const isVerified = <?= isEmailVerified() ? 'true' : 'false' ?>;
    const csrfToken = '<?= getCSRFToken() ?>';
    const siteUrl = '<?= SITE_URL ?>';
    const loadedDesign = <?= $loadedDesign ? json_encode($loadedDesign['design_data']) : 'null' ?>;
    const loadedDesignId = <?= $loadedDesign ? $loadedDesign['id'] : 'null' ?>;
    const loadedDesignName = <?= $loadedDesign ? json_encode($loadedDesign['name']) : 'null' ?>;
</script>

<?php require_once 'includes/footer.php'; ?>
