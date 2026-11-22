<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = 'Porsche Store & 3D Configurator Studio';
require_once __DIR__ . '/things/header.php';

// Fetch top featured cars
$featuredCars = $database->query('SELECT * FROM vehicles WHERE base_price >= 80000 ORDER BY base_price DESC LIMIT 4')->fetchAll();
if (empty($featuredCars)) {
    $featuredCars = $database->query('SELECT * FROM vehicles ORDER BY base_price DESC LIMIT 4')->fetchAll();
}
?>

<?php if (isset($_SESSION['welcome_name'])): ?>
    <div class="welcome-box" id="welcomeBox">
        <i class="fa-solid fa-circle-check"></i> Welcome to the Porsche Experience, <?= safe($_SESSION['welcome_name']) ?>!
    </div>
    <?php unset($_SESSION['welcome_name']); ?>
<?php endif; ?>

<!-- Cinematic Luxury Hero Section -->
<section class="hero-luxury" style="background-image: linear-gradient(180deg, rgba(7,8,10,0.4) 0%, rgba(7,8,10,0.85) 75%, #07080a 100%), url('assets/images/hero/main-hero.jpg');">
    <div class="hero-content">
        <div class="hero-eyebrow">
            <span class="eyebrow-line"></span>
            <span class="eyebrow-text">INTELLIGENT PERFORMANCE &bull; BESPOKE MOTORSPORT DNA</span>
            <span class="eyebrow-line"></span>
        </div>

        <h1 class="hero-title">DRIVEN BY DREAMS.<br><span class="gradient-text">CRAFTED BY YOU.</span></h1>
        
        <p class="hero-subtitle">
            Enter the interactive Porsche 3D Studio. Customize every specification in real time with dynamic pricing calculation, deploy active aerodynamics, and secure your factory build reservation.
        </p>

        <div class="hero-cta-group">
            <a class="btn-porsche-primary btn-lg" href="configurator.php">
                <i class="fa-solid fa-cube"></i> Launch 3D Configurator
            </a>
            <a class="btn-ghost-luxury btn-lg" href="models.php">
                <i class="fa-solid fa-car"></i> Explore Range
            </a>
        </div>

        <!-- Quick Performance Indicators -->
        <div class="hero-metrics">
            <div class="metric-card">
                <span class="metric-number">2.7 <span class="metric-unit">s</span></span>
                <span class="metric-label">0-100 km/h (911 Turbo S)</span>
            </div>
            <div class="metric-card">
                <span class="metric-number">640 <span class="metric-unit">hp</span></span>
                <span class="metric-label">Max Twin-Turbo Output</span>
            </div>
            <div class="metric-card">
                <span class="metric-number">800 <span class="metric-unit">V</span></span>
                <span class="metric-label">Next-Gen E-Performance</span>
            </div>
            <div class="metric-card">
                <span class="metric-number">360 <span class="metric-unit">&deg;</span></span>
                <span class="metric-label">Interactive 3D Studio</span>
            </div>
        </div>
    </div>
</section>

<!-- 3D Studio Experience Showcase Feature -->
<section class="wrap studio-feature-section">
    <div class="section-header-center">
        <span class="label-tiny">REVOLUTIONARY DIGITAL CRAFT</span>
        <h2>The Porsche 3D Configurator Experience</h2>
        <p class="section-sub">Real-time photorealistic automotive rendering engineered directly in your browser.</p>
    </div>

    <div class="feature-banner-grid">
        <div class="feature-banner-visual">
            <div class="visual-card">
                <img src="assets/images/vehicles/911-turbo/exterior/main.jpg" 
                     onerror="this.onerror=null;this.src='assets/images/placeholder.svg'" 
                     alt="3D Porsche Studio Preview">
                <div class="visual-badge">
                    <span class="pulse-dot"></span> Real-Time 3D WebGL Engine
                </div>
            </div>
        </div>

        <div class="feature-banner-details">
            <div class="experience-perk">
                <div class="perk-icon"><i class="fa-solid fa-palette"></i></div>
                <div>
                    <h4>Official Heritage &amp; Legends Paints</h4>
                    <p>Experiment with Guards Red, Gentian Blue, Racing Yellow, and exclusive Paint-to-Sample metallic finishes.</p>
                </div>
            </div>

            <div class="experience-perk">
                <div class="perk-icon"><i class="fa-solid fa-dharmachakra"></i></div>
                <div>
                    <h4>Forged Wheels &amp; Ceramic Calipers</h4>
                    <p>Switch between RS Spyder alloys, GT3 Center-Locks, and Porsche Ceramic Composite Brakes (PCCB).</p>
                </div>
            </div>

            <div class="experience-perk">
                <div class="perk-icon"><i class="fa-solid fa-wind"></i></div>
                <div>
                    <h4>Active Aerodynamics &amp; Carbon Aerokit</h4>
                    <p>Deploy rear high-downforce spoilers and lightweight carbon fiber components with real-time 3D feedback.</p>
                </div>
            </div>

            <div class="experience-perk">
                <div class="perk-icon"><i class="fa-solid fa-calculator"></i></div>
                <div>
                    <h4>Live Dynamic Price Calculation</h4>
                    <p>Watch your build price recalculate instantly with each bespoke modification, with zero hidden surprises.</p>
                </div>
            </div>

            <div class="feature-action-row">
                <a href="configurator.php" class="btn-porsche-primary">
                    <i class="fa-solid fa-sliders"></i> Open 3D Studio Now
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Featured Porsche Models -->
<section class="wrap featured-section">
    <div class="section-header-between">
        <div>
            <span class="label-tiny">ICONIC LINEUP</span>
            <h2>Featured Porsche Vehicles</h2>
            <p class="section-sub">Select a high-performance sports car, sedan, or SUV to customize.</p>
        </div>
        <a href="models.php" class="btn-ghost-luxury">
            View All Models <i class="fa-solid fa-arrow-right"></i>
        </a>
    </div>

    <div class="grid">
        <?php foreach ($featuredCars as $car) {
            include 'things/vehicle-card.php';
        } ?>
    </div>
</section>

<!-- Porsche Bespoke Pillars -->
<section class="wrap pillars-section">
    <div class="section-header-center">
        <span class="label-tiny">THE BESPOKE PROCESS</span>
        <h2>How to Order Your Custom Porsche</h2>
    </div>

    <div class="pillars-grid">
        <div class="pillar-card">
            <span class="pillar-step">01</span>
            <h3>Select Your Model</h3>
            <p>Choose from iconic 911 sports coupes, all-electric Taycan saloons, or versatile Macan and Cayenne SUVs.</p>
        </div>
        <div class="pillar-card">
            <span class="pillar-step">02</span>
            <h3>Customize in 3D</h3>
            <p>Personalize colors, rims, calipers, leather interiors, and performance packs in our 360-degree WebGL studio.</p>
        </div>
        <div class="pillar-card">
            <span class="pillar-step">03</span>
            <h3>Real-Time Pricing</h3>
            <p>Every option dynamically updates your itemized investment breakdown with complete factory pricing transparency.</p>
        </div>
        <div class="pillar-card">
            <span class="pillar-step">04</span>
            <h3>Direct Dealership Order</h3>
            <p>Reserve priority factory build slots or schedule a VIP test drive at your preferred certified Porsche Center.</p>
        </div>
    </div>
</section>

<!-- Ferry Porsche Heritage Quote -->
<section class="wrap quote-section">
    <div class="quote-box">
        <i class="fa-solid fa-quote-left quote-icon"></i>
        <blockquote class="quote-text">
            &ldquo;In the beginning I looked around and could not find quite the car I dreamed of which is why I decided to build it myself.&rdquo;
        </blockquote>
        <cite class="quote-author">&mdash; Ferry Porsche</cite>
    </div>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
