<?php
/**
 * Authentication and Role-Based Authorization Provider
 * Lost & Found Management System
 */

declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once APP_ROOT . '/config/database.php';

/**
 * Initialize hardened PHP session with secure cookie parameters.
 */
function init_secure_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        $cookieParams = [
            'lifetime' => 86400, // 24 hours
            'path'     => '/',
            'domain'   => '',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        session_set_cookie_params($cookieParams);
        session_start();
    }
}

// Ensure session is started securely
init_secure_session();

/**
 * Check if the user is authenticated.
 * @return bool
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['username']);
}

/**
 * Get the currently logged-in user array or null.
 * @return array|null
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'        => (int)$_SESSION['user_id'],
        'username'  => (string)$_SESSION['username'],
        'email'     => (string)($_SESSION['email'] ?? ''),
        'full_name' => (string)($_SESSION['full_name'] ?? 'Authorized User'),
        'role'      => (string)($_SESSION['role'] ?? 'staff'),
        'status'    => (string)($_SESSION['status'] ?? 'active')
    ];
}

/**
 * Check if the currently logged-in user is an Administrator.
 * @return bool
 */
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['role'] === 'admin');
}

/**
 * Check if the currently logged-in user is Staff.
 * @return bool
 */
function is_staff(): bool {
    return is_logged_in() && ($_SESSION['role'] === 'staff');
}

/**
 * Require authentication on protected pages. Redirects to login with a flash message if not authenticated.
 * @return void
 */
function require_login(): void {
    if (!is_logged_in()) {
        if (!function_exists('set_flash')) {
            require_once APP_ROOT . '/includes/functions.php';
        }
        set_flash('error', 'Authentication required. Please sign in to access the system.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    // Check if account has been deactivated
    if (isset($_SESSION['status']) && $_SESSION['status'] !== 'active') {
        logout_user();
        if (!function_exists('set_flash')) {
            require_once APP_ROOT . '/includes/functions.php';
        }
        set_flash('error', 'Your account has been deactivated by an Administrator. Please contact security administration.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Require Administrator role. Strictly terminates or redirects unauthorized staff.
 * @return void
 */
function require_admin(): void {
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        if (!function_exists('set_flash')) {
            require_once APP_ROOT . '/includes/functions.php';
        }
        set_flash('error', 'Access Denied: You do not possess administrator credentials to access this section.');
        header('Location: ' . BASE_URL . '/lost_items/index.php');
        exit;
    }
}

/**
 * Safely authenticate a user and regenerate the session ID to prevent session fixation.
 * @param array $user
 * @return void
 */
function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $user['email'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['status']    = $user['status'];
    $_SESSION['logged_at'] = time();

    // Update last login timestamp in DB
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);
    } catch (Exception $e) {
        error_log('Failed to update last login: ' . $e->getMessage());
    }
}

/**
 * Invalidate and destroy session completely.
 * @return void
 */
function logout_user(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
}
