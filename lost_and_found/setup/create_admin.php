<?php
/**
 * Initial Administrator Provisioning Utility (Setup Script)
 * Capstone Project: System Administration & Maintenance
 * 
 * SECURITY DIRECTIVE:
 * Once the initial administrator account is provisioned, this file
 * must be deleted or moved outside the web root (C:\xampp\htdocs).
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/csrf.php';
require_once APP_ROOT . '/includes/functions.php';

$db = get_db_connection();

// Check if an active administrator account already exists
$adminCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'")->fetchColumn();
$isLocked = ($adminCount > 0);

$message = null;
$messageType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLocked) {
    require_valid_csrf();
    
    $username = strtolower(trim($_POST['username'] ?? ''));
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $fullName = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($fullName) || empty($password)) {
        $message = 'All fields are strictly required.';
        $messageType = 'error';
    } elseif (strlen($password) < 8) {
        $message = 'Administrator password must be at least 8 characters long.';
        $messageType = 'error';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare(
                "INSERT INTO users (username, email, full_name, role, password_hash, status, created_at)
                 VALUES (:u, :e, :fn, 'admin', :h, 'active', NOW())"
            );
            $stmt->execute([
                'u'  => $username,
                'e'  => $email,
                'fn' => $fullName,
                'h'  => $hash
            ]);

            log_activity('SETUP_INITIAL_ADMIN', 'users', (int)$db->lastInsertId(), "Initial admin account provisioned: $username");
            $message = "Initial Administrator '$username' successfully provisioned! Please delete or secure this setup file.";
            $messageType = 'success';
            $isLocked = true;
        } catch (Exception $e) {
            $message = 'Error creating admin account: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

$logoUrl = get_logo_url();
$faviconUrl = get_favicon_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Provisioning — Setup</title>
    <link rel="icon" type="image/svg+xml" href="<?= e($faviconUrl) ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forms.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body class="auth-body">
    <div class="auth-card" style="max-width: 500px;">
        <div class="auth-logo-wrapper">
            <div class="auth-square-logo">
                <img src="<?= e($logoUrl) ?>" alt="Logo">
            </div>
        </div>

        <div class="auth-header">
            <div class="auth-brand-name">Initial Deployment Setup</div>
            <h2 class="auth-title">Administrator Provisioning</h2>
            <p class="auth-description">One-time initial administrator bootstrap for System Administration Capstone.</p>
        </div>

        <?php if ($message): ?>
            <div class="flash-alert <?= e($messageType) ?>" style="margin-bottom: 20px;">
                <span><?= e($message) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($isLocked): ?>
            <div class="card" style="background:#FAFCFE; border-left: 4px solid var(--accent-main); padding: 18px; margin-bottom: 20px;">
                <h4 style="font-size: 14px; margin-bottom: 6px;">Setup Endpoint Locked</h4>
                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 12px;">
                    An active Administrator account is already registered in the database. To prevent unauthorized account creation, this setup endpoint is automatically locked.
                </p>
                <div style="font-size: 12px; color: var(--error); font-weight: 600; margin-bottom: 12px;">
                    Security Notice: Please delete or rename <code>lost_and_found/setup/create_admin.php</code> now.
                </div>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary" style="width: 100%;">
                    <span>Proceed to Sign In</span>
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?= BASE_URL ?>/setup/create_admin.php">
                <?php csrf_field(); ?>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="full_name" class="form-label required">Full Legal Name</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Lead Administrator" required autofocus>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="username" class="form-label required">Administrator Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="e.g. admin" required>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="email" class="form-label required">Official Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="e.g. admin@organization.edu" required>
                </div>

                <div class="form-group" style="margin-bottom: 14px;">
                    <label for="password" class="form-label required">Master Password</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="8" required>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label for="confirm_password" class="form-label required">Confirm Master Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="8" required>
                </div>

                <button type="submit" class="btn btn-primary auth-btn-block">
                    <span>Provision First Administrator Account</span>
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
