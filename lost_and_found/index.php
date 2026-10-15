<?php
/**
 * Public Landing & Gateway Experience
 * Lost & Found Management System
 */

declare(strict_types=1);

define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/functions.php';

// Redirect authenticated users immediately to their respective workspace
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ' . BASE_URL . '/dashboard.php');
    } else {
        header('Location: ' . BASE_URL . '/lost_items/index.php');
    }
    exit;
}

$sysName = get_system_setting('system_name', 'Lost & Found Management System');
$orgName = get_system_setting('organization_name', 'Campus Security & Property Custody');
$logoUrl = get_logo_url();
$faviconUrl = get_favicon_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($sysName) ?> — Authorized Custody Portal</title>
    <link rel="icon" type="image/svg+xml" href="<?= e($faviconUrl) ?>">
    <link rel="shortcut icon" href="<?= e($faviconUrl) ?>">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body class="auth-body">
    <!-- Liquid-Glass Top Floating Brand Nav -->
    <header class="glass-panel" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); width: calc(100% - 40px); max-width: 900px; border-radius: 9999px; padding: 10px 24px; display: flex; align-items: center; justify-content: space-between; z-index: 50;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 34px; height: 34px; border-radius: 8px; overflow: hidden; background: #172033; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border-color);">
                <img src="<?= e($logoUrl) ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 0;">
            </div>
            <span style="font-family: 'Outfit', sans-serif; font-size: 15px; font-weight: 700; color: #172033; letter-spacing: -0.01em;">
                <?= e($sysName) ?>
            </span>
        </div>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-sm" style="border-radius: 9999px; padding: 7px 18px;">
            <span>Staff Sign In</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </header>

    <!-- Hero Content -->
    <main class="landing-hero" style="margin-top: 100px;">
        <div class="landing-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
            <span>Internal Custody &amp; Property Recovery Network</span>
        </div>
        <h1>Lost something? Find your way back to it.</h1>
        <p>A simple, organized way for authorized staff to record, track, and reconnect lost belongings with the people looking for them.</p>
        
        <div style="margin-top: 28px; display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary" style="padding: 12px 28px; font-size: 15px; font-weight: 600;">
                <span>Sign In to Continue</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
            </a>
        </div>
    </main>

    <!-- Restrained Recovery Info Surface -->
    <section class="glass-panel" style="max-width: 860px; width: 100%; border-radius: 16px; padding: 32px; margin-bottom: 40px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px;">
            <div style="display: flex; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 8px; background: #DCEBFF; color: #2563EB; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 15px; margin-bottom: 4px;">Smart Match Engine</h3>
                    <p style="font-size: 13px; color: #64748B;">Automated correlation suggests candidate found items based on categories, descriptions, and date proximity.</p>
                </div>
            </div>

            <div style="display: flex; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 8px; background: #DCFCE7; color: #166534; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 15px; margin-bottom: 4px;">Secure Custody Vault</h3>
                    <p style="font-size: 13px; color: #64748B;">Role-based custody access ensures only authorized staff log and verify returned items with full audit trail logging.</p>
                </div>
            </div>

            <div style="display: flex; gap: 14px;">
                <div style="width: 40px; height: 40px; border-radius: 8px; background: #EDE9FE; color: #6D28D9; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <div>
                    <h3 style="font-size: 15px; margin-bottom: 4px;">SysAdmin Monitoring</h3>
                    <p style="font-size: 13px; color: #64748B;">Integrated database backup, restore, real-time health checks, and user administration for capstone demonstration.</p>
                </div>
            </div>
        </div>
    </section>

    <footer style="text-align: center; font-size: 12px; color: #94A3B8;">
        &copy; <?= date('Y') ?> <?= e($orgName) ?>. Authorized Personnel Only.
    </footer>
</body>
</html>
