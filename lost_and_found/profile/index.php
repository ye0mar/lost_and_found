<?php
/**
 * User Profile & Password Security Management
 * Accessible by both Administrators and Staff
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/csrf.php';
require_once APP_ROOT . '/includes/functions.php';

require_login();

$db = get_db_connection();
$currentUser = current_user();

// Fetch fresh user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $currentUser['id']]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    $action = $_POST['action'] ?? '';

    // Update Profile Information
    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));

        if (empty($fullName) || empty($email)) {
            set_flash('error', 'Full name and email address are required.');
        } else {
            // Check if email taken by someone else
            $check = $db->prepare("SELECT id FROM users WHERE email = :e AND id != :id");
            $check->execute(['e' => $email, 'id' => $user['id']]);
            if ($check->fetch()) {
                set_flash('error', 'That email address is already in use by another user.');
            } else {
                $upd = $db->prepare("UPDATE users SET full_name = :fn, email = :e WHERE id = :id");
                $upd->execute(['fn' => $fullName, 'e' => $email, 'id' => $user['id']]);

                // Update session
                $_SESSION['full_name'] = $fullName;
                $_SESSION['email']     = $email;

                log_activity('UPDATE_PROFILE', 'users', $user['id'], 'Updated user profile information');
                set_flash('success', 'Your profile details have been saved.');
            }
        }
        header('Location: ' . BASE_URL . '/profile/index.php');
        exit;
    }

    // Change Password
    if ($action === 'change_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPass, $user['password_hash'])) {
            set_flash('error', 'Your current password was entered incorrectly.');
        } elseif (strlen($newPass) < 8) {
            set_flash('error', 'The new password must be at least 8 characters long.');
        } elseif ($newPass !== $confirmPass) {
            set_flash('error', 'The new passwords did not match.');
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $upd = $db->prepare("UPDATE users SET password_hash = :h WHERE id = :id");
            $upd->execute(['h' => $newHash, 'id' => $user['id']]);

            log_activity('CHANGE_PASSWORD', 'users', $user['id'], 'User successfully changed account password');
            set_flash('success', 'Your password has been changed securely.');
        }
        header('Location: ' . BASE_URL . '/profile/index.php');
        exit;
    }
}

$pageTitle = 'Account Profile';
$pageHeading = 'Personal Account & Security Profile';
$activeNav = 'profile';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <div class="dashboard-split-grid">
            <!-- Left: Profile Details -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Profile Information</span>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/profile/index.php">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="username" class="form-label">Username (System Identifier)</label>
                            <input type="text" id="username" class="form-control" value="<?= e($user['username']) ?>" disabled>
                            <span class="form-helper">System usernames are immutable for custody audit integrity.</span>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="full_name" class="form-label required">Full Legal Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="email" class="form-label required">Official Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label class="form-label">Assigned Security Role</label>
                            <div style="font-size: 13.5px; font-weight: 600; text-transform: uppercase; color: var(--accent-main);">
                                <?= e($user['role']) ?>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <span>Update Profile Information</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Change Password -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Change Password</span>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/profile/index.php">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="current_password" class="form-label required">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 16px;">
                            <label for="new_password" class="form-label required">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" minlength="8" required>
                            <span class="form-helper">Must be at least 8 characters long.</span>
                        </div>

                        <div class="form-group" style="margin-bottom: 24px;">
                            <label for="confirm_password" class="form-label required">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-secondary" style="width: 100%;">
                            <span>Save New Password</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
