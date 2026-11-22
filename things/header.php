<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/vehicles-data.php';

// Only protect pages that explicitly set $requiresAuth = true
if (!empty($requiresAuth) && !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$reservationCount = 0;
if (isset($_SESSION['user_id'])) {
    $query = $database->prepare('SELECT COUNT(*) FROM reservations WHERE user_id = ?');
    $query->execute([$_SESSION['user_id']]);
    $reservationCount = (int) $query->fetchColumn();
}

$currentScript = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= safe($pageTitle ?? 'Porsche Experience Store') ?> | Porsche</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700;800&family=Barlow:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <header class="nav" id="mainHeader">
        <div class="nav-brand">
            <a href="index.php" class="brand-link" title="Porsche Center">
                <span class="porsche-wordmark">PORSCHE</span>
                <span class="brand-tag">STORE &amp; 3D STUDIO</span>
            </a>
        </div>

        <nav class="nav-links">
            <a href="index.php" class="<?= $currentScript === 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house-chimney"></i> HOME
            </a>
            <a href="models.php" class="<?= $currentScript === 'models.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-car-side"></i> MODELS
            </a>
            <a href="configurator.php" class="nav-highlight <?= $currentScript === 'configurator.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-cube"></i> 3D CONFIGURATOR <span class="nav-badge">3D</span>
            </a>
            <a href="reservations.php" class="<?= $currentScript === 'reservations.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-receipt"></i> MY ORDERS 
                <?php if ($reservationCount > 0): ?>
                    <span class="order-counter"><?= $reservationCount ?></span>
                <?php endif; ?>
            </a>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin.php" class="admin-link <?= strpos($currentScript, 'admin') === 0 ? 'active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> ADMIN
                </a>
            <?php endif; ?>
        </nav>

        <div class="nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-pill">
                    <i class="fa-solid fa-circle-user"></i>
                    <span class="username"><?= safe($_SESSION['user_name'] ?? 'Driver') ?></span>
                    <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                        <span class="badge-role">ADMIN</span>
                    <?php endif; ?>
                    <a href="logout.php" class="btn-logout" title="Log Out"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn-login-ghost"><i class="fa-regular fa-user"></i> Sign In</a>
                <a href="configurator.php" class="btn-nav-cta">Build Your 911 <i class="fa-solid fa-arrow-right"></i></a>
            <?php endif; ?>
        </div>
    </header>

    <main>
