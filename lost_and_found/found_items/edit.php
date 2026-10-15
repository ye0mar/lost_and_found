<?php
/**
 * Edit Found Item Record
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
    header('Location: ' . BASE_URL . '/found_items/index.php');
    exit;
}

$db = get_db_connection();
$stmt = $db->prepare("SELECT * FROM found_items WHERE id = :id");
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('error', 'Found item record not found.');
    header('Location: ' . BASE_URL . '/found_items/index.php');
    exit;
}

$categories = $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

$pageTitle = 'Edit ' . $item['item_name'];
$pageHeading = 'Edit Found Property Dossier';
$activeNav = 'found_items';

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
                    <h2 class="card-title">Modify Custody Information</h2>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                        Update storage location, description, or release status.
                    </p>
                </div>
                <a href="<?= BASE_URL ?>/found_items/view.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">&larr; Back to Dossier</a>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/found_items/process.php" enctype="multipart/form-data">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">

                <div class="form-grid-2col">
                    <div class="form-group col-span-2">
                        <label for="item_name" class="form-label required">Item Name &amp; Visual Description</label>
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
                            <option value="Available" <?= $item['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                            <option value="Matched" <?= $item['status'] === 'Matched' ? 'selected' : '' ?>>Matched</option>
                            <option value="Returned" <?= $item['status'] === 'Returned' ? 'selected' : '' ?>>Returned</option>
                            <option value="Disposed" <?= $item['status'] === 'Disposed' ? 'selected' : '' ?>>Disposed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_found" class="form-label required">Date Found</label>
                        <input type="date" id="date_found" name="date_found" class="form-control" value="<?= e($item['date_found']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="location_found" class="form-label required">Location Found</label>
                        <input type="text" id="location_found" name="location_found" class="form-control" value="<?= e($item['location_found']) ?>" required>
                    </div>

                    <div class="form-group col-span-2">
                        <label for="storage_location" class="form-label required">Storage Locker / Safe Location</label>
                        <input type="text" id="storage_location" name="storage_location" class="form-control" value="<?= e($item['storage_location'] ?? '') ?>" required>
                    </div>

                    <div class="form-group col-span-2">
                        <label for="description" class="form-label required">Description</label>
                        <textarea id="description" name="description" class="form-control" required><?= e($item['description']) ?></textarea>
                    </div>

                    <div class="form-group col-span-2">
                        <label for="additional_notes" class="form-label">Internal Notes</label>
                        <textarea id="additional_notes" name="additional_notes" class="form-control"><?= e($item['additional_notes'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group col-span-2">
                        <label class="form-label">Replace Photograph (Optional)</label>
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
                    <a href="<?= BASE_URL ?>/found_items/view.php?id=<?= $item['id'] ?>" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
