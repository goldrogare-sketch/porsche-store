<?php
session_start();
require 'things/admin-guard.php';

$pageTitle = 'Admin - Cars';
$message = '';

if (isset($_POST['delete_car'])) {
    $carId = (int) $_POST['delete_car'];

    $query = $database->prepare('SELECT COUNT(*) FROM reservations WHERE vehicle_id = ?');
    $query->execute([$carId]);
    $reservationCount = $query->fetchColumn();

    if ($reservationCount > 0) {
        $message = 'This car has reservations and cannot be deleted.';
    } else {
        $query = $database->prepare('DELETE FROM vehicles WHERE id = ?');
        $query->execute([$carId]);
        $message = 'Car deleted.';
    }
}

require 'things/header.php';
?>

<section class="wrap">
    <h1>Manage cars</h1>

    <?php if ($message != '') { ?>
        <p class="notice"><?= $message ?></p>
    <?php } ?>

    <p>
        <a class="btn" href="admin-car-form.php">Add new car</a>
        <a class="btn ghost" href="admin.php">Back</a>
    </p>

    <table>
        <?php foreach ($allCars as $car) { ?>
            <tr>
                <td><?= safe($car['name']) ?></td>
                <td><?= safe($car['category']) ?></td>
                <td>$<?= number_format($car['base_price']) ?></td>
                <td><a class="btn ghost" href="admin-car-form.php?id=<?= $car['id'] ?>">Edit</a></td>
                <td>
                    <form method="post">
                        <button class="btn ghost confirm-delete" name="delete_car"
                            value="<?= $car['id'] ?>">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</section>

<script src="assets/js/forms.js"></script>

<?php require 'things/footer.php'; ?>