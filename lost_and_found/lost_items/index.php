<?php
/**
 * Lost Items Directory
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

// Build query
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(l.item_name LIKE :s1 OR l.description LIKE :s2 OR l.location_lost LIKE :s3)";
    $params['s1'] = "%$search%";
    $params['s2'] = "%$search%";
    $params['s3'] = "%$search%";
}

if ($category !== '') {
    $where[] = "l.category_id = :cat";
    $params['cat'] = $category;
}

if ($status !== '') {
    $where[] = "l.status = :status";
    $params['status'] = $status;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM lost_items l $whereSql");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRecords / $perPage));

// Order By
$orderBy = "l.date_lost DESC";
if ($sort === 'oldest') $orderBy = "l.date_lost ASC";
if ($sort === 'name') $orderBy = "l.item_name ASC";

// Query records with category and reporter
$sql = "SELECT l.*, c.name AS category_name, u.full_name AS reporter_name 
        FROM lost_items l 
        LEFT JOIN categories c ON l.category_id = c.id 
        LEFT JOIN users u ON l.reported_by_user_id = u.id 
        $whereSql 
        ORDER BY $orderBy 
        LIMIT $perPage OFFSET $offset";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Categories for filter dropdown
$categories = $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

$pageTitle = 'Lost Items';
$pageHeading = 'Lost Belongings Registry';
$activeNav = 'lost_items';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <!-- Toolbar / Search & Filters -->
        <form method="GET" action="<?= BASE_URL ?>/lost_items/index.php" class="filter-toolbar">
            <div class="filter-input-wrap">
                <input type="text" name="search" class="form-control" placeholder="Search item name, keyword, location..." value="<?= e($search) ?>">
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
                    <option value="Reported" <?= $status === 'Reported' ? 'selected' : '' ?>>Reported</option>
                    <option value="Under Review" <?= $status === 'Under Review' ? 'selected' : '' ?>>Under Review</option>
                    <option value="Matched" <?= $status === 'Matched' ? 'selected' : '' ?>>Matched</option>
                    <option value="Claimed" <?= $status === 'Claimed' ? 'selected' : '' ?>>Claimed</option>
                    <option value="Closed" <?= $status === 'Closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <span>Filter</span>
                </button>
                <?php if ($search || $category || $status): ?>
                    <a href="<?= BASE_URL ?>/lost_items/index.php" class="btn btn-secondary" title="Reset Filters">Reset</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/lost_items/report.php" class="btn btn-primary" style="margin-left:auto;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span>Report Lost Item</span>
                </a>
            </div>
        </form>

        <!-- Records Table Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <span class="card-title">Registered Lost Item Records</span>
                    <span style="font-size: 12px; color: var(--text-muted); margin-left: 8px;">(<?= $totalRecords ?> total found)</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Date Lost</th>
                            <th>Location Where Lost</th>
                            <th>Reported By</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 10px;display:block;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                    No lost item records match your filter criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td style="font-weight: 600;">
                                        <a href="<?= BASE_URL ?>/lost_items/view.php?id=<?= $item['id'] ?>" style="color:var(--text-primary);"><?= e($item['item_name']) ?></a>
                                    </td>
                                    <td><?= e($item['category_name'] ?? 'General') ?></td>
                                    <td><?= format_date($item['date_lost']) ?></td>
                                    <td><?= e($item['location_lost']) ?></td>
                                    <td><?= e($item['reporter_name'] ?? 'Officer') ?></td>
                                    <td><?= render_status_badge($item['status']) ?></td>
                                    <td style="text-align: right;">
                                        <div style="display:inline-flex;gap:6px;">
                                            <a href="<?= BASE_URL ?>/lost_items/view.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm" title="Inspect & Match">
                                                <span>Inspect</span>
                                            </a>
                                            <a href="<?= BASE_URL ?>/lost_items/edit.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm" title="Edit">
                                                <span>Edit</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
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
