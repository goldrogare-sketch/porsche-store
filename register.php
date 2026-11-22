<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/db.php';

$pageTitle = 'Create Your Porsche Driver ID';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $typedUsername = trim($_POST['username'] ?? '');
    $typedPassword = $_POST['password'] ?? '';
    $typedConfirm = $_POST['confirm'] ?? '';

    $query = $database->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $query->execute([$typedUsername]);
    $sameNameCount = $query->fetchColumn();

    if ($typedUsername == '' || $typedPassword == '') {
        $errorMessage = 'Please complete all required fields.';
    } elseif (strlen($typedUsername) < 3) {
        $errorMessage = 'Username must be at least 3 characters.';
    } elseif (strlen($typedPassword) < 6) {
        $errorMessage = 'Password must be at least 6 characters.';
    } elseif ($typedPassword != $typedConfirm) {
        $errorMessage = 'Passwords do not match.';
    } elseif ($sameNameCount > 0) {
        $errorMessage = 'This Porsche Driver ID is already registered.';
    } else {
        $hashedPassword = password_hash($typedPassword, PASSWORD_DEFAULT);
        $query = $database->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $query->execute([$typedUsername, $hashedPassword, 'user']);

        $_SESSION['welcome_name'] = $typedUsername;
        $_SESSION['user_id'] = (int) $database->lastInsertId();
        $_SESSION['user_name'] = $typedUsername;
        $_SESSION['user_role'] = 'user';

        header('Location: index.php');
        exit;
    }
}

require_once __DIR__ . '/things/header.php';
?>

<section class="wrap login-page">
    <div class="luxury-auth-card">
        <div class="auth-header">
            <div class="porsche-wordmark">PORSCHE</div>
            <h1>Create Driver ID</h1>
            <p class="auth-sub">Register to save your 3D custom specifications, receive build slot updates, and manage your garage.</p>
        </div>

        <?php if ($errorMessage != ''): ?>
            <div class="notice notice-error"><?= safe($errorMessage) ?></div>
        <?php endif; ?>

        <form action="register.php" method="post" class="auth-form stack">
            <div class="form-group">
                <label for="username">Desired Porsche Driver ID / Username</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="username" name="username" placeholder="At least 3 characters" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-shield-check"></i>
                    <input type="password" id="confirm" name="confirm" placeholder="Re-type password" required>
                </div>
            </div>

            <button type="submit" class="btn-porsche-primary btn-block">
                Create Account &amp; Enter Studio <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="auth-footer">
            <p>Already registered? <a href="login.php" class="text-link">Sign In</a></p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
