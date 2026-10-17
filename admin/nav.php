<?php
/**
 * APEX Car Rental Management System
 * Admin Navigation & Sidebar
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Auth.php';

Auth::requireRole(['admin', 'staff'], 'login.php');
$adminUser = Auth::user();
$activeAdminNav = $activeAdminNav ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Admin Portal') ?> | APEX Fleet Operations</title>
    
    <link rel="stylesheet" href="<?= apex_url('assets/css/apex-theme.css') ?>">
    <link rel="stylesheet" href="<?= apex_url('assets/css/admin.css') ?>">
    <?php if (!empty($includeMap)): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <?php endif; ?>
    <!-- Chart.js for Admin Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body style="background:#F1F5F9;">

<div style="display:flex; min-height:100vh;">
    <!-- Admin Sidebar (Windows BSoD Blue Accents & Hard Slate) -->
    <aside style="width:260px; background:#0B132B; border-right:1px solid #1E293B; color:#CBD5E1; display:flex; flex-direction:column; flex-shrink:0;">
        <div style="padding:22px 20px; border-bottom:1px solid #1E293B; display:flex; align-items:center; gap:10px;">
            <span class="brand-badge">APEX</span>
            <div>
                <div style="font-weight:800; font-size:15px; color:#FFFFFF;">Fleet Operations</div>
                <div style="font-size:11px; color:#94A3B8;">Admin Control Center</div>
            </div>
        </div>

        <nav style="padding:20px 0; flex:1;">
            <div style="font-size:11px; font-weight:700; color:#64748B; padding:0 20px 8px; text-transform:uppercase;">Operations</div>
            
            <a href="<?= apex_url('admin/index.php') ?>" style="display:flex; align-items:center; gap:12px; padding:10px 20px; font-size:14px; font-weight:600; color:<?= $activeAdminNav === 'dashboard' ? '#FFFFFF' : '#94A3B8' ?>; background:<?= $activeAdminNav === 'dashboard' ? '#1E293B' : 'transparent' ?>; border-left:4px solid <?= $activeAdminNav === 'dashboard' ? 'var(--apex-primary)' : 'transparent' ?>;">
                📊 Dashboard Overview
            </a>

            <a href="<?= apex_url('admin/bookings.php') ?>" style="display:flex; align-items:center; gap:12px; padding:10px 20px; font-size:14px; font-weight:600; color:<?= $activeAdminNav === 'bookings' ? '#FFFFFF' : '#94A3B8' ?>; background:<?= $activeAdminNav === 'bookings' ? '#1E293B' : 'transparent' ?>; border-left:4px solid <?= $activeAdminNav === 'bookings' ? 'var(--apex-primary)' : 'transparent' ?>;">
                📑 Rental Workflow Queue
            </a>

            <a href="<?= apex_url('admin/fleet-tracking.php') ?>" style="display:flex; align-items:center; gap:12px; padding:10px 20px; font-size:14px; font-weight:600; color:<?= $activeAdminNav === 'tracking' ? '#FFFFFF' : '#94A3B8' ?>; background:<?= $activeAdminNav === 'tracking' ? '#1E293B' : 'transparent' ?>; border-left:4px solid <?= $activeAdminNav === 'tracking' ? 'var(--apex-primary)' : 'transparent' ?>;">
                🛰 Master Fleet Live Map
            </a>

            <div style="font-size:11px; font-weight:700; color:#64748B; padding:20px 20px 8px; text-transform:uppercase;">Fleet & Inventory</div>

            <a href="<?= apex_url('admin/vehicles.php') ?>" style="display:flex; align-items:center; gap:12px; padding:10px 20px; font-size:14px; font-weight:600; color:<?= $activeAdminNav === 'vehicles' ? '#FFFFFF' : '#94A3B8' ?>; background:<?= $activeAdminNav === 'vehicles' ? '#1E293B' : 'transparent' ?>; border-left:4px solid <?= $activeAdminNav === 'vehicles' ? 'var(--apex-primary)' : 'transparent' ?>;">
                🚗 Vehicle Fleet (21)
            </a>

            <a href="<?= apex_url('admin/maintenance.php') ?>" style="display:flex; align-items:center; gap:12px; padding:10px 20px; font-size:14px; font-weight:600; color:<?= $activeAdminNav === 'maintenance' ? '#FFFFFF' : '#94A3B8' ?>; background:<?= $activeAdminNav === 'maintenance' ? '#1E293B' : 'transparent' ?>; border-left:4px solid <?= $activeAdminNav === 'maintenance' ? 'var(--apex-primary)' : 'transparent' ?>;">
                🔧 Maintenance Log
            </a>

            <a href="<?= apex_url('admin/customers.php') ?>" style="display:flex; align-items:center; gap:12px; padding:10px 20px; font-size:14px; font-weight:600; color:<?= $activeAdminNav === 'customers' ? '#FFFFFF' : '#94A3B8' ?>; background:<?= $activeAdminNav === 'customers' ? '#1E293B' : 'transparent' ?>; border-left:4px solid <?= $activeAdminNav === 'customers' ? 'var(--apex-primary)' : 'transparent' ?>;">
                👥 Customer Accounts
            </a>
        </nav>

        <div style="padding:16px 20px; border-top:1px solid #1E293B; font-size:13px;">
            <a href="<?= apex_url('') ?>" style="color:#38BDF8; font-weight:600; display:block; margin-bottom:8px;">
                &larr; Return to Public Website
            </a>
            <div style="color:#64748B; font-size:11px;">
                Logged in as <strong><?= h($adminUser['name']) ?></strong> (<?= strtoupper(h($adminUser['role'])) ?>)
            </div>
        </div>
    </aside>

    <!-- Main Content Body -->
    <div style="flex:1; display:flex; flex-direction:column; overflow-x:hidden;">
        <header style="background:#FFFFFF; border-bottom:1px solid var(--apex-border-crisp); padding:16px 32px; display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h1 style="font-size:20px; font-weight:800; color:var(--apex-text-main); margin:0;">
                    <?= h($pageTitle ?? 'Operations') ?>
                </h1>
            </div>
            <div style="display:flex; align-items:center; gap:14px;">
                <span class="badge badge-sim">ADMIN MODE</span>
                <a href="<?= apex_url('logout.php') ?>" class="btn btn-secondary btn-sm">Sign Out</a>
            </div>
        </header>

        <main style="padding:32px; flex:1;">
