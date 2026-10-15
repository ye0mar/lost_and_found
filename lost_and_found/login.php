<?php
/**
 * Dedicated Authentication Screen
 * Lost & Found Management System
 */

declare(strict_types=1);

define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/csrf.php';
require_once APP_ROOT . '/includes/functions.php';

// Redirect if already authenticated
if (is_logged_in()) {
    header('Location: ' . (is_admin() ? BASE_URL . '/dashboard.php' : BASE_URL . '/lost_items/index.php'));
    exit;
}

$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    
    $loginInput = trim($_POST['login'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($loginInput) || empty($password)) {
        $errorMessage = 'Please enter both your username/email and password.';
    } else {
        try {
            $db = get_db_connection();
            // Lookup user by username OR email
            $stmt = $db->prepare("SELECT * FROM users WHERE (username = :login OR email = :login) LIMIT 1");
            $stmt->execute(['login' => $loginInput]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                if ($user['status'] !== 'active') {
                    $errorMessage = 'Your account has been deactivated. Contact an Administrator.';
                    log_activity('LOGIN_FAILED_DEACTIVATED', 'users', (int)$user['id'], 'Attempted login with deactivated account: ' . $loginInput);
                } else {
                    // Successful login
                    login_user($user);
                    log_activity('USER_LOGIN', 'users', (int)$user['id'], 'User successfully signed in from ' . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'));
                    set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');

                    if ($user['role'] === 'admin') {
                        header('Location: ' . BASE_URL . '/dashboard.php');
                    } else {
                        header('Location: ' . BASE_URL . '/lost_items/index.php');
                    }
                    exit;
                }
            } else {
                $errorMessage = 'Invalid username or password. Please verify your credentials.';
                log_activity('LOGIN_FAILED_INVALID_CREDENTIALS', 'users', null, 'Failed attempt for credential: ' . $loginInput);
            }
        } catch (Exception $e) {
            error_log('Login error: ' . $e->getMessage());
            $errorMessage = 'A system error occurred. Please verify your database connection in XAMPP.';
        }
    }
}

$sysName = get_system_setting('system_name', 'Lost & Found Management System');
$logoUrl = get_logo_url();
$faviconUrl = get_favicon_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — <?= e($sysName) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= e($faviconUrl) ?>">
    <link rel="shortcut icon" href="<?= e($faviconUrl) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forms.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <!-- Square Logo Container (Preserves aspect ratio, never cropped to circle) -->
        <div class="auth-logo-wrapper">
            <div class="auth-square-logo">
                <img src="<?= e($logoUrl) ?>" alt="Lost &amp; Found System Logo" onerror="this.onerror=null; this.src='<?= BASE_URL ?>/assets/images/logo-fallback.svg';">
            </div>
        </div>

        <div class="auth-header">
            <div class="auth-brand-name"><?= e($sysName) ?></div>
            <h2 class="auth-title">Welcome Back</h2>
            <p class="auth-description">Sign in to access the Lost &amp; Found Management System.</p>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="flash-alert error" style="margin-bottom: 20px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span><?= e($errorMessage) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <form method="POST" action="<?= BASE_URL ?>/login.php" autocomplete="on">
            <?php csrf_field(); ?>

            <div class="form-group" style="margin-bottom: 16px;">
                <label for="login" class="form-label required">Username or Email</label>
                <input type="text" id="login" name="login" class="form-control" placeholder="admin or officer.smith" value="<?= e($_POST['login'] ?? '') ?>" required autofocus>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label for="password" class="form-label required">Password</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••" required>
                    <button type="button" class="password-toggle-btn" data-target="password" aria-label="Toggle password view">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary auth-btn-block">
                <span>Sign In to System</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
        </form>

        <div class="auth-footer-note">
            <p>Access is restricted exclusively to authorized staff and administrators.<br>Public self-registration is disabled for security custody.</p>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
