<?php
/**
 * APEX Car Rental Management System
 * Booking Confirmation & Success Summary
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Booking.php';

Auth::requireLogin();

$ref = trim($_GET['ref'] ?? '');
if (empty($ref)) {
    header("Location: " . apex_url('my-bookings.php'));
    exit;
}

$booking = Booking::getByRef($ref);
if (!$booking) {
    header("Location: " . apex_url('my-bookings.php'));
    exit;
}

$pageTitle = "Reservation Confirmed: " . $booking['booking_ref'] . " | APEX";
require_once __DIR__ . '/templates/header.php';
?>

<div class="container" style="max-width:850px; padding:40px 20px 80px;">
    <div class="card" style="border-top:4px solid var(--apex-primary);">
        <div class="card-body" style="padding:40px; text-align:center;">
            <div style="width:64px; height:64px; background:var(--apex-success-bg); color:var(--apex-success); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:32px; font-weight:bold; margin-bottom:16px;">
                ✓
            </div>

            <h1 style="font-size:26px; font-weight:800; color:var(--apex-text-main); margin-bottom:8px;">
                Reservation Request Submitted Successfully
            </h1>
            <p style="font-size:15px; color:var(--apex-text-muted); margin-bottom:24px;">
                Your reservation request has been received by our fleet operations desk. Please keep your booking reference number for tracking and pickup.
            </p>

            <div style="background:#F1F5F9; border:1px solid var(--apex-border-crisp); padding:16px 24px; border-radius:4px; display:inline-block; margin-bottom:32px;">
                <span style="font-size:12px; font-weight:700; color:var(--apex-text-muted); text-transform:uppercase; display:block; margin-bottom:4px;">Booking Reference Number</span>
                <span style="font-size:28px; font-weight:900; color:var(--apex-primary); font-family:monospace; letter-spacing:1px;">
                    <?= h($booking['booking_ref']) ?>
                </span>
            </div>

            <!-- Booking Details Summary Table -->
            <div style="text-align:left; border:1px solid var(--apex-border-crisp); border-radius:4px; overflow:hidden; margin-bottom:32px;">
                <table class="table">
                    <tr>
                        <th style="width:35%;">Status:</th>
                        <td><span class="badge badge-<?= h($booking['status']) ?>"><?= strtoupper(h($booking['status'])) ?></span></td>
                    </tr>
                    <tr>
                        <th>Reserved Vehicle:</th>
                        <td><strong><?= h($booking['brand'] . ' ' . $booking['model']) ?></strong> (<?= h($booking['license_plate']) ?> &bull; <?= h($booking['category_name']) ?>)</td>
                    </tr>
                    <tr>
                        <th>Pickup Schedule:</th>
                        <td><?= date('M d, Y h:i A', strtotime($booking['pickup_datetime'])) ?> at <strong><?= h($booking['pickup_location']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Return Schedule:</th>
                        <td><?= date('M d, Y h:i A', strtotime($booking['return_datetime'])) ?> at <strong><?= h($booking['return_location']) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Total Rental Period:</th>
                        <td><?= (int)$booking['total_days'] ?> Days @ <?= format_pesos($booking['daily_rate']) ?>/day</td>
                    </tr>
                    <?php if (!empty($booking['addons'])): ?>
                        <tr>
                            <th>Included Add-Ons:</th>
                            <td>
                                <?php foreach ($booking['addons'] as $addon): ?>
                                    <div>&bull; <?= h($addon['name']) ?> (<?= format_pesos($addon['subtotal']) ?>)</div>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr style="background:#F8FAFC;">
                        <th>Grand Total Amount:</th>
                        <td><strong style="font-size:18px; color:var(--apex-primary);"><?= format_pesos($booking['total_amount']) ?></strong> (PHP)</td>
                    </tr>
                </table>
            </div>

            <div style="display:flex; justify-content:center; gap:16px;">
                <a href="<?= apex_url('my-bookings.php') ?>" class="btn btn-primary btn-lg">View in My Bookings &rarr;</a>
                <button onclick="window.print()" class="btn btn-secondary btn-lg">Print Summary Receipt</button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
