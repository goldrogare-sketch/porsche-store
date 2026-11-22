<?php
require_once 'things/db.php';
require_once 'things/vehicles-data.php';

if (!isset($_SESSION['user_id']) && !isset($isPublicPage)) {
    header('Location: login.php');
    exit;
}

$reservationCount = 0;
if (isset($_SESSION['user_id'])) {
    $query = $database->prepare('SELECT COUNT(*) FROM reservations WHERE user_id = ?');
    $query->execute([$_SESSION['user_id']]);
    $reservationCount = $query->fetchColumn();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= $pageTitle ?></title>
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;600&family=Barlow+Condensed:wght@600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <header class="nav">
        <a href="index.php">
            <img src="assets/images/logo/logo.jpg" alt="Porsche logo" height="28">
        </a>

        <?php if (isset($_SESSION['user_id'])) { ?>
            <nav>
                <a href="index.php">HOME</a>
                <a href="models.php">MODELS</a>
                <a href="configurator.php">CONFIGURATOR</a>
                <a href="reservations.php">RESERVATIONS (<?= $reservationCount ?>)</a>

                <?php if ($_SESSION['user_role'] == 'admin') { ?>
                    <a href="admin.php">ADMIN</a>
                <?php } ?>

                <a href="logout.php">LOGOUT</a>
            </nav>
        <?php } ?>
    </header>

    <main>