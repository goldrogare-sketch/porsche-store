<?php
session_start();
$pageTitle = 'Models';
require 'things/header.php';
?>

<section class="wrap">
    <h1>Models</h1>

    <form id="filters" class="filters">
        <select name="category">
            <option value="">All categories</option>
            <?php foreach ($allCategories as $categoryName) { ?>
                <option><?= ($categoryName) ?></option>
            <?php } ?>
        </select>

        <select name="engine">
            <option value="">All engines</option>
            <option>petrol</option>
            <option>electric</option>
            <option>hybrid</option>
        </select>

        <input type="number" name="max" placeholder="Max price">

        <select name="sort">
            <option value="price_asc">Price: low to high</option>
            <option value="price_desc">Price: high to low</option>
            <option value="newest">Newest</option>
        </select>

        <button class="btn">Apply</button>
    </form>

    <div class="grid" id="grid">
        <?php foreach ($allCars as $car) {
            include 'things/vehicle-card.php';
        } ?>
    </div>

    <p id="noResults" style="display:none">No models match. Clear a filter and try again.</p>
</section>

<script src="assets/js/models.js"></script>

<?php require 'things/footer.php'; ?>