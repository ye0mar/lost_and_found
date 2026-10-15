<?php
/**
 * Top Liquid-Glass Navigation Bar Component
 */

declare(strict_types=1);

$user = current_user();
$pageHeading = $pageHeading ?? 'Overview';
?>
<header class="app-topbar glass-panel">
    <div class="topbar-left">
        <button type="button" class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <h1 class="topbar-page-title"><?= e($pageHeading) ?></h1>
    </div>

    <div class="topbar-right">
        <?php if ($user): ?>
            <!-- User Profile Dropdown -->
            <div class="profile-menu">
                <button type="button" class="profile-trigger" id="profileTrigger" aria-expanded="false">
                    <div class="user-avatar-badge">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <span style="font-weight: 500;"><?= e($user['full_name']) ?></span>
                    <span style="font-size: 11px; padding: 2px 6px; border-radius: 4px; background: <?= is_admin() ? '#DCEBFF' : '#E2E8F0' ?>; color: <?= is_admin() ? '#1D4ED8' : '#475569' ?>; font-weight: 600; text-transform: uppercase;">
                        <?= e($user['role']) ?>
                    </span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </button>

                <div class="profile-dropdown-card" id="profileDropdown">
                    <div style="padding: 8px 12px; border-bottom: 1px solid var(--border-light); margin-bottom: 4px;">
                        <div style="font-size: 12px; color: var(--text-secondary);">Signed in as</div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?= e($user['email'] ?: $user['username']) ?>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/profile/index.php" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>Account Profile</span>
                    </a>
                    <?php if (is_admin()): ?>
                        <a href="<?= BASE_URL ?>/admin/settings.php" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            <span>System Settings</span>
                        </a>
                    <?php endif; ?>
                    <div class="dropdown-divider"></div>
                    <a href="<?= BASE_URL ?>/logout.php" class="dropdown-item" style="color: var(--error);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        <span>Sign Out</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>
