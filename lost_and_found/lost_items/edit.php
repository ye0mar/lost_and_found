<?php
/**
 * Edit Lost Item Record
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/csrf.php';
require_once APP_ROOT . '/includes/functions.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/lost_items/index.php');
    exit;
}

$db = get_db_connection();
$stmt = $db->prepare("SELECT * FROM lost_items WHERE id = :id");
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('error', 'Lost item record not found.');
    header('Location: ' . BASE_URL . '/lost_items/index.php');
    exit;
}

$categories = $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

$pageTitle = 'Edit ' . $item['item_name'];
$pageHeading = 'Edit Lost Property Dossier';
$activeNav = 'lost_items';

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
                    <h2 class="card-title">Modify Lost Item Information</h2>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                        Update status, owner details, or physical description.
                    </p>
                </div>
                <a href="<?= BASE_URL ?>/lost_items/view.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">&larr; Back to Dossier</a>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/lost_items/process.php" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">

                <div class="form-grid-2col">
                    <div class="form-group col-span-2">
                        <label for="item_name" class="form-label required">Item Name &amp; Model</label>
                        <input type="text" id="item_name" name="item_name" class="form-control" value="<?= e($item['item_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id" class="form-label required">Category</label>
                        <select id="category_id" name="category_id" class="form-control" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $item['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label required">Custody Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Reported" <?= $item['status'] === 'Reported' ? 'selected' : '' ?>>Reported</option>
                            <option value="Under Review" <?= $item['status'] === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                            <option value="Matched" <?= $item['status'] === 'Matched' ? 'selected' : '' ?>>Matched</option>
                            <option value="Claimed" <?= $item['status'] === 'Claimed' ? 'selected' : '' ?>>Claimed</option>
                            <option value="Closed" <?= $item['status'] === 'Closed' ? 'selected' : '' ?>>Closed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_lost" class="form-label required">Date Lost</label>
                        <input type="date" id="date_lost" name="date_lost" class="form-control" value="<?= e($item['date_lost']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="location_lost" class="form-label required">Location Where Lost</label>
                        <input type="text" id="location_lost" name="location_lost" class="form-control" value="<?= e($item['location_lost']) ?>" required>
                    </div>

                    <div class="form-group col-span-2">
                        <label for="description" class="form-label required">Description</label>
                        <textarea id="description" name="description" class="form-control" required><?= e($item['description']) ?></textarea>
                    </div>

                    <div class="col-span-2" style="border-top: 1px solid var(--border-light); padding-top: 18px; margin-top: 6px;">
                        <h4 style="font-size: 14px; margin-bottom: 4px;">Owner Contact Information</h4>
                    </div>

                    <div class="form-group">
                        <label for="contact_name" class="form-label">Owner Name</label>
                        <input type="text" id="contact_name" name="contact_name" class="form-control" value="<?= e($item['contact_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="contact_phone" class="form-label">Phone Number</label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="form-control" value="<?= e($item['contact_phone'] ?? '') ?>">
                    </div>

                    <div class="form-group col-span-2">
                        <label for="contact_email" class="form-label">Email Address</label>
                        <input type="email" id="contact_email" name="contact_email" class="form-control" value="<?= e($item['contact_email'] ?? '') ?>">
                    </div>

                    <div class="form-group col-span-2">
                        <label class="form-label">Replace Reference Photo (Optional)</label>
                        <label class="file-upload-box" for="itemImageInput">
                            <input type="file" id="itemImageInput" name="item_image" accept="image/jpeg,image/png,image/webp">
                            <div id="uploadPrompt">
                                <div style="font-size: 13.5px; font-weight: 500;">Click to upload a new photograph</div>
                                <div class="form-helper">Leave empty to keep existing image</div>
                            </div>
                            <img id="imagePreview" class="file-preview-thumbnail" alt="Selected Preview">
                        </label>
                    </div>
                </div>

                <div class="form-actions-bar">
                    <a href="<?= BASE_URL ?>/lost_items/view.php?id=<?= $item['id'] ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
