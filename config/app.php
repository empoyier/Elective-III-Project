<?php
/**
 * APEX Car Rental Management System
 * Application Settings & Constants (Philippines)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Asia/Manila');

define('APP_NAME', 'APEX Car Rental Services');
define('APP_SHORT_NAME', 'APEX');
define('APP_TAGLINE', 'Reliable Vehicle Rental & Simulated Fleet Tracking in the Philippines');
define('APP_CURRENCY', '₱');
define('APP_CURRENCY_CODE', 'PHP');
define('APP_LOCATIONS', [
    'NAIA Terminal 3 (Pasay / Paranaque)',
    'BGC High Street Hub (Taguig City)',
    'Ayala Center Hub (Makati CBD)',
    'Quezon City North Hub (Trinoma/SM North)',
    'Clark International Airport Hub (Pampanga)',
    'Mactan-Cebu International Airport Hub (Lapu-Lapu City)'
]);

/**
 * Helper to compute absolute base URL for assets and links
 */
function apex_base_url() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = dirname($scriptName);
    
    // Normalize if running inside /admin or /api subfolders
    $dir = preg_replace('#/(admin|api|views|tests)$#', '', $dir);
    if ($dir === '/' || $dir === '\\') {
        $dir = '';
    }
    return rtrim($dir, '/');
}

/**
 * Generate project relative URL
 */
function apex_url($path = '') {
    $base = apex_base_url();
    $path = ltrim($path, '/');
    return ($base ? $base . '/' : '/') . $path;
}

/**
 * Format currency in Philippine Pesos
 */
function format_pesos($amount) {
    return '₱' . number_format((float)$amount, 2);
}

/**
 * Sanitize HTML output
 */
function h($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}
