<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/db.php';

$pageTitle = 'Sign In & Driver ID Portal';
$errorMessage = '';

// Quick Demo Login Helper for Testing
if (isset($_POST['quick_demo'])) {
    $demoType = $_POST['quick_demo'];
    $roleTarget = $demoType === 'admin' ? 'admin' : 'user';
    $stmt = $database->prepare('SELECT * FROM users WHERE role = ? ORDER BY id ASC LIMIT 1');
    $stmt->execute([$roleTarget]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['welcome_name'] = $user['username'];
        header('Location: ' . ($user['role'] === 'admin' ? 'admin.php' : 'index.php'));
        exit;
    }
}

// Standard Form Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
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
        $errorMessage = 'Invalid username or password. Please verify your credentials.';
    }
}

require_once __DIR__ . '/things/header.php';
?>

<section class="wrap login-page">
    <div class="luxury-auth-card">
        <div class="auth-header">
            <div class="porsche-wordmark">PORSCHE</div>
            <h1>Driver ID Sign In</h1>
            <p class="auth-sub">Access your customized builds, track factory allocations, and manage your garage.</p>
        </div>

        <?php if ($errorMessage != ''): ?>
            <div class="notice notice-error"><?= safe($errorMessage) ?></div>
        <?php endif; ?>

        <form action="login.php" method="post" class="auth-form stack">
            <div class="form-group">
                <label for="username">Porsche Driver ID / Username</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="username" name="username" placeholder="Username" required autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-icon">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="btn-porsche-primary btn-block">
                Sign In to Garage <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="demo-login-box">
            <span class="demo-lbl">Instant Demo Access</span>
            <form method="post" class="demo-buttons-row">
                <button type="submit" name="quick_demo" value="user" class="btn-demo-pill">
                    <i class="fa-solid fa-user-check"></i> Customer Demo (FARID)
                </button>
                <button type="submit" name="quick_demo" value="admin" class="btn-demo-pill admin">
                    <i class="fa-solid fa-shield-halved"></i> Admin Demo
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <p>New to Porsche? <a href="register.php" class="text-link">Create a Porsche ID</a></p>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
