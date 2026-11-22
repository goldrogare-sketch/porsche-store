<?php
session_start();
require 'things/admin-guard.php';
$pageTitle = 'Admin';
require 'things/header.php';

$carCount = count($allCars);
$categoryCount = count($allCategories);
$userCount = $database->query('SELECT COUNT(*) FROM users')->fetchColumn();
?>

<section class="wrap">
    <h1>Admin panel</h1>

    <div class="grid">
        <article class="card"> /*?? */
            <div>
                <h3>Cars</h3>
                <p>Total: <span class="count-up" data-number="<?= $carCount ?>">0</span></p>
                <a class="btn" href="admin-cars.php">Manage cars</a>
            </div>
        </article>

        <article class="card">
            <div>
                <h3>Categories</h3>
                <p>Total: <span class="count-up" data-number="<?= $categoryCount ?>">0</span></p>
                <a class="btn" href="admin-categories.php">Manage categories</a>
            </div>
        </article>

        <article class="card">
            <div>
                <h3>Users</h3>
                <p>Total: <span class="count-up" data-number="<?= $userCount ?>">0</span></p>
                <a class="btn" href="admin-users.php">Manage users</a>
            </div>
        </article>
    </div>
</section>

<?php require 'things/footer.php'; ?>