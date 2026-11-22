<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Vehicle Details';
require_once __DIR__ . '/things/header.php';

$carId = 0;
if (isset($_GET['id'])) {
    $carId = (int) $_GET['id'];
}

if (!isset($allCars[$carId])) {
    ?>
    <section class="wrap">
        <div class="empty-state-card">
            <i class="fa-solid fa-triangle-exclamation empty-icon"></i>
            <h2>Vehicle Model Not Found</h2>
            <p>The requested Porsche vehicle does not exist or has been retired.</p>
            <div class="empty-actions">
                <a class="btn-porsche-primary" href="models.php"><i class="fa-solid fa-arrow-left"></i> Return to Models</a>
            </div>
        </div>
    </section>
    <?php
    require_once __DIR__ . '/things/footer.php';
    exit;
}

$car = $allCars[$carId];
$pageTitle = $car['name'] . ' Specifications & Details';
?>

<section class="wrap vehicle-details-page">
    <div class="details-breadcrumbs">
        <a href="models.php"><i class="fa-solid fa-arrow-left"></i> All Models</a>
        <span>/</span>
        <span class="current"><?= safe($car['name']) ?></span>
    </div>

    <div class="details-grid">
        <!-- Visual Media Gallery -->
        <div class="details-media-col">
            <div class="details-main-image">
                <img id="mainGalleryImg" src="assets/images/vehicles/<?= $car['slug'] ?>/exterior/main.jpg"
                     onerror="this.onerror=null;this.src='assets/images/placeholder.svg'" 
                     alt="<?= safe($car['name']) ?> Exterior View">
                <span class="media-tag"><i class="fa-solid fa-camera"></i> Exterior Studio</span>
            </div>

            <div class="details-thumbnails">
                <button type="button" class="thumb-btn active" onclick="switchDetailImg('assets/images/vehicles/<?= $car['slug'] ?>/exterior/main.jpg', 'Exterior')">
                    <img src="assets/images/vehicles/<?= $car['slug'] ?>/exterior/main.jpg" 
                         onerror="this.onerror=null;this.src='assets/images/placeholder.svg'" alt="Exterior Thumbnail">
                    <span>Exterior</span>
                </button>
                <button type="button" class="thumb-btn" onclick="switchDetailImg('assets/images/vehicles/<?= $car['slug'] ?>/interior/main.jpg', 'Interior')">
                    <img src="assets/images/vehicles/<?= $car['slug'] ?>/interior/main.jpg" 
                         onerror="this.onerror=null;this.src='assets/images/placeholder.svg'" alt="Interior Thumbnail">
                    <span>Interior</span>
                </button>
            </div>
        </div>

        <!-- Technical Specs & Ordering CTA -->
        <div class="details-info-col">
            <div class="details-header">
                <div class="details-badges">
                    <span class="card-badge badge-engine"><?= safe(ucfirst($car['engine'])) ?></span>
                    <span class="card-badge badge-category"><?= safe(ucfirst($car['category'])) ?></span>
                    <span class="badge-year"><?= $car['year'] ?> Model Year</span>
                </div>
                <h1><?= safe($car['name']) ?></h1>
                <p class="details-description"><?= safe($car['description']) ?></p>
            </div>

            <div class="details-price-bar">
                <span class="price-lbl">Starting Base Price</span>
                <span class="price-val">$<?= number_format($car['base_price']) ?></span>
            </div>

            <!-- Key Specs Metric Grid -->
            <div class="details-metrics-grid">
                <div class="d-metric">
                    <span class="dm-val"><?= $car['hp'] ?> <span class="dm-unit">hp</span></span>
                    <span class="dm-lbl">Max Power Output</span>
                </div>
                <div class="d-metric">
                    <span class="dm-val"><?= $car['accel'] ?> <span class="dm-unit">s</span></span>
                    <span class="dm-lbl">0 - 100 km/h</span>
                </div>
                <div class="d-metric">
                    <span class="dm-val"><?= $car['top_speed'] ?> <span class="dm-unit">km/h</span></span>
                    <span class="dm-lbl">Top Track Speed</span>
                </div>
                <div class="d-metric">
                    <span class="dm-val" style="text-transform: capitalize;"><?= safe($car['engine']) ?></span>
                    <span class="dm-lbl">Drive System</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="details-actions">
                <a class="btn-porsche-primary btn-lg" href="configurator.php?id=<?= $car['id'] ?>">
                    <i class="fa-solid fa-cube"></i> Customize in 3D Studio
                </a>
                <button type="button" class="btn-ghost-luxury btn-lg" id="btnPlaySoundDetails">
                    <i class="fa-solid fa-volume-high"></i> Rev Engine
                </button>
            </div>

            <div class="details-highlights">
                <h4>Porsche Factory Highlights</h4>
                <ul>
                    <li><i class="fa-solid fa-check text-red"></i> Real-time 3D interactive preview with custom colors and aero packages</li>
                    <li><i class="fa-solid fa-check text-red"></i> Available Porsche Ceramic Composite Brakes (PCCB)</li>
                    <li><i class="fa-solid fa-check text-red"></i> Sport Chrono Package with mode switch and track telemetry</li>
                    <li><i class="fa-solid fa-check text-red"></i> Priority allocation and delivery through official Porsche Centers</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<script src="assets/js/porsche-3d.js"></script>
<script>
function switchDetailImg(src, label) {
    var main = document.getElementById('mainGalleryImg');
    if (main) main.src = src;
    var btns = document.querySelectorAll('.thumb-btn');
    btns.forEach(function(b) { b.classList.remove('active'); });
    if (event && event.currentTarget) event.currentTarget.classList.add('active');
}

var soundBtn = document.getElementById('btnPlaySoundDetails');
if (soundBtn) {
    soundBtn.addEventListener('click', function() {
        if (typeof window.playPorscheEngineSound === 'function') {
            window.playPorscheEngineSound();
            if (typeof window.toast === 'function') window.toast('Engine Roar Activated!');
        }
    });
}
</script>

<?php require_once __DIR__ . '/things/footer.php'; ?>
