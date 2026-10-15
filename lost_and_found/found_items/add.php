<?php
/**
 * Register Found Item Interface
 * Accessible by both Administrators and Staff
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/csrf.php';
require_once APP_ROOT . '/includes/functions.php';

require_login();

$db = get_db_connection();
$categories = $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
$defaultStorage = get_system_setting('default_storage_location', 'Central Security Custody Vault - Room 104');

$pageTitle = 'Add Found Item';
$pageHeading = 'Register Found Property into Custody';
$activeNav = 'add_found';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <div class="card" style="max-width: 960px; margin: 0 auto;">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Found Property Custody Intake</h2>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                        Record found item details and assign a secure physical storage location.
                    </p>
                </div>
                <a href="<?= BASE_URL ?>/found_items/index.php" class="btn btn-secondary btn-sm">&larr; Back to Registry</a>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/found_items/process.php" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="create">

                <div class="form-grid-2col">
                    <div class="form-group col-span-2">
                        <label for="item_name" class="form-label required">Item Name &amp; Visual Description</label>
                        <input type="text" id="item_name" name="item_name" class="form-control" placeholder="e.g. Black Sony WH-1000XM4 Wireless Headphones" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="category_id" class="form-label required">Category</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_found" class="form-label required">Date Recovered / Handed In</label>
                        <input type="date" id="date_found" name="date_found" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="location_found" class="form-label required">Location Found</label>
                        <input type="text" id="location_found" name="location_found" class="form-control" placeholder="e.g. Science Lecture Hall 101, Row 4" required>
                    </div>

                    <div class="form-group">
                        <label for="storage_location" class="form-label required">Physical Custody Storage Location</label>
                        <input type="text" id="storage_location" name="storage_location" class="form-control" value="<?= e($defaultStorage) ?>" required>
                        <span class="form-helper">Locker number, bin code, or security safe compartment.</span>
                    </div>

                    <div class="form-group col-span-2">
                        <label for="description" class="form-label required">Item Description &amp; Condition</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Describe physical condition, colors, case, contents, and identifiable markings..." required></textarea>
                    </div>

                    <div class="form-group col-span-2">
                        <label for="additional_notes" class="form-label">Internal Custody Notes (Optional)</label>
                        <textarea id="additional_notes" name="additional_notes" class="form-control" placeholder="Notes on finder identity, turnover remarks, or custody chain info..."></textarea>
                    </div>

                    <div class="form-group col-span-2">
                        <label class="form-label">Photograph of Recovered Property (Optional)</label>
                        <label class="file-upload-box" for="itemImageInput">
                            <input type="file" id="itemImageInput" name="item_image" accept="image/jpeg,image/png,image/webp">
                            <div id="uploadPrompt">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;display:block;color:var(--text-secondary);"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <div style="font-size: 13.5px; font-weight: 500;">Click or drag custody photograph here</div>
                                <div class="form-helper">PNG, JPG, or WebP up to 5MB</div>
                            </div>
                            <img id="imagePreview" class="file-preview-thumbnail" alt="Selected Preview">
                        </label>
                    </div>
                </div>

                <div class="form-actions-bar">
                    <a href="<?= BASE_URL ?>/found_items/index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Deposit Item into Custody</span>
                    </button>
                </div>
            </form>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
