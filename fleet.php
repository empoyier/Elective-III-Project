<?php
/**
 * APEX Car Rental Management System
 * Main Car Selection Page (Where all fleet models are browsed, filtered, and selected)
 */

$pageTitle = "Select Your Vehicle | APEX Car Rental Services";
$activeNav = "fleet";

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Vehicle.php';

// Collect query filters
$selectedCategory = $_GET['category'] ?? '';
$pickupDatetime = $_GET['pickup_datetime'] ?? '';
$returnDatetime = $_GET['return_datetime'] ?? '';
$pickupLocation = $_GET['location'] ?? APP_LOCATIONS[0];
$transmission = $_GET['transmission'] ?? '';
$fuelType = $_GET['fuel_type'] ?? '';
$searchTerm = $_GET['search'] ?? '';

$filters = [
    'category'        => $selectedCategory,
    'pickup_datetime' => $pickupDatetime,
    'return_datetime' => $returnDatetime,
    'transmission'    => $transmission,
    'fuel_type'       => $fuelType,
    'search'          => $searchTerm,
    'sort'            => $_GET['sort'] ?? 'rate_asc'
];

$vehicles = Vehicle::getAll($filters);
$categories = Vehicle::getCategories();
$locations = APP_LOCATIONS;

require_once __DIR__ . '/templates/header.php';
?>

<!-- Clean Top Header (No Eyebrows) -->
<div style="background:#FFFFFF; padding:28px 0;">
    <div class="container-wide">
        <h1 style="font-size:32px; font-weight:800; color:var(--apex-text-main); margin-bottom:6px; letter-spacing:-0.5px;">
            Select Your Vehicle
        </h1>
        <p style="font-size:15px; color:var(--apex-text-muted);">
            Choose from 21 verified models across the Philippines with transparent daily ₱ rates and guaranteed date availability.
        </p>

        <!-- Fast Category Switcher Tabs -->
        <div class="category-pills" style="margin-top:20px; margin-bottom:0;">
            <a href="<?= apex_url('fleet.php') ?>" class="category-pill <?= empty($selectedCategory) ? 'active' : '' ?>">
                All Vehicles (21)
            </a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= apex_url('fleet.php?category=' . urlencode($cat['slug']) . ($pickupDatetime ? '&pickup_datetime=' . urlencode($pickupDatetime) . '&return_datetime=' . urlencode($returnDatetime) : '')) ?>" 
                   class="category-pill <?= $selectedCategory === $cat['slug'] ? 'active' : '' ?>">
                    <?= h($cat['name']) ?> (<?= (int)$cat['vehicle_count'] ?>)
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Compact Horizontal Date & Schedule Filter Bar -->
<div style="background:#FFFFFF; padding:16px 0;">
    <div class="container-wide">
        <form action="<?= apex_url('fleet.php') ?>" method="GET" style="display:flex; flex-wrap:wrap; align-items:flex-end; gap:16px;">
            <?php if ($selectedCategory): ?>
                <input type="hidden" name="category" value="<?= h($selectedCategory) ?>">
            <?php endif; ?>

            <div style="flex:1; min-width:200px;">
                <label class="form-label" style="font-size:12px; margin-bottom:4px;">Pickup Location Hub</label>
                <select name="location" class="form-control" style="padding:8px 10px; font-size:13px;">
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= h($loc) ?>" <?= $pickupLocation === $loc ? 'selected' : '' ?>><?= h($loc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex:1; min-width:180px;">
                <label class="form-label" style="font-size:12px; margin-bottom:4px;">Pickup Date & Time</label>
                <input type="datetime-local" name="pickup_datetime" class="form-control" style="padding:8px 10px; font-size:13px;" value="<?= h($pickupDatetime) ?>">
            </div>

            <div style="flex:1; min-width:180px;">
                <label class="form-label" style="font-size:12px; margin-bottom:4px;">Return Date & Time</label>
                <input type="datetime-local" name="return_datetime" class="form-control" style="padding:8px 10px; font-size:13px;" value="<?= h($returnDatetime) ?>">
            </div>

            <div style="flex:1; min-width:160px;">
                <label class="form-label" style="font-size:12px; margin-bottom:4px;">Search Model</label>
                <input type="text" name="search" class="form-control" style="padding:8px 10px; font-size:13px;" placeholder="e.g. Vios, Innova..." value="<?= h($searchTerm) ?>">
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary" style="padding:9px 18px; font-size:13px;">
                    Check Availability
                </button>
                <?php if ($pickupDatetime || $searchTerm || $selectedCategory): ?>
                    <a href="<?= apex_url('fleet.php') ?>" class="btn btn-secondary" style="padding:9px 14px; font-size:13px;">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Main Fleet Grid (Clean, Hard-Bordered Cards) -->
<div class="container-wide" style="padding:36px 20px 80px;">
    <?php if (empty($vehicles)): ?>
        <div class="card" style="padding:60px 20px; text-align:center; max-width:600px; margin:40px auto;">
            <h3 style="font-size:18px; font-weight:700; color:var(--apex-text-main); margin-bottom:8px;">No Vehicles Match Your Selection</h3>
            <p style="font-size:14px; color:var(--apex-text-muted); margin-bottom:20px;">
                Please try clearing your filters or choosing different rental dates.
            </p>
            <a href="<?= apex_url('fleet.php') ?>" class="btn btn-primary">View All 21 Vehicles</a>
        </div>
    <?php else: ?>
        <div class="fleet-grid">
            <?php foreach ($vehicles as $veh): ?>
                <div class="vehicle-card">
                    <!-- Photo Header -->
                    <div class="vehicle-thumb-wrap">
                        <img src="<?= h($veh['image_url']) ?>" alt="<?= h($veh['brand'] . ' ' . $veh['model']) ?>" class="vehicle-thumb" loading="lazy">
                        <span class="vehicle-category-badge"><?= h($veh['category_name']) ?></span>
                        <?php if (!empty($veh['image_credit'])): ?>
                            <span class="vehicle-credit-tag"><?= h($veh['image_credit']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Card Body -->
                    <div class="vehicle-card-body">
                        <div class="vehicle-title-wrap">
                            <h3 class="vehicle-name"><?= h($veh['brand'] . ' ' . $veh['model']) ?></h3>
                            <span style="font-size:12px; color:var(--apex-text-muted); font-family:monospace;">
                                <?= h($veh['license_plate']) ?> &bull; <?= h($veh['year']) ?> Model
                            </span>
                        </div>

                        <!-- Technical Specs Bar -->
                        <div class="vehicle-specs-row">
                            <span class="spec-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                <?= h($veh['transmission']) ?>
                            </span>
                            <span class="spec-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                <?= (int)$veh['seats'] ?> Seats
                            </span>
                            <span class="spec-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v6h6"/><path d="M21 12A9 9 0 0 0 6 5.3L3 8"/><path d="M21 22v-6h-6"/><path d="M3 12a9 9 0 0 0 15 6.7l3-2.7"/></svg>
                                <?= h($veh['fuel_type']) ?>
                            </span>
                        </div>

                        <!-- Feature Badges -->
                        <div class="vehicle-features-list">
                            <?php 
                            $feats = !empty($veh['features_json']) ? (json_decode($veh['features_json'], true) ?: []) : [];
                            foreach (array_slice($feats, 0, 3) as $f): 
                            ?>
                                <span class="feature-tag"><?= h($f) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <p style="font-size:12px; color:var(--apex-text-secondary); line-height:1.5; margin-bottom:14px; flex:1;">
                            <?= h(substr($veh['description'] ?? '', 0, 110)) ?>...
                        </p>

                        <!-- Date Availability Check Flag -->
                        <?php if (isset($veh['is_date_available'])): ?>
                            <div style="margin-bottom:12px;">
                                <?php if ($veh['is_date_available']): ?>
                                    <span class="badge badge-available">✓ Available on Requested Dates</span>
                                <?php else: ?>
                                    <span class="badge badge-rejected">✕ Booked on Requested Dates</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Card Footer with Rate and Select Button -->
                        <div class="vehicle-card-footer">
                            <div class="price-box">
                                <span class="price-amount"><?= format_pesos($veh['daily_rate']) ?></span>
                                <span class="price-period">per day (PHP)</span>
                            </div>

                            <?php 
                            $bookUrl = apex_url('book.php?vehicle_id=' . (int)$veh['id']);
                            if (!empty($pickupDatetime) && !empty($returnDatetime)) {
                                $bookUrl .= '&pickup_datetime=' . urlencode($pickupDatetime) . '&return_datetime=' . urlencode($returnDatetime);
                            }
                            ?>
                            <a href="<?= $bookUrl ?>" class="btn btn-primary btn-sm">
                                Select &amp; Book &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
