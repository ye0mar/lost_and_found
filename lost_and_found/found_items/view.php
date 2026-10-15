<?php
/**
 * Found Item Detailed Dossier & Release Workflow
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
$stmt = $db->prepare(
    "SELECT f.*, c.name AS category_name, u.full_name AS recorder_name, u.email AS recorder_email 
     FROM found_items f
     LEFT JOIN categories c ON f.category_id = c.id
     LEFT JOIN users u ON f.recorded_by_user_id = u.id
     WHERE f.id = :id"
);
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('error', 'Found item record not found.');
    header('Location: ' . BASE_URL . '/found_items/index.php');
    exit;
}

// Check if matched with any lost items
$matchStmt = $db->prepare(
    "SELECT m.*, l.item_name AS lost_name, l.contact_name, l.contact_phone, l.contact_email 
     FROM item_matches m
     JOIN lost_items l ON m.lost_item_id = l.id
     WHERE m.found_item_id = :fid"
);
$matchStmt->execute(['fid' => $id]);
$matches = $matchStmt->fetchAll();

$pageTitle = $item['item_name'];
$pageHeading = 'Found Property Custody Dossier';
$activeNav = 'found_items';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <a href="<?= BASE_URL ?>/found_items/index.php" class="btn btn-secondary btn-sm">&larr; Back to Inventory</a>
            <div style="display: flex; gap: 10px;">
                <a href="<?= BASE_URL ?>/found_items/edit.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <span>Edit Record</span>
                </a>
            </div>
        </div>

        <div class="dashboard-split-grid">
            <div>
                <div class="card">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                        <div>
                            <span style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--accent-main);">
                                <?= e($item['category_name'] ?? 'General') ?>
                            </span>
                            <h2 style="font-size: 22px; font-weight: 700; margin-top: 4px;"><?= e($item['item_name']) ?></h2>
                        </div>
                        <div><?= render_status_badge($item['status']) ?></div>
                    </div>

                    <?php if (!empty($item['image_path'])): ?>
                        <div style="margin: 20px 0; border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border-color); background: #FAFBFC; max-height: 340px; display: flex; align-items: center; justify-content: center;">
                            <img src="<?= BASE_URL . '/' . e($item['image_path']) ?>" alt="<?= e($item['item_name']) ?>" style="max-width: 100%; max-height: 340px; object-fit: contain;">
                        </div>
                    <?php endif; ?>

                    <div style="margin-bottom: 24px;">
                        <h4 style="font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;">Description</h4>
                        <div style="font-size: 14.5px; line-height: 1.6; color: var(--text-primary); white-space: pre-wrap; background: var(--surface-secondary); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                            <?= e($item['description']) ?>
                        </div>
                    </div>

                    <?php if (!empty($item['additional_notes'])): ?>
                        <div style="margin-bottom: 24px;">
                            <h4 style="font-size: 13px; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;">Internal Notes</h4>
                            <div style="font-size: 13.5px; color: var(--text-secondary); background: #FFFBEB; padding: 12px; border-radius: var(--radius-md); border: 1px solid #FDE68A;">
                                <?= e($item['additional_notes']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="form-grid-2col" style="border-top: 1px solid var(--border-light); padding-top: 18px;">
                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Date Found</span>
                            <div style="font-size: 14px; font-weight: 600; margin-top: 2px;"><?= format_date($item['date_found']) ?></div>
                        </div>

                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Location Recovered</span>
                            <div style="font-size: 14px; font-weight: 600; margin-top: 2px;"><?= e($item['location_found']) ?></div>
                        </div>

                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Custody Storage Locker</span>
                            <div style="font-size: 14px; font-weight: 700; color: var(--accent-main); margin-top: 2px;">
                                <?= e($item['storage_location'] ?: 'Vault') ?>
                            </div>
                        </div>

                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Intake Officer</span>
                            <div style="font-size: 14px; font-weight: 600; margin-top: 2px;"><?= e($item['recorder_name'] ?? 'Officer') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Status Operations & Release to Owner -->
            <div>
                <!-- Return / Release Workflow -->
                <div class="card" style="border-top: 4px solid var(--success);">
                    <h3 class="card-title" style="margin-bottom: 6px;">Release to Verified Owner</h3>
                    <p style="font-size: 12.5px; color: var(--text-secondary); line-height: 1.4; margin-bottom: 16px;">
                        When claimant provides verification (identification, unlocking pin, invoice), mark as Returned.
                    </p>

                    <form method="POST" action="<?= BASE_URL ?>/found_items/process.php" onsubmit="return confirmAction('Confirm item return? This records custody release.');">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="return_item">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label class="form-label required">Claimant Name &amp; ID Verified</label>
                            <input type="text" name="claimant_notes" class="form-control" placeholder="e.g. Returned to Sarah Jenkins, Student ID verified" required>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; background-color: var(--success);" <?= $item['status'] === 'Returned' ? 'disabled' : '' ?>>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span><?= $item['status'] === 'Returned' ? 'Already Returned' : 'Confirm & Release Item' ?></span>
                        </button>
                    </form>
                </div>

                <!-- Active Associations -->
                <div class="card">
                    <h3 class="card-title" style="margin-bottom: 12px;">Matched Inquiries</h3>
                    <?php if (empty($matches)): ?>
                        <div style="color: var(--text-muted); font-size: 13px; text-align: center; padding: 18px;">
                            No active matches tied to this custody record.
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <?php foreach ($matches as $match): ?>
                                <div style="border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px; background: #FAFCFE;">
                                    <div style="font-size: 13px; font-weight: 600;"><?= e($match['lost_name']) ?></div>
                                    <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                                        Claimant: <?= e($match['contact_name'] ?? 'N/A') ?> (<?= e($match['contact_phone'] ?? 'No phone') ?>)
                                    </div>
                                    <div style="font-size: 11px; color: var(--accent-main); font-weight: 600; margin-top: 4px;">
                                        Score: <?= (int)$match['match_score'] ?>% &bull; <?= e($match['status']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Update Status Card -->
                <div class="card">
                    <h4 style="font-size: 14px; margin-bottom: 12px;">Custody Status</h4>
                    <form method="POST" action="<?= BASE_URL ?>/found_items/process.php">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">

                        <div class="form-group" style="margin-bottom: 12px;">
                            <select name="status" class="form-control">
                                <option value="Available" <?= $item['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
                                <option value="Matched" <?= $item['status'] === 'Matched' ? 'selected' : '' ?>>Matched</option>
                                <option value="Returned" <?= $item['status'] === 'Returned' ? 'selected' : '' ?>>Returned</option>
                                <option value="Disposed" <?= $item['status'] === 'Disposed' ? 'selected' : '' ?>>Disposed</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-secondary btn-sm" style="width:100%;">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
