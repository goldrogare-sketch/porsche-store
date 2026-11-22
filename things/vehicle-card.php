<article class="luxury-car-card card" 
         data-category="<?= safe($car['category']) ?>" 
         data-engine="<?= safe($car['engine']) ?>" 
         data-price="<?= $car['base_price'] ?>" 
         data-year="<?= $car['year'] ?>"
         data-hp="<?= $car['hp'] ?>"
         data-name="<?= safe(strtolower($car['name'])) ?>">
    <div class="card-media">
        <img src="assets/images/vehicles/<?= $car['slug'] ?>/exterior/main.jpg" 
             onerror="this.onerror=null;this.src='assets/images/placeholder.svg'" 
             alt="<?= safe($car['name']) ?>" 
             loading="lazy">
        <div class="card-tags">
            <span class="card-badge badge-engine"><i class="fa-solid fa-bolt-lightning"></i> <?= safe(ucfirst($car['engine'])) ?></span>
            <span class="card-badge badge-category"><?= safe(ucfirst($car['category'])) ?></span>
        </div>
    </div>

    <div class="card-body">
        <div class="card-title-row">
            <h3><?= safe($car['name']) ?></h3>
            <span class="card-year"><?= $car['year'] ?></span>
        </div>
        <p class="card-desc"><?= safe($car['description']) ?></p>

        <div class="card-specs-row">
            <div class="spec-cell">
                <span class="s-val"><?= $car['hp'] ?></span>
                <span class="s-lbl">Horsepower</span>
            </div>
            <div class="spec-cell">
                <span class="s-val"><?= $car['accel'] ?>s</span>
                <span class="s-lbl">0-100 km/h</span>
            </div>
            <div class="spec-cell">
                <span class="s-val"><?= $car['top_speed'] ?></span>
                <span class="s-lbl">Top km/h</span>
            </div>
        </div>

        <div class="card-footer-row">
            <div class="card-price-box">
                <span class="price-from">From</span>
                <span class="price-amount">$<?= number_format($car['base_price']) ?></span>
            </div>
            <div class="card-btn-group">
                <a class="btn-ghost-sm" href="vehicle-details.php?id=<?= $car['id'] ?>" title="View Full Specifications">
                    <i class="fa-solid fa-circle-info"></i> Specs
                </a>
                <a class="btn-porsche-sm" href="configurator.php?id=<?= $car['id'] ?>" title="Customize in 3D">
                    <i class="fa-solid fa-cube"></i> Configure 3D
                </a>
            </div>
        </div>
    </div>
</article>
