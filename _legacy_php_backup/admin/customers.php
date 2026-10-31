<?php
/**
 * APEX Car Rental Management System
 * Admin Customer Accounts & Access Management
 */

$pageTitle = "Customer Accounts Management";
$activeAdminNav = "customers";

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Database.php';

$message = '';
$messageType = 'info';

// Handle Block / Unblock Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_toggle_status'])) {
    $userId = (int)$_POST['user_id'];
    $newStatus = $_POST['new_status'];

    if (in_array($newStatus, ['active', 'blocked'], true) && $userId > 0) {
        Database::query("UPDATE `users` SET `status` = ? WHERE `id` = ?", [$newStatus, $userId]);
        $message = "User status updated to " . strtoupper($newStatus) . ".";
        $messageType = 'success';
    }
}

// Fetch all customers with total bookings count
$customers = Database::fetchAll(
    "SELECT u.*, COUNT(b.id) AS total_bookings, 
            MAX(b.created_at) AS last_booking_date 
     FROM `users` u 
     LEFT JOIN `bookings` b ON b.user_id = u.id 
     WHERE u.role = 'customer' 
     GROUP BY u.id 
     ORDER BY u.created_at DESC"
);

require_once __DIR__ . '/nav.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= h($message) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Registered Customer Accounts (<?= count($customers) ?>)</span>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Email</th>
                        <th>Contact Phone</th>
                        <th>LTO Driver's License</th>
                        <th>Total Bookings</th>
                        <th>Account Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:32px; color:var(--apex-text-muted);">No customer accounts registered yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): ?>
                            <tr>
                                <td><strong><?= h($c['name']) ?></strong></td>
                                <td><?= h($c['email']) ?></td>
                                <td><?= h($c['phone'] ?: '—') ?></td>
                                <td><span style="font-family:monospace;"><?= h($c['license_number'] ?: 'Not Provided') ?></span></td>
                                <td><strong><?= (int)$c['total_bookings'] ?></strong> reservation(s)</td>
                                <td>
                                    <span class="badge badge-<?= $c['status'] === 'active' ? 'available' : 'blocked' ?>">
                                        <?= strtoupper(h($c['status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <form action="<?= apex_url('admin/customers.php') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to change account status?');">
                                        <input type="hidden" name="action_toggle_status" value="1">
                                        <input type="hidden" name="user_id" value="<?= (int)$c['id'] ?>">

                                        <?php if ($c['status'] === 'active'): ?>
                                            <input type="hidden" name="new_status" value="blocked">
                                            <button type="submit" class="btn btn-danger btn-sm">Block Account</button>
                                        <?php else: ?>
                                            <input type="hidden" name="new_status" value="active">
                                            <button type="submit" class="btn btn-secondary btn-sm" style="color:var(--apex-success); border-color:#A7F3D0;">Unblock Account</button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
