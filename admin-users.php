<?php
session_start();
require 'things/admin-guard.php';

$pageTitle = 'Admin - Users';
$message = '';
$myId = $_SESSION['user_id'];

if (isset($_POST['add_user'])) {
    $newUsername = trim($_POST['new_username']);
    $newPassword = $_POST['new_password'];
    $newRole = $_POST['new_role'];

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
        $message = 'User added.';
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
        $message = 'This user has reservations and cannot be deleted.';
    } else {
        $query = $database->prepare('DELETE FROM users WHERE id = ?');
        $query->execute([$userId]);
        $message = 'User deleted.';
    }
}

$allUsers = $database->query('SELECT id, username, role FROM users ORDER BY id')->fetchAll();

require 'things/header.php';
?>

<section class="wrap">
    <h1>Manage users</h1>

    <?php if ($message != '') { ?>
        <p class="notice"><?= $message ?></p>
    <?php } ?>

    <form method="post" class="filters">
        <input name="new_username" placeholder="Username" required>
        <input type="password" name="new_password" placeholder="Password" required>
        <select name="new_role">
            <option>user</option>
            <option>admin</option>
        </select>
        <button class="btn" name="add_user" value="1">Add user</button>
        <a class="btn ghost" href="admin.php">Back</a>
    </form>

    <table>
        <?php foreach ($allUsers as $user) { ?>
            <tr>
                <td><?= safe($user['username']) ?></td>

                <?php if ($user['id'] == $myId) { ?>
                    <td colspan="2">You (<?= $user['role'] ?>)</td>
                <?php } else { ?>
                    <td>
                        <form method="post" class="filters">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="new_role" data-selected="<?= $user['role'] ?>">
                                <option>user</option>
                                <option>admin</option>
                            </select>
                            <button class="btn ghost" name="change_role" value="1">Save</button>
                        </form>
                    </td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <button class="btn ghost confirm-delete" name="delete_user" value="1">Delete</button>
                        </form>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table>
</section>

<script src="assets/js/forms.js"></script>

<?php require 'things/footer.php'; ?>