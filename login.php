<?php
session_start();
require_once 'things/db.php'; /* ??*/

$isPublicPage = true;
$pageTitle = 'Login';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {    /* ?? */
    $typedUsername = trim($_POST['username'] ?? '');
    $typedPassword = $_POST['password'] ?? '';

    $query = $database->prepare('SELECT * FROM users WHERE username = ?');
    $query->execute([$typedUsername]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($typedPassword, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['welcome_name'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $errorMessage = 'Invalid username or password.';
    }
}

require 'things/header.php';
?>

<section class="wrap login-page">
    <div class="login-box">
        <h1>Login</h1>

        <?php if ($errorMessage != '') { ?>
            <p class="notice"><?= $errorMessage ?></p>
        <?php } ?>

        <form action="login.php" method="post" class="stack">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="btn">Login</button>
        </form>

        <p>No account yet? <a href="register.php">Register</a></p>
    </div>
</section>

<?php require 'things/footer.php'; ?>