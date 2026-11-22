<?php
session_start();
require_once 'things/vehicles-data.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: configurator.php');
    exit;
}

$carId = (int) $_POST['car'];
$colorId = (int) $_POST['color'];
$wheelId = (int) $_POST['wheel'];

if (!isset($allCars[$carId]) || !isset($allColors[$colorId]) || !isset($allWheels[$wheelId])) {
    header('Location: configurator.php');
    exit;
}

$totalPrice = $allCars[$carId]['base_price'] + $allColors[$colorId]['price'] + $allWheels[$wheelId]['price'];

$chosenFeatureoption = [];
if (isset($_POST['features'])) {
    foreach ($_POST['features'] as $featureId) {
        $featureId = (int) $featureId;
        if (isset($allFeatures[$featureId])) {
            $totalPrice = $totalPrice + $allFeatures[$featureId]['price'];
            array_push($chosenFeatureoption, $allFeatures[$featureId]['name']); /*?? */
        }
    }
}

$featureText = implode(', ', $chosenFeatureoption);
if ($featureText == '') {
    $featureText = 'None';
}

$query = $database->prepare('INSERT INTO reservations (user_id, vehicle_id, color, wheel, features, total) VALUES (?, ?, ?, ?, ?, ?)');
$query->execute([$_SESSION['user_id'], $carId, $allColors[$colorId]['name'], $allWheels[$wheelId]['name'], $featureText, $totalPrice]);

header('Location: reservations.php');
exit;