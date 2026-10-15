<?php
/**
 * Administrator Home Dashboard
 * Lost & Found Management System
 */

declare(strict_types=1);

define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/functions.php';

// Enforce strict Administrator-only access
require_admin();

$db = get_db_connection();

// 1. Fetch Real Database Statistics
$totalLost = (int)$db->query("SELECT COUNT(*) FROM lost_items")->fetchColumn();
$totalFound = (int)$db->query("SELECT COUNT(*) FROM found_items")->fetchColumn();
$recentLost = (int)$db->query("SELECT COUNT(*) FROM lost_items WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();
$totalMatched = (int)$db->query("SELECT COUNT(*) FROM found_items WHERE status = 'Matched'")->fetchColumn();
$totalReturned = (int)$db->query("SELECT COUNT(*) FROM found_items WHERE status = 'Returned'")->fetchColumn();

// 2. Fetch Recent Lost Items (Limit 5)
$stmtLost = $db->query(
    "SELECT l.*, c.name AS category_name, u.full_name AS reporter_name 
     FROM lost_items l
     LEFT JOIN categories c ON l.category_id = c.id
     LEFT JOIN users u ON l.reported_by_user_id = u.id
     ORDER BY l.created_at DESC LIMIT 5"
);
$recentLostItems = $stmtLost->fetchAll();

// 3. Fetch Recent Found Items (Limit 5)
$stmtFound = $db->query(
    "SELECT f.*, c.name AS category_name, u.full_name AS recorder_name 
     FROM found_items f
     LEFT JOIN categories c ON f.category_id = c.id
     LEFT JOIN users u ON f.recorded_by_user_id = u.id
     ORDER BY f.created_at DESC LIMIT 5"
);
$recentFoundItems = $stmtFound->fetchAll();

// 4. Fetch Recent Activity Logs (Limit 6)
$stmtLogs = $db->query(
    "SELECT a.*, u.full_name, u.username, u.role 
     FROM activity_logs a
     LEFT JOIN users u ON a.user_id = u.id
     ORDER BY a.created_at DESC LIMIT 6"
);
$recentActivities = $stmtLogs->fetchAll();

$pageTitle = 'Dashboard';
$pageHeading = 'Operations Dashboard';
$activeNav = 'home';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <!-- Quick Actions Ribbon -->
        <div class="quick-actions-bar">
            <span class="quick-actions-title">Quick Custody Actions:</span>
            <a href="<?= BASE_URL ?>/lost_items/report.php" class="btn btn-primary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Report Lost Item</span>
            </a>
            <a href="<?= BASE_URL ?>/found_items/add.php" class="btn btn-secondary btn-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                <span>Add Found Item</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/system_monitor.php" class="btn btn-secondary btn-sm" style="margin-left:auto;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/></svg>
                <span>System Monitor</span>
            </a>
        </div>

        <!-- Real Statistics Grid -->
        <div class="metrics-grid">
            <div class="metric-card accent-blue">
                <div class="metric-header">
                    <span class="metric-label">Total Lost Items</span>
                    <div class="metric-icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </div>
                </div>
                <div class="metric-value"><?= number_format($totalLost) ?></div>
                <div class="metric-footer"><?= $recentLost ?> reported in past 7 days</div>
            </div>

            <div class="metric-card accent-emerald">
                <div class="metric-header">
                    <span class="metric-label">Total Found Items</span>
                    <div class="metric-icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                </div>
                <div class="metric-value"><?= number_format($totalFound) ?></div>
                <div class="metric-footer">Safely registered in custody</div>
            </div>

            <div class="metric-card accent-purple">
                <div class="metric-header">
                    <span class="metric-label">Items Matched</span>
                    <div class="metric-icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                    </div>
                </div>
                <div class="metric-value"><?= number_format($totalMatched) ?></div>
                <div class="metric-footer">Potential or confirmed matches</div>
            </div>

            <div class="metric-card accent-amber">
                <div class="metric-header">
                    <span class="metric-label">Returned / Claimed</span>
                    <div class="metric-icon-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <div class="metric-value"><?= number_format($totalReturned) ?></div>
                <div class="metric-footer">Successfully restored to owners</div>
            </div>
        </div>

        <!-- Split Grid: Recent Items & Activity Log Feed -->
        <div class="dashboard-split-grid">
            <!-- Left Column: Tables for Recent Items -->
            <div>
                <!-- Recent Lost Items -->
                <div class="card">
                    <div class="card-header">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span class="card-title">Recently Reported Lost Items</span>
                        </div>
                        <a href="<?= BASE_URL ?>/lost_items/index.php" style="font-size: 12.5px; font-weight: 500;">View All &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Date Lost</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentLostItems)): ?>
                                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px;">No lost items reported yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentLostItems as $item): ?>
                                        <tr>
                                            <td style="font-weight:600;">
                                                <a href="<?= BASE_URL ?>/lost_items/view.php?id=<?= $item['id'] ?>"><?= e($item['item_name']) ?></a>
                                                <div style="font-size:11.5px;color:var(--text-muted);font-weight:normal;"><?= e($item['location_lost']) ?></div>
                                            </td>
                                            <td><?= e($item['category_name'] ?? 'General') ?></td>
                                            <td><?= format_date($item['date_lost']) ?></td>
                                            <td><?= render_status_badge($item['status']) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/lost_items/view.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">Inspect</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Found Items -->
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Recently Received Found Items</span>
                        <a href="<?= BASE_URL ?>/found_items/index.php" style="font-size: 12.5px; font-weight: 500;">View All &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Date Found</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentFoundItems)): ?>
                                    <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:24px;">No found items registered yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recentFoundItems as $item): ?>
                                        <tr>
                                            <td style="font-weight:600;">
                                                <a href="<?= BASE_URL ?>/found_items/view.php?id=<?= $item['id'] ?>"><?= e($item['item_name']) ?></a>
                                                <div style="font-size:11.5px;color:var(--text-muted);font-weight:normal;"><?= e($item['location_found']) ?></div>
                                            </td>
                                            <td><?= e($item['category_name'] ?? 'General') ?></td>
                                            <td><?= format_date($item['date_found']) ?></td>
                                            <td><?= render_status_badge($item['status']) ?></td>
                                            <td>
                                                <a href="<?= BASE_URL ?>/found_items/view.php?id=<?= $item['id'] ?>" class="btn btn-secondary btn-sm">Inspect</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Audit Activity Feed & System Summary -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Recent Activity Audit</span>
                        <a href="<?= BASE_URL ?>/admin/activity_log.php" style="font-size: 12.5px; font-weight: 500;">Full Log &rarr;</a>
                    </div>
                    <div class="activity-feed-list">
                        <?php if (empty($recentActivities)): ?>
                            <div style="color:var(--text-muted);font-size:13px;text-align:center;padding:20px;">No recorded events yet.</div>
                        <?php else: ?>
                            <?php foreach ($recentActivities as $act): ?>
                                <div class="activity-feed-item">
                                    <div class="activity-dot"></div>
                                    <div class="activity-content">
                                        <div class="activity-action"><?= e($act['action']) ?></div>
                                        <div class="activity-details"><?= e($act['details'] ?? 'Executed event') ?></div>
                                        <div class="activity-time">
                                            By <?= e($act['full_name'] ?? 'System') ?> &bull; <?= e($act['created_at']) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Capstone Maintenance Notice Card -->
                <div class="card" style="background: #FAFBFC;">
                    <h4 style="font-size: 14px; margin-bottom: 8px;">Capstone Demonstration Status</h4>
                    <p style="font-size: 12.5px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 14px;">
                        System operational in local XAMPP environment. Database connected via MySQL PDO. All audit records, authentication hashing, and CSRF barriers active.
                    </p>
                    <a href="<?= BASE_URL ?>/admin/backup_recovery.php" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Manage Database Backups</span>
                    </a>
                </div>
            </div>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
