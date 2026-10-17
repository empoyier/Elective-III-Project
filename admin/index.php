<?php
/**
 * APEX Car Rental Management System
 * Admin Dashboard Overview & Operational KPIs
 */

$pageTitle = "Dashboard Overview";
$activeAdminNav = "dashboard";

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Analytics.php';
require_once __DIR__ . '/../src/Booking.php';

$kpis = Analytics::getSummaryKpis();
$recentBookings = Analytics::getRecentBookings(6);
$categoryData = Analytics::getFleetByCategory();
$statusData = Analytics::getBookingsByStatus();

require_once __DIR__ . '/nav.php';
?>

<!-- KPI Metric Cards Grid -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap:20px; margin-bottom:32px;">
    <div class="card" style="border-left:4px solid var(--apex-primary); padding:20px;">
        <div style="font-size:12px; font-weight:700; color:var(--apex-text-muted); text-transform:uppercase;">Total Fleet</div>
        <div style="font-size:32px; font-weight:900; color:var(--apex-text-main); margin:4px 0;"><?= $kpis['total_vehicles'] ?></div>
        <div style="font-size:12px; color:var(--apex-success); font-weight:600;">Active in Inventory</div>
    </div>

    <div class="card" style="border-left:4px solid var(--apex-success); padding:20px;">
        <div style="font-size:12px; font-weight:700; color:var(--apex-text-muted); text-transform:uppercase;">Available Now</div>
        <div style="font-size:32px; font-weight:900; color:var(--apex-success); margin:4px 0;"><?= $kpis['available_vehicles'] ?></div>
        <div style="font-size:12px; color:var(--apex-text-muted);">Ready for dispatch</div>
    </div>

    <div class="card" style="border-left:4px solid var(--apex-warning); padding:20px;">
        <div style="font-size:12px; font-weight:700; color:var(--apex-text-muted); text-transform:uppercase;">Pending Review</div>
        <div style="font-size:32px; font-weight:900; color:var(--apex-warning); margin:4px 0;"><?= $kpis['pending_bookings'] ?></div>
        <div style="font-size:12px; color:var(--apex-text-muted);">Awaiting staff action</div>
    </div>

    <div class="card" style="border-left:4px solid #38BDF8; padding:20px;">
        <div style="font-size:12px; font-weight:700; color:var(--apex-text-muted); text-transform:uppercase;">Out on Rental</div>
        <div style="font-size:32px; font-weight:900; color:#0284C7; margin:4px 0;"><?= $kpis['active_rentals'] ?></div>
        <div style="font-size:12px; color:var(--apex-text-muted);">Currently tracking</div>
    </div>

    <div class="card" style="border-left:4px solid #8B5CF6; padding:20px;">
        <div style="font-size:12px; font-weight:700; color:var(--apex-text-muted); text-transform:uppercase;">Recognized Revenue</div>
        <div style="font-size:26px; font-weight:900; color:#6D28D9; margin:4px 0;"><?= format_pesos($kpis['total_revenue']) ?></div>
        <div style="font-size:12px; color:var(--apex-text-muted);">Active & completed</div>
    </div>

    <div class="card" style="border-left:4px solid #059669; padding:20px;">
        <div style="font-size:12px; font-weight:700; color:var(--apex-text-muted); text-transform:uppercase;">Fleet Utilization</div>
        <div style="font-size:32px; font-weight:900; color:var(--apex-text-main); margin:4px 0;"><?= $kpis['fleet_utilization'] ?>%</div>
        <div style="font-size:12px; color:var(--apex-text-muted);">Active vs total fleet</div>
    </div>
</div>

<!-- Chart.js Operational Charts -->
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-bottom:32px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Fleet Distribution by Category</span>
        </div>
        <div class="card-body" style="height:280px; display:flex; align-items:center; justify-content:center;">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Bookings Breakdown by Status</span>
        </div>
        <div class="card-body" style="height:280px; display:flex; align-items:center; justify-content:center;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Booking Requests Queue -->
<div class="card">
    <div class="card-header">
        <span class="card-title">Recent Reservation Requests</span>
        <a href="<?= apex_url('admin/bookings.php') ?>" class="btn btn-secondary btn-sm">View Full Queue &rarr;</a>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ref #</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Pickup Schedule</th>
                        <th>Total (PHP)</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentBookings)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:24px; color:var(--apex-text-muted);">No reservations recorded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td><strong style="font-family:monospace;"><?= h($b['booking_ref']) ?></strong></td>
                                <td><?= h($b['customer_name']) ?></td>
                                <td><?= h($b['brand'] . ' ' . $b['model']) ?> (<?= h($b['license_plate']) ?>)</td>
                                <td><?= date('M d, Y', strtotime($b['pickup_datetime'])) ?></td>
                                <td><strong style="color:var(--apex-primary);"><?= format_pesos($b['total_amount']) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= h($b['status']) ?>"><?= strtoupper(str_replace('_', ' ', $b['status'])) ?></span>
                                </td>
                                <td>
                                    <a href="<?= apex_url('admin/bookings.php?filter_ref=' . urlencode($b['booking_ref'])) ?>" class="btn btn-secondary btn-sm">
                                        Review &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Category Chart
    const catLabels = <?= json_encode(array_column($categoryData, 'name')) ?>;
    const catCounts = <?= json_encode(array_map('intval', array_column($categoryData, 'vehicle_count'))) ?>;
    
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catCounts,
                backgroundColor: ['#0078D7', '#0284C7', '#059669', '#D97706', '#8B5CF6', '#DC2626', '#1E293B'],
                borderWidth: 1,
                borderColor: '#FFFFFF'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });

    // 2. Status Chart
    const statusLabels = <?= json_encode(array_map(fn($s) => strtoupper(str_replace('_', ' ', $s['status'])), $statusData)) ?>;
    const statusCounts = <?= json_encode(array_map('intval', array_column($statusData, 'count'))) ?>;

    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: statusLabels,
            datasets: [{
                label: 'Bookings',
                data: statusCounts,
                backgroundColor: '#0078D7',
                borderRadius: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
