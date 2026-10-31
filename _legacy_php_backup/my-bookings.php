<?php
/**
 * APEX Car Rental Management System
 * Customer Dashboard: Booking History & Active Rental Telemetry Access
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Booking.php';

Auth::requireLogin();
$currentUser = Auth::user();

// Handle cancellation request
$message = '';
$messageType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $cancelId = (int)$_POST['cancel_booking_id'];
    $reason = trim($_POST['cancel_reason'] ?? 'Customer requested cancellation.');

    // Fetch booking to verify ownership and eligibility
    $bk = Booking::getById($cancelId);
    if ($bk && $bk['user_id'] == Auth::id() && in_array($bk['status'], ['pending', 'approved'], true)) {
        $res = Booking::updateStatus($cancelId, 'cancelled', Auth::id(), $reason);
        if ($res['success']) {
            $message = 'Your booking ' . $bk['booking_ref'] . ' has been cancelled successfully.';
            $messageType = 'success';
        } else {
            $message = $res['message'];
            $messageType = 'danger';
        }
    } else {
        $message = 'This booking is not eligible for cancellation.';
        $messageType = 'danger';
    }
}

$bookings = Booking::getUserBookings(Auth::id());
$activeRental = null;
foreach ($bookings as $b) {
    if ($b['status'] === 'out_on_rental') {
        $activeRental = $b;
        break;
    }
}

$pageTitle = "My Reservations & Active Rentals | APEX";
$activeNav = "bookings";
require_once __DIR__ . '/templates/header.php';
?>

<div style="background:#FFFFFF; border-bottom:1px solid var(--apex-border-crisp); padding:24px 0;">
    <div class="container">
        <h1 style="font-size:26px; font-weight:800; color:var(--apex-text-main); margin-bottom:4px;">Customer Dashboard</h1>
        <p style="font-size:14px; color:var(--apex-text-muted);">
            Manage your vehicle reservations, review historical trips, and launch simulated real-time tracking for active rentals.
        </p>
    </div>
</div>

<div class="container" style="padding:32px 0 80px;">
    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>"><?= h($message) ?></div>
    <?php endif; ?>

    <!-- Active Rental Spotlight Banner if an active trip exists -->
    <?php if ($activeRental): ?>
        <div class="card" style="border:2px solid var(--apex-primary); margin-bottom:32px; background:#F8FAFC;">
            <div class="card-body" style="padding:28px;">
                <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:20px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                            <span class="badge badge-out_on_rental" style="font-size:12px; padding:4px 10px;">● ACTIVE TRIP IN PROGRESS</span>
                            <span class="badge badge-sim">SIMULATED GPS ACTIVE</span>
                        </div>
                        <h2 style="font-size:22px; font-weight:800; color:var(--apex-text-main); margin-bottom:4px;">
                            <?= h($activeRental['brand'] . ' ' . $activeRental['model']) ?>
                        </h2>
                        <div style="font-size:13px; color:var(--apex-text-muted); font-family:monospace;">
                            Reference: <strong><?= h($activeRental['booking_ref']) ?></strong> &bull; Plate: <?= h($activeRental['license_plate']) ?> &bull; Due: <?= date('M d, Y h:i A', strtotime($activeRental['return_datetime'])) ?>
                        </div>
                    </div>

                    <a href="<?= apex_url('track.php?booking_id=' . (int)$activeRental['id']) ?>" class="btn btn-primary btn-lg" style="box-shadow: 0 2px 6px rgba(0,120,215,0.25);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                        Launch Simulated Vehicle Tracker &rarr;
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Booking History Table -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">All Reservations History</span>
            <a href="<?= apex_url('fleet.php') ?>" class="btn btn-primary btn-sm">+ Reserve Another Vehicle</a>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($bookings)): ?>
                <div style="padding:40px; text-align:center;">
                    <p style="color:var(--apex-text-muted); margin-bottom:16px;">You haven't made any reservations yet.</p>
                    <a href="<?= apex_url('fleet.php') ?>" class="btn btn-secondary">Browse Vehicle Fleet</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ref #</th>
                                <th>Vehicle</th>
                                <th>Pickup Date</th>
                                <th>Return Date</th>
                                <th>Total (PHP)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td><strong style="font-family:monospace;"><?= h($b['booking_ref']) ?></strong></td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <img src="<?= h($b['image_url']) ?>" alt="Car" style="width:50px; height:32px; object-fit:cover; border-radius:2px; border:1px solid #CBD5E1;">
                                            <div>
                                                <strong><?= h($b['brand'] . ' ' . $b['model']) ?></strong>
                                                <div style="font-size:11px; color:var(--apex-text-muted);"><?= h($b['category_name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= date('M d, Y h:i A', strtotime($b['pickup_datetime'])) ?></td>
                                    <td><?= date('M d, Y h:i A', strtotime($b['return_datetime'])) ?></td>
                                    <td><strong style="color:var(--apex-primary);"><?= format_pesos($b['total_amount']) ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?= h($b['status']) ?>"><?= strtoupper(str_replace('_', ' ', $b['status'])) ?></span>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:8px;">
                                            <?php if ($b['status'] === 'out_on_rental'): ?>
                                                <a href="<?= apex_url('track.php?booking_id=' . (int)$b['id']) ?>" class="btn btn-primary btn-sm">
                                                    Live Map
                                                </a>
                                            <?php endif; ?>

                                            <?php if (in_array($b['status'], ['pending', 'approved'], true)): ?>
                                                <form action="<?= apex_url('my-bookings.php') ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                                    <input type="hidden" name="cancel_booking_id" value="<?= (int)$b['id'] ?>">
                                                    <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--apex-danger);">
                                                        Cancel
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
