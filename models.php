<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Explore Porsche Models & Model Range';
require_once __DIR__ . '/things/header.php';

$categoryFilter = $_GET['category'] ?? '';
$engineFilter = $_GET['engine'] ?? '';
?>

<section class="wrap models-page">
    <div class="models-header">
        <span class="label-tiny">THE MODEL RANGE</span>
        <h1>Explore All Porsche Models</h1>
        <p class="section-sub">Discover the Porsche model spectrum. From track-born supercars to all-electric GTs and luxury SUVs.</p>
    </div>

    <!-- Luxury Filter Toolbar -->
    <form id="filters" class="luxury-filters-bar">
        <div class="filter-field search-field">
            <i class="fa-solid fa-magnifying-glass filter-icon"></i>
            <input type="text" name="search" id="modelSearch" placeholder="Search by model name (e.g. 911, Taycan)...">
        </div>

        <div class="filter-field">
            <select name="category" id="categorySelect">
                <option value="">All Categories</option>
                <?php foreach ($allCategories as $categoryName): ?>
                    <option value="<?= safe(strtolower($categoryName)) ?>" <?= strtolower($categoryFilter) === strtolower($categoryName) ? 'selected' : '' ?>>
                        <?= safe(ucfirst($categoryName)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-field">
            <select name="engine" id="engineSelect">
                <option value="">All Powertrains</option>
                <option value="petrol" <?= $engineFilter === 'petrol' ? 'selected' : '' ?>>Petrol (Gasoline)</option>
                <option value="electric" <?= $engineFilter === 'electric' ? 'selected' : '' ?>>100% Electric</option>
                <option value="hybrid" <?= $engineFilter === 'hybrid' ? 'selected' : '' ?>>E-Hybrid</option>
            </select>
        </div>

        <div class="filter-field price-field">
            <input type="number" name="max" id="maxPriceInput" placeholder="Max Price ($)">
        </div>

        <div class="filter-field">
            <select name="sort" id="sortSelect">
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="hp_desc">Power: Most Powerful</option>
                <option value="newest">Newest Model Year</option>
            </select>
        </div>

        <button type="button" class="btn-ghost-sm" id="btnResetFilters" title="Reset Filters">
            <i class="fa-solid fa-rotate-left"></i> Reset
        </button>
    </form>

    <div class="models-results-meta">
        <span id="resultsCount">Showing <?= count($allCars) ?> models</span>
    </div>

    <!-- Models Grid -->
    <div class="grid" id="grid">
        <?php foreach ($allCars as $car): ?>
            <?php include 'things/vehicle-card.php'; ?>
        <?php endforeach; ?>
    </div>

    <div id="noResults" class="empty-filter-state" style="display:none;">
        <i class="fa-solid fa-car-tunnel empty-icon"></i>
        <h3>No Porsche Models Match Your Filter Criteria</h3>
        <p>Try clearing your price limit or selecting a different powertrain category.</p>
        <button type="button" class="btn-porsche-primary" id="btnClearFilters">
            <i class="fa-solid fa-filter-circle-xmark"></i> Clear All Filters
        </button>
    </div>
</section>

<script src="assets/js/models.js"></script>

<?php require_once __DIR__ . '/things/footer.php'; ?>
