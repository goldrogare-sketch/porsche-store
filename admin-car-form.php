<?php
session_start();
require 'things/admin-guard.php';
require_once 'things/vehicles-data.php';

$pageTitle = 'Admin - Car';
$message = '';
$engineOptions = ['petrol', 'electric', 'hybrid'];

$carId = 0;
$name = '';
$slug = '';
$description = '';
$category = '';
$engine = 'petrol';
$horsepower = '';
$acceleration = '';
$topSpeed = '';
$price = '';
$year = date('Y');

if (isset($_GET['id'])) {
    $editId = (int) $_GET['id'];

    if (isset($allCars[$editId])) {
        $oldCar = $allCars[$editId];
        $carId = $oldCar['id'];
        $name = $oldCar['name'];
        $slug = $oldCar['slug'];
        $description = $oldCar['description'];
        $category = $oldCar['category'];
        $engine = $oldCar['engine'];
        $horsepower = $oldCar['hp'];
        $acceleration = $oldCar['accel'];
        $topSpeed = $oldCar['top_speed'];
        $price = $oldCar['base_price'];
        $year = $oldCar['year'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $carId = (int) $_POST['car_id'];
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $category = $_POST['category'];
    $engine = $_POST['engine'];
    $horsepower = (int) $_POST['horsepower'];
    $acceleration = (float) $_POST['acceleration'];
    $topSpeed = (int) $_POST['top_speed'];
    $price = (int) $_POST['price'];
    $year = (int) $_POST['year'];

    $query = $database->prepare('SELECT COUNT(*) FROM vehicles WHERE slug = ? AND id != ?');
    $query->execute([$slug, $carId]);
    $sameSlugCount = $query->fetchColumn();

    if ($name == '' || $description == '') {
        $message = 'Please fill in the name and description.';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $message = 'Slug can only contain lowercase letters, numbers and dashes.';
    } elseif ($sameSlugCount > 0) {
        $message = 'This slug is already used by another car.';
    } elseif (!in_array($category, $allCategories)) {
        $message = 'Please choose a valid category.';
    } elseif (!in_array($engine, $engineOptions)) {
        $message = 'Please choose a valid engine.';
    } elseif ($horsepower <= 0 || $acceleration <= 0 || $topSpeed <= 0 || $price <= 0 || $year <= 0) {
        $message = 'Numbers must be greater than zero.';
    } elseif ($carId == 0) {
        $query = $database->prepare('INSERT INTO vehicles (slug, name, description, category, engine, hp, accel, top_speed, base_price, year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $query->execute([$slug, $name, $description, $category, $engine, $horsepower, $acceleration, $topSpeed, $price, $year]);
        header('Location: admin-cars.php');
        exit;
    } else {
        $query = $database->prepare('UPDATE vehicles SET slug = ?, name = ?, description = ?, category = ?, engine = ?, hp = ?, accel = ?, top_speed = ?, base_price = ?, year = ? WHERE id = ?');
        $query->execute([$slug, $name, $description, $category, $engine, $horsepower, $acceleration, $topSpeed, $price, $year, $carId]);
        header('Location: admin-cars.php');
        exit;
    }
}

require 'things/header.php';
?>

<section class="wrap">
    <h1>Car details</h1>

    <?php if ($message != '') { ?>
        <p class="notice"><?= $message ?></p>
    <?php } ?>

    <form method="post" class="stack">
        <input type="hidden" name="car_id" value="<?= $carId ?>">

        <label>Name</label>
        <input name="name" value="<?= safe($name) ?>" required>

        <label>Slug (also the images folder name, example: 911-gt3)</label>
        <input name="slug" value="<?= safe($slug) ?>" required>

        <label>Description</label>
        <input name="description" value="<?= safe($description) ?>" required>

        <label>Category</label>
        <select name="category" data-selected="<?= safe($category) ?>">
            <?php foreach ($allCategories as $categoryName) { ?>
                <option><?= safe($categoryName) ?></option>
            <?php } ?>
        </select>

        <label>Engine</label>
        <select name="engine" data-selected="<?= safe($engine) ?>">
            <?php foreach ($engineOptions as $engineName) { ?>
                <option><?= $engineName ?></option>
            <?php } ?>
        </select>

        <label>Horsepower</label>
        <input type="number" name="horsepower" value="<?= safe($horsepower) ?>" required>

        <label>0-100 km/h (seconds)</label>
        <input type="number" step="0.1" name="acceleration" value="<?= safe($acceleration) ?>" required>

        <label>Top speed (km/h)</label>
        <input type="number" name="top_speed" value="<?= safe($topSpeed) ?>" required>

        <label>Base price ($)</label>
        <input type="number" name="price" value="<?= safe($price) ?>" required>

        <label>Year</label>
        <input type="number" name="year" value="<?= safe($year) ?>" required>

        <button class="btn">Save</button>
        <a class="btn ghost" href="admin-cars.php">Cancel</a>
    </form>
</section>

<script src="assets/js/forms.js"></script>

<?php require 'things/footer.php'; ?>