<?php
session_start();
require_once 'things/db.php';

if (isset($_POST['cancel_id']) && isset($_SESSION['user_id'])) {
    $query = $database->prepare('DELETE FROM reservations WHERE id = ? AND user_id = ?');
    $query->execute([$_POST['cancel_id'], $_SESSION['user_id']]);
}

$pageTitle = 'Reservations';
require 'things/header.php';

$query = $database->prepare('SELECT reservations.*, vehicles.name FROM reservations JOIN vehicles ON reservations.vehicle_id = vehicles.id WHERE reservations.user_id = ? ORDER BY reservations.id DESC');
$query->execute([$_SESSION['user_id']]);
$myReservations = $query->fetchAll();

$totalPrice = 0;
foreach ($myReservations as $reservation) {
    $totalPrice = $totalPrice + $reservation['total'];
}
?>

<section class="wrap">
    <h1>My reservations</h1>

    <?php if (count($myReservations) == 0) { ?>
        <p>You have no reservations yet. <a href="configurator.php">Configure a vehicle</a></p>
    <?php } else { ?>
        <table>
            <?php foreach ($myReservations as $reservation) { ?>
                <tr>
                    <td>
                        <strong><?= ($reservation['name']) ?></strong><br>
                        Colour: <?= ($reservation['color']) ?><br>
                        Wheels: <?= ($reservation['wheel']) ?><br>
                        Options: <?= ($reservation['features']) ?>
                    </td>
                    <td>$<?= number_format($reservation['total']) ?></td>
                    <td>
                        <form method="post">
                            <button class="btn ghost confirm-delete" name="cancel_id"
                                value="<?= $reservation['id'] ?>">Cancel</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <p class="price">Total $<?= number_format($totalPrice) ?></p>
    <?php } ?>
</section>

<script src="assets/js/forms.js"></script>

<?php require 'things/footer.php'; ?>