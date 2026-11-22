<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/things/db.php';
require_once __DIR__ . '/things/vehicles-data.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: configurator.php');
    exit;
}

$carId = isset($_POST['car']) ? (int) $_POST['car'] : 1;
$colorId = isset($_POST['color']) ? (int) $_POST['color'] : 1;
$wheelId = isset($_POST['wheel']) ? (int) $_POST['wheel'] : 1;
$caliperId = isset($_POST['caliper']) ? (int) $_POST['caliper'] : 1;
$interiorId = isset($_POST['interior']) ? (int) $_POST['interior'] : 1;
$aeroId = isset($_POST['aero']) ? (int) $_POST['aero'] : 1;

if (!isset($allCars[$carId])) {
    header('Location: configurator.php');
    exit;
}

$selectedCar = $allCars[$carId];
$selectedColor = $allColors[$colorId] ?? $allColors[1];
$selectedWheel = $allWheels[$wheelId] ?? $allWheels[1];
$selectedCaliper = $allCalipers[$caliperId] ?? $allCalipers[1];
$selectedInterior = $allInteriors[$interiorId] ?? $allInteriors[1];
$selectedAero = $allAero[$aeroId] ?? $allAero[1];

// Calculate precise total price
$totalPrice = (int) $selectedCar['base_price']
    + (int) $selectedColor['price']
    + (int) $selectedWheel['price']
    + (int) $selectedCaliper['price']
    + (int) $selectedInterior['price']
    + (int) $selectedAero['price'];

$chosenFeatures = [];
if (!empty($_POST['features']) && is_array($_POST['features'])) {
    foreach ($_POST['features'] as $fId) {
        $fId = (int) $fId;
        if (isset($allFeatures[$fId])) {
            $totalPrice += (int) $allFeatures[$fId]['price'];
            $chosenFeatures[] = $allFeatures[$fId]['name'];
        }
    }
}

$featureText = !empty($chosenFeatures) ? implode(', ', $chosenFeatures) : 'Standard Equipment';

// Customer Details from booking modal
$customerName = trim($_POST['customer_name'] ?? '');
$customerEmail = trim($_POST['customer_email'] ?? '');
$customerPhone = trim($_POST['customer_phone'] ?? '');
$dealership = trim($_POST['dealership'] ?? 'Porsche Center Stuttgart-Zuffenhausen');
$reservationType = trim($_POST['reservation_type'] ?? 'Custom Factory Build Order');

// User association
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    // If not logged in, see if user exists with this username/email or assign to guest
    if ($customerEmail !== '') {
        $checkUser = $database->prepare('SELECT id FROM users WHERE username = ?');
        $checkUser->execute([$customerEmail]);
        $existingId = $checkUser->fetchColumn();
        if ($existingId) {
            $userId = (int) $existingId;
        }
    }
    if (!$userId) {
        // Fallback to demo user ID 2 (FARID) or 1 (admin)
        $userId = 2;
    }
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $customerName ?: 'Porsche Driver';
    $_SESSION['user_role'] = 'user';
}

// Generate Unique Porsche Order Reference Code
$modelCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $selectedCar['slug']));
$orderCode = 'POR-' . substr($modelCode, 0, 5) . '-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 5));

// Insert into reservations table
$insertSql = 'INSERT INTO reservations (
    user_id, vehicle_id, color, wheel, caliper, interior, aero, features, total, 
    order_code, status, customer_name, customer_email, customer_phone, dealership, reservation_type
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

$stmt = $database->prepare($insertSql);
$stmt->execute([
    $userId,
    $carId,
    $selectedColor['name'],
    $selectedWheel['name'],
    $selectedCaliper['name'],
    $selectedInterior['name'],
    $selectedAero['name'],
    $featureText,
    $totalPrice,
    $orderCode,
    'Confirmed',
    $customerName ?: ($_SESSION['user_name'] ?? 'Porsche Driver'),
    $customerEmail ?: 'driver@porsche-experience.com',
    $customerPhone ?: '+1 (555) 911-0000',
    $dealership,
    $reservationType
]);

$_SESSION['latest_order_code'] = $orderCode;
$_SESSION['latest_order_car'] = $selectedCar['name'];
$_SESSION['latest_order_total'] = $totalPrice;

header('Location: reservations.php?booked=success');
exit;
