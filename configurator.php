<?php
session_start();
$pageTitle = 'Configurator';
require 'things/header.php';

$carId = array_key_first($allCars);
if (isset($_GET['id'])) {
    $carId = (int) $_GET['id'];
}

if (!isset($allCars[$carId])) {
    echo 'Unknown model';
    exit;
}

$car = $allCars[$carId];
?>

<section class="wrap two">
    <div>
        <img src="assets/images/vehicles/<?= $car['slug'] ?>/exterior/main.jpg"
            onerror="this.onerror=null;this.src='assets/images/placeholder.svg'" alt="<?= safe($car['name']) ?>">
    </div>

    <form id="cfg" method="post" action="reserve.php" data-base="<?= $car['base_price'] ?>" data-id="<?= $carId ?>">
        <h1>Configure</h1>
        <input type="hidden" name="car" value="<?= $carId ?>">

        <label>Model
            <select id="modelSelect">
                <?php foreach ($allCars as $carOption) { ?>
                    <option value="<?= $carOption['id'] ?>"><?= safe($carOption['name']) ?></option>
                <?php } ?>
            </select>
        </label>

        <h3>Exterior colour</h3>
        <?php foreach ($allColors as $color) { ?>
            <label class="sw">
                <input type="radio" name="color" value="<?= $color['id'] ?>" data-price="<?= $color['price'] ?>">
                <span style="background:<?= $color['hex'] ?>"></span>
                <?= $color['name'] ?> (+$<?= number_format($color['price']) ?>)
            </label>
        <?php } ?>

        <h3>Wheels</h3>
        <?php foreach ($allWheels as $wheel) { ?>
            <label>
                <input type="radio" name="wheel" value="<?= $wheel['id'] ?>" data-price="<?= $wheel['price'] ?>">
                <?= $wheel['name'] ?> (+$<?= number_format($wheel['price']) ?>)
            </label>
        <?php } ?>

        <h3>Options</h3>
        <?php foreach ($allFeatures as $feature) { ?>
            <label>
                <input type="checkbox" name="features[]" value="<?= $feature['id'] ?>"
                    data-price="<?= $feature['price'] ?>">
                <?= $feature['name'] ?> (+$<?= number_format($feature['price']) ?>)
            </label>
        <?php } ?>

        <div id="summary" class="summary">Calculating&hellip;</div>

        <button class="btn">Reserve this vehicle</button>
    </form>
</section>

<script src="assets/js/configurator.js"></script>

<?php require 'things/footer.php'; ?>