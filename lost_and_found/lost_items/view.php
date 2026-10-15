<?php
/**
 * Lost Item Detailed View & Match Recommendation Engine
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
$stmt = $db->prepare(
    "SELECT l.*, c.name AS category_name, u.full_name AS reporter_name, u.email AS reporter_email 
     FROM lost_items l
     LEFT JOIN categories c ON l.category_id = c.id
     LEFT JOIN users u ON l.reported_by_user_id = u.id
     WHERE l.id = :id"
);
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('error', 'The requested lost item record could not be found.');
    header('Location: ' . BASE_URL . '/lost_items/index.php');
    exit;
}

// Calculate intelligent suggestions using the matching engine
$suggestedMatches = find_suggested_matches($id);

$pageTitle = $item['item_name'];
$pageHeading = 'Lost Item Custody Dossier';
$activeNav = 'lost_items';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <a href="<?= BASE_URL ?>/lost_items/index.php" class="btn btn-secondary btn-sm">&larr; Back to Lost Registry</a>
            <div style="display: flex; gap: 10px;">
                <a href="<?= BASE_URL ?>/lost_items/edit.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <span>Edit Record</span>
                </a>
            </div>
        </div>

        <div class="dashboard-split-grid">
            <!-- Left: Dossier & Physical Attributes -->
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

                    <div class="form-grid-2col" style="border-top: 1px solid var(--border-light); padding-top: 18px;">
                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Date Lost</span>
                            <div style="font-size: 14px; font-weight: 600; margin-top: 2px;"><?= format_date($item['date_lost']) ?></div>
                        </div>

                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Reported Location</span>
                            <div style="font-size: 14px; font-weight: 600; margin-top: 2px;"><?= e($item['location_lost']) ?></div>
                        </div>

                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Filed By Staff Officer</span>
                            <div style="font-size: 14px; font-weight: 600; margin-top: 2px;"><?= e($item['reporter_name'] ?? 'Officer') ?></div>
                        </div>

                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Date Registered</span>
                            <div style="font-size: 14px; font-weight: 600; margin-top: 2px;"><?= format_date($item['created_at']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Owner / Contact Details Card -->
                <div class="card">
                    <h3 class="card-title" style="margin-bottom: 14px;">Reported Owner Information</h3>
                    <div class="form-grid-2col">
                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Contact Name</span>
                            <div style="font-weight: 600; font-size: 14px; margin-top: 2px;"><?= e($item['contact_name'] ?: 'None specified') ?></div>
                        </div>
                        <div>
                            <span style="font-size: 12px; color: var(--text-secondary);">Contact Phone</span>
                            <div style="font-weight: 600; font-size: 14px; margin-top: 2px;"><?= e($item['contact_phone'] ?: 'None specified') ?></div>
                        </div>
                        <div class="col-span-2">
                            <span style="font-size: 12px; color: var(--text-secondary);">Contact Email</span>
                            <div style="font-weight: 600; font-size: 14px; margin-top: 2px;"><?= e($item['contact_email'] ?: 'None specified') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Intelligent Matching Suggestions -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title" style="display:flex;align-items:center;gap:8px;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--accent-main);"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                <span>Suggested Found Matches</span>
                            </h3>
                            <p style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                                Automated cross-analysis of available custody inventory.
                            </p>
                        </div>
                    </div>

                    <?php if (empty($suggestedMatches)): ?>
                        <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 8px;display:block;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                            No high-confidence matches found in current custody inventory.
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            <?php foreach ($suggestedMatches as $m): ?>
                                <?php $cand = $m['found_item']; ?>
                                <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px; background: #FAFCFE;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                        <span style="font-size: 11.5px; font-weight: 700; color: #166534; background: #DCFCE7; padding: 2px 8px; border-radius: 9999px;">
                                            <?= (int)$m['score'] ?>% Confidence Match
                                        </span>
                                        <span style="font-size: 11px; color: var(--text-muted);"><?= format_date($cand['date_found']) ?></span>
                                    </div>

                                    <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 4px;">
                                        <a href="<?= BASE_URL ?>/found_items/view.php?id=<?= $cand['id'] ?>" target="_blank" style="color:var(--text-primary);">
                                            <?= e($cand['item_name']) ?> &rarr;
                                        </a>
                                    </h4>

                                    <p style="font-size: 12px; color: var(--text-secondary); line-height: 1.4; margin-bottom: 8px;">
                                        <?= e(substr($cand['description'], 0, 100)) ?>...
                                    </p>

                                    <div style="font-size: 11px; color: #475569; margin-bottom: 10px;">
                                        <strong>Location:</strong> <?= e($cand['location_found']) ?> &bull; <strong>Storage:</strong> <?= e($cand['storage_location']) ?>
                                    </div>

                                    <!-- Match Form -->
                                    <form method="POST" action="<?= BASE_URL ?>/lost_items/process.php" onsubmit="return confirmAction('Confirm this match? This will update records and associate the items.');">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="action" value="confirm_match">
                                        <input type="hidden" name="lost_item_id" value="<?= $item['id'] ?>">
                                        <input type="hidden" name="found_item_id" value="<?= $cand['id'] ?>">
                                        <input type="hidden" name="match_score" value="<?= $m['score'] ?>">

                                        <button type="submit" class="btn btn-primary btn-sm" style="width: 100%;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            <span>Confirm Property Match</span>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Fast Status Update -->
                <div class="card">
                    <h4 style="font-size: 14px; margin-bottom: 12px;">Quick Status Management</h4>
                    <form method="POST" action="<?= BASE_URL ?>/lost_items/process.php">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">

                        <div class="form-group" style="margin-bottom: 12px;">
                            <select name="status" class="form-control">
                                <option value="Reported" <?= $item['status'] === 'Reported' ? 'selected' : '' ?>>Reported</option>
                                <option value="Under Review" <?= $item['status'] === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                                <option value="Matched" <?= $item['status'] === 'Matched' ? 'selected' : '' ?>>Matched</option>
                                <option value="Claimed" <?= $item['status'] === 'Claimed' ? 'selected' : '' ?>>Claimed</option>
                                <option value="Closed" <?= $item['status'] === 'Closed' ? 'selected' : '' ?>>Closed</option>
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
