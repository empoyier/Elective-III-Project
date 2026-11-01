<?php
/**
 * APEX Car Rental Management System
 * Automated Database Setup & Seed Migration Script
 * Can be executed via CLI (`php setup.php`) or accessed via browser.
 */

$isCli = (php_sapi_name() === 'cli');

function out(string $msg, string $type = 'info', bool $isCli = false) {
    if ($isCli) {
        $prefix = match($type) {
            'success' => '[SUCCESS] ',
            'error'   => '[ERROR] ',
            'warn'    => '[WARN] ',
            default   => '[INFO] '
        };
        echo $prefix . $msg . PHP_EOL;
    } else {
        $color = match($type) {
            'success' => '#059669',
            'error'   => '#dc2626',
            'warn'    => '#d97706',
            default   => '#0078D7'
        };
        echo "<div style='font-family:sans-serif; margin:6px 0; padding:10px 14px; border-left:4px solid {$color}; background:#f8fafc; color:#0f172a;'>";
        echo "<strong>" . strtoupper($type) . ":</strong> " . htmlspecialchars($msg);
        echo "</div>";
    }
}

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>APEX Database Installer</title>";
    echo "<style>body{background:#0b132b;color:#f8fafc;font-family:Segoe UI,system-ui,sans-serif;padding:30px;max-width:800px;margin:0 auto;} .card{background:#ffffff;border-radius:4px;border:1px solid #cbd5e1;padding:24px;color:#0f172a;} h1{color:#0078D7;margin-top:0;}</style>";
    echo "</head><body><div class='card'><h1>APEX Database Setup & Seed Migration</h1>";
}

out("Initializing APEX Car Rental Database setup...", 'info', $isCli);

$config = require __DIR__ . '/config/database.php';

try {
    // 1. Connect to MySQL server (without selecting db)
    $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', $config['host'], $config['port'], $config['charset']);
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    out("Connected to MySQL server at {$config['host']}:{$config['port']}.", 'success', $isCli);

    // 2. Re-create database cleanly
    $dbName = $config['database'];
    $pdo->exec("DROP DATABASE IF EXISTS `{$dbName}`;");
    $pdo->exec("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `{$dbName}`;");
    out("Clean database `{$dbName}` created and selected.", 'success', $isCli);

    // 3. Execute schema.sql
    $schemaFile = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new RuntimeException("Schema file not found: {$schemaFile}");
    }
    $schemaSql = file_get_contents($schemaFile);
    // Execute multiple statements
    $pdo->exec($schemaSql);
    out("Schema tables verified and created successfully.", 'success', $isCli);

    // 4. Execute seed.sql
    $seedFile = __DIR__ . '/database/seed.sql';
    if (!file_exists($seedFile)) {
        throw new RuntimeException("Seed file not found: {$seedFile}");
    }
    $seedSql = file_get_contents($seedFile);
    $pdo->exec($seedSql);
    out("Philippine fleet, GPS routes, add-ons, and demo accounts seeded successfully.", 'success', $isCli);

    // 5. Verification checks
    $vehicleCount = $pdo->query("SELECT COUNT(*) FROM `vehicles`")->fetchColumn();
    $categoryCount = $pdo->query("SELECT COUNT(*) FROM `vehicle_categories`")->fetchColumn();
    $routesCount = $pdo->query("SELECT COUNT(*) FROM `tracking_routes`")->fetchColumn();
    $userCount = $pdo->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    $bookingCount = $pdo->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();

    out("Verification Report:", 'info', $isCli);
    out("Vehicles loaded: {$vehicleCount} (Target: >= 20)", ($vehicleCount >= 20 ? 'success' : 'warn'), $isCli);
    out("Vehicle Categories: {$categoryCount}", 'info', $isCli);
    out("Simulated Philippine GPS Routes: {$routesCount}", 'info', $isCli);
    out("Demo User Accounts: {$userCount}", 'info', $isCli);
    out("Demonstration Bookings: {$bookingCount}", 'info', $isCli);

    out("Setup completed successfully! You can now launch APEX.", 'success', $isCli);

    if (!$isCli) {
        echo "<p style='margin-top:20px;'><a href='index.php' style='display:inline-block;padding:10px 20px;background:#0078D7;color:#fff;text-decoration:none;font-weight:bold;border-radius:2px;'>Go to APEX Front Page &rarr;</a></p>";
    }

} catch (Exception $e) {
    out("Setup failed: " . $e->getMessage(), 'error', $isCli);
    if ($isCli) {
        exit(1);
    }
}

if (!$isCli) {
    echo "</div></body></html>";
}
