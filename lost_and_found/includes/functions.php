<?php
/**
 * Core Helper Functions
 * Lost & Found Management System
 */

declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once APP_ROOT . '/config/database.php';

/**
 * Escape output for safe HTML rendering to prevent Cross-Site Scripting (XSS).
 * @param mixed $value
 * @return string
 */
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Set a flash notification message in the session.
 * @param string $type success|error|warning|info
 * @param string $message
 * @return void
 */
function set_flash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash_messages'][] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Retrieve and clear all queued flash messages.
 * @return array
 */
function get_flash_messages(): array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Log an event to the activity_logs table for audit trail and capstone demonstration.
 * @param string $action
 * @param string|null $entityType
 * @param int|null $entityId
 * @param string|null $details
 * @return void
 */
function log_activity(string $action, ?string $entityType = null, ?int $entityId = null, ?string $details = null): void {
    try {
        $db = get_db_connection();
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

        $stmt = $db->prepare(
            "INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent, created_at)
             VALUES (:user_id, :action, :entity_type, :entity_id, :details, :ip_address, :user_agent, NOW())"
        );
        $stmt->execute([
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'details'     => $details,
            'ip_address'  => $ip,
            'user_agent'  => $ua
        ]);
    } catch (Exception $e) {
        error_log('Failed to write activity log: ' . $e->getMessage());
    }
}

/**
 * Retrieve a system setting value from the database with a fallback default.
 * @param string $key
 * @param string $default
 * @return string
 */
function get_system_setting(string $key, string $default = ''): string {
    static $cache = [];
    if (isset($cache[$key])) {
        return $cache[$key];
    }
    try {
        $db = get_db_connection();
        $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :k LIMIT 1");
        $stmt->execute(['k' => $key]);
        $val = $stmt->fetchColumn();
        if ($val !== false) {
            $cache[$key] = (string)$val;
            return (string)$val;
        }
    } catch (Exception $e) {
        error_log('Failed to fetch setting ' . $key . ': ' . $e->getMessage());
    }
    return $default;
}

/**
 * Centralized Logo Path Resolver
 * Preserves the user's logo file location and provides graceful fallbacks.
 * @return string
 */
function get_logo_url(): string {
    // 1. Check picture/logo.jpg
    if (file_exists(APP_ROOT . '/picture/logo.jpg')) {
        return BASE_URL . '/picture/logo.jpg';
    }
    // 2. Check assets/images/logo.png
    if (file_exists(APP_ROOT . '/assets/images/logo.png')) {
        return BASE_URL . '/assets/images/logo.png';
    }
    // 3. Check assets/images/logo.jpg
    if (file_exists(APP_ROOT . '/assets/images/logo.jpg')) {
        return BASE_URL . '/assets/images/logo.jpg';
    }
    // 4. Check picture/logo.svg
    if (file_exists(APP_ROOT . '/picture/logo.svg')) {
        return BASE_URL . '/picture/logo.svg';
    }
    // 5. Default fallback SVG
    return BASE_URL . '/assets/images/logo-fallback.svg';
}

/**
 * Centralized Favicon Path Resolver
 * @return string
 */
function get_favicon_url(): string {
    if (file_exists(APP_ROOT . '/picture/logo.jpg')) {
        return BASE_URL . '/picture/logo.jpg';
    }
    if (file_exists(APP_ROOT . '/assets/images/favicon.png')) {
        return BASE_URL . '/assets/images/favicon.png';
    }
    if (file_exists(APP_ROOT . '/picture/logo.svg')) {
        return BASE_URL . '/picture/logo.svg';
    }
    return get_logo_url();
}

/**
 * Handle secure image uploads with size, MIME type, and safe name generation.
 * @param array $file $_FILES['image']
 * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function handle_image_upload(array $file): array {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'path' => null, 'error' => 'Invalid file parameter.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'path' => null, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => null, 'error' => 'File upload error code: ' . $file['error']];
    }

    // Limit size (default 5MB)
    $maxBytes = 5 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'path' => null, 'error' => 'Uploaded image exceeds maximum allowed size of 5MB.'];
    }

    // Validate MIME type safely
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!array_key_exists($mime, $allowedMimes)) {
        return ['success' => false, 'path' => null, 'error' => 'Invalid image format. Only JPG, PNG, and WebP are accepted.'];
    }

    $ext = $allowedMimes[$mime];
    $uploadDir = APP_ROOT . '/uploads/items';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate non-guessable safe filename
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'path' => null, 'error' => 'Failed to save uploaded image to custody storage.'];
    }

    return ['success' => true, 'path' => 'uploads/items/' . $filename, 'error' => null];
}

/**
 * Intelligent Matching Engine
 * Calculates possible matches between a lost item and available found items.
 * @param int $lostItemId
 * @return array
 */
function find_suggested_matches(int $lostItemId): array {
    $db = get_db_connection();
    
    // Fetch target lost item
    $stmt = $db->prepare("SELECT * FROM lost_items WHERE id = :id");
    $stmt->execute(['id' => $lostItemId]);
    $lost = $stmt->fetch();
    if (!$lost) {
        return [];
    }

    // Fetch candidate found items that are currently Available or Suggested
    $candStmt = $db->prepare(
        "SELECT f.*, c.name AS category_name 
         FROM found_items f
         LEFT JOIN categories c ON f.category_id = c.id
         WHERE f.status IN ('Available', 'Matched')
         ORDER BY f.date_found DESC"
    );
    $candStmt->execute();
    $candidates = $candStmt->fetchAll();

    $matches = [];
    $lostWords = array_unique(preg_split('/[\s,\.\-]+/', strtolower($lost['item_name'] . ' ' . $lost['description'])));
    $lostWords = array_filter($lostWords, function($w) { return strlen($w) > 2; });

    foreach ($candidates as $cand) {
        $score = 0;
        $reasons = [];

        // 1. Same category boost (40 points)
        if (!empty($lost['category_id']) && $lost['category_id'] == $cand['category_id']) {
            $score += 40;
            $reasons[] = 'Matching category';
        }

        // 2. Keyword overlap in name & description (up to 40 points)
        $candWords = array_unique(preg_split('/[\s,\.\-]+/', strtolower($cand['item_name'] . ' ' . $cand['description'])));
        $commonWords = array_intersect($lostWords, $candWords);
        if (!empty($commonWords)) {
            $wordScore = min(count($commonWords) * 12, 40);
            $score += $wordScore;
            $reasons[] = count($commonWords) . ' keyword match (' . implode(', ', array_slice($commonWords, 0, 3)) . ')';
        }

        // 3. Location proximity clue (up to 15 points)
        if (stripos($cand['location_found'], $lost['location_lost']) !== false ||
            stripos($lost['location_lost'], $cand['location_found']) !== false) {
            $score += 15;
            $reasons[] = 'Proximity in reported location';
        }

        // 4. Temporal consistency (Found date on or after Lost date) (5 points)
        if ($cand['date_found'] >= $lost['date_lost']) {
            $score += 5;
        }

        if ($score >= 35) {
            $matches[] = [
                'found_item' => $cand,
                'score'      => min($score, 99),
                'reasons'    => $reasons
            ];
        }
    }

    // Sort by highest score first
    usort($matches, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return $matches;
}

/**
 * Render visual badge for item statuses.
 * @param string $status
 * @return string
 */
function render_status_badge(string $status): string {
    $map = [
        'Reported'     => ['bg' => '#E0EDFD', 'text' => '#1E40AF', 'border' => '#BFDBFE'],
        'Under Review' => ['bg' => '#FEF3C7', 'text' => '#92400E', 'border' => '#FDE68A'],
        'Matched'      => ['bg' => '#EDE9FE', 'text' => '#6D28D9', 'border' => '#DDD6FE'],
        'Available'    => ['bg' => '#DCFCE7', 'text' => '#166534', 'border' => '#BBF7D0'],
        'Returned'     => ['bg' => '#D1FAE5', 'text' => '#065F46', 'border' => '#A7F3D0'],
        'Claimed'      => ['bg' => '#D1FAE5', 'text' => '#065F46', 'border' => '#A7F3D0'],
        'Closed'       => ['bg' => '#F1F5F9', 'text' => '#475569', 'border' => '#E2E8F0'],
        'Disposed'     => ['bg' => '#FEE2E2', 'text' => '#991B1B', 'border' => '#FECACA'],
        'active'       => ['bg' => '#DCFCE7', 'text' => '#166534', 'border' => '#BBF7D0'],
        'inactive'     => ['bg' => '#FEE2E2', 'text' => '#991B1B', 'border' => '#FECACA'],
    ];

    $cfg = $map[$status] ?? ['bg' => '#F1F5F9', 'text' => '#475569', 'border' => '#E2E8F0'];

    return sprintf(
        '<span style="display:inline-flex;align-items:center;padding:3px 9px;border-radius:9999px;font-size:12px;font-weight:600;background:%s;color:%s;border:1px solid %s;letter-spacing:0.02em;">%s</span>',
        $cfg['bg'],
        $cfg['text'],
        $cfg['border'],
        e($status)
    );
}

/**
 * Format date for friendly human reading.
 * @param string|null $dateStr
 * @return string
 */
function format_date(?string $dateStr): string {
    if (empty($dateStr)) {
        return '—';
    }
    $timestamp = strtotime($dateStr);
    return date('M d, Y', $timestamp);
}
