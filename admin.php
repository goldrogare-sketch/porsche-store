<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/admin-guard.php';
$pageTitle = 'Porsche Admin Operations Hub';
require_once __DIR__ . '/things/header.php';

$carCount = count($allCars);
$categoryCount = count($allCategories);
$userCount = $database->query('SELECT COUNT(*) FROM users')->fetchColumn();
$orderCount = $database->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
$totalRevenue = $database->query('SELECT COALESCE(SUM(total), 0) FROM reservations')->fetchColumn();
?>

<section class="wrap admin-hub">
    <div class="admin-header-row">
        <div>
            <span class="label-tiny">CENTRAL CONTROL</span>
            <h1>Porsche Dealership &amp; Studio Admin</h1>
            <p class="section-sub">Manage fleet inventory, monitor 3D configurator reservations, and manage user authorizations.</p>
        </div>
    </div>

    <div class="admin-kpi-grid">
        <article class="card kpi-card">
            <div class="kpi-icon"><i class="fa-solid fa-receipt"></i></div>
            <div class="kpi-info">
                <h3>Customer Orders</h3>
                <p class="kpi-number"><span class="count-up" data-number="<?= $orderCount ?>">0</span></p>
                <span class="kpi-sub">Total Value: $<?= number_format($totalRevenue) ?></span>
                <div class="kpi-action">
                    <a class="btn-porsche-primary" href="admin-orders.php"><i class="fa-solid fa-list-check"></i> Manage Orders</a>
                </div>
            </div>
        </article>

        <article class="card kpi-card">
            <div class="kpi-icon"><i class="fa-solid fa-car-side"></i></div>
            <div class="kpi-info">
                <h3>Vehicle Fleet</h3>
                <p class="kpi-number"><span class="count-up" data-number="<?= $carCount ?>">0</span></p>
                <span class="kpi-sub">Active models in store catalog</span>
                <div class="kpi-action">
                    <a class="btn-porsche-primary" href="admin-cars.php"><i class="fa-solid fa-sliders"></i> Manage Cars</a>
                </div>
            </div>
        </article>

        <article class="card kpi-card">
            <div class="kpi-icon"><i class="fa-solid fa-layer-group"></i></div>
            <div class="kpi-info">
                <h3>Vehicle Categories</h3>
                <p class="kpi-number"><span class="count-up" data-number="<?= $categoryCount ?>">0</span></p>
                <span class="kpi-sub">Sports, Sedans, SUVs</span>
                <div class="kpi-action">
                    <a class="btn-porsche-primary" href="admin-categories.php"><i class="fa-solid fa-tags"></i> Categories</a>
                </div>
            </div>
        </article>

        <article class="card kpi-card">
            <div class="kpi-icon"><i class="fa-solid fa-users"></i></div>
            <div class="kpi-info">
                <h3>Driver Accounts</h3>
                <p class="kpi-number"><span class="count-up" data-number="<?= $userCount ?>">0</span></p>
                <span class="kpi-sub">Registered buyers &amp; admins</span>
                <div class="kpi-action">
                    <a class="btn-porsche-primary" href="admin-users.php"><i class="fa-solid fa-user-gear"></i> Manage Users</a>
                </div>
            </div>
        </article>
    </div>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
