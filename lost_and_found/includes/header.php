<?php
/**
 * Common HTML Header Component
 * Lost & Found Management System
 */

declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/functions.php';

$pageTitle = $pageTitle ?? get_system_setting('system_name', 'Lost & Found Management System');
$logoUrl = get_logo_url();
$faviconUrl = get_favicon_url();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — Custody & Recovery</title>
    
    <!-- Browser Favicon (Square Logo Integration) -->
    <link rel="icon" type="image/svg+xml" href="<?= e($faviconUrl) ?>">
    <link rel="shortcut icon" href="<?= e($faviconUrl) ?>">

    <!-- Google Fonts: Outfit for display & Poppins for UI hierarchy -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Architecture -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/forms.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/responsive.css">
</head>
<body>
    <div class="app-layout">
