<?php
/**
 * Role-Restricted Application Sidebar Component
 * Lost & Found Management System
 */

declare(strict_types=1);

$user = current_user();
$activeNav = $activeNav ?? '';
$logoUrl = get_logo_url();
?>
<aside class="app-sidebar">
    <!-- Brand / Square Logo Header -->
    <div class="sidebar-header">
        <a href="<?= is_admin() ? BASE_URL . '/dashboard.php' : BASE_URL . '/lost_items/index.php' ?>" class="brand-link">
            <div class="brand-logo-container">
                <img src="<?= e($logoUrl) ?>" alt="Lost &amp; Found System Logo" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/logo-fallback.svg';">
            </div>
            <div class="brand-info">
                <span class="brand-title">Lost &amp; Found</span>
                <span class="brand-subtitle"><?= is_admin() ? 'Administration' : 'Staff Custody' ?></span>
            </div>
        </a>
    </div>

    <!-- Navigation List strictly conditioned on user role -->
    <nav class="sidebar-nav">
        <?php if (is_admin()): ?>
            <!-- Administrator Navigation Item 1 -->
            <a href="<?= BASE_URL ?>/dashboard.php" class="nav-link <?= $activeNav === 'home' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Home</span>
            </a>
        <?php endif; ?>

        <!-- Common Items for Both Roles -->
        <a href="<?= BASE_URL ?>/lost_items/index.php" class="nav-link <?= $activeNav === 'lost_items' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <span>Lost Items</span>
        </a>

        <a href="<?= BASE_URL ?>/lost_items/report.php" class="nav-link <?= $activeNav === 'report_lost' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            <span>Report Lost Item</span>
        </a>

        <a href="<?= BASE_URL ?>/found_items/index.php" class="nav-link <?= $activeNav === 'found_items' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            <span>Found Items</span>
        </a>

        <a href="<?= BASE_URL ?>/found_items/add.php" class="nav-link <?= $activeNav === 'add_found' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Add Found Item</span>
        </a>

        <?php if (is_admin()): ?>
            <!-- Administrator-Only Administration Section -->
            <div class="nav-section-title">Administration</div>

            <a href="<?= BASE_URL ?>/admin/settings.php" class="nav-link <?= $activeNav === 'settings' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                <span>Settings</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/manage_users.php" class="nav-link <?= $activeNav === 'manage_users' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Manage Users</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/activity_log.php" class="nav-link <?= $activeNav === 'activity_log' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span>Activity Log</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/backup_recovery.php" class="nav-link <?= $activeNav === 'backup_recovery' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Backup &amp; Recovery</span>
            </a>

            <a href="<?= BASE_URL ?>/admin/system_monitor.php" class="nav-link <?= $activeNav === 'system_monitor' ? 'active' : '' ?>">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span>System Monitor</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-user-card">
            <div class="user-avatar-badge">
                <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?= e($user['full_name'] ?? 'Staff') ?></div>
                <div class="user-role-badge"><?= e($user['role'] ?? 'staff') ?></div>
            </div>
        </div>
    </div>
</aside>
