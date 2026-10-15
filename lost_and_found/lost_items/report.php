<?php
/**
 * Report Lost Item Interface
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

$pageTitle = 'Report Lost Item';
$pageHeading = 'Report a Lost Belonging';
$activeNav = 'report_lost';

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
                    <h2 class="card-title">Lost Property Incident Form</h2>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                        Record identifying characteristics and owner contact details for automated custody matching.
                    </p>
                </div>
                <a href="<?= BASE_URL ?>/lost_items/index.php" class="btn btn-secondary btn-sm">&larr; Back to List</a>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/lost_items/process.php" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="create">

                <div class="form-grid-2col">
                    <!-- Item Name -->
                    <div class="form-group col-span-2">
                        <label for="item_name" class="form-label required">Item Name &amp; Model</label>
                        <input type="text" id="item_name" name="item_name" class="form-control" placeholder="e.g. Apple MacBook Air 13-inch Space Gray" required autofocus>
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label for="category_id" class="form-label required">Category</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date Lost -->
                    <div class="form-group">
                        <label for="date_lost" class="form-label required">Date Lost</label>
                        <input type="date" id="date_lost" name="date_lost" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <!-- Location Lost -->
                    <div class="form-group col-span-2">
                        <label for="location_lost" class="form-label required">Location Where Lost</label>
                        <input type="text" id="location_lost" name="location_lost" class="form-control" placeholder="e.g. Science Library, 2nd Floor Study Commons" required>
                    </div>

                    <!-- Detailed Description -->
                    <div class="form-group col-span-2">
                        <label for="description" class="form-label required">Distinguishing Characteristics &amp; Description</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Describe color, serial numbers, case/stickers, scratches, or unique markings..." required></textarea>
                        <span class="form-helper">Detailed descriptions significantly increase automated match confidence.</span>
                    </div>

                    <!-- Contact Details Section Header -->
                    <div class="col-span-2" style="border-top: 1px solid var(--border-light); padding-top: 18px; margin-top: 6px;">
                        <h4 style="font-size: 14px; margin-bottom: 4px;">Reporter / Owner Contact Information</h4>
                        <p style="font-size: 12px; color: var(--text-secondary);">Direct contact used by custody staff when the item is identified.</p>
                    </div>

                    <div class="form-group">
                        <label for="contact_name" class="form-label">Full Name of Owner / Reporter</label>
                        <input type="text" id="contact_name" name="contact_name" class="form-control" placeholder="e.g. Sarah Jenkins">
                    </div>

                    <div class="form-group">
                        <label for="contact_phone" class="form-label">Phone Number</label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="form-control" placeholder="e.g. 555-0144">
                    </div>

                    <div class="form-group col-span-2">
                        <label for="contact_email" class="form-label">Email Address</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-control" placeholder="e.g. sjenkins@organization.edu">
                    </div>

                    <!-- Image Upload Dropzone -->
                    <div class="form-group col-span-2">
                        <label class="form-label">Reference Photograph (Optional)</label>
                        <label class="file-upload-box" for="itemImageInput">
                            <input type="file" id="itemImageInput" name="item_image" accept="image/jpeg,image/png,image/webp">
                            <div id="uploadPrompt">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;display:block;color:var(--text-secondary);"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <div style="font-size: 13.5px; font-weight: 500; color: var(--text-primary);">Click or drag reference photo here</div>
                                <div class="form-helper">PNG, JPG, or WebP up to 5MB</div>
                            </div>
                            <img id="imagePreview" class="file-preview-thumbnail" alt="Selected Preview">
                        </label>
                    </div>
                </div>

                <div class="form-actions-bar">
                    <a href="<?= BASE_URL ?>/lost_items/index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Submit Lost Item Report</span>
                    </button>
                </div>
            </form>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
