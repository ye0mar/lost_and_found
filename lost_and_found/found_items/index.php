<?php
/**
 * Found Items Inventory Registry
 * Accessible by both Administrators and Staff
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/functions.php';

require_login();

$db = get_db_connection();

// Filter parameters
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status'] ?? '');
$sort     = trim($_GET['sort'] ?? 'newest');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 10;
$offset   = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(f.item_name LIKE :s1 OR f.description LIKE :s2 OR f.location_found LIKE :s3 OR f.storage_location LIKE :s4)";
    $params['s1'] = "%$search%";
    $params['s2'] = "%$search%";
    $params['s3'] = "%$search%";
    $params['s4'] = "%$search%";
}

if ($category !== '') {
    $where[] = "f.category_id = :cat";
    $params['cat'] = $category;
}

if ($status !== '') {
    $where[] = "f.status = :status";
    $params['status'] = $status;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM found_items f $whereSql");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

$orderBy = "f.date_found DESC";
if ($sort === 'oldest') $orderBy = "f.date_found ASC";
if ($sort === 'name') $orderBy = "f.item_name ASC";

$sql = "SELECT f.*, c.name AS category_name, u.full_name AS recorder_name 
        FROM found_items f 
        LEFT JOIN categories c ON f.category_id = c.id 
        LEFT JOIN users u ON f.recorded_by_user_id = u.id 
        $whereSql 
        ORDER BY $orderBy 
        LIMIT $perPage OFFSET $offset";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$categories = $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

$pageTitle = 'Found Items';
$pageHeading = 'Custody Inventory Registry';
$activeNav = 'found_items';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <!-- Toolbar / Search & Filters -->
        <form method="GET" action="<?= BASE_URL ?>/found_items/index.php" class="filter-toolbar">
            <div class="filter-input-wrap">
                <input type="text" name="search" class="form-control" placeholder="Search item, storage locker, location..." value="<?= e($search) ?>">
            </div>

            <div class="filter-select-wrap">
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-select-wrap">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="Available" <?= $status === 'Available' ? 'selected' : '' ?>>Available</option>
                    <option value="Matched" <?= $status === 'Matched' ? 'selected' : '' ?>>Matched</option>
                    <option value="Returned" <?= $status === 'Returned' ? 'selected' : '' ?>>Returned</option>
                    <option value="Disposed" <?= $status === 'Disposed' ? 'selected' : '' ?>>Disposed</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <span>Filter</span>
                </button>
                <?php if ($search || $category || $status): ?>
                    <a href="<?= BASE_URL ?>/found_items/index.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/found_items/add.php" class="btn btn-primary" style="margin-left:auto;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Add Found Item</span>
                </a>
            </div>
        </form>

        <div class="card">
            <div class="card-header">
                <div>
                    <span class="card-title">Secured Custody Records</span>
                    <span style="font-size: 12px; color: var(--text-muted); margin-left: 8px;">(<?= $totalRecords ?> items in records)</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Date Found</th>
                            <th>Location Found</th>
                            <th>Custody Storage</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                                    No found items match the selected filter.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td style="font-weight: 600;">
                                        <a href="<?= BASE_URL ?>/found_items/view.php?id=<?= $item['id'] ?>" style="color:var(--text-primary);"><?= e($item['item_name']) ?></a>
                                    </td>
                                    <td><?= e($item['category_name'] ?? 'General') ?></td>
                                    <td><?= format_date($item['date_found']) ?></td>
                                    <td><?= e($item['location_found']) ?></td>
                                    <td>
                                        <span style="background:var(--surface-secondary);padding:3px 8px;border-radius:4px;font-size:12px;font-family:monospace;">
                                            <?= e($item['storage_location'] ?: 'Vault') ?>
                                        </span>
                                    </td>
                                    <td><?= render_status_badge($item['status']) ?></td>
                                    <td style="text-align: right;">
                                        <div style="display:inline-flex;gap:6px;">
                                            <a href="<?= BASE_URL ?>/found_items/view.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">Inspect</a>
                                            <a href="<?= BASE_URL ?>/found_items/edit.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div style="display:flex;justify-content:center;gap:6px;margin-top:24px;">
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>" 
                           class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
