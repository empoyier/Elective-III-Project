<?php
/**
 * APEX Car Rental Management System
 * Admin Master Live Fleet Tracking Map
 */

$pageTitle = "Master Fleet Live Tracking Map";
$activeAdminNav = "tracking";
$includeMap = true;

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Tracking.php';

$activeSessions = Tracking::getAllActiveSessions();

require_once __DIR__ . '/nav.php';
?>

<div class="simulation-disclaimer-banner" style="margin-bottom:20px;">
    <div>
        <span class="sim-tag">FLEET DISPATCH HUD</span>
        <strong>DISPATCH SIMULATION NOTICE:</strong> Monitoring <?= count($activeSessions) ?> active rental vehicle(s) currently traversing Philippine expressway corridors.
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 340px; gap:24px; min-height:650px;">
    <!-- Master Fleet Map -->
    <div style="background:#0B132B; border:1px solid var(--apex-border-crisp); border-radius:4px; height:650px; position:relative; overflow:hidden;">
        <div id="master-fleet-map" style="width:100%; height:100%;"></div>
    </div>

    <!-- Active Vehicle Fleet Sidebar -->
    <div style="display:flex; flex-direction:column; gap:16px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Active Dispatched Fleet (<?= count($activeSessions) ?>)</span>
            </div>
            <div class="card-body" style="padding:12px; max-height:580px; overflow-y:auto;">
                <?php if (empty($activeSessions)): ?>
                    <p style="font-size:13px; color:var(--apex-text-muted); text-align:center; padding:20px 0;">
                        No vehicles are currently out on rental. Confirm a pending reservation pickup in the <a href="<?= apex_url('admin/bookings.php') ?>">Rental Workflow</a> to dispatch vehicles.
                    </p>
                <?php else: ?>
                    <?php foreach ($activeSessions as $s): ?>
                        <div class="card" style="margin-bottom:12px; border:1px solid #CBD5E1; padding:12px; cursor:pointer;" onclick="focusVehicle(<?= (float)$s['current_lat'] ?>, <?= (float)$s['current_lng'] ?>, <?= (int)$s['booking_id'] ?>)">
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                <img src="<?= h($s['image_url']) ?>" alt="Car" style="width:50px; height:32px; object-fit:cover; border-radius:2px; border:1px solid #CBD5E1;">
                                <div>
                                    <strong style="font-size:14px;"><?= h($s['brand'] . ' ' . $s['model']) ?></strong>
                                    <div style="font-size:11px; color:var(--apex-text-muted); font-family:monospace;"><?= h($s['license_plate']) ?> &bull; <?= h($s['booking_ref']) ?></div>
                                </div>
                            </div>

                            <div style="font-size:12px; color:var(--apex-text-secondary); margin-bottom:6px;">
                                Client: <strong><?= h($s['customer_name']) ?></strong>
                            </div>

                            <div style="display:flex; justify-content:space-between; font-size:12px; background:#F8FAFC; padding:6px 8px; border-radius:2px; font-family:monospace;">
                                <span>Speed: <strong style="color:var(--apex-primary);" id="admin-spd-<?= $s['booking_id'] ?>"><?= (float)$s['current_speed'] ?></strong> km/h</span>
                                <span>Fuel: <strong style="color:var(--apex-success);" id="admin-fuel-<?= $s['booking_id'] ?>"><?= (float)$s['fuel_level_pct'] ?></strong>%</span>
                            </div>

                            <div style="margin-top:8px; text-align:right;">
                                <a href="<?= apex_url('track.php?booking_id=' . (int)$s['booking_id']) ?>" target="_blank" class="btn btn-secondary btn-sm" style="font-size:11px; padding:4px 8px;">
                                    Dedicated HUD &rarr;
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
let masterMap;
let vehicleMarkers = {};
const activeSessions = <?= json_encode($activeSessions) ?>;
const feedUrl = "<?= apex_url('api/tracking-feed.php') ?>";

document.addEventListener('DOMContentLoaded', () => {
    // Center initially on Metro Manila
    masterMap = L.map('master-fleet-map').setView([14.5507, 121.0300], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors &bull; APEX Fleet Dispatch'
    }).addTo(masterMap);

    const carSvg = `
        <div style="width:34px; height:34px; background:#0078D7; border:2px solid #FFFFFF; border-radius:50%; box-shadow:0 2px 6px rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; color:#FFFFFF;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="8" rx="2"/>
                <path d="M5 11l2-5h10l2 5"/>
                <circle cx="7.5" cy="15.5" r="1.5"/>
                <circle cx="16.5" cy="15.5" r="1.5"/>
            </svg>
        </div>
    `;
    const carIcon = L.divIcon({
        className: 'apex-fleet-marker',
        html: carSvg,
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });

    const markerGroup = L.featureGroup();

    activeSessions.forEach(s => {
        const marker = L.marker([parseFloat(s.current_lat), parseFloat(s.current_lng)], { icon: carIcon }).addTo(masterMap);
        marker.bindPopup(`<strong>${s.brand} ${s.model}</strong> (${s.license_plate})<br>Ref: ${s.booking_ref}<br>Speed: ${s.current_speed} km/h`);
        vehicleMarkers[s.booking_id] = marker;
        markerGroup.addLayer(marker);
    });

    if (activeSessions.length > 0) {
        masterMap.fitBounds(markerGroup.getBounds(), { padding: [50, 50], maxZoom: 14 });
    }

    // Periodic Poller for each active vehicle
    setInterval(async () => {
        for (const s of activeSessions) {
            try {
                const resp = await fetch(`${feedUrl}?booking_id=${s.booking_id}`);
                const data = await resp.json();
                if (data.success && data.telemetry.active) {
                    const t = data.telemetry;
                    if (vehicleMarkers[s.booking_id]) {
                        vehicleMarkers[s.booking_id].setLatLng([t.current_lat, t.current_lng]);
                    }
                    const spdEl = document.getElementById(`admin-spd-${s.booking_id}`);
                    if (spdEl) spdEl.textContent = t.current_speed.toFixed(1);
                    const fuelEl = document.getElementById(`admin-fuel-${s.booking_id}`);
                    if (fuelEl) fuelEl.textContent = Math.round(t.fuel_level_pct);
                }
            } catch (err) {
                console.warn(err);
            }
        }
    }, 3500);
});

function focusVehicle(lat, lng, bookingId) {
    if (masterMap) {
        masterMap.setView([lat, lng], 14, { animate: true });
        if (vehicleMarkers[bookingId]) {
            vehicleMarkers[bookingId].openPopup();
        }
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
