<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/admin-guard.php';

$pageTitle = 'Admin - Users';
$message = '';
$myId = $_SESSION['user_id'] ?? 0;

if (isset($_POST['add_user'])) {
    $newUsername = trim($_POST['new_username'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $newRole = $_POST['new_role'] ?? 'user';

    $query = $database->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $query->execute([$newUsername]);
    $sameNameCount = $query->fetchColumn();

    if (strlen($newUsername) < 3) {
        $message = 'Username must be at least 3 characters.';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters.';
    } elseif ($newRole != 'user' && $newRole != 'admin') {
        $message = 'Invalid role.';
    } elseif ($sameNameCount > 0) {
        $message = 'This username is already taken.';
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = $database->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $query->execute([$newUsername, $hashedPassword, $newRole]);
        $message = 'User added successfully.';
    }
}

if (isset($_POST['change_role'])) {
    $userId = (int) $_POST['user_id'];
    $newRole = $_POST['new_role'];

    if ($userId == $myId) {
        $message = 'You cannot change your own role.';
    } elseif ($newRole != 'user' && $newRole != 'admin') {
        $message = 'Invalid role.';
    } else {
        $query = $database->prepare('UPDATE users SET role = ? WHERE id = ?');
        $query->execute([$newRole, $userId]);
        $message = 'Role updated.';
    }
}

if (isset($_POST['delete_user'])) {
    $userId = (int) $_POST['user_id'];

    $query = $database->prepare('SELECT COUNT(*) FROM reservations WHERE user_id = ?');
    $query->execute([$userId]);
    $reservationCount = $query->fetchColumn();

    if ($userId == $myId) {
        $message = 'You cannot delete your own account.';
    } elseif ($reservationCount > 0) {
        $message = 'This user has active reservations and cannot be deleted.';
    } else {
        $query = $database->prepare('DELETE FROM users WHERE id = ?');
        $query->execute([$userId]);
        $message = 'User account deleted.';
    }
}

$allUsers = $database->query('SELECT id, username, role FROM users ORDER BY id')->fetchAll();

require_once __DIR__ . '/things/header.php';
?>

<section class="wrap admin-page">
    <div class="admin-header-row">
        <div>
            <span class="label-tiny">ACCESS &amp; DRIVER ACCOUNTS</span>
            <h1>Manage User Accounts</h1>
            <p class="section-sub">Manage driver authorizations and store administrator roles.</p>
        </div>
        <a class="btn-ghost-luxury" href="admin.php"><i class="fa-solid fa-arrow-left"></i> Admin Hub</a>
    </div>

    <?php if ($message != ''): ?>
        <div class="notice"><?= safe($message) ?></div>
    <?php endif; ?>

    <form method="post" class="luxury-filters-bar" style="max-width: 720px; margin-bottom: 2rem;">
        <input name="new_username" placeholder="New Username" required style="max-width:200px;">
        <input type="password" name="new_password" placeholder="Password (min 6 chars)" required style="max-width:200px;">
        <select name="new_role" style="max-width:140px;">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
        <button class="btn-porsche-primary" name="add_user" value="1"><i class="fa-solid fa-user-plus"></i> Add Account</button>
    </form>

    <div class="admin-table-container">
        <table class="luxury-admin-table">
            <thead>
                <tr>
                    <th>Driver Username</th>
                    <th>Role</th>
                    <th>Change Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allUsers as $user): ?>
                    <tr>
                        <td>
                            <strong style="color:#fff; font-size:1.05rem;"><i class="fa-solid fa-user-circle"></i> <?= safe($user['username']) ?></strong>
                            <?php if ($user['id'] == $myId): ?>
                                <span class="card-badge" style="margin-left:0.5rem; background:rgba(0,255,136,0.15); color:#00ff88;">Current Session</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="card-badge <?= $user['role'] === 'admin' ? 'badge-engine' : '' ?>"><?= strtoupper($user['role']) ?></span>
                        </td>
                        <td>
                            <?php if ($user['id'] != $myId): ?>
                                <form method="post" style="display:flex; gap:0.5rem; align-items:center;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="new_role" style="max-width:120px;">
                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>user</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                                    </select>
                                    <button class="btn-table-sm" name="change_role" value="1" title="Save Role"><i class="fa-solid fa-check"></i></button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Self</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['id'] != $myId): ?>
                                <form method="post" onsubmit="return confirm('Delete this user account?');">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button class="btn-table-danger" name="delete_user" value="1"><i class="fa-solid fa-trash"></i> Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
