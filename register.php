<?php
session_start();
require_once 'things/db.php';

$isPublicPage = true;
$pageTitle = 'Register';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $typedUsername = trim($_POST['username']);
    $typedPassword = $_POST['password'];
    $typedConfirm = $_POST['confirm'];

    $query = $database->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $query->execute([$typedUsername]);
    $sameNameCount = $query->fetchColumn();

    if ($typedUsername == '' || $typedPassword == '') {
        $errorMessage = 'Please fill in all fields.';
    } elseif (strlen($typedUsername) < 3) {
        $errorMessage = 'Username must be at least 3 characters.';
    } elseif (strlen($typedPassword) < 6) {
        $errorMessage = 'Password must be at least 6 characters.';
    } elseif ($typedPassword != $typedConfirm) {
        $errorMessage = 'Passwords do not match.';
    } elseif ($sameNameCount > 0) {
        $errorMessage = 'This username is already taken.';
    } else {
        $hashedPassword = password_hash($typedPassword, PASSWORD_DEFAULT);
        $query = $database->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $query->execute([$typedUsername, $hashedPassword]);
        header('Location: login.php');
        exit;
    }
}

require 'things/header.php';
?>

<section class="wrap login-page">
    <div class="login-box">
        <h1>Register</h1>

        <?php if ($errorMessage != '') { ?>
            <p class="notice"><?= $errorMessage ?></p>
        <?php } ?>

        <form action="register.php" method="post" class="stack">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm">Confirm password:</label>
            <input type="password" id="confirm" name="confirm" required>

            <button type="submit" class="btn">Create account</button>
        </form>

        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</section>

<?php require 'things/footer.php'; ?>