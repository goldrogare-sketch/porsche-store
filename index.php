<?php
session_start();
$pageTitle = 'Porsche Store';
require 'things/header.php'; /*?? */

$featuredCars = $database->query('SELECT * FROM vehicles ORDER BY base_price DESC LIMIT 3')->fetchAll();
?>

<?php if (isset($_SESSION['welcome_name'])) { ?>
    <div class="welcome-box" id="welcomeBox">Welcome, <?= ($_SESSION['welcome_name']) ?>!</div>
    <?php unset($_SESSION['welcome_name']); ?>
<?php } ?>

<section class="hero" style="background-image:url('assets/images/hero/main-hero.jpg')">
    <div>
        <h1>Build the Porsche that is yours.</h1>
        <p>Choose a model, set the colour , and see the price update as you go.</p>

        <a class="btn" href="models.php">Explore models</a>
        <a class="btn ghost" href="configurator.php">Configure your car</a>
    </div>
</section>

<section class="wrap">
    <h2>Featured models</h2>

    <div class="grid">
        <?php foreach ($featuredCars as $car) {
            include 'things/vehicle-card.php'; /*?? */
        } ?>
    </div>
</section>

<?php require 'things/footer.php'; ?> /* ?? */