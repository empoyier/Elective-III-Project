<?php
/**
 * APEX Car Rental Management System
 * Shared Header Template
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Auth.php';

$currentUser = Auth::user();
$pageTitle = $pageTitle ?? APP_NAME;
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    
    <!-- Hard CSS Design System -->
    <link rel="stylesheet" href="<?= apex_url('assets/css/apex-theme.css') ?>">
    <link rel="stylesheet" href="<?= apex_url('assets/css/customer.css') ?>">
    <?php if (!empty($includeTrackingCss)): ?>
        <link rel="stylesheet" href="<?= apex_url('assets/css/tracking.css') ?>">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <?php endif; ?>
</head>
<body>

<header class="site-nav">
    <div class="container-wide nav-inner">
        <a href="<?= apex_url('') ?>" class="brand-link">
            <span class="brand-badge">APEX</span>
            <span class="brand-name">Car Rental Services</span>
        </a>

        <ul class="nav-links">
            <li><a href="<?= apex_url('fleet.php') ?>" class="<?= $activeNav === 'fleet' ? 'active' : '' ?>">Select Vehicle</a></li>
            <li><a href="<?= apex_url('track.php') ?>" class="<?= $activeNav === 'tracking' ? 'active' : '' ?>">Simulated Tracking</a></li>
            <li><a href="<?= apex_url('my-bookings.php') ?>" class="<?= $activeNav === 'bookings' ? 'active' : '' ?>">My Reservations</a></li>
            <?php if (Auth::isStaff()): ?>
                <li><a href="<?= apex_url('admin/index.php') ?>" style="color:#38BDF8; font-weight:700;">Admin Dashboard &rarr;</a></li>
            <?php endif; ?>
        </ul>

        <div class="nav-actions">
            <?php if ($currentUser): ?>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span style="color:#CBD5E1; font-size:13px;">Hello, <strong><?= h($currentUser['name']) ?></strong></span>
                    <a href="<?= apex_url('logout.php') ?>" class="btn btn-secondary btn-sm">Sign Out</a>
                </div>
            <?php else: ?>
                <a href="<?= apex_url('login.php') ?>" class="btn btn-secondary btn-sm">Sign In</a>
                <a href="<?= apex_url('register.php') ?>" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main>
