<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/admin-guard.php';

$pageTitle = 'Admin - Manage Fleet';
$message = '';

if (isset($_POST['delete_car'])) {
    $carId = (int) $_POST['delete_car'];

    $query = $database->prepare('SELECT COUNT(*) FROM reservations WHERE vehicle_id = ?');
    $query->execute([$carId]);
    $reservationCount = $query->fetchColumn();

    if ($reservationCount > 0) {
        $message = 'This car has active customer reservations and cannot be deleted.';
    } else {
        $query = $database->prepare('DELETE FROM vehicles WHERE id = ?');
        $query->execute([$carId]);
        $message = 'Car removed from inventory.';
    }
}

require_once __DIR__ . '/things/header.php';
?>

<section class="wrap admin-page">
    <div class="admin-header-row">
        <div>
            <span class="label-tiny">INVENTORY MANAGEMENT</span>
            <h1>Manage Vehicle Fleet</h1>
            <p class="section-sub">Add new models, configure powertrain specifications, or adjust base pricing.</p>
        </div>
        <div class="admin-actions-bar">
            <a class="btn-porsche-primary" href="admin-car-form.php"><i class="fa-solid fa-plus"></i> Add New Car</a>
            <a class="btn-ghost-luxury" href="admin.php"><i class="fa-solid fa-arrow-left"></i> Admin Hub</a>
        </div>
    </div>

    <?php if ($message != ''): ?>
        <div class="notice"><?= safe($message) ?></div>
    <?php endif; ?>

    <div class="admin-table-container">
        <table class="luxury-admin-table">
            <thead>
                <tr>
                    <th>Model Name &amp; Slug</th>
                    <th>Category</th>
                    <th>Engine</th>
                    <th>Performance</th>
                    <th>Base Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allCars as $car): ?>
                    <tr>
                        <td>
                            <strong style="color:#fff; font-size:1.1rem;"><?= safe($car['name']) ?></strong><br>
                            <small class="text-muted">Slug: <?= safe($car['slug']) ?> &bull; Year <?= $car['year'] ?></small>
                        </td>
                        <td><span class="card-badge badge-category"><?= safe(ucfirst($car['category'])) ?></span></td>
                        <td><span class="card-badge badge-engine"><?= safe(ucfirst($car['engine'])) ?></span></td>
                        <td>
                            <span><?= $car['hp'] ?> HP &bull; 0-100: <?= $car['accel'] ?>s &bull; <?= $car['top_speed'] ?> km/h</span>
                        </td>
                        <td>
                            <strong class="price-highlight">$<?= number_format($car['base_price']) ?></strong>
                        </td>
                        <td>
                            <div style="display:flex; gap:0.5rem;">
                                <a class="btn-table-sm" href="admin-car-form.php?id=<?= $car['id'] ?>" title="Edit Model">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <form method="post" onsubmit="return confirm('Delete this vehicle model permanently?');">
                                    <button class="btn-table-danger" name="delete_car" value="<?= $car['id'] ?>" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
