<?php
require_once __DIR__ . '/db.php';

$allCars = [];
$carRows = $database->query('SELECT * FROM vehicles ORDER BY id')->fetchAll();
foreach ($carRows as $carRow) {
    $allCars[$carRow['id']] = $carRow;
}

$allCategories = $database->query('SELECT name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);

// Official Porsche Paint Colours
$allColors = [
    1 => ['id' => 1, 'name' => 'Guards Red', 'hex' => '#D5001C', 'price' => 0, 'finish' => 'Solid'],
    2 => ['id' => 2, 'name' => 'Carrara White Metallic', 'hex' => '#f2f3f5', 'price' => 0, 'finish' => 'Metallic'],
    3 => ['id' => 3, 'name' => 'Jet Black Metallic', 'hex' => '#0e0f12', 'price' => 840, 'finish' => 'Metallic'],
    4 => ['id' => 4, 'name' => 'GT Silver Metallic', 'hex' => '#b8bcc2', 'price' => 1420, 'finish' => 'Metallic'],
    5 => ['id' => 5, 'name' => 'Gentian Blue Metallic', 'hex' => '#122b52', 'price' => 1420, 'finish' => 'Metallic'],
    6 => ['id' => 6, 'name' => 'Racing Yellow', 'hex' => '#F4CA05', 'price' => 2840, 'finish' => 'Special'],
    7 => ['id' => 7, 'name' => 'Crayon / Chalk', 'hex' => '#c2c4bf', 'price' => 3270, 'finish' => 'Special'],
    8 => ['id' => 8, 'name' => 'Python Green', 'hex' => '#218838', 'price' => 3270, 'finish' => 'Special'],
    9 => ['id' => 9, 'name' => 'Frozen Blue Metallic', 'hex' => '#427495', 'price' => 3270, 'finish' => 'Special'],
    10 => ['id' => 10, 'name' => 'Carmine Red', 'hex' => '#931221', 'price' => 3270, 'finish' => 'Special'],
    11 => ['id' => 11, 'name' => 'Shade Green Metallic', 'hex' => '#3b4c40', 'price' => 3500, 'finish' => 'Special'],
    12 => ['id' => 12, 'name' => 'Ruby Star Neo', 'hex' => '#9c1c54', 'price' => 3900, 'finish' => 'Legends']
];

// Official Wheel & Rim Options
$allWheels = [
    1 => ['id' => 1, 'name' => '19-inch Carrera Classic', 'price' => 0, 'style' => 'classic', 'color' => '#d4d8dd'],
    2 => ['id' => 2, 'name' => '20/21-inch Carrera S in Titanium Grey', 'price' => 1850, 'style' => 'carrera_s', 'color' => '#7a818a'],
    3 => ['id' => 3, 'name' => '20/21-inch RS Spyder Design High-Gloss', 'price' => 3250, 'style' => 'spyder', 'color' => '#222326'],
    4 => ['id' => 4, 'name' => '20/21-inch 911 GT3 Center Lock Lightweight', 'price' => 4850, 'style' => 'gt3', 'color' => '#111214'],
    5 => ['id' => 5, 'name' => '21-inch Exclusive Design in Neodyme Gold', 'price' => 4200, 'style' => 'neodyme', 'color' => '#c9a65f'],
    6 => ['id' => 6, 'name' => '21-inch Mission E Aero Wheels', 'price' => 3950, 'style' => 'mission_e', 'color' => '#e2e5e8']
];

// Performance Brake Calipers
$allCalipers = [
    1 => ['id' => 1, 'name' => 'Standard Black Calipers', 'price' => 0, 'hex' => '#1e1f22'],
    2 => ['id' => 2, 'name' => 'Guards Red Performance Calipers', 'price' => 900, 'hex' => '#D5001C'],
    3 => ['id' => 3, 'name' => 'E-Performance Acid Green Calipers', 'price' => 1150, 'hex' => '#8fe314'],
    4 => ['id' => 4, 'name' => 'Porsche Ceramic Composite Brakes (PCCB) Yellow', 'price' => 8950, 'hex' => '#F4CA05'],
    5 => ['id' => 5, 'name' => 'High-Gloss Silver Metallic Calipers', 'price' => 900, 'hex' => '#c8cdd3']
];

// Interior Upholstery
$allInteriors = [
    1 => ['id' => 1, 'name' => 'Standard Black Sport-Tex Leatherette', 'price' => 0, 'hex' => '#212224'],
    2 => ['id' => 2, 'name' => 'Two-Tone Leather in Black / Bordeaux Red', 'price' => 3850, 'hex' => '#5e141a'],
    3 => ['id' => 3, 'name' => 'Heritage Classic Leather in Cognac', 'price' => 5200, 'hex' => '#7e4d25'],
    4 => ['id' => 4, 'name' => 'Agate Grey Club Leather', 'price' => 4400, 'hex' => '#404345'],
    5 => ['id' => 5, 'name' => 'Race-Tex Alcantara with Guards Red Stitching', 'price' => 4650, 'hex' => '#18191b'],
    6 => ['id' => 6, 'name' => 'Chalk & Slate Grey Two-Tone Leather', 'price' => 4200, 'hex' => '#8f9397']
];

// Aerodynamics & Exterior Packages
$allAero = [
    1 => ['id' => 1, 'name' => 'Standard Aerodynamics (Flush Active Spoiler)', 'price' => 0, 'code' => 'standard'],
    2 => ['id' => 2, 'name' => 'Sport Aerodynamics (Deployable Sport Wing)', 'price' => 2850, 'code' => 'wing'],
    3 => ['id' => 3, 'name' => 'SportDesign Carbon Aerokit (Splitters & Fixed Wing)', 'price' => 5400, 'code' => 'carbon_aero'],
    4 => ['id' => 4, 'name' => 'Lightweight Carbon Fiber Roof & Mirror Caps', 'price' => 3600, 'code' => 'carbon_roof']
];

// Performance Packages & Luxury Equipment
$allFeatures = [
    1 => ['id' => 1, 'name' => 'Sport Chrono Package with Mode Switch & Lap Timer', 'price' => 2790, 'desc' => 'Steering wheel drive mode dial and dashboard analog/digital chronometer.'],
    2 => ['id' => 2, 'name' => 'Porsche Dynamic Chassis Control Sport (PDCC) & Rear-Axle Steering', 'price' => 4150, 'desc' => 'Active roll stabilization and virtual wheelbase reduction for agile cornering.'],
    3 => ['id' => 3, 'name' => 'Burmester® High-End 3D Surround Sound System (1,455W)', 'price' => 4680, 'desc' => 'Audiophile acoustic mastery with 21 individually controlled speakers.'],
    4 => ['id' => 4, 'name' => 'Matrix LED Headlights with PDLS+ & Glacier Ice Blue DRL', 'price' => 2420, 'desc' => '84 independent LEDs per headlight with 4-point daytime running light signature.'],
    5 => ['id' => 5, 'name' => 'Sport Exhaust System with Quad Tailpipes in Dark Chrome', 'price' => 3120, 'desc' => 'Variable acoustic valves for an unmistakable throaty Porsche exhaust note.'],
    6 => ['id' => 6, 'name' => 'Panoramic Glass Sunroof with Electric Slide & Tilt', 'price' => 2100, 'desc' => 'Large tinted glass surface providing an airy, luminous cabin ambience.'],
    7 => ['id' => 7, 'name' => 'Adaptive 18-Way Sports Seats Plus with Memory & Ventilation', 'price' => 3450, 'desc' => 'Electro-pneumatic side bolster adjustments, heating, and active cooling.'],
    8 => ['id' => 8, 'name' => 'Front Axle Hydraulic Lift System', 'price' => 2770, 'desc' => 'Raises the front body by approx. 40mm at speeds up to 35 km/h for curbs and ramps.']
];
