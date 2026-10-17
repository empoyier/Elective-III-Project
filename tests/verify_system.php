<?php
/**
 * APEX Car Rental Management System
 * Automated Verification & Unit Test Suite
 * CLI Execution: php tests/verify_system.php
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Vehicle.php';
require_once __DIR__ . '/../src/Booking.php';
require_once __DIR__ . '/../src/Tracking.php';
require_once __DIR__ . '/../src/Maintenance.php';
require_once __DIR__ . '/../src/Analytics.php';

$passed = 0;
$failed = 0;

function assertTest(string $desc, bool $condition) {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$desc}" . PHP_EOL;
    } else {
        $failed++;
        echo "  [FAIL] {$desc}" . PHP_EOL;
    }
}

echo "==========================================================" . PHP_EOL;
echo "  APEX CAR RENTAL SYSTEM - AUTOMATED VERIFICATION SUITE   " . PHP_EOL;
echo "==========================================================" . PHP_EOL . PHP_EOL;

// 1. Database Connection & Schema Test
echo "Test Suite 1: Database Schema & Relational Tables" . PHP_EOL;
try {
    $tables = Database::fetchAll("SHOW TABLES");
    $tableNames = array_map('current', $tables);
    $requiredTables = [
        'users', 'vehicle_categories', 'vehicles', 'addons',
        'bookings', 'booking_addons', 'booking_status_logs',
        'maintenance_records', 'return_inspections',
        'tracking_routes', 'rental_tracking_sessions'
    ];
    foreach ($requiredTables as $t) {
        assertTest("Table '{$t}' exists in database", in_array($t, $tableNames, true));
    }
} catch (Exception $e) {
    assertTest("Database connection failed: " . $e->getMessage(), false);
}
echo PHP_EOL;

// 2. Vehicle Catalog & Philippine Fleet Verification
echo "Test Suite 2: Philippine Fleet & Seed Inventory" . PHP_EOL;
$vehicles = Vehicle::getAll(['admin_view' => true]);
assertTest("Fleet has at least 21 vehicle models", count($vehicles) >= 21);

$categories = Vehicle::getCategories();
assertTest("Fleet spans 7 distinct vehicle categories", count($categories) === 7);

$hasImages = true;
$hasCredits = true;
foreach ($vehicles as $v) {
    if (empty($v['image_url'])) $hasImages = false;
    if (empty($v['image_credit'])) $hasCredits = false;
}
assertTest("All vehicles have online image URLs configured", $hasImages);
assertTest("All vehicles feature photographer attribution credits", $hasCredits);
echo PHP_EOL;

// 3. Pricing Calculation Engine Test
echo "Test Suite 3: Server-Side Pricing Engine (Philippine Pesos)" . PHP_EOL;
$sampleVehicle = $vehicles[0]; // Vios (₱1,800/day)
$pickup = '2026-11-01 09:00:00';
$return = '2026-11-04 18:00:00'; // 4 days duration
$pricing = Booking::calculatePricing($sampleVehicle['id'], $pickup, $return, [1]); // Addon 1 (CDW ₱450/day)

assertTest("Calculated duration equals 4 days", $pricing['total_days'] === 4);
$expectedBase = 4 * (float)$sampleVehicle['daily_rate'];
assertTest("Base amount matches (4 * {$sampleVehicle['daily_rate']}) = {$expectedBase}", (float)$pricing['base_amount'] === $expectedBase);
$expectedAddon = 4 * 450.00;
assertTest("Add-on CDW matches (4 * 450.00) = {$expectedAddon}", (float)$pricing['addons_amount'] === $expectedAddon);
$expectedTotal = $expectedBase + $expectedAddon;
assertTest("Total amount matches grand sum: {$expectedTotal}", (float)$pricing['total_amount'] === $expectedTotal);
echo PHP_EOL;

// 4. Booking Concurrency & Date Conflict Prevention
echo "Test Suite 4: Date Conflict Engine & Prevention" . PHP_EOL;
// Use vehicle 7 (Toyota Fortuner) which has active booking from 2026-10-16 to 2026-10-20
$activeBooking = Database::fetchOne("SELECT * FROM `bookings` WHERE `id` = 1");
if ($activeBooking) {
    // Conflict attempt 1: Overlapping inside the period (2026-10-17 to 2026-10-19)
    $conflict1 = Vehicle::checkAvailability($activeBooking['vehicle_id'], '2026-10-17 10:00:00', '2026-10-19 12:00:00');
    assertTest("Conflict detected for interval strictly inside active booking", $conflict1['available'] === false);

    // Conflict attempt 2: Overlapping start (2026-10-15 to 2026-10-18)
    $conflict2 = Vehicle::checkAvailability($activeBooking['vehicle_id'], '2026-10-15 10:00:00', '2026-10-18 12:00:00');
    assertTest("Conflict detected for interval overlapping pickup date", $conflict2['available'] === false);

    // Conflict attempt 3: Overlapping end (2026-10-19 to 2026-10-22)
    $conflict3 = Vehicle::checkAvailability($activeBooking['vehicle_id'], '2026-10-19 10:00:00', '2026-10-22 12:00:00');
    assertTest("Conflict detected for interval overlapping return date", $conflict3['available'] === false);

    // Valid attempt: Completely after the active booking (2026-10-21 to 2026-10-24)
    $valid = Vehicle::checkAvailability($activeBooking['vehicle_id'], '2026-10-21 10:00:00', '2026-10-24 12:00:00');
    assertTest("Permits booking for valid dates after existing return", $valid['available'] === true);
} else {
    assertTest("Active demonstration booking exists", false);
}
echo PHP_EOL;

// 5. Simulated Waypoint Tracking Engine
echo "Test Suite 5: Simulated Tracking Waypoint Telemetry" . PHP_EOL;
$session = Tracking::getSessionForBooking(1);
assertTest("Active tracking session retrieved for Booking #1", !empty($session));
assertTest("Route waypoints parsed as GeoJSON array", is_array($session['waypoints']) && count($session['waypoints']) > 0);

// Advance simulation tick
$tick = Tracking::advanceSimulation(1);
assertTest("Simulation tick advances telemetry (speed, coords, fuel %)", $tick['active'] === true && $tick['current_speed'] > 0);
assertTest("Fuel level is within realistic bounds (0 - 100%)", $tick['fuel_level_pct'] >= 0 && $tick['fuel_level_pct'] <= 100);
echo PHP_EOL;

// 6. Security & Authentication Checks
echo "Test Suite 6: Security, Auth & Account Status" . PHP_EOL;
$loginAdmin = Auth::login('admin@apexrentals.ph', 'admin123');
assertTest("Administrator login with bcrypt verified", $loginAdmin['success'] === true && $loginAdmin['user']['role'] === 'admin');

$loginBad = Auth::login('admin@apexrentals.ph', 'wrongpassword');
assertTest("Rejects incorrect credentials", $loginBad['success'] === false);

$csrfToken = Auth::generateCsrfToken();
assertTest("CSRF token generated", !empty($csrfToken));
assertTest("CSRF token successfully verified", Auth::verifyCsrfToken($csrfToken));
assertTest("Rejects invalid CSRF token", !Auth::verifyCsrfToken('invalid_token_xyz'));
echo PHP_EOL;

// Summary
echo "==========================================================" . PHP_EOL;
echo "  TEST RESULTS: Passed: {$passed} | Failed: {$failed}     " . PHP_EOL;
echo "==========================================================" . PHP_EOL;

exit($failed > 0 ? 1 : 0);
