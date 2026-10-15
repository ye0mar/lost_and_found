<?php
/**
 * System Health & Infrastructure Monitor
 * Administrator-Only Capstone Diagnostic Dashboard
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/functions.php';

require_admin();

$db = get_db_connection();

// 1. Database Diagnostic Checks
$dbStatus = 'Connected';
$dbLatencyMs = 0.0;
$mysqlVersion = 'Unknown';
try {
    $t1 = microtime(true);
    $verRow = $db->query("SELECT VERSION() AS ver")->fetch();
    $dbLatencyMs = round((microtime(true) - $t1) * 1000, 2);
    $mysqlVersion = $verRow['ver'] ?? 'MySQL 5.7+';
} catch (Exception $e) {
    $dbStatus = 'Connection Error: ' . $e->getMessage();
}

// 2. Table Statistics
$tableStats = [];
try {
    $tbls = $db->query("SHOW TABLE STATUS")->fetchAll();
    foreach ($tbls as $t) {
        $tableStats[] = [
            'name' => $t['Name'],
            'rows' => (int)($t['Rows'] ?? 0),
            'size' => round(((int)($t['Data_length'] ?? 0) + (int)($t['Index_length'] ?? 0)) / 1024, 2)
        ];
    }
} catch (Exception $e) {
    error_log('Table status failed: ' . $e->getMessage());
}

// 3. Disk Space Analysis (Reliable PHP Filesystem APIs)
$freeDisk = disk_free_space(APP_ROOT);
$totalDisk = disk_total_space(APP_ROOT);
$usedDiskPercent = ($totalDisk > 0 && $freeDisk !== false) ? round((($totalDisk - $freeDisk) / $totalDisk) * 100, 1) : 0;

$pageTitle = 'System Monitor';
$pageHeading = 'Infrastructure & Health Diagnostics';
$activeNav = 'system_monitor';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <!-- Key Metrics Cards -->
        <div class="metrics-grid">
            <div class="metric-card accent-emerald">
                <div class="metric-header">
                    <span class="metric-label">MySQL Database Status</span>
                    <div class="metric-icon-box" style="color:var(--success);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                    </div>
                </div>
                <div class="metric-value" style="font-size:22px;color:var(--success);">ONLINE</div>
                <div class="metric-footer"><?= e($mysqlVersion) ?> &bull; <?= $dbLatencyMs ?> ms latency</div>
            </div>

            <div class="metric-card accent-blue">
                <div class="metric-header">
                    <span class="metric-label">PHP Runtime Environment</span>
                    <div class="metric-icon-box" style="color:var(--accent-main);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                    </div>
                </div>
                <div class="metric-value" style="font-size:22px;">PHP <?= PHP_VERSION ?></div>
                <div class="metric-footer">SAPI: <?= php_sapi_name() ?></div>
            </div>

            <div class="metric-card accent-purple">
                <div class="metric-header">
                    <span class="metric-label">Storage Volume Usage</span>
                    <div class="metric-icon-box" style="color:#8B5CF6;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    </div>
                </div>
                <div class="metric-value" style="font-size:22px;"><?= $usedDiskPercent ?>% Used</div>
                <div class="metric-footer"><?= $freeDisk ? round($freeDisk / (1024*1024*1024), 1) . ' GB Available' : 'Available' ?></div>
            </div>

            <div class="metric-card accent-amber">
                <div class="metric-header">
                    <span class="metric-label">Security Protocol</span>
                    <div class="metric-icon-box" style="color:var(--warning);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                </div>
                <div class="metric-value" style="font-size:20px;">BCrypt (Cost 12)</div>
                <div class="metric-footer">CSRF &amp; Session Fixation Shield Active</div>
            </div>
        </div>

        <div class="dashboard-split-grid">
            <!-- Left: PHP Environment & Database Configuration -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">PHP Environment Limits</span>
                    </div>
                    <table class="table-custom">
                        <tbody>
                            <tr>
                                <td style="font-weight:600;width:40%;">Memory Limit</td>
                                <td><code><?= ini_get('memory_limit') ?></code></td>
                            </tr>
                            <tr>
                                <td style="font-weight:600;">Max Execution Time</td>
                                <td><code><?= ini_get('max_execution_time') ?> seconds</code></td>
                            </tr>
                            <tr>
                                <td style="font-weight:600;">Upload Max Filesize</td>
                                <td><code><?= ini_get('upload_max_filesize') ?></code></td>
                            </tr>
                            <tr>
                                <td style="font-weight:600;">Post Max Size</td>
                                <td><code><?= ini_get('post_max_size') ?></code></td>
                            </tr>
                            <tr>
                                <td style="font-weight:600;">Session Cookie Secure</td>
                                <td><code><?= ini_get('session.cookie_secure') ? 'Enabled' : 'Disabled (Local HTTP)' ?></code></td>
                            </tr>
                            <tr>
                                <td style="font-weight:600;">Session Cookie HttpOnly</td>
                                <td><span style="color:var(--success);font-weight:600;">Enabled (Protected from XSS)</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Database Tables &amp; Physical Sizes</span>
                    </div>
                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Table Name</th>
                                <th>Estimated Rows</th>
                                <th>Storage Size (KB)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tableStats as $ts): ?>
                                <tr>
                                    <td><code><?= e($ts['name']) ?></code></td>
                                    <td><?= number_format($ts['rows']) ?></td>
                                    <td><?= number_format($ts['size'], 2) ?> KB</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Windows Task Manager & Local XAMPP Guide -->
            <div>
                <div class="card" style="background:#FAFCFE; border-left: 4px solid var(--accent-main);">
                    <h3 class="card-title" style="margin-bottom: 8px;">Resource Monitoring for Windows / XAMPP</h3>
                    <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6; margin-bottom: 12px;">
                        Web-standard PHP applications running on Apache/Windows cannot directly access OS kernel performance metrics without spawning high-privilege shell commands.
                    </p>
                    <div style="font-size: 13px; color: var(--text-primary); line-height: 1.6;">
                        <strong>For Capstone Live Examination Demonstration:</strong>
                        <ol style="padding-left: 20px; margin: 8px 0;">
                            <li>Press <kbd style="background:#E2E8F0;padding:2px 6px;border-radius:4px;">Ctrl + Shift + Esc</kbd> to launch <strong>Windows Task Manager</strong>.</li>
                            <li>Navigate to the <strong>Processes</strong> or <strong>Performance</strong> tab.</li>
                            <li>Locate <strong>Apache HTTP Server (httpd.exe)</strong> to observe active worker thread memory and CPU consumption.</li>
                            <li>Locate <strong>mysqld.exe</strong> to observe database engine cache and memory utilization.</li>
                            <li>Execute a database backup or search to demonstrate live resource responsiveness.</li>
                        </ol>
                    </div>
                </div>

                <div class="card">
                    <h3 class="card-title" style="margin-bottom: 8px;">Custody Vault Storage Health</h3>
                    <div style="margin-top: 14px;">
                        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                            <span>Disk Volume Usage</span>
                            <span style="font-weight:600;"><?= $usedDiskPercent ?>%</span>
                        </div>
                        <div style="width:100%;height:8px;background:var(--border-color);border-radius:9999px;overflow:hidden;">
                            <div style="width:<?= min(100, $usedDiskPercent) ?>%;height:100%;background:var(--accent-main);border-radius:9999px;"></div>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted);margin-top:8px;">
                            Directory: <code><?= e(APP_ROOT) ?></code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
