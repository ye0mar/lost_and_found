<?php
/**
 * CSRF Protection Utilities
 * Lost & Found Management System
 */

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate or retrieve the current session CSRF token.
 * @return string
 */
function get_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden HTML input containing the CSRF token.
 * @return void
 */
function csrf_field(): void {
    $token = htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8');
    echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Validate incoming CSRF token against session token.
 * @param string|null $token
 * @return bool
 */
function validate_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Enforce CSRF check on POST requests. Terminate with 403 on failure.
 * @return void
 */
function require_valid_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submittedToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!validate_csrf_token($submittedToken)) {
            http_response_code(403);
            die('Security Violation: Invalid or expired CSRF token. Please refresh the page and try again.');
        }
    }
}
