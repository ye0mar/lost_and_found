<?php
/**
 * Database Configuration & Connection Provider
 * Lost & Found Management System
 * Capstone Project: System Administration and Maintenance
 */

// Strict error reporting
declare(strict_types=1);

// Prevent direct script execution if accessed outside root
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

// Database Credentials for Local XAMPP Environment
// Default XAMPP MySQL host: 127.0.0.1 or localhost, user: root, password: '' (empty)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'lost_and_found_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// Base URL Configuration for C:\xampp\htdocs\lost_and_found
// Works dynamically regardless of Apache port (e.g., http://localhost/lost_and_found or http://localhost:8080/lost_and_found)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Check if script is running in /lost_and_found subfolder
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $baseSubdir = '/lost_and_found';
    
    if (strpos($scriptDir, '/lost_and_found') !== false) {
        define('BASE_URL', $protocol . $host . '/lost_and_found');
    } else {
        define('BASE_URL', $protocol . $host);
    }
}

/**
 * Returns a singleton PDO database connection instance.
 * @return PDO
 */
function get_db_connection(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Safe error handling without revealing credentials
            error_log('Database Connection Error: ' . $e->getMessage());
            
            // Present a clear diagnostic screen for student/administrator during Capstone setup
            http_response_code(500);
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Database Connection Error - Lost & Found System</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #F5F7FA; color: #172033; padding: 40px; margin: 0; }
                    .error-card { max-width: 620px; margin: 60px auto; background: #fff; border: 1px solid #E2E8F0; border-radius: 12px; padding: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
                    h2 { color: #C2414B; margin-top: 0; font-size: 22px; }
                    p { line-height: 1.6; color: #64748B; font-size: 15px; }
                    .code-box { background: #F0F3F7; padding: 14px; border-radius: 8px; font-family: monospace; font-size: 13px; color: #172033; margin: 16px 0; border: 1px solid #E2E8F0; }
                    ul { color: #172033; font-size: 14px; line-height: 1.7; padding-left: 20px; }
                </style>
            </head>
            <body>
                <div class="error-card">
                    <h2>Database Connection Error</h2>
                    <p>The system was unable to connect to the MySQL database server. This is expected if you have not yet started MySQL in XAMPP or imported the database schema.</p>
                    <div class="code-box">Database: <?= htmlspecialchars(DB_NAME) ?> on <?= htmlspecialchars(DB_HOST) ?>:<?= htmlspecialchars(DB_PORT) ?></div>
                    <h3>Resolution Steps:</h3>
                    <ul>
                        <li>Open the <strong>XAMPP Control Panel</strong> and click <strong>Start</strong> next to MySQL.</li>
                        <li>Open <strong>phpMyAdmin</strong> (<a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a>).</li>
                        <li>Import the database file located at: <code>database/lost_and_found_db.sql</code>.</li>
                        <li>Ensure <code>config/database.php</code> credentials match your local MySQL configuration.</li>
                    </ul>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }
    
    return $pdo;
}
