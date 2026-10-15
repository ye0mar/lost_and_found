<?php
/**
 * Safe Logout & Session Invalidation Endpoint
 */

declare(strict_types=1);

define('APP_ROOT', __DIR__);
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/functions.php';

if (is_logged_in()) {
    $user = current_user();
    log_activity('USER_LOGOUT', 'users', $user['id'], 'User logged out securely.');
    logout_user();
}

set_flash('success', 'You have been safely signed out.');
header('Location: ' . BASE_URL . '/login.php');
exit;
