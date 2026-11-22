<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/admin-guard.php';
require_once __DIR__ . '/things/vehicles-data.php';

$pageTitle = 'Admin - Edit Vehicle';
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

require_once __DIR__ . '/things/header.php';
?>

<section class="wrap admin-page">
    <div class="admin-header-row">
        <div>
            <span class="label-tiny">VEHICLE CATALOG EDITOR</span>
            <h1><?= $carId === 0 ? 'Add New Porsche Model' : 'Edit ' . safe($name) ?></h1>
            <p class="section-sub">Define performance metrics, powertrain specifications, and starting base price.</p>
        </div>
        <a class="btn-ghost-luxury" href="admin-cars.php"><i class="fa-solid fa-arrow-left"></i> Back to Fleet</a>
    </div>

    <?php if ($message != ''): ?>
        <div class="notice"><?= safe($message) ?></div>
    <?php endif; ?>

    <div class="luxury-auth-card" style="max-width: 680px; margin: 0 auto;">
        <form method="post" class="auth-form stack">
            <input type="hidden" name="car_id" value="<?= $carId ?>">

            <div class="modal-form-grid">
                <div class="form-group full-width">
                    <label>Vehicle Model Name *</label>
                    <input name="name" class="form-input" value="<?= safe($name) ?>" placeholder="e.g. Porsche 911 GT3 RS" required>
                </div>

                <div class="form-group">
                    <label>Slug (Image Directory Key) *</label>
                    <input name="slug" class="form-input" value="<?= safe($slug) ?>" placeholder="e.g. 911-gt3-rs" required>
                </div>

                <div class="form-group">
                    <label>Model Year *</label>
                    <input type="number" name="year" class="form-input" value="<?= safe($year) ?>" required>
                </div>

                <div class="form-group full-width">
                    <label>Marketing Description *</label>
                    <input name="description" class="form-input" value="<?= safe($description) ?>" placeholder="Brief tagline or description" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" class="form-input">
                        <?php foreach ($allCategories as $categoryName): ?>
                            <option value="<?= safe($categoryName) ?>" <?= $category === $categoryName ? 'selected' : '' ?>><?= safe(ucfirst($categoryName)) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Powertrain / Engine</label>
                    <select name="engine" class="form-input">
                        <?php foreach ($engineOptions as $engineName): ?>
                            <option value="<?= $engineName ?>" <?= $engine === $engineName ? 'selected' : '' ?>><?= ucfirst($engineName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Max Horsepower (HP) *</label>
                    <input type="number" name="horsepower" class="form-input" value="<?= safe($horsepower) ?>" required>
                </div>

                <div class="form-group">
                    <label>0-100 km/h (Seconds) *</label>
                    <input type="number" step="0.1" name="acceleration" class="form-input" value="<?= safe($acceleration) ?>" required>
                </div>

                <div class="form-group">
                    <label>Top Speed (km/h) *</label>
                    <input type="number" name="top_speed" class="form-input" value="<?= safe($topSpeed) ?>" required>
                </div>

                <div class="form-group">
                    <label>Starting Base Price ($) *</label>
                    <input type="number" name="price" class="form-input" value="<?= safe($price) ?>" required>
                </div>
            </div>

            <div style="display:flex; gap:1rem; margin-top:1rem;">
                <button type="submit" class="btn-porsche-primary btn-block"><i class="fa-solid fa-floppy-disk"></i> Save Vehicle Specification</button>
                <a class="btn-ghost-luxury" href="admin-cars.php">Cancel</a>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
