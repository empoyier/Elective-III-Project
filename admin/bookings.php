<?php
/**
 * APEX Car Rental Management System
 * Admin Rental Workflow & Booking Queue Management
 */

$pageTitle = "Rental Workflow & Bookings";
$activeAdminNav = "bookings";

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Booking.php';

$message = '';
$messageType = 'info';

// Handle Action Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($bookingId > 0) {
        if ($action === 'approve') {
            $res = Booking::updateStatus($bookingId, 'approved', Auth::id(), 'Approved by staff.');
            $message = $res['message'];
            $messageType = $res['success'] ? 'success' : 'danger';
        } else if ($action === 'reject') {
            $res = Booking::updateStatus($bookingId, 'rejected', Auth::id(), $notes ?: 'Reservation declined by fleet staff.');
            $message = $res['message'];
            $messageType = $res['success'] ? 'success' : 'danger';
        } else if ($action === 'pickup') {
            $res = Booking::updateStatus($bookingId, 'out_on_rental', Auth::id(), 'Vehicle dispatched to customer. Tracking session initialized.');
            $message = $res['message'];
            $messageType = $res['success'] ? 'success' : 'danger';
        } else if ($action === 'return') {
            $additional = [
                'odometer_reading'   => (int)($_POST['odometer_reading'] ?? 0),
                'fuel_level_returned'=> (int)($_POST['fuel_level_returned'] ?? 100),
                'has_damage'         => !empty($_POST['has_damage']) ? 1 : 0,
                'damage_notes'       => trim($_POST['damage_notes'] ?? ''),
                'additional_charges' => (float)($_POST['additional_charges'] ?? 0.0)
            ];
            $res = Booking::updateStatus($bookingId, 'returned', Auth::id(), 'Vehicle returned and inspection recorded.', $additional);
            $message = $res['message'];
            $messageType = $res['success'] ? 'success' : 'danger';
        }
    }
}

// Fetch Filtered Bookings
$statusFilter = $_GET['status'] ?? '';
$searchFilter = $_GET['search'] ?? ($_GET['filter_ref'] ?? '');

$bookings = Booking::getAllBookings([
    'status' => $statusFilter,
    'search' => $searchFilter
]);

require_once __DIR__ . '/nav.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= h($message) ?></div>
<?php endif; ?>

<!-- Status Filter Tabs -->
<div class="category-pills" style="margin-bottom:20px;">
    <a href="<?= apex_url('admin/bookings.php') ?>" class="category-pill <?= empty($statusFilter) ? 'active' : '' ?>">All Bookings</a>
    <a href="<?= apex_url('admin/bookings.php?status=pending') ?>" class="category-pill <?= $statusFilter === 'pending' ? 'active' : '' ?>">Pending Requests</a>
    <a href="<?= apex_url('admin/bookings.php?status=approved') ?>" class="category-pill <?= $statusFilter === 'approved' ? 'active' : '' ?>">Approved (Ready for Pickup)</a>
    <a href="<?= apex_url('admin/bookings.php?status=out_on_rental') ?>" class="category-pill <?= $statusFilter === 'out_on_rental' ? 'active' : '' ?>">Out on Rental (Active)</a>
    <a href="<?= apex_url('admin/bookings.php?status=returned') ?>" class="category-pill <?= $statusFilter === 'returned' ? 'active' : '' ?>">Returned (Completed)</a>
    <a href="<?= apex_url('admin/bookings.php?status=cancelled') ?>" class="category-pill <?= $statusFilter === 'cancelled' ? 'active' : '' ?>">Cancelled / Rejected</a>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">Manage Reservations & Dispatch Workflow (<?= count($bookings) ?> Records)</span>
        
        <form action="<?= apex_url('admin/bookings.php') ?>" method="GET" style="display:flex; gap:8px;">
            <?php if ($statusFilter): ?>
                <input type="hidden" name="status" value="<?= h($statusFilter) ?>">
            <?php endif; ?>
            <input type="text" name="search" class="form-control" style="padding:4px 10px; width:220px;" placeholder="Search ref, customer..." value="<?= h($searchFilter) ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Search</button>
        </form>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Ref #</th>
                        <th>Customer</th>
                        <th>Vehicle & Plate</th>
                        <th>Pickup & Return Schedule</th>
                        <th>Total Amount</th>
                        <th>Current Status</th>
                        <th style="text-align:right;">Workflow Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:32px; color:var(--apex-text-muted);">No bookings found for the selected filter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><strong style="font-family:monospace; color:var(--apex-primary);"><?= h($b['booking_ref']) ?></strong></td>
                                <td>
                                    <strong><?= h($b['customer_name']) ?></strong>
                                    <div style="font-size:11px; color:var(--apex-text-muted);"><?= h($b['customer_email']) ?> &bull; <?= h($b['customer_phone']) ?></div>
                                </td>
                                <td>
                                    <strong><?= h($b['brand'] . ' ' . $b['model']) ?></strong>
                                    <div style="font-size:11px; color:var(--apex-text-muted); font-family:monospace;"><?= h($b['license_plate']) ?> (<?= h($b['category_name']) ?>)</div>
                                </td>
                                <td style="font-size:12px;">
                                    <div><strong>Out:</strong> <?= date('M d, Y h:i A', strtotime($b['pickup_datetime'])) ?></div>
                                    <div><strong>In:</strong> <?= date('M d, Y h:i A', strtotime($b['return_datetime'])) ?></div>
                                </td>
                                <td><strong style="color:var(--apex-primary);"><?= format_pesos($b['total_amount']) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= h($b['status']) ?>"><?= strtoupper(str_replace('_', ' ', $b['status'])) ?></span>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:inline-flex; gap:6px;">
                                        <!-- Actions per Status -->
                                        <?php if ($b['status'] === 'pending'): ?>
                                            <form action="<?= apex_url('admin/bookings.php') ?>" method="POST" style="display:inline;">
                                                <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-primary btn-sm">Approve</button>
                                            </form>

                                            <button type="button" class="btn btn-danger btn-sm" onclick="openRejectModal(<?= (int)$b['id'] ?>, '<?= h($b['booking_ref']) ?>')">Reject</button>
                                        <?php elseif ($b['status'] === 'approved'): ?>
                                            <form action="<?= apex_url('admin/bookings.php') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Confirm customer vehicle pickup and begin simulated tracking?');">
                                                <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                                                <input type="hidden" name="action" value="pickup">
                                                <button type="submit" class="btn btn-primary btn-sm">Confirm Pickup &rarr;</button>
                                            </form>
                                        <?php elseif ($b['status'] === 'out_on_rental'): ?>
                                            <a href="<?= apex_url('track.php?booking_id=' . (int)$b['id']) ?>" class="btn btn-secondary btn-sm" target="_blank">
                                                View Live Map
                                            </a>
                                            <button type="button" class="btn btn-primary btn-sm" onclick="openReturnModal(<?= (int)$b['id'] ?>, '<?= h($b['booking_ref']) ?>', '<?= h($b['brand'] . ' ' . $b['model']) ?>')">
                                                Record Return & Inspection
                                            </button>
                                        <?php else: ?>
                                            <span style="font-size:12px; color:var(--apex-text-muted);">Completed</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal 1: Reject Booking Modal -->
<div id="rejectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div class="card" style="width:440px; max-width:90%; margin:auto;">
        <div class="card-header">
            <span class="card-title">Decline Booking <span id="rejectRef"></span></span>
            <button type="button" onclick="closeRejectModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= apex_url('admin/bookings.php') ?>" method="POST">
                <input type="hidden" name="booking_id" id="rejectBookingId">
                <input type="hidden" name="action" value="reject">

                <div class="form-group">
                    <label class="form-label" for="reject_reason">Reason for Rejection *</label>
                    <textarea name="notes" id="reject_reason" class="form-control" rows="3" required placeholder="e.g. Incomplete driver license documents, scheduling conflict..."></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Return & Vehicle Inspection Modal -->
<div id="returnModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
    <div class="card" style="width:520px; max-width:90%; margin:auto;">
        <div class="card-header">
            <span class="card-title">Record Return & Inspection (<span id="returnRef"></span>)</span>
            <button type="button" onclick="closeReturnModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= apex_url('admin/bookings.php') ?>" method="POST">
                <input type="hidden" name="booking_id" id="returnBookingId">
                <input type="hidden" name="action" value="return">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div class="form-group">
                        <label class="form-label" for="odometer_reading">Return Odometer (km)</label>
                        <input type="number" name="odometer_reading" id="odometer_reading" class="form-control" required placeholder="e.g. 14200">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="fuel_level_returned">Fuel Level Returned (%)</label>
                        <input type="number" name="fuel_level_returned" id="fuel_level_returned" class="form-control" min="0" max="100" value="100" required>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display:flex; align-items:center; gap:8px; font-weight:600; font-size:13px; cursor:pointer;">
                        <input type="checkbox" name="has_damage" value="1" id="has_damage_cb" style="accent-color:var(--apex-danger);">
                        <span>Check if damage / issues detected during inspection</span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="form-label" for="damage_notes">Damage / Condition Inspection Notes</label>
                    <textarea name="damage_notes" id="damage_notes" class="form-control" rows="2" placeholder="Vehicle condition, scratches, cleanliness..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="additional_charges">Additional Charges (PHP, if applicable)</label>
                    <input type="number" step="0.01" name="additional_charges" id="additional_charges" class="form-control" value="0.00">
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeReturnModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Complete Return & Finalize</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openRejectModal(id, ref) {
    document.getElementById('rejectBookingId').value = id;
    document.getElementById('rejectRef').textContent = ref;
    document.getElementById('rejectModal').style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

function openReturnModal(id, ref, vehicle) {
    document.getElementById('returnBookingId').value = id;
    document.getElementById('returnRef').textContent = ref;
    document.getElementById('returnModal').style.display = 'flex';
}
function closeReturnModal() {
    document.getElementById('returnModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
