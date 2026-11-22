<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$pageTitle = '3D Porsche Car Configurator & Studio';
require_once __DIR__ . '/things/header.php';

$carId = 1;
if (isset($_GET['id'])) {
    $requestedId = (int) $_GET['id'];
    if (isset($allCars[$requestedId])) {
        $carId = $requestedId;
    }
}
$selectedCar = $allCars[$carId] ?? reset($allCars);
?>

<div class="configurator-wrapper">
    <!-- Top Model Switcher & Performance Spec Bar -->
    <header class="cfg-top-bar">
        <div class="cfg-model-picker">
            <span class="label-tiny">SELECT MODEL</span>
            <select id="modelSelector" class="model-select-dropdown">
                <?php foreach ($allCars as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $carId ? 'selected' : '' ?>
                        data-base="<?= $c['base_price'] ?>"
                        data-slug="<?= safe($c['slug']) ?>"
                        data-category="<?= safe($c['category']) ?>"
                        data-hp="<?= $c['hp'] ?>"
                        data-accel="<?= $c['accel'] ?>"
                        data-speed="<?= $c['top_speed'] ?>"
                        data-engine="<?= safe($c['engine']) ?>">
                        <?= safe($c['name']) ?> &bull; From $<?= number_format($c['base_price']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="cfg-quick-specs">
            <div class="quick-spec-item">
                <span class="spec-val" id="specHp"><?= $selectedCar['hp'] ?> hp</span>
                <span class="spec-lbl">Power</span>
            </div>
            <div class="quick-spec-divider"></div>
            <div class="quick-spec-item">
                <span class="spec-val" id="specAccel"><?= $selectedCar['accel'] ?> s</span>
                <span class="spec-lbl">0-100 km/h</span>
            </div>
            <div class="quick-spec-divider"></div>
            <div class="quick-spec-item">
                <span class="spec-val" id="specSpeed"><?= $selectedCar['top_speed'] ?> km/h</span>
                <span class="spec-lbl">Top Speed</span>
            </div>
            <div class="quick-spec-divider"></div>
            <div class="quick-spec-item">
                <span class="spec-val" id="specEngine" style="text-transform: capitalize;"><?= safe($selectedCar['engine']) ?></span>
                <span class="spec-lbl">Powertrain</span>
            </div>
        </div>
    </header>

    <div class="cfg-layout">
        <!-- 3D WebGL Canvas Stage -->
        <div class="cfg-canvas-col">
            <div class="cfg-canvas-container" id="porsche3DStage">
                <!-- Three.js Canvas injected here -->
                <div class="canvas-loading" id="canvasLoader">
                    <div class="spinner"></div>
                    <span>Initializing Porsche 3D Studio...</span>
                </div>
            </div>

            <!-- Floating 3D Control Bar -->
            <div class="canvas-floating-dock">
                <!-- Camera Preset Angles -->
                <div class="dock-group camera-presets">
                    <button type="button" class="dock-btn active" data-preset="front34" title="Front 3/4 View">
                        <i class="fa-solid fa-car-side"></i> <span>Front 3/4</span>
                    </button>
                    <button type="button" class="dock-btn" data-preset="side" title="Side Profile">
                        <i class="fa-solid fa-grip-lines"></i> <span>Profile</span>
                    </button>
                    <button type="button" class="dock-btn" data-preset="rear34" title="Rear 3/4 View">
                        <i class="fa-solid fa-car-rear"></i> <span>Rear</span>
                    </button>
                    <button type="button" class="dock-btn" data-preset="cockpit" title="Interior Cockpit View">
                        <i class="fa-solid fa-couch"></i> <span>Interior</span>
                    </button>
                    <button type="button" class="dock-btn" data-preset="wheels" title="Wheels & Brakes View">
                        <i class="fa-solid fa-circle-dot"></i> <span>Wheels</span>
                    </button>
                    <button type="button" class="dock-btn" data-preset="top" title="Top View">
                        <i class="fa-solid fa-arrows-up-down"></i> <span>Top</span>
                    </button>
                </div>

                <!-- Studio Interactive Actions -->
                <div class="dock-group studio-actions">
                    <button type="button" class="dock-btn" id="btnTurntable" title="Toggle 360 Turntable Rotation">
                        <i class="fa-solid fa-rotate"></i> <span>Auto 360°</span>
                    </button>
                    <button type="button" class="dock-btn active" id="btnToggleLights" title="Headlights & Taillights On/Off">
                        <i class="fa-solid fa-lightbulb"></i> <span>Lights</span>
                    </button>
                    <button type="button" class="dock-btn" id="btnEngineSound" title="Rev Porsche Flat-Six Engine">
                        <i class="fa-solid fa-volume-high"></i> <span>Engine Rev</span>
                    </button>
                    <button type="button" class="dock-btn" id="btnTakeScreenshot" title="Take HD Screenshot of your Porsche">
                        <i class="fa-solid fa-camera"></i> <span>Save Photo</span>
                    </button>
                </div>
            </div>

            <!-- Hint overlay -->
            <div class="canvas-hint">
                <i class="fa-solid fa-hand-pointer"></i> Drag to rotate 360&deg; &bull; Pinch/Scroll to zoom &bull; Real-time 3D Preview
            </div>
        </div>

        <!-- Customization Controls Panel -->
        <div class="cfg-panel-col">
            <form id="configuratorForm" method="post" action="reserve.php" 
                  data-base="<?= $selectedCar['base_price'] ?>" 
                  data-id="<?= $selectedCar['id'] ?>"
                  data-category="<?= safe($selectedCar['category']) ?>">
                
                <input type="hidden" name="car" id="inputCarId" value="<?= $selectedCar['id'] ?>">

                <!-- Tab Navigation -->
                <div class="cfg-tabs-nav">
                    <button type="button" class="cfg-tab active" data-tab="tab-color">
                        <i class="fa-solid fa-palette"></i> <span>Paint</span>
                    </button>
                    <button type="button" class="cfg-tab" data-tab="tab-wheels">
                        <i class="fa-solid fa-dharmachakra"></i> <span>Wheels</span>
                    </button>
                    <button type="button" class="cfg-tab" data-tab="tab-brakes">
                        <i class="fa-solid fa-compact-disc"></i> <span>Brakes</span>
                    </button>
                    <button type="button" class="cfg-tab" data-tab="tab-interior">
                        <i class="fa-solid fa-couch"></i> <span>Interior</span>
                    </button>
                    <button type="button" class="cfg-tab" data-tab="tab-aero">
                        <i class="fa-solid fa-wind"></i> <span>Aerokit</span>
                    </button>
                    <button type="button" class="cfg-tab" data-tab="tab-options">
                        <i class="fa-solid fa-sliders"></i> <span>Options</span>
                    </button>
                    <button type="button" class="cfg-tab" data-tab="tab-summary">
                        <i class="fa-solid fa-file-invoice-dollar"></i> <span>Summary</span>
                    </button>
                </div>

                <div class="cfg-tabs-content">
                    <!-- Tab 1: Exterior Color & Finish -->
                    <div class="cfg-tab-pane active" id="tab-color">
                        <div class="pane-header">
                            <h3>Exterior Paint Colour</h3>
                            <p class="pane-sub">Choose from Porsche Heritage, Metallic and Legends finishes.</p>
                        </div>
                        <div class="colors-grid">
                            <?php foreach ($allColors as $idx => $color): ?>
                                <label class="color-swatch-card <?= $idx === 1 ? 'selected' : '' ?>">
                                    <input type="radio" name="color" value="<?= $color['id'] ?>" 
                                           data-price="<?= $color['price'] ?>" 
                                           data-hex="<?= $color['hex'] ?>" 
                                           data-name="<?= safe($color['name']) ?>"
                                           <?= $idx === 1 ? 'checked' : '' ?>>
                                    <span class="color-swatch-circle" style="background-color: <?= $color['hex'] ?>;">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                    <div class="color-info">
                                        <span class="color-name"><?= safe($color['name']) ?></span>
                                        <span class="color-meta"><?= safe($color['finish']) ?> &bull; <?= $color['price'] == 0 ? 'Included' : '+$' . number_format($color['price']) ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tab 2: Wheels & Rims -->
                    <div class="cfg-tab-pane" id="tab-wheels">
                        <div class="pane-header">
                            <h3>Wheels &amp; Rims</h3>
                            <p class="pane-sub">Performance-engineered lightweight forged alloy and aero designs.</p>
                        </div>
                        <div class="options-list">
                            <?php foreach ($allWheels as $wIdx => $wheel): ?>
                                <label class="option-card wheel-card <?= $wIdx === 1 ? 'selected' : '' ?>">
                                    <input type="radio" name="wheel" value="<?= $wheel['id'] ?>" 
                                           data-price="<?= $wheel['price'] ?>"
                                           data-style="<?= $wheel['style'] ?>"
                                           data-color="<?= $wheel['color'] ?>"
                                           data-name="<?= safe($wheel['name']) ?>"
                                           <?= $wIdx === 1 ? 'checked' : '' ?>>
                                    <div class="option-card-content">
                                        <div class="wheel-icon-box" style="color: <?= $wheel['color'] ?>">
                                            <i class="fa-solid fa-dharmachakra"></i>
                                        </div>
                                        <div class="option-text">
                                            <h4><?= safe($wheel['name']) ?></h4>
                                            <span class="badge-price"><?= $wheel['price'] == 0 ? 'Standard Equipment' : '+$' . number_format($wheel['price']) ?></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tab 3: Performance Brake Calipers -->
                    <div class="cfg-tab-pane" id="tab-brakes">
                        <div class="pane-header">
                            <h3>Performance Brake System</h3>
                            <p class="pane-sub">Visible high-performance calipers with drilled composite ceramic discs.</p>
                        </div>
                        <div class="options-list">
                            <?php foreach ($allCalipers as $cIdx => $caliper): ?>
                                <label class="option-card caliper-card <?= $cIdx === 1 ? 'selected' : '' ?>">
                                    <input type="radio" name="caliper" value="<?= $caliper['id'] ?>" 
                                           data-price="<?= $caliper['price'] ?>"
                                           data-hex="<?= $caliper['hex'] ?>"
                                           data-name="<?= safe($caliper['name']) ?>"
                                           <?= $cIdx === 1 ? 'checked' : '' ?>>
                                    <div class="option-card-content">
                                        <span class="caliper-color-badge" style="background-color: <?= $caliper['hex'] ?>;"></span>
                                        <div class="option-text">
                                            <h4><?= safe($caliper['name']) ?></h4>
                                            <span class="badge-price"><?= $caliper['price'] == 0 ? 'Standard' : '+$' . number_format($caliper['price']) ?></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tab 4: Interior Leather & Upholstery -->
                    <div class="cfg-tab-pane" id="tab-interior">
                        <div class="pane-header">
                            <h3>Interior Upholstery &amp; Leather</h3>
                            <p class="pane-sub">Mastercrafted automotive leather, Race-Tex and ergonomic sports seating.</p>
                        </div>
                        <div class="options-list">
                            <?php foreach ($allInteriors as $iIdx => $interior): ?>
                                <label class="option-card interior-card <?= $iIdx === 1 ? 'selected' : '' ?>">
                                    <input type="radio" name="interior" value="<?= $interior['id'] ?>" 
                                           data-price="<?= $interior['price'] ?>"
                                           data-hex="<?= $interior['hex'] ?>"
                                           data-name="<?= safe($interior['name']) ?>"
                                           <?= $iIdx === 1 ? 'checked' : '' ?>>
                                    <div class="option-card-content">
                                        <span class="leather-swatch" style="background-color: <?= $interior['hex'] ?>;"></span>
                                        <div class="option-text">
                                            <h4><?= safe($interior['name']) ?></h4>
                                            <span class="badge-price"><?= $interior['price'] == 0 ? 'Standard' : '+$' . number_format($interior['price']) ?></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tab 5: Aerodynamics & Exterior Packages -->
                    <div class="cfg-tab-pane" id="tab-aero">
                        <div class="pane-header">
                            <h3>Aerodynamics &amp; Exterior Styling</h3>
                            <p class="pane-sub">Motorsport active aerodynamics, carbon fiber splitters and high-downforce wings.</p>
                        </div>
                        <div class="options-list">
                            <?php foreach ($allAero as $aIdx => $aero): ?>
                                <label class="option-card aero-card <?= $aIdx === 1 ? 'selected' : '' ?>">
                                    <input type="radio" name="aero" value="<?= $aero['id'] ?>" 
                                           data-price="<?= $aero['price'] ?>"
                                           data-code="<?= $aero['code'] ?>"
                                           data-name="<?= safe($aero['name']) ?>"
                                           <?= $aIdx === 1 ? 'checked' : '' ?>>
                                    <div class="option-card-content">
                                        <div class="aero-icon-box"><i class="fa-solid fa-wind"></i></div>
                                        <div class="option-text">
                                            <h4><?= safe($aero['name']) ?></h4>
                                            <span class="badge-price"><?= $aero['price'] == 0 ? 'Standard Package' : '+$' . number_format($aero['price']) ?></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tab 6: Performance & Technology Packages -->
                    <div class="cfg-tab-pane" id="tab-options">
                        <div class="pane-header">
                            <h3>Performance &amp; Luxury Equipment</h3>
                            <p class="pane-sub">Precision chassis dynamics, acoustics, and driver assistance systems.</p>
                        </div>
                        <div class="features-list">
                            <?php foreach ($allFeatures as $feature): ?>
                                <label class="feature-checkbox-card">
                                    <input type="checkbox" name="features[]" value="<?= $feature['id'] ?>" 
                                           data-price="<?= $feature['price'] ?>"
                                           data-name="<?= safe($feature['name']) ?>">
                                    <div class="feature-content">
                                        <div class="feature-header">
                                            <span class="feature-name"><?= safe($feature['name']) ?></span>
                                            <span class="feature-price">+$<?= number_format($feature['price']) ?></span>
                                        </div>
                                        <p class="feature-desc"><?= safe($feature['desc']) ?></p>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tab 7: Specification Summary -->
                    <div class="cfg-tab-pane" id="tab-summary">
                        <div class="pane-header">
                            <h3>Bespoke Build Summary</h3>
                            <p class="pane-sub">Review your individual configuration and pricing breakdown.</p>
                        </div>
                        <div class="summary-breakdown-card" id="summaryBreakdown">
                            <!-- Populated dynamically via JS -->
                        </div>
                    </div>
                </div>

                <!-- Bottom Floating Sticky Price & Action Bar -->
                <div class="cfg-action-bar">
                    <div class="cfg-price-display">
                        <span class="label-tiny">TOTAL VEHICLE PRICE</span>
                        <div class="price-val-row">
                            <span class="currency">$</span>
                            <span class="amount" id="totalPriceTicker"><?= number_format($selectedCar['base_price']) ?></span>
                        </div>
                    </div>

                    <div class="cfg-action-buttons">
                        <button type="button" class="btn-ghost-luxury" id="btnOpenBreakdown" title="View Full Price Breakdown">
                            <i class="fa-solid fa-list-check"></i> Specs
                        </button>
                        <button type="button" class="btn-porsche-primary" id="btnLaunchOrderModal">
                            <i class="fa-solid fa-shield-halved"></i> Reserve &amp; Order
                        </button>
                    </div>
                </div>

                <!-- Order / Reservation Modal -->
                <div class="porsche-modal" id="orderModal">
                    <div class="porsche-modal-backdrop" id="modalBackdrop"></div>
                    <div class="porsche-modal-box">
                        <button type="button" class="modal-close-btn" id="modalCloseBtn">&times;</button>
                        <div class="modal-header">
                            <div class="porsche-wordmark">PORSCHE</div>
                            <h2>Reserve Your Bespoke <?= safe($selectedCar['name']) ?></h2>
                            <p class="modal-sub">Confirm your 3D customization and allocate your production slot at your certified Porsche Center.</p>
                        </div>

                        <div class="modal-build-badge">
                            <div class="badge-car-title">
                                <span class="badge-model" id="modalCarName"><?= safe($selectedCar['name']) ?></span>
                                <span class="badge-total" id="modalTotalPrice">$<?= number_format($selectedCar['base_price']) ?></span>
                            </div>
                            <div class="badge-specs-summary" id="modalSpecsSummary">
                                <!-- JS Injected summary tags -->
                            </div>
                        </div>

                        <div class="modal-form-grid">
                            <div class="form-group full-width">
                                <label for="reservationType">Reservation Type</label>
                                <select name="reservation_type" id="reservationType" class="form-input">
                                    <option value="Custom Factory Build Order">Custom Factory Build Order (Priority Allocation)</option>
                                    <option value="VIP Test Drive Booking">Schedule VIP Dealership Test Drive</option>
                                    <option value="Purchase Quote Request">Official Dealership Purchase Quote</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="custName">Full Name *</label>
                                <input type="text" id="custName" name="customer_name" class="form-input" 
                                       value="<?= safe($_SESSION['user_name'] ?? '') ?>" placeholder="e.g. Alexander Porsche" required>
                            </div>

                            <div class="form-group">
                                <label for="custEmail">Email Address *</label>
                                <input type="email" id="custEmail" name="customer_email" class="form-input" 
                                       placeholder="driver@porsche-experience.com" required>
                            </div>

                            <div class="form-group">
                                <label for="custPhone">Phone Number *</label>
                                <input type="tel" id="custPhone" name="customer_phone" class="form-input" 
                                       placeholder="+1 (555) 911-0000" required>
                            </div>

                            <div class="form-group">
                                <label for="dealershipSelect">Preferred Porsche Center</label>
                                <select name="dealership" id="dealershipSelect" class="form-input">
                                    <option value="Porsche Experience Center Los Angeles">Porsche Experience Center Los Angeles, CA</option>
                                    <option value="Porsche Center Beverly Hills">Porsche Center Beverly Hills, CA</option>
                                    <option value="Porsche Center Manhattan">Porsche Center Manhattan, New York, NY</option>
                                    <option value="Porsche Experience Center Stuttgart">Porsche Experience Center Stuttgart-Zuffenhausen</option>
                                    <option value="Porsche Center Mayfair London">Porsche Center Mayfair, London, UK</option>
                                    <option value="Porsche Center Dubai">Porsche Center Dubai, UAE</option>
                                </select>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn-porsche-primary btn-block">
                                <i class="fa-solid fa-lock"></i> Submit Reservation &amp; View Order Sheet
                            </button>
                            <p class="modal-note">
                                <i class="fa-solid fa-shield"></i> Your reservation is registered securely. A dedicated Porsche Brand Ambassador will contact you with build slot confirmation.
                            </p>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Three.js and OrbitControls from CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script src="assets/js/porsche-3d.js"></script>
<script src="assets/js/configurator.js"></script>

<?php require_once __DIR__ . '/things/footer.php'; ?>
