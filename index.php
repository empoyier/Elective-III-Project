<?php
/**
 * APEX Car Rental Management System
 * Opening Front Page (50/50 Split Hero with Left Image & Right "APEX Car Rental Services")
 */

$pageTitle = "APEX Car Rental Services | Philippines Fleet & Tracking";
$activeNav = "home";

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Vehicle.php';

// Fetch featured fleet and categories
$featuredVehicles = Vehicle::getFeatured(6);
$categories = Vehicle::getCategories();
$locations = APP_LOCATIONS;

require_once __DIR__ . '/templates/header.php';
?>

<!-- 1. OPENING 50/50 SPLIT HERO -->
<section class="hero-split-section">
    <div class="hero-split-grid">
        <!-- LEFT HALF: Image half the size of the page with crisp borders -->
        <div class="hero-media-half">
            <img src="https://images.unsplash.com/photo-1617469767053-d3b523a0b982?auto=format&fit=crop&w=1600&q=85" 
                 alt="APEX Fleet Luxury Sports Vehicle on Road" 
                 loading="eager">
            <div class="hero-media-caption">
                Photo by Giorgio Trovato on Unsplash &bull; TOYOTA GAZOO Racing
            </div>
        </div>

        <!-- RIGHT HALF: "APEX Car Rental Services", "Get Started" button, and quick booking -->
        <div class="hero-content-half">
            <h1 class="hero-headline">APEX Car Rental Services</h1>
            <p class="hero-lead">
                Premium and reliable vehicle reservations across Metro Manila, Clark, and Cebu. 
                Experience guaranteed date-based availability, transparent Philippine Peso pricing, 
                and simulated real-time GPS fleet telemetry.
            </p>

            <div class="hero-cta-group">
                <a href="#quick-book" class="btn btn-primary btn-lg">Get Started &darr;</a>
                <a href="<?= apex_url('fleet.php') ?>" class="btn btn-secondary btn-lg">Browse Fleet (21 Models)</a>
            </div>

            <!-- Quick Booking Reservation Bar -->
            <div id="quick-book" class="hero-search-box">
                <div class="hero-search-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--apex-primary);"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.9 2 11.2 2 11.5V16c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                    <span>Instant Reservation Search</span>
                </div>

                <form action="<?= apex_url('fleet.php') ?>" method="GET">
                    <div class="hero-search-grid">
                        <div class="form-group hero-search-full">
                            <label class="form-label" for="pickup_location">Pickup & Return Location</label>
                            <select name="location" id="pickup_location" class="form-control" required>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= h($loc) ?>"><?= h($loc) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="pickup_datetime">Pickup Date & Time</label>
                            <input type="datetime-local" 
                                   name="pickup_datetime" 
                                   id="pickup_datetime" 
                                   class="form-control" 
                                   value="<?= date('Y-m-d\TH:i', strtotime('+1 day 09:00')) ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="return_datetime">Return Date & Time</label>
                            <input type="datetime-local" 
                                   name="return_datetime" 
                                   id="return_datetime" 
                                   class="form-control" 
                                   value="<?= date('Y-m-d\TH:i', strtotime('+4 days 18:00')) ?>" 
                                   required>
                        </div>

                        <div class="form-group hero-search-full">
                            <label class="form-label" for="category">Vehicle Category</label>
                            <select name="category" id="category" class="form-control">
                                <option value="">All Categories (Economy, Sedan, SUV, Van, Luxury, Sports, EV)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= h($cat['slug']) ?>"><?= h($cat['name']) ?> (<?= (int)$cat['vehicle_count'] ?> models)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-weight:700;">
                        Search Available Vehicles &rarr;
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- 2. FOUR HARD-BORDERED BENEFIT CARDS (NO GLOW, NO EYEBROWS) -->
<section style="padding: 48px 0; background:#FFFFFF; border-bottom:1px solid var(--apex-border-crisp);">
    <div class="container">
        <div class="benefits-grid">
            <div class="benefit-card">
                <div class="benefit-icon">₱</div>
                <div class="benefit-title">Transparent ₱ Pricing</div>
                <div class="benefit-desc">
                    All rental fees are calculated transparently with zero hidden costs. Daily rates, security deposit, and optional add-ons are itemized up front.
                </div>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">🛡</div>
                <div class="benefit-title">Zero Double-Booking Guarantee</div>
                <div class="benefit-desc">
                    Our database concurrency engine validates date intervals to mathematically prevent duplicate reservations for the same vehicle.
                </div>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">🛰</div>
                <div class="benefit-title">Simulated Real-Time Tracking</div>
                <div class="benefit-desc">
                    Monitor your vehicle's simulated progression along iconic Philippine expressways with live speed, distance, and fuel telemetry HUD.
                </div>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon">📋</div>
                <div class="benefit-title">Verified Fleet & LTO Compliant</div>
                <div class="benefit-desc">
                    Every vehicle undergoes regular scheduled maintenance and thorough return inspection to ensure optimal reliability and road safety.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. FEATURED FLEET SHOWCASE -->
<section style="padding: 60px 0;">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Featured Fleet in the Philippines</h2>
                <div class="section-subtitle">Select from our professionally maintained subcompacts, SUVs, MPVs, and luxury sedans.</div>
            </div>
            <a href="<?= apex_url('fleet.php') ?>" class="btn btn-secondary">View All 21 Vehicles &rarr;</a>
        </div>

        <!-- Fast Category Filter -->
        <div class="category-pills">
            <a href="<?= apex_url('fleet.php') ?>" class="category-pill active">All Vehicles (21)</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= apex_url('fleet.php?category=' . urlencode($cat['slug'])) ?>" class="category-pill">
                    <?= h($cat['name']) ?> (<?= (int)$cat['vehicle_count'] ?>)
                </a>
            <?php endforeach; ?>
        </div>

        <!-- 3-Column Fleet Cards -->
        <div class="fleet-grid">
            <?php foreach ($featuredVehicles as $veh): ?>
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

                        <div class="vehicle-features-list">
                            <?php 
                            $feats = !empty($veh['features_json']) ? (json_decode($veh['features_json'], true) ?: []) : [];
                            foreach (array_slice($feats, 0, 3) as $f): 
                            ?>
                                <span class="feature-tag"><?= h($f) ?></span>
                            <?php endforeach; ?>
                        </div>

                        <div class="vehicle-card-footer">
                            <div class="price-box">
                                <span class="price-amount"><?= format_pesos($veh['daily_rate']) ?></span>
                                <span class="price-period">per day</span>
                            </div>

                            <a href="<?= apex_url('book.php?vehicle_id=' . (int)$veh['id']) ?>" class="btn btn-primary btn-sm">
                                Reserve &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;">
            <a href="<?= apex_url('fleet.php') ?>" class="btn btn-secondary btn-lg">Explore Full Catalog with Real-Time Filters &rarr;</a>
        </div>
    </div>
</section>

<!-- 4. PHILIPPINE SERVICE HUBS & MAP NOTICE -->
<section id="locations" style="padding: 60px 0; background:#FFFFFF; border-top:1px solid var(--apex-border-crisp);">
    <div class="container">
        <div class="section-header">
            <div>
                <h2 class="section-title">Philippine Rental Locations & Express Hubs</h2>
                <div class="section-subtitle">Pick up and return your vehicle at major international airports and commercial centers.</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:20px; margin-top:24px;">
            <div style="background:#F8FAFC; border:1px solid var(--apex-border-crisp); padding:20px; border-radius:4px;">
                <h3 style="font-size:16px; font-weight:700; color:var(--apex-primary); margin-bottom:8px;">Metro Manila Hubs</h3>
                <p style="font-size:13px; color:var(--apex-text-muted); line-height:1.6;">
                    &bull; NAIA Terminal 3 Arrival Wing<br>
                    &bull; Bonifacio Global City (BGC High Street)<br>
                    &bull; Ayala Center (Makati CBD)<br>
                    &bull; Quezon City North Hub (Trinoma/SM North)
                </p>
            </div>

            <div style="background:#F8FAFC; border:1px solid var(--apex-border-crisp); padding:20px; border-radius:4px;">
                <h3 style="font-size:16px; font-weight:700; color:var(--apex-primary); margin-bottom:8px;">Central Luzon Hub</h3>
                <p style="font-size:13px; color:var(--apex-text-muted); line-height:1.6;">
                    &bull; Clark International Airport (CRK)<br>
                    &bull; Subic Bay FreePort Transfer Zone<br>
                    &bull; Direct NLEX / SCTEX Expressway connectivity
                </p>
            </div>

            <div style="background:#F8FAFC; border:1px solid var(--apex-border-crisp); padding:20px; border-radius:4px;">
                <h3 style="font-size:16px; font-weight:700; color:var(--apex-primary); margin-bottom:8px;">Visayas Regional Hub</h3>
                <p style="font-size:13px; color:var(--apex-text-muted); line-height:1.6;">
                    &bull; Mactan-Cebu International Airport (MCIA)<br>
                    &bull; Cebu South Road Properties (SRP)<br>
                    &bull; Direct CCLEX Bridge Access
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
