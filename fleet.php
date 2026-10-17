<?php
/**
 * APEX Car Rental Management System
 * Full Vehicle Fleet Catalog with Date-Based Availability & Multi-Facet Filters
 */

$pageTitle = "Fleet Catalog | APEX Car Rental Services";
$activeNav = "fleet";

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Vehicle.php';

// Collect query filters
$selectedCategory = $_GET['category'] ?? '';
$pickupDatetime = $_GET['pickup_datetime'] ?? '';
$returnDatetime = $_GET['return_datetime'] ?? '';
$transmission = $_GET['transmission'] ?? '';
$fuelType = $_GET['fuel_type'] ?? '';
$seats = $_GET['seats'] ?? '';
$priceMin = $_GET['price_min'] ?? '';
$priceMax = $_GET['price_max'] ?? '';
$searchTerm = $_GET['search'] ?? '';

$filters = [
    'category'        => $selectedCategory,
    'pickup_datetime' => $pickupDatetime,
    'return_datetime' => $returnDatetime,
    'transmission'    => $transmission,
    'fuel_type'       => $fuelType,
    'seats'           => $seats,
    'price_min'       => $priceMin,
    'price_max'       => $priceMax,
    'search'          => $searchTerm,
    'sort'            => $_GET['sort'] ?? 'rate_asc'
];

$vehicles = Vehicle::getAll($filters);
$categories = Vehicle::getCategories();

require_once __DIR__ . '/templates/header.php';
?>

<div style="background:#FFFFFF; border-bottom:1px solid var(--apex-border-crisp); padding:24px 0;">
    <div class="container">
        <h1 style="font-size:28px; font-weight:800; color:var(--apex-text-main); margin-bottom:4px;">Philippine Vehicle Fleet</h1>
        <p style="font-size:14px; color:var(--apex-text-muted);">
            Explore our curated inventory of <?= count($vehicles) ?> vehicles available for rental. Filter by category, specifications, or rental dates.
        </p>
    </div>
</div>

<div class="container" style="padding:32px 0 60px;">
    <!-- Active Date Notification if dates filtered -->
    <?php if (!empty($pickupDatetime) && !empty($returnDatetime)): ?>
        <div class="alert alert-info" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
            <div>
                <strong>Filtering by Requested Rental Dates:</strong>
                <?= date('M d, Y h:i A', strtotime($pickupDatetime)) ?> &rarr; <?= date('M d, Y h:i A', strtotime($returnDatetime)) ?>
            </div>
            <a href="<?= apex_url('fleet.php' . ($selectedCategory ? '?category=' . urlencode($selectedCategory) : '')) ?>" class="btn btn-secondary btn-sm">Clear Dates</a>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 280px 1fr; gap:28px;">
        <!-- Filter Sidebar -->
        <aside>
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Filter Fleet</span>
                    <a href="<?= apex_url('fleet.php') ?>" style="font-size:12px; font-weight:600;">Reset All</a>
                </div>
                <div class="card-body">
                    <form action="<?= apex_url('fleet.php') ?>" method="GET">
                        <!-- Date Availability Filter -->
                        <div class="form-group">
                            <label class="form-label" for="f_pickup">Pickup Date & Time</label>
                            <input type="datetime-local" name="pickup_datetime" id="f_pickup" class="form-control" value="<?= h($pickupDatetime) ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="f_return">Return Date & Time</label>
                            <input type="datetime-local" name="return_datetime" id="f_return" class="form-control" value="<?= h($returnDatetime) ?>">
                        </div>

                        <hr style="border:0; border-top:1px solid var(--apex-border-subtle); margin:16px 0;">

                        <!-- Category -->
                        <div class="form-group">
                            <label class="form-label" for="f_cat">Category</label>
                            <select name="category" id="f_cat" class="form-control">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= h($cat['slug']) ?>" <?= $selectedCategory === $cat['slug'] ? 'selected' : '' ?>>
                                        <?= h($cat['name']) ?> (<?= (int)$cat['vehicle_count'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Transmission -->
                        <div class="form-group">
                            <label class="form-label" for="f_trans">Transmission</label>
                            <select name="transmission" id="f_trans" class="form-control">
                                <option value="">Any Transmission</option>
                                <option value="Automatic" <?= $transmission === 'Automatic' ? 'selected' : '' ?>>Automatic</option>
                                <option value="Manual" <?= $transmission === 'Manual' ? 'selected' : '' ?>>Manual</option>
                            </select>
                        </div>

                        <!-- Fuel Type -->
                        <div class="form-group">
                            <label class="form-label" for="f_fuel">Fuel / Powertrain</label>
                            <select name="fuel_type" id="f_fuel" class="form-control">
                                <option value="">Any Fuel Type</option>
                                <option value="Gasoline" <?= $fuelType === 'Gasoline' ? 'selected' : '' ?>>Gasoline</option>
                                <option value="Diesel" <?= $fuelType === 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                <option value="Hybrid" <?= $fuelType === 'Hybrid' ? 'selected' : '' ?>>Hybrid</option>
                                <option value="Electric" <?= $fuelType === 'Electric' ? 'selected' : '' ?>>100% Electric</option>
                            </select>
                        </div>

                        <!-- Minimum Seats -->
                        <div class="form-group">
                            <label class="form-label" for="f_seats">Minimum Seats</label>
                            <select name="seats" id="f_seats" class="form-control">
                                <option value="">Any Capacity</option>
                                <option value="5" <?= $seats === '5' ? 'selected' : '' ?>>5+ Passengers</option>
                                <option value="7" <?= $seats === '7' ? 'selected' : '' ?>>7+ Passengers (MPV/SUV)</option>
                                <option value="10" <?= $seats === '10' ? 'selected' : '' ?>>10+ Passengers (Van)</option>
                            </select>
                        </div>

                        <!-- Keyword Search -->
                        <div class="form-group">
                            <label class="form-label" for="f_search">Search Model / Keyword</label>
                            <input type="text" name="search" id="f_search" class="form-control" placeholder="e.g. Vios, Fortuner..." value="<?= h($searchTerm) ?>">
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:8px;">
                            Apply Filters
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Fleet Results List -->
        <main>
            <!-- Category Pills Bar -->
            <div class="category-pills" style="margin-bottom:20px;">
                <a href="<?= apex_url('fleet.php') ?>" class="category-pill <?= empty($selectedCategory) ? 'active' : '' ?>">All Vehicles</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= apex_url('fleet.php?category=' . urlencode($cat['slug'])) ?>" class="category-pill <?= $selectedCategory === $cat['slug'] ? 'active' : '' ?>">
                        <?= h($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($vehicles)): ?>
                <div class="card" style="padding:40px; text-align:center;">
                    <h3 style="font-size:18px; color:var(--apex-text-main); margin-bottom:8px;">No vehicles match your selected criteria</h3>
                    <p style="font-size:14px; color:var(--apex-text-muted); margin-bottom:20px;">
                        Try adjusting your filters, selecting different dates, or clearing your search.
                    </p>
                    <a href="<?= apex_url('fleet.php') ?>" class="btn btn-secondary">Clear All Filters</a>
                </div>
            <?php else: ?>
                <div class="fleet-grid" style="grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));">
                    <?php foreach ($vehicles as $veh): ?>
                        <div class="vehicle-card">
                            <div class="vehicle-thumb-wrap">
                                <img src="<?= h($veh['image_url']) ?>" alt="<?= h($veh['brand'] . ' ' . $veh['model']) ?>" class="vehicle-thumb" loading="lazy">
                                <span class="vehicle-category-badge"><?= h($veh['category_name']) ?></span>
                                <?php if (!empty($veh['image_credit'])): ?>
                                    <span class="vehicle-credit-tag"><?= h($veh['image_credit']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="vehicle-card-body">
                                <div class="vehicle-title-wrap">
                                    <h3 class="vehicle-name"><?= h($veh['brand'] . ' ' . $veh['model']) ?></h3>
                                    <span style="font-size:12px; color:var(--apex-text-muted); font-family:monospace;"><?= h($veh['license_plate']) ?> &bull; <?= h($veh['year']) ?></span>
                                </div>

                                <div class="vehicle-specs-row">
                                    <span class="spec-item"><?= h($veh['transmission']) ?></span>
                                    <span class="spec-item">&bull; <?= (int)$veh['seats'] ?> Seats</span>
                                    <span class="spec-item">&bull; <?= h($veh['fuel_type']) ?></span>
                                </div>

                                <p style="font-size:12px; color:var(--apex-text-secondary); line-height:1.5; margin-bottom:12px; flex:1;">
                                    <?= h(substr($veh['description'] ?? '', 0, 115)) ?>...
                                </p>

                                <!-- Date Availability Tag if dates were requested -->
                                <?php if (isset($veh['is_date_available'])): ?>
                                    <div style="margin-bottom:12px;">
                                        <?php if ($veh['is_date_available']): ?>
                                            <span class="badge badge-available">✓ Available on Chosen Dates</span>
                                        <?php else: ?>
                                            <span class="badge badge-rejected">✕ Unavailable on Chosen Dates</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="vehicle-card-footer">
                                    <div class="price-box">
                                        <span class="price-amount"><?= format_pesos($veh['daily_rate']) ?></span>
                                        <span class="price-period">per day</span>
                                    </div>

                                    <?php 
                                    $bookUrl = apex_url('book.php?vehicle_id=' . (int)$veh['id']);
                                    if (!empty($pickupDatetime) && !empty($returnDatetime)) {
                                        $bookUrl .= '&pickup_datetime=' . urlencode($pickupDatetime) . '&return_datetime=' . urlencode($returnDatetime);
                                    }
                                    ?>
                                    <a href="<?= $bookUrl ?>" class="btn btn-primary btn-sm">
                                        Book Now &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
