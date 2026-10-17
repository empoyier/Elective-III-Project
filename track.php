<?php
/**
 * APEX Car Rental Management System
 * Simulated Real-Time Vehicle Tracking Interface (Leaflet.js)
 */

$includeTrackingCss = true;
$activeNav = "tracking";

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Tracking.php';
require_once __DIR__ . '/src/Booking.php';

$bookingId = (int)($_GET['booking_id'] ?? 0);

// If no booking_id specified, find active booking
if ($bookingId <= 0) {
    if (Auth::isLoggedIn()) {
        $userActive = Database::fetchOne("SELECT `id` FROM `bookings` WHERE `user_id` = ? AND `status` = 'out_on_rental' LIMIT 1", [Auth::id()]);
        if ($userActive) {
            $bookingId = (int)$userActive['id'];
        }
    }

    // Fallback to any active demonstration booking
    if ($bookingId <= 0) {
        $demoActive = Database::fetchOne("SELECT `id` FROM `bookings` WHERE `status` = 'out_on_rental' LIMIT 1");
        if ($demoActive) {
            $bookingId = (int)$demoActive['id'];
        }
    }
}

$session = $bookingId > 0 ? Tracking::getSessionForBooking($bookingId) : null;
$pageTitle = "Simulated Vehicle Tracking HUD | APEX";

require_once __DIR__ . '/templates/header.php';
?>

<div style="background:#FFFFFF; border-bottom:1px solid var(--apex-border-crisp); padding:20px 0;">
    <div class="container-wide" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div>
            <h1 style="font-size:24px; font-weight:800; color:var(--apex-text-main); margin-bottom:2px;">
                Simulated Real-Time Fleet Tracking
            </h1>
            <p style="font-size:13px; color:var(--apex-text-muted);">
                Mathematical waypoint telemetry along predefined Philippine expressways &bull; Operating without physical GPS hardware
            </p>
        </div>
        <div>
            <span class="badge badge-sim" style="font-size:13px; padding:6px 12px;">
                ● LIVE SIMULATION ENGINE
            </span>
        </div>
    </div>
</div>

<div class="container-wide tracking-page-wrap">
    <!-- Required Simulation Disclaimer -->
    <div class="simulation-disclaimer-banner">
        <div>
            <span class="sim-tag">NOTICE</span>
            <strong>SIMULATED TELEMETRY:</strong> All geographic coordinates, speeds, distance counters, and fuel levels on this interface are synthetic demonstrations generated via mathematical route interpolation and do not represent physical hardware GPS.
        </div>
        <div style="font-family:monospace; font-size:12px; color:#94A3B8;">
            Mode: Synthetic Waypoint Loop
        </div>
    </div>

    <?php if (!$session): ?>
        <div class="card" style="padding:60px 20px; text-align:center;">
            <h2 style="font-size:20px; color:var(--apex-text-main); margin-bottom:8px;">No Active Rental Vehicle Found</h2>
            <p style="font-size:14px; color:var(--apex-text-muted); margin-bottom:24px;">
                Simulated tracking is only available for vehicles currently dispatched under "Out on Rental" status.
            </p>
            <a href="<?= apex_url('fleet.php') ?>" class="btn btn-primary">Book a Vehicle to Begin &rarr;</a>
        </div>
    <?php else: ?>
        <div class="tracking-layout-grid">
            <!-- Left: Interactive Leaflet Map -->
            <div class="map-container-wrap">
                <!-- Floating HUD Speed & Fuel Gauge -->
                <div class="map-hud-overlay">
                    <div class="hud-vehicle-title" id="hud-vehicle-title">
                        <?= h($session['brand'] . ' ' . $session['model']) ?>
                    </div>
                    <span class="hud-plate-tag" id="hud-plate"><?= h($session['license_plate']) ?></span>
                    <span style="font-size:11px; color:#94A3B8; margin-left:8px;" id="hud-ref"><?= h($session['booking_ref']) ?></span>

                    <div class="hud-metrics-row">
                        <div class="hud-stat-box">
                            <div class="hud-stat-label">Simulated Speed</div>
                            <div>
                                <span class="hud-stat-val" id="hud-speed"><?= number_format((float)$session['current_speed'], 1) ?></span>
                                <span class="hud-stat-unit">km/h</span>
                            </div>
                        </div>

                        <div class="hud-stat-box">
                            <div class="hud-stat-label"><?= $session['fuel_type'] === 'Electric' ? 'Battery Level' : 'Estimated Fuel' ?></div>
                            <div>
                                <span class="hud-stat-val" id="hud-fuel"><?= number_format((float)$session['fuel_level_pct'], 0) ?></span>
                                <span class="hud-stat-unit">%</span>
                            </div>
                        </div>
                    </div>

                    <div style="font-size:11px; color:#94A3B8; display:flex; justify-content:space-between;">
                        <span>Telemetry Ping:</span>
                        <span id="hud-ping" style="color:#F8FAFC; font-family:monospace;"><?= date('H:i:s') ?></span>
                    </div>
                </div>

                <div id="tracking-map"></div>
            </div>

            <!-- Right: Route & Simulation Diagnostics Sidebar -->
            <div class="tracking-sidebar">
                <div class="telemetry-card">
                    <h3 class="telemetry-card-title">Assigned Route Details</h3>
                    <div style="font-size:14px; font-weight:700; color:var(--apex-primary); margin-bottom:4px;">
                        <?= h($session['route_name']) ?>
                    </div>
                    <p style="font-size:12px; color:var(--apex-text-muted); line-height:1.5; margin-bottom:14px;">
                        <?= h($session['route_description']) ?>
                    </p>

                    <div style="font-size:12px; margin-bottom:6px; display:flex; justify-content:space-between;">
                        <span>Start: <strong><?= h($session['start_location']) ?></strong></span>
                    </div>
                    <div style="font-size:12px; margin-bottom:14px; display:flex; justify-content:space-between;">
                        <span>End: <strong><?= h($session['end_location']) ?></strong></span>
                    </div>

                    <div style="font-size:12px; font-weight:600; display:flex; justify-content:space-between;">
                        <span>Route Progress:</span>
                        <span id="txt-progress-dist"><?= number_format((float)$session['distance_traveled_km'], 1) ?> / <?= number_format((float)$session['total_distance_km'], 1) ?> km</span>
                    </div>
                    <div class="progress-track">
                        <?php 
                        $pct = min(100, round(((float)$session['distance_traveled_km'] / max(1, (float)$session['total_distance_km'])) * 100));
                        ?>
                        <div class="progress-bar-fill" id="progress-bar" style="width: <?= $pct ?>%;"></div>
                    </div>

                    <div style="font-size:12px; font-weight:600; display:flex; justify-content:space-between;">
                        <span><?= $session['fuel_type'] === 'Electric' ? 'Battery Reserve' : 'Fuel Tank' ?>:</span>
                        <span id="txt-fuel-pct"><?= number_format((float)$session['fuel_level_pct'], 1) ?>%</span>
                    </div>
                    <div class="progress-track">
                        <div class="fuel-gauge-fill" id="fuel-gauge" style="width: <?= (float)$session['fuel_level_pct'] ?>%;"></div>
                    </div>
                </div>

                <!-- Simulation Interactive Controls -->
                <div class="telemetry-card">
                    <h3 class="telemetry-card-title">Simulation Controls</h3>
                    <p style="font-size:12px; color:var(--apex-text-muted); margin-bottom:12px;">
                        Adjust telemetry tick frequency or pause route animation.
                    </p>

                    <div style="display:flex; gap:8px; margin-bottom:12px;">
                        <button type="button" id="btn-toggle-play" class="btn btn-primary btn-sm" style="flex:1;">
                            ⏸ Pause Simulation
                        </button>
                    </div>

                    <div style="font-size:12px; font-weight:600; margin-bottom:6px; color:var(--apex-text-secondary);">
                        Tick Frequency Multiplier:
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:6px;">
                        <button type="button" class="btn btn-secondary btn-sm btn-speed active" data-interval="3000">1x (Normal)</button>
                        <button type="button" class="btn btn-secondary btn-sm btn-speed" data-interval="1500">2x (Fast)</button>
                        <button type="button" class="btn btn-secondary btn-sm btn-speed" data-interval="600">5x (Sprint)</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($session): ?>
<!-- Leaflet & Simulation JavaScript Engine -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const bookingId = <?= (int)$session['booking_id'] ?>;
    const initialLat = <?= (float)$session['current_lat'] ?>;
    const initialLng = <?= (float)$session['current_lng'] ?>;
    const waypoints = <?= json_encode($session['waypoints']) ?>;
    const feedEndpoint = "<?= apex_url('api/tracking-feed.php') ?>";

    // 1. Initialize Leaflet Map centered on initial waypoint
    const map = L.map('tracking-map', {
        zoomControl: true,
        attributionControl: true
    }).setView([initialLat, initialLng], 13);

    // Add OpenStreetMap Tile Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors &bull; APEX Tracking Simulation'
    }).addTo(map);

    // 2. Draw Predefined Route Polyline (Hard Windows BSoD Blue)
    const latLngs = waypoints.map(wp => [wp[0], wp[1]]);
    const polyline = L.polyline(latLngs, {
        color: '#0078D7',
        weight: 5,
        opacity: 0.85,
        lineCap: 'round',
        lineJoin: 'round'
    }).addTo(map);

    // Zoom map to fit polyline
    map.fitBounds(polyline.getBounds(), { padding: [40, 40] });

    // 3. Custom Vehicle Marker with Hard Border & Direction Icon
    const carIcon = L.divIcon({
        className: 'apex-vehicle-marker',
        html: `
            <div style="width:36px; height:36px; background:#0078D7; border:2px solid #FFFFFF; border-radius:50%; box-shadow:0 2px 6px rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; color:#FFFFFF;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="8" rx="2"/>
                    <path d="M5 11l2-5h10l2 5"/>
                    <circle cx="7.5" cy="15.5" r="1.5"/>
                    <circle cx="16.5" cy="15.5" r="1.5"/>
                </svg>
            </div>
        `,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });

    const marker = L.marker([initialLat, initialLng], { icon: carIcon }).addTo(map);
    marker.bindPopup(`<strong><?= h($session['brand'] . ' ' . $session['model']) ?></strong><br>Simulated Speed: <span id="popup-speed"><?= (float)$session['current_speed'] ?></span> km/h`);

    // 4. Telemetry Loop
    let isPlaying = true;
    let pollIntervalMs = 3000;
    let timerId = null;

    async function fetchTelemetryTick() {
        if (!isPlaying) return;

        try {
            const resp = await fetch(`${feedEndpoint}?booking_id=${bookingId}`);
            const data = await resp.json();

            if (data.success && data.telemetry.active) {
                const t = data.telemetry;

                // Move vehicle marker smoothly
                const newPos = [t.current_lat, t.current_lng];
                marker.setLatLng(newPos);
                map.panTo(newPos, { animate: true, duration: 0.8 });

                // Update HUD Elements
                document.getElementById('hud-speed').textContent = t.current_speed.toFixed(1);
                document.getElementById('hud-fuel').textContent = Math.round(t.fuel_level_pct);
                document.getElementById('hud-ping').textContent = new Date().toLocaleTimeString();

                // Update Sidebar
                document.getElementById('txt-progress-dist').textContent = `${t.distance_traveled_km.toFixed(1)} / ${t.total_distance_km.toFixed(1)} km`;
                const pct = Math.min(100, Math.round((t.distance_traveled_km / (t.total_distance_km || 1)) * 100));
                document.getElementById('progress-bar').style.width = `${pct}%`;

                document.getElementById('txt-fuel-pct').textContent = `${t.fuel_level_pct.toFixed(1)}%`;
                const fuelGauge = document.getElementById('fuel-gauge');
                fuelGauge.style.width = `${t.fuel_level_pct}%`;
                if (t.fuel_level_pct < 20) {
                    fuelGauge.classList.add('low');
                } else {
                    fuelGauge.classList.remove('low');
                }

                // Update popup if open
                const pSpeed = document.getElementById('popup-speed');
                if (pSpeed) pSpeed.textContent = t.current_speed.toFixed(1);
            }
        } catch (e) {
            console.warn("Simulation polling tick error:", e);
        }

        if (isPlaying) {
            timerId = setTimeout(fetchTelemetryTick, pollIntervalMs);
        }
    }

    // Start ticker
    timerId = setTimeout(fetchTelemetryTick, pollIntervalMs);

    // 5. Controls Handlers
    const playBtn = document.getElementById('btn-toggle-play');
    playBtn.addEventListener('click', () => {
        isPlaying = !isPlaying;
        if (isPlaying) {
            playBtn.textContent = '⏸ Pause Simulation';
            playBtn.className = 'btn btn-primary btn-sm';
            fetchTelemetryTick();
        } else {
            playBtn.textContent = '▶ Resume Simulation';
            playBtn.className = 'btn btn-secondary btn-sm';
            if (timerId) clearTimeout(timerId);
        }
    });

    document.querySelectorAll('.btn-speed').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.btn-speed').forEach(b => b.classList.remove('active', 'btn-primary'));
            btn.classList.add('active');
            pollIntervalMs = parseInt(btn.getAttribute('data-interval'), 10);
        });
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
