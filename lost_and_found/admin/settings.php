<?php
/**
 * System Settings & Custody Parameters Administration
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/csrf.php';
require_once APP_ROOT . '/includes/functions.php';

require_admin();

$db = get_db_connection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_settings') {
        $settingsToUpdate = [
            'system_name'             => trim($_POST['system_name'] ?? ''),
            'organization_name'       => trim($_POST['organization_name'] ?? ''),
            'retention_period_days'   => (string)max(1, (int)($_POST['retention_period_days'] ?? 90)),
            'default_storage_location'=> trim($_POST['default_storage_location'] ?? ''),
            'max_upload_size_mb'      => (string)max(1, min(50, (int)($_POST['max_upload_size_mb'] ?? 5)))
        ];

        try {
            $stmt = $db->prepare(
                "INSERT INTO system_settings (setting_key, setting_value) 
                 VALUES (:k, :v) 
                 ON DUPLICATE KEY UPDATE setting_value = :v2"
            );
            foreach ($settingsToUpdate as $key => $val) {
                $stmt->execute(['k' => $key, 'v' => $val, 'v2' => $val]);
            }

            log_activity('ADMIN_UPDATE_SETTINGS', 'system_settings', null, 'System settings updated');
            set_flash('success', 'System parameters successfully updated and saved to MySQL.');
        } catch (Exception $e) {
            error_log('Settings update error: ' . $e->getMessage());
            set_flash('error', 'Failed to update system settings.');
        }
        header('Location: ' . BASE_URL . '/admin/settings.php');
        exit;
    }

    if ($action === 'add_category') {
        $name = trim($_POST['category_name'] ?? '');
        $desc = trim($_POST['category_desc'] ?? '');

        if (!empty($name)) {
            $slug = strtolower(preg_replace('/[^a-z0-9\-]+/i', '-', $name));
            try {
                $stmt = $db->prepare(
                    "INSERT INTO categories (name, slug, description, is_active, created_at)
                     VALUES (:n, :s, :d, 1, NOW())"
                );
                $stmt->execute(['n' => $name, 's' => $slug, 'd' => $desc]);
                log_activity('ADMIN_ADD_CATEGORY', 'categories', (int)$db->lastInsertId(), "Added category: $name");
                set_flash('success', "Category '$name' created successfully.");
            } catch (Exception $e) {
                set_flash('error', 'Category name or slug already exists.');
            }
        }
        header('Location: ' . BASE_URL . '/admin/settings.php');
        exit;
    }
}

// Fetch current categories
$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$pageTitle = 'Settings';
$pageHeading = 'System Configuration & Parameters';
$activeNav = 'settings';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <div class="dashboard-split-grid">
            <!-- Left: General System Settings -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <span class="card-title">General Application Parameters</span>
                            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;">
                                Configure organization identity and custody holding policies.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/admin/settings.php">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="save_settings">

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="system_name" class="form-label required">Application Display Name</label>
                            <input type="text" id="system_name" name="system_name" class="form-control" value="<?= e(get_system_setting('system_name', 'Lost & Found Custody System')) ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="organization_name" class="form-label required">Organization / School Department</label>
                            <input type="text" id="organization_name" name="organization_name" class="form-control" value="<?= e(get_system_setting('organization_name', 'Campus Security & Property Custody')) ?>" required>
                        </div>

                        <div class="form-grid-2col" style="margin-bottom: 16px;">
                            <div class="form-group">
                                <label for="retention_period_days" class="form-label required">Retention Period (Days)</label>
                                <input type="number" id="retention_period_days" name="retention_period_days" class="form-control" value="<?= e(get_system_setting('retention_period_days', '90')) ?>" min="7" max="365" required>
                                <span class="form-helper">Days before unclaimed items become eligible for disposal.</span>
                            </div>

                            <div class="form-group">
                                <label for="max_upload_size_mb" class="form-label required">Max Image Size (MB)</label>
                                <input type="number" id="max_upload_size_mb" name="max_upload_size_mb" class="form-control" value="<?= e(get_system_setting('max_upload_size_mb', '5')) ?>" min="1" max="50" required>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label for="default_storage_location" class="form-label required">Default Custody Storage Location</label>
                            <input type="text" id="default_storage_location" name="default_storage_location" class="form-control" value="<?= e(get_system_setting('default_storage_location', 'Central Security Custody Vault - Room 104')) ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Save System Settings</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Item Categories Management -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Item Categories</span>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/admin/settings.php" style="margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid var(--border-light);">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="add_category">

                        <div class="form-group" style="margin-bottom: 10px;">
                            <label class="form-label required">Add New Category</label>
                            <input type="text" name="category_name" class="form-control" placeholder="e.g. Laboratory Instruments" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 12px;">
                            <input type="text" name="category_desc" class="form-control" placeholder="Brief description of category items...">
                        </div>

                        <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Add Category</span>
                        </button>
                    </form>

                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php foreach ($categories as $cat): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: var(--surface-secondary); border-radius: var(--radius-sm);">
                                <div>
                                    <div style="font-size: 13px; font-weight: 600;"><?= e($cat['name']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-secondary);"><?= e($cat['description'] ?: 'No notes') ?></div>
                                </div>
                                <span style="font-size: 11px; font-weight: 600; color: #166534; background: #DCFCE7; padding: 2px 6px; border-radius: 4px;">Active</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
