<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/admin-guard.php';
require_once __DIR__ . '/things/db.php';
require_once __DIR__ . '/things/vehicles-data.php';

$pageTitle = 'Admin - Orders & Reservations';
$message = '';

// Update Order Status
if (isset($_POST['update_status'])) {
    $orderId = (int) $_POST['order_id'];
    $newStatus = trim($_POST['new_status']);
    $allowedStatuses = ['Pending', 'Confirmed', 'In Production', 'Ready for Delivery', 'Cancelled'];

    if (in_array($newStatus, $allowedStatuses)) {
        $stmt = $database->prepare('UPDATE reservations SET status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $orderId]);
        $message = "Order #$orderId status updated to '$newStatus'.";
    }
}

// Delete Order
if (isset($_POST['delete_order'])) {
    $orderId = (int) $_POST['order_id'];
    $stmt = $database->prepare('DELETE FROM reservations WHERE id = ?');
    $stmt->execute([$orderId]);
    $message = "Order #$orderId has been deleted.";
}

// Fetch all reservations with car and user details
$statusFilter = $_GET['status'] ?? '';
$sql = 'SELECT reservations.*, vehicles.name AS car_name, vehicles.slug AS car_slug 
        FROM reservations 
        LEFT JOIN vehicles ON reservations.vehicle_id = vehicles.id';

if ($statusFilter !== '') {
    $sql .= ' WHERE reservations.status = ' . $database->quote($statusFilter);
}
$sql .= ' ORDER BY reservations.id DESC';

$allOrders = $database->query($sql)->fetchAll();
$totalRevenue = 0;
foreach ($allOrders as $o) {
    $totalRevenue += (int) $o['total'];
}

require_once __DIR__ . '/things/header.php';
?>

<section class="wrap admin-page">
    <div class="admin-header-row">
        <div>
            <span class="label-tiny">PORSCHE MANAGEMENT SUITE</span>
            <h1>Manage Vehicle Reservations &amp; Orders</h1>
            <p class="section-sub">Review incoming bespoke builds, manage production allocation, and update customer build progress.</p>
        </div>
        <div class="admin-revenue-stat">
            <span class="stat-lbl">Total Pipeline Revenue</span>
            <span class="stat-val">$<?= number_format($totalRevenue) ?></span>
            <span class="stat-sub"><?= count($allOrders) ?> Total Allocations</span>
        </div>
    </div>

    <?php if ($message != ''): ?>
        <div class="notice"><?= safe($message) ?></div>
    <?php endif; ?>

    <div class="admin-actions-bar">
        <div class="status-filter-pills">
            <a href="admin-orders.php" class="filter-pill <?= $statusFilter === '' ? 'active' : '' ?>">All Orders (<?= count($allOrders) ?>)</a>
            <a href="admin-orders.php?status=Confirmed" class="filter-pill <?= $statusFilter === 'Confirmed' ? 'active' : '' ?>">Confirmed</a>
            <a href="admin-orders.php?status=In Production" class="filter-pill <?= $statusFilter === 'In Production' ? 'active' : '' ?>">In Production</a>
            <a href="admin-orders.php?status=Ready for Delivery" class="filter-pill <?= $statusFilter === 'Ready for Delivery' ? 'active' : '' ?>">Ready for Delivery</a>
            <a href="admin-orders.php?status=Cancelled" class="filter-pill <?= $statusFilter === 'Cancelled' ? 'active' : '' ?>">Cancelled</a>
        </div>
        <a class="btn-ghost-luxury" href="admin.php"><i class="fa-solid fa-arrow-left"></i> Admin Hub</a>
    </div>

    <?php if (empty($allOrders)): ?>
        <div class="empty-state-card">
            <i class="fa-solid fa-inbox empty-icon"></i>
            <h3>No Orders Found</h3>
            <p>No reservations currently match this filter criteria.</p>
        </div>
    <?php else: ?>
        <div class="admin-table-container">
            <table class="luxury-admin-table">
                <thead>
                    <tr>
                        <th>Order &amp; Customer</th>
                        <th>Model &amp; Specifications</th>
                        <th>Preferred Dealership</th>
                        <th>Build Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allOrders as $order): ?>
                        <tr>
                            <td>
                                <strong class="order-id-tag"><?= safe($order['order_code'] ?? 'POR-ORD-' . $order['id']) ?></strong><br>
                                <span class="customer-name"><i class="fa-solid fa-user"></i> <?= safe($order['customer_name'] ?? 'Driver') ?></span><br>
                                <span class="customer-contact"><i class="fa-solid fa-envelope"></i> <?= safe($order['customer_email'] ?? 'N/A') ?></span><br>
                                <span class="customer-contact"><i class="fa-solid fa-phone"></i> <?= safe($order['customer_phone'] ?? 'N/A') ?></span><br>
                                <small class="text-muted"><i class="fa-regular fa-clock"></i> <?= date('M d, Y H:i', strtotime($order['created_at'])) ?></small>
                            </td>
                            <td>
                                <strong class="car-title"><?= safe($order['car_name'] ?? 'Porsche Model') ?></strong><br>
                                <span class="spec-chip"><i class="fa-solid fa-palette"></i> <?= safe($order['color']) ?></span>
                                <span class="spec-chip"><i class="fa-solid fa-dharmachakra"></i> <?= safe($order['wheel']) ?></span>
                                <span class="spec-chip"><i class="fa-solid fa-compact-disc"></i> <?= safe($order['caliper'] ?? 'Black') ?></span><br>
                                <span class="spec-chip"><i class="fa-solid fa-couch"></i> <?= safe($order['interior'] ?? 'Leather') ?></span>
                                <span class="spec-chip"><i class="fa-solid fa-wind"></i> <?= safe($order['aero'] ?? 'Standard') ?></span><br>
                                <small class="features-summary"><strong>Options:</strong> <?= safe($order['features']) ?></small>
                            </td>
                            <td>
                                <span class="dealership-tag"><i class="fa-solid fa-location-dot"></i> <?= safe($order['dealership'] ?? 'Porsche Experience Center') ?></span><br>
                                <small class="order-type"><?= safe($order['reservation_type'] ?? 'Factory Build Order') ?></small>
                            </td>
                            <td>
                                <strong class="price-highlight">$<?= number_format($order['total']) ?></strong>
                            </td>
                            <td>
                                <form method="post" class="status-change-form">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="new_status" class="status-dropdown">
                                        <option value="Confirmed" <?= ($order['status'] ?? '') === 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="In Production" <?= ($order['status'] ?? '') === 'In Production' ? 'selected' : '' ?>>In Production</option>
                                        <option value="Ready for Delivery" <?= ($order['status'] ?? '') === 'Ready for Delivery' ? 'selected' : '' ?>>Ready for Delivery</option>
                                        <option value="Cancelled" <?= ($order['status'] ?? '') === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" value="1" class="btn-table-sm" title="Save Status">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="post" onsubmit="return confirm('Delete this order record permanently?');">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" name="delete_order" value="1" class="btn-table-danger" title="Delete Order">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/things/footer.php'; ?>
