<?php
require_once 'things/db.php';

$allCars = [];
$carRows = $database->query('SELECT * FROM vehicles ORDER BY id')->fetchAll();
foreach ($carRows as $carRow) {
    $allCars[$carRow['id']] = $carRow;
}

$allCategories = $database->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);

$allColors = [
    1 => ['id' => 1, 'name' => 'Black', 'hex' => '#111111', 'price' => 0],
    2 => ['id' => 2, 'name' => 'White', 'hex' => '#f5f5f5', 'price' => 0],
    3 => ['id' => 3, 'name' => 'Silver', 'hex' => '#b8bcc2', 'price' => 1500],
    4 => ['id' => 4, 'name' => 'Red', 'hex' => '#D5001C', 'price' => 2500]
];

$allWheels = [
    1 => ['id' => 1, 'name' => '19-inch Classic', 'price' => 0],
    2 => ['id' => 2, 'name' => '20-inch Sport', 'price' => 2000],
    3 => ['id' => 3, 'name' => '21-inch Turbo', 'price' => 3500]
];

$allFeatures = [
    1 => ['id' => 1, 'name' => 'Sport Chrono Package', 'price' => 2500],
    2 => ['id' => 2, 'name' => 'Panoramic Roof', 'price' => 1800],
    3 => ['id' => 3, 'name' => 'Premium Sound System', 'price' => 3200]
];