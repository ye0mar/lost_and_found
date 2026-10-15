<?php
/**
 * System Audit Trail & Activity Log
 * Administrator-Only Interface
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/functions.php';

require_admin();

$db = get_db_connection();

$search = trim($_GET['search'] ?? '');
$actionFilter = trim($_GET['action_filter'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(a.action LIKE :s1 OR a.details LIKE :s2 OR u.username LIKE :s3 OR u.full_name LIKE :s4)";
    $params['s1'] = "%$search%";
    $params['s2'] = "%$search%";
    $params['s3'] = "%$search%";
    $params['s4'] = "%$search%";
}

if ($actionFilter !== '') {
    $where[] = "a.action = :act";
    $params['act'] = $actionFilter;
}

$whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $db->prepare(
    "SELECT COUNT(*) FROM activity_logs a 
     LEFT JOIN users u ON a.user_id = u.id 
     $whereSql"
);
$countStmt->execute($params);
$totalRows = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalRows / $perPage));

$sql = "SELECT a.*, u.username, u.full_name, u.role 
        FROM activity_logs a
        LEFT JOIN users u ON a.user_id = u.id
        $whereSql
        ORDER BY a.created_at DESC
        LIMIT $perPage OFFSET $offset";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Distinct actions for dropdown
$actions = $db->query("SELECT DISTINCT action FROM activity_logs ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Activity Log';
$pageHeading = 'Security & Custody Audit Trail';
$activeNav = 'activity_log';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <!-- Filter Bar -->
        <form method="GET" action="<?= BASE_URL ?>/admin/activity_log.php" class="filter-toolbar">
            <div class="filter-input-wrap">
                <input type="text" name="search" class="form-control" placeholder="Search user, action, details..." value="<?= e($search) ?>">
            </div>

            <div class="filter-select-wrap">
                <select name="action_filter" class="form-control">
                    <option value="">All Action Types</option>
                    <?php foreach ($actions as $act): ?>
                        <option value="<?= e($act) ?>" <?= $actionFilter === $act ? 'selected' : '' ?>><?= e($act) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary">Filter</button>
                <?php if ($search || $actionFilter): ?>
                    <a href="<?= BASE_URL ?>/admin/activity_log.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="card">
            <div class="card-header">
                <div>
                    <span class="card-title">Immutable Audit Trail Records</span>
                    <span style="font-size: 12px; color: var(--text-muted); margin-left: 8px;">(<?= $totalRows ?> events logged)</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User Officer</th>
                            <th>Action Identifier</th>
                            <th>Target Entity</th>
                            <th>Event Details</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-muted);">No activity records found matching criteria.</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td style="font-size:12px;color:var(--text-secondary);white-space:nowrap;">
                                        <?= e($log['created_at']) ?>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;font-size:13px;"><?= e($log['full_name'] ?? 'System') ?></div>
                                        <div style="font-size:11px;color:var(--text-muted);">@<?= e($log['username'] ?? 'sys') ?> (<?= e($log['role'] ?? 'cron') ?>)</div>
                                    </td>
                                    <td>
                                        <code style="font-size:11.5px;background:var(--surface-secondary);padding:2px 6px;border-radius:4px;color:var(--accent-hover);">
                                            <?= e($log['action']) ?>
                                        </code>
                                    </td>
                                    <td style="font-size:12px;">
                                        <?= e($log['entity_type'] ?: '—') ?> <?= $log['entity_id'] ? '#' . (int)$log['entity_id'] : '' ?>
                                    </td>
                                    <td style="font-size:13px;color:var(--text-primary);max-width:320px;">
                                        <?= e($log['details'] ?? '—') ?>
                                    </td>
                                    <td style="font-size:11.5px;color:var(--text-muted);font-family:monospace;">
                                        <?= e($log['ip_address'] ?? '127.0.0.1') ?>
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
