<article class="card" data-category="<?= safe($car['category']) ?>" data-engine="<?= safe($car['engine']) ?>"
    data-price="<?= $car['base_price'] ?>" data-year="<?= $car['year'] ?>">
    <img src="assets/images/vehicles/<?= $car['slug'] ?>/exterior/main.jpg"
        onerror="this.onerror=null;this.src='assets/images/placeholder.svg'" alt="<?= safe($car['name']) ?>"
        loading="lazy">

    <div>
        <h3><?= ($car['name']) ?></h3>
        <p><?= ($car['description']) ?></p>
        <p><?= $car['hp'] ?> hp ; 0-100 km/h <?= $car['accel'] ?>s</p>
        <p class="price">From $<?= number_format($car['base_price']) ?></p>

        <a class="btn ghost" href="vehicle-details.php?id=<?= $car['id'] ?>">View details</a>
        <a class="btn" href="configurator.php?id=<?= $car['id'] ?>">Configure</a>
    </div>
</article>