<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/db.php';

$pageTitle = 'My Porsche Reservations & Orders';
$message = '';
$messageType = '';

// Handle Cancellation
if (isset($_POST['cancel_id']) && isset($_SESSION['user_id'])) {
    $cancelId = (int) $_POST['cancel_id'];
    $query = $database->prepare('DELETE FROM reservations WHERE id = ? AND user_id = ?');
    $query->execute([$cancelId, $_SESSION['user_id']]);
    $message = 'Your reservation #' . $cancelId . ' has been cancelled successfully.';
    $messageType = 'notice';
}

require_once __DIR__ . '/things/header.php';

$userId = $_SESSION['user_id'] ?? null;
$myReservations = [];

if ($userId) {
    $query = $database->prepare('SELECT reservations.*, vehicles.name, vehicles.slug, vehicles.base_price, vehicles.hp, vehicles.accel 
                                 FROM reservations 
                                 JOIN vehicles ON reservations.vehicle_id = vehicles.id 
                                 WHERE reservations.user_id = ? 
                                 ORDER BY reservations.id DESC');
    $query->execute([$userId]);
    $myReservations = $query->fetchAll();
} elseif (isset($_GET['lookup_code'])) {
    $lookupCode = trim($_GET['lookup_code']);
    $query = $database->prepare('SELECT reservations.*, vehicles.name, vehicles.slug, vehicles.base_price, vehicles.hp, vehicles.accel 
                                 FROM reservations 
                                 JOIN vehicles ON reservations.vehicle_id = vehicles.id 
                                 WHERE reservations.order_code = ? 
                                 ORDER BY reservations.id DESC');
    $query->execute([$lookupCode]);
    $myReservations = $query->fetchAll();
}

$totalInvested = 0;
foreach ($myReservations as $res) {
    $totalInvested += (int) $res['total'];
}
?>

<section class="wrap reservations-page">
    <?php if (isset($_GET['booked']) && isset($_SESSION['latest_order_code'])): ?>
        <div class="booking-success-banner">
            <div class="success-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div class="success-text">
                <h3>Congratulations on Reserving Your Bespoke <?= safe($_SESSION['latest_order_car'] ?? 'Porsche') ?>!</h3>
                <p>Your official Porsche build order reference is: <strong class="order-code-highlight"><?= safe($_SESSION['latest_order_code']) ?></strong></p>
                <p class="sub-text">A confirmation email has been dispatched and your allocated Porsche Center concierge will contact you within 24 hours.</p>
            </div>
            <button type="button" class="btn-ghost-luxury" onclick="window.print()">
                <i class="fa-solid fa-print"></i> Print Voucher
            </button>
        </div>
        <?php unset($_SESSION['latest_order_code'], $_SESSION['latest_order_car'], $_SESSION['latest_order_total']); ?>
    <?php endif; ?>

    <?php if ($message != ''): ?>
        <div class="notice <?= $messageType ?>"><?= safe($message) ?></div>
    <?php endif; ?>

    <div class="orders-header-row">
        <div>
            <span class="label-tiny">CUSTOMER GARAGE &amp; ALLOCATIONS</span>
            <h1>My Porsche Orders &amp; Reservations</h1>
            <p class="section-sub">Track production status, manage bespoke build sheets, or schedule concierge test drives.</p>
        </div>

        <?php if (!empty($myReservations)): ?>
            <div class="orders-summary-stat">
                <span class="stat-lbl">Active Builds Value</span>
                <span class="stat-val">$<?= number_format($totalInvested) ?></span>
                <span class="stat-sub"><?= count($myReservations) ?> Vehicle<?= count($myReservations) > 1 ? 's' : '' ?> on Order</span>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$userId && empty($myReservations)): ?>
        <div class="empty-state-card">
            <div class="empty-icon"><i class="fa-solid fa-car"></i></div>
            <h2>Track Your Porsche Order</h2>
            <p>Sign in to your Porsche Driver ID to view saved configurations, or look up an order by reference code.</p>
            <div class="lookup-form-wrap">
                <form method="get" class="order-lookup-form">
                    <input type="text" name="lookup_code" placeholder="Enter Order Code (e.g. POR-911-2026-X82A)" required>
                    <button type="submit" class="btn-porsche-primary"><i class="fa-solid fa-magnifying-glass"></i> Find Order</button>
                </form>
            </div>
            <div class="empty-actions">
                <a href="login.php" class="btn-ghost-luxury"><i class="fa-solid fa-user"></i> Sign In to Account</a>
                <a href="configurator.php" class="btn-porsche-primary"><i class="fa-solid fa-cube"></i> Launch 3D Configurator</a>
            </div>
        </div>
    <?php elseif (empty($myReservations)): ?>
        <div class="empty-state-card">
            <div class="empty-icon"><i class="fa-solid fa-road"></i></div>
            <h2>No Active Reservations Found</h2>
            <p>You have not customized or reserved a Porsche yet. Experience our real-time 3D Studio to build and order your dream car.</p>
            <div class="empty-actions">
                <a href="configurator.php" class="btn-porsche-primary"><i class="fa-solid fa-cube"></i> Start 3D Customization</a>
                <a href="models.php" class="btn-ghost-luxury"><i class="fa-solid fa-car-side"></i> Explore All Models</a>
            </div>
        </div>
    <?php else: ?>
        <div class="reservations-list">
            <?php foreach ($myReservations as $order): ?>
                <div class="luxury-order-card" id="order-<?= $order['id'] ?>">
                    <div class="order-card-header">
                        <div class="order-car-info">
                            <span class="order-type-badge"><?= safe($order['reservation_type'] ?? 'Custom Factory Build') ?></span>
                            <h3><?= safe($order['name']) ?></h3>
                            <span class="order-code-badge"><i class="fa-solid fa-hashtag"></i> <?= safe($order['order_code'] ?? 'POR-ORD-' . $order['id']) ?></span>
                        </div>
                        <div class="order-price-badge">
                            <span class="price-lbl">Total Reserved Build</span>
                            <span class="price-num">$<?= number_format($order['total']) ?></span>
                        </div>
                    </div>

                    <!-- Order Progress Stepper -->
                    <div class="order-progress-stepper">
                        <div class="step completed">
                            <div class="step-dot"><i class="fa-solid fa-check"></i></div>
                            <span class="step-label">Order Placed</span>
                        </div>
                        <div class="step-line active"></div>
                        <div class="step completed">
                            <div class="step-dot"><i class="fa-solid fa-building-circle-check"></i></div>
                            <span class="step-label">Confirmed by Center</span>
                        </div>
                        <div class="step-line <?= in_array($order['status'], ['In Production', 'Ready for Delivery']) ? 'active' : '' ?>"></div>
                        <div class="step <?= in_array($order['status'], ['In Production', 'Ready for Delivery']) ? 'completed' : 'pending' ?>">
                            <div class="step-dot"><i class="fa-solid fa-gears"></i></div>
                            <span class="step-label">Scheduled Production</span>
                        </div>
                        <div class="step-line <?= $order['status'] === 'Ready for Delivery' ? 'active' : '' ?>"></div>
                        <div class="step <?= $order['status'] === 'Ready for Delivery' ? 'completed' : 'pending' ?>">
                            <div class="step-dot"><i class="fa-solid fa-key"></i></div>
                            <span class="step-label">Delivery Ready</span>
                        </div>
                    </div>

                    <!-- Detailed Specifications Grid -->
                    <div class="order-spec-grid">
                        <div class="spec-block">
                            <span class="spec-label"><i class="fa-solid fa-palette"></i> Exterior Paint</span>
                            <span class="spec-value"><?= safe($order['color']) ?></span>
                        </div>
                        <div class="spec-block">
                            <span class="spec-label"><i class="fa-solid fa-dharmachakra"></i> Wheels &amp; Rims</span>
                            <span class="spec-value"><?= safe($order['wheel']) ?></span>
                        </div>
                        <div class="spec-block">
                            <span class="spec-label"><i class="fa-solid fa-compact-disc"></i> Brake Calipers</span>
                            <span class="spec-value"><?= safe($order['caliper'] ?? 'Standard Black') ?></span>
                        </div>
                        <div class="spec-block">
                            <span class="spec-label"><i class="fa-solid fa-couch"></i> Interior Upholstery</span>
                            <span class="spec-value"><?= safe($order['interior'] ?? 'Standard Sport-Tex') ?></span>
                        </div>
                        <div class="spec-block">
                            <span class="spec-label"><i class="fa-solid fa-wind"></i> Aerodynamics</span>
                            <span class="spec-value"><?= safe($order['aero'] ?? 'Standard Aero') ?></span>
                        </div>
                        <div class="spec-block">
                            <span class="spec-label"><i class="fa-solid fa-location-dot"></i> Delivering Porsche Center</span>
                            <span class="spec-value"><?= safe($order['dealership'] ?? 'Porsche Experience Center Stuttgart') ?></span>
                        </div>
                    </div>

                    <div class="order-options-box">
                        <span class="spec-label"><i class="fa-solid fa-sliders"></i> Equipment &amp; Packages:</span>
                        <p class="options-text"><?= safe($order['features']) ?></p>
                    </div>

                    <div class="order-footer-bar">
                        <div class="order-meta-info">
                            <span><i class="fa-regular fa-calendar"></i> Booked on: <?= date('M d, Y', strtotime($order['created_at'])) ?></span>
                            <?php if (!empty($order['customer_name'])): ?>
                                <span>&bull; Registered to: <?= safe($order['customer_name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="order-actions">
                            <a href="configurator.php?id=<?= $order['vehicle_id'] ?>" class="btn-ghost-luxury" title="Open 3D Configurator">
                                <i class="fa-solid fa-cube"></i> Modify in 3D
                            </a>
                            <button type="button" class="btn-ghost-luxury" onclick="window.print()">
                                <i class="fa-solid fa-print"></i> Print Build
                            </button>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you wish to cancel this reservation? This cannot be undone.');">
                                <input type="hidden" name="cancel_id" value="<?= $order['id'] ?>">
                                <button type="submit" class="btn-ghost-danger">
                                    <i class="fa-solid fa-trash-can"></i> Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
