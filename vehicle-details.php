<?php
session_start();
$pageTitle = 'Vehicle details';
require 'things/header.php';

$carId = 0;
if (isset($_GET['id'])) {
     $carId = (int) $_GET['id']; /*?? */
}

if (!isset($allCars[$carId])) {
    ?>
    <section class="wrap">
        <p>Model isnot faoundd<a href="models.php">Back to models</a></p>
    </section>
    <?php
    require 'things/footer.php';
    exit;
}

$car = $allCars[$carId];
?>

<section class="wrap two">
    <div>
        <img src="assets/images/vehicles/<?= $car['slug'] ?>/exterior/main.jpg"
            onerror="this.onerror=null;this.src='assets/images/placeholder.svg'" alt="<?= ($car['name']) ?> exterior">
        <img src="assets/images/vehicles/<?= $car['slug'] ?>/interior/main.jpg"
            onerror="this.onerror=null;this.src='assets/images/placeholder.svg'" alt="<?= ($car['name']) ?> interior">
    </div>

    <div>
        <h1><?= ($car['name']) ?></h1>
        <p><?= ($car['description']) ?></p>
        <p class="price">$<?= number_format($car['base_price']) ?></p>

        <ul>
            <li><?= $car['hp'] ?> hp</li>
            <li>0-100 km/h: <?= $car['accel'] ?> s</li>
            <li>Top speed: <?= $car['top_speed'] ?> km/h</li>
            <li>Engine: <?= ($car['engine']) ?></li>
        </ul>

        <a class="btn" href="configurator.php?id=<?= $car['id'] ?>">Configure this vehicle</a>
    </div>
</section>

<?php require 'things/footer.php'; ?>