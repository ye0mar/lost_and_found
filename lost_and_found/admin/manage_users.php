<?php
/**
 * User & Staff Access Administration
 * Administrator-Only Management Suite
 */

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/auth.php';
require_once APP_ROOT . '/includes/csrf.php';
require_once APP_ROOT . '/includes/functions.php';

require_admin();

$db = get_db_connection();
$currentUser = current_user();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_user') {
        $username  = strtolower(trim($_POST['username'] ?? ''));
        $email     = strtolower(trim($_POST['email'] ?? ''));
        $fullName  = trim($_POST['full_name'] ?? '');
        $password  = $_POST['password'] ?? '';
        $role      = $_POST['role'] === 'admin' ? 'admin' : 'staff';
        $status    = $_POST['status'] === 'inactive' ? 'inactive' : 'active';

        if (empty($username) || empty($email) || empty($fullName) || empty($password)) {
            set_flash('error', 'All fields are required to create a user account.');
        } elseif (strlen($password) < 8) {
            set_flash('error', 'Password must be at least 8 characters long.');
        } else {
            // Check for existing username or email
            $checkStmt = $db->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
            $checkStmt->execute(['u' => $username, 'e' => $email]);
            if ($checkStmt->fetch()) {
                set_flash('error', 'A user with that username or email already exists.');
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $ins = $db->prepare(
                    "INSERT INTO users (username, email, full_name, role, password_hash, status, created_at)
                     VALUES (:u, :e, :fn, :role, :hash, :status, NOW())"
                );
                $ins->execute([
                    'u'      => $username,
                    'e'      => $email,
                    'fn'     => $fullName,
                    'role'   => $role,
                    'hash'   => $hash,
                    'status' => $status
                ]);
                $newId = (int)$db->lastInsertId();
                log_activity('ADMIN_CREATE_USER', 'users', $newId, "Created $role account: $username ($fullName)");
                set_flash('success', "Account for '$fullName' ($username) successfully created.");
            }
        }
        header('Location: ' . BASE_URL . '/admin/manage_users.php');
        exit;
    }

    if ($action === 'toggle_status') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId === $currentUser['id']) {
            set_flash('error', 'Security safeguard: You cannot deactivate your own active session account.');
        } else {
            $uStmt = $db->prepare("SELECT status, role, username FROM users WHERE id = :id");
            $uStmt->execute(['id' => $userId]);
            $target = $uStmt->fetch();

            if ($target) {
                // Prevent deactivating the last active administrator
                if ($target['role'] === 'admin' && $target['status'] === 'active') {
                    $adminCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'")->fetchColumn();
                    if ($adminCount <= 1) {
                        set_flash('error', 'Lockout safeguard: Cannot deactivate the sole active Administrator account.');
                        header('Location: ' . BASE_URL . '/admin/manage_users.php');
                        exit;
                    }
                }

                $newStatus = ($target['status'] === 'active') ? 'inactive' : 'active';
                $upd = $db->prepare("UPDATE users SET status = :st WHERE id = :id");
                $upd->execute(['st' => $newStatus, 'id' => $userId]);

                log_activity('ADMIN_TOGGLE_USER_STATUS', 'users', $userId, "Changed status of {$target['username']} to $newStatus");
                set_flash('success', "Account {$target['username']} has been set to " . strtoupper($newStatus) . ".");
            }
        }
        header('Location: ' . BASE_URL . '/admin/manage_users.php');
        exit;
    }

    if ($action === 'reset_password') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newPass = $_POST['new_password'] ?? '';

        if (strlen($newPass) < 8) {
            set_flash('error', 'New password must contain at least 8 characters.');
        } else {
            $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $stmt->execute(['hash' => $hash, 'id' => $userId]);

            log_activity('ADMIN_RESET_PASSWORD', 'users', $userId, "Reset password for user ID #$userId");
            set_flash('success', "Password successfully reset for user ID #$userId.");
        }
        header('Location: ' . BASE_URL . '/admin/manage_users.php');
        exit;
    }
}

// Fetch all users
$users = $db->query("SELECT * FROM users ORDER BY role ASC, full_name ASC")->fetchAll();

$pageTitle = 'Manage Users';
$pageHeading = 'User & Staff Access Administration';
$activeNav = 'manage_users';

require_once APP_ROOT . '/includes/header.php';
require_once APP_ROOT . '/includes/sidebar.php';
?>

<div class="app-main">
    <?php require_once APP_ROOT . '/includes/navbar.php'; ?>
    
    <div class="content-wrapper">
        <?php require_once APP_ROOT . '/includes/flash_messages.php'; ?>

        <div class="dashboard-split-grid">
            <!-- Left: Existing Users Table -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <div>
                            <span class="card-title">Authorized System Accounts</span>
                            <span style="font-size: 12px; color: var(--text-muted); margin-left: 8px;">(<?= count($users) ?> total)</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600;"><?= e($u['full_name']) ?></div>
                                            <div style="font-size: 12px; color: var(--text-secondary);"><?= e($u['email']) ?> &bull; <code><?= e($u['username']) ?></code></div>
                                        </td>
                                        <td>
                                            <span style="padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 600; text-transform: uppercase; background: <?= $u['role'] === 'admin' ? '#DCEBFF' : '#E2E8F0' ?>; color: <?= $u['role'] === 'admin' ? '#1D4ED8' : '#334155' ?>;">
                                                <?= e($u['role']) ?>
                                            </span>
                                        </td>
                                        <td><?= render_status_badge($u['status']) ?></td>
                                        <td style="font-size: 12px; color: var(--text-secondary);">
                                            <?= $u['last_login'] ? e($u['last_login']) : 'Never' ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <div style="display:inline-flex;gap:6px;">
                                                <!-- Toggle Status Form -->
                                                <form method="POST" action="<?= BASE_URL ?>/admin/manage_users.php" onsubmit="return confirmAction('Change account status for <?= e($u['username']) ?>?');">
                                                    <?php csrf_field(); ?>
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <button type="submit" class="btn btn-secondary btn-sm" <?= $u['id'] === $currentUser['id'] ? 'disabled title="Cannot deactivate self"' : '' ?>>
                                                        <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                                    </button>
                                                </form>

                                                <!-- Reset Password Modal Trigger -->
                                                <button type="button" class="btn btn-secondary btn-sm" onclick="promptPasswordReset(<?= $u['id'] ?>, '<?= e($u['username']) ?>')">
                                                    Reset Pass
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Create New Staff Form -->
            <div>
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Provision New Account</span>
                    </div>

                    <form method="POST" action="<?= BASE_URL ?>/admin/manage_users.php">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="create_user">

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label for="full_name" class="form-label required">Full Legal Name</label>
                            <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Officer Alex Parker" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label for="username" class="form-label required">Username</label>
                            <input type="text" id="username" name="username" class="form-control" placeholder="e.g. officer.parker" required>
                            <span class="form-helper">Lowercase, alphanumeric without spaces.</span>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label for="email" class="form-label required">Official Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="e.g. aparker@organization.edu" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label for="password" class="form-label required">Initial Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                        </div>

                        <div class="form-group" style="margin-bottom: 14px;">
                            <label for="role" class="form-label required">Account Role</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="staff" selected>Staff (Custody Officer)</option>
                                <option value="admin">Administrator (Full Access)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="status" class="form-label required">Initial Status</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Create Authorized Account</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Hidden Password Reset Form -->
        <form id="resetPassForm" method="POST" action="<?= BASE_URL ?>/admin/manage_users.php" style="display:none;">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="resetUserId">
            <input type="hidden" name="new_password" id="resetNewPass">
        </form>

        <script>
        function promptPasswordReset(userId, username) {
            const pass = prompt('Enter a new temporary password for ' + username + ' (minimum 8 characters):');
            if (pass && pass.length >= 8) {
                document.getElementById('resetUserId').value = userId;
                document.getElementById('resetNewPass').value = pass;
                document.getElementById('resetPassForm').submit();
            } else if (pass !== null) {
                alert('Password was too short. Must be at least 8 characters.');
            }
        }
        </script>
<?php
require_once APP_ROOT . '/includes/footer.php';
?>
