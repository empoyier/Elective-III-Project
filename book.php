<?php
/**
 * APEX Car Rental Management System
 * 4-Step Interactive Reservation Wizard
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/Vehicle.php';
require_once __DIR__ . '/src/Booking.php';

// Require customer to be logged in before booking
if (!Auth::isLoggedIn()) {
    header("Location: " . apex_url('login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? 'book.php')));
    exit;
}

$currentUser = Auth::user();

$vehicleId = (int)($_GET['vehicle_id'] ?? $_POST['vehicle_id'] ?? 1);
$vehicle = Vehicle::getById($vehicleId);
if (!$vehicle) {
    header("Location: " . apex_url('fleet.php'));
    exit;
}

$addons = Booking::getAddons();
$locations = APP_LOCATIONS;

$error = '';
$success = '';

// Default values
$pickupDatetime = $_POST['pickup_datetime'] ?? $_GET['pickup_datetime'] ?? date('Y-m-d\TH:i', strtotime('+1 day 09:00'));
$returnDatetime = $_POST['return_datetime'] ?? $_GET['return_datetime'] ?? date('Y-m-d\TH:i', strtotime('+4 days 18:00'));
$pickupLocation = $_POST['pickup_location'] ?? $locations[0];
$returnLocation = $_POST['return_location'] ?? $locations[0];
$selectedAddonIds = array_map('intval', $_POST['addon_ids'] ?? []);
$notes = trim($_POST['customer_notes'] ?? '');
$licenseNumber = trim($_POST['license_number'] ?? ($currentUser['license_number'] ?? ''));

// Form submission handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    if (!Auth::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security session expired. Please refresh and try again.';
    } else if (empty($licenseNumber)) {
        $error = 'Please provide your valid LTO Driver\'s License number.';
    } else {
        // Update user's license number in db if needed
        if (empty($currentUser['license_number'])) {
            Database::query("UPDATE `users` SET `license_number` = ? WHERE `id` = ?", [$licenseNumber, Auth::id()]);
            $_SESSION['apex_user']['license_number'] = $licenseNumber;
        }

        $result = Booking::createBooking(
            Auth::id(),
            $vehicleId,
            $pickupDatetime,
            $returnDatetime,
            $pickupLocation,
            $returnLocation,
            $selectedAddonIds,
            $notes
        );

        if ($result['success']) {
            header("Location: " . apex_url('booking-success.php?ref=' . urlencode($result['booking_ref'])));
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

// Calculate preliminary pricing preview
try {
    $pricing = Booking::calculatePricing($vehicleId, $pickupDatetime, $returnDatetime, $selectedAddonIds);
} catch (Exception $e) {
    $pricing = [
        'total_days' => 3,
        'daily_rate' => (float)$vehicle['daily_rate'],
        'base_amount' => (float)$vehicle['daily_rate'] * 3,
        'addons_amount' => 0.0,
        'tax_amount' => 0.0,
        'deposit_amount' => 5000.0,
        'total_amount' => (float)$vehicle['daily_rate'] * 3,
        'grand_total' => ((float)$vehicle['daily_rate'] * 3) + 5000.0,
        'addons' => []
    ];
}

$pageTitle = "Reserve " . $vehicle['brand'] . ' ' . $vehicle['model'] . " | APEX";
require_once __DIR__ . '/templates/header.php';
?>

<div style="background:#FFFFFF; border-bottom:1px solid var(--apex-border-crisp); padding:24px 0;">
    <div class="container">
        <h1 style="font-size:26px; font-weight:800; color:var(--apex-text-main); margin-bottom:4px;">Vehicle Reservation Wizard</h1>
        <p style="font-size:14px; color:var(--apex-text-muted);">
            Complete your booking in 4 clear steps with live Philippine Peso pricing and date-conflict validation.
        </p>
    </div>
</div>

<div class="container" style="padding:32px 0 60px;">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <!-- 4-Step Progress Indicator -->
    <div class="wizard-steps">
        <div class="wizard-step active" id="step-indicator-1">
            <span class="wizard-step-num">1</span>
            <span>Dates & Locations</span>
        </div>
        <div class="wizard-step" id="step-indicator-2">
            <span class="wizard-step-num">2</span>
            <span>Optional Add-Ons</span>
        </div>
        <div class="wizard-step" id="step-indicator-3">
            <span class="wizard-step-num">3</span>
            <span>Price Review</span>
        </div>
        <div class="wizard-step" id="step-indicator-4">
            <span class="wizard-step-num">4</span>
            <span>Confirmation</span>
        </div>
    </div>

    <form action="<?= apex_url('book.php?vehicle_id=' . $vehicleId) ?>" method="POST" id="bookingWizardForm">
        <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
        <input type="hidden" name="vehicle_id" value="<?= $vehicleId ?>">

        <div style="display:grid; grid-template-columns: 1fr 380px; gap:28px;">
            <!-- Left: Wizard Step Panels -->
            <div>
                <!-- STEP 1: Dates & Location -->
                <div class="card" id="step-panel-1" style="margin-bottom:24px;">
                    <div class="card-header">
                        <span class="card-title">Step 1: Rental Schedule & Philippine Hub Locations</span>
                    </div>
                    <div class="card-body">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                            <div class="form-group">
                                <label class="form-label" for="pickup_datetime">Pickup Date & Time</label>
                                <input type="datetime-local" name="pickup_datetime" id="pickup_datetime" class="form-control" value="<?= h($pickupDatetime) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="return_datetime">Return Date & Time</label>
                                <input type="datetime-local" name="return_datetime" id="return_datetime" class="form-control" value="<?= h($returnDatetime) ?>" required>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                            <div class="form-group">
                                <label class="form-label" for="pickup_location">Pickup Hub</label>
                                <select name="pickup_location" id="pickup_location" class="form-control" required>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= h($loc) ?>" <?= $pickupLocation === $loc ? 'selected' : '' ?>><?= h($loc) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="return_location">Return Hub</label>
                                <select name="return_location" id="return_location" class="form-control" required>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= h($loc) ?>" <?= $returnLocation === $loc ? 'selected' : '' ?>><?= h($loc) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Optional Add-Ons -->
                <div class="card" id="step-panel-2" style="margin-bottom:24px;">
                    <div class="card-header">
                        <span class="card-title">Step 2: Custom Rental Add-Ons</span>
                    </div>
                    <div class="card-body">
                        <p style="font-size:13px; color:var(--apex-text-muted); margin-bottom:16px;">
                            Enhance your rental with optional extras. Charges are calculated per day or as a one-time fixed fee.
                        </p>

                        <?php foreach ($addons as $addon): 
                            $isSel = in_array((int)$addon['id'], $selectedAddonIds, true);
                        ?>
                            <label class="addon-selection-card <?= $isSel ? 'selected' : '' ?>">
                                <div class="addon-left">
                                    <input type="checkbox" name="addon_ids[]" value="<?= (int)$addon['id'] ?>" <?= $isSel ? 'checked' : '' ?> style="width:18px; height:18px; accent-color:var(--apex-primary);">
                                    <div class="addon-details">
                                        <h4><?= h($addon['name']) ?></h4>
                                        <p><?= h($addon['description']) ?></p>
                                    </div>
                                </div>
                                <div class="addon-rate">
                                    <?= format_pesos($addon['daily_rate']) ?>
                                    <span style="font-size:11px; font-weight:normal; color:var(--apex-text-muted); display:block;">
                                        <?= $addon['price_type'] === 'daily' ? 'per day' : 'one-time fee' ?>
                                    </span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- STEP 3 & 4: Driver License & Confirmation -->
                <div class="card" id="step-panel-3">
                    <div class="card-header">
                        <span class="card-title">Step 3 & 4: Driver Information & Confirmation</span>
                    </div>
                    <div class="card-body">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="<?= h($currentUser['name']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?= h($currentUser['email']) ?>" disabled>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-bottom:16px;">
                            <div class="form-group">
                                <label class="form-label" for="license_number">LTO Driver's License Number *</label>
                                <input type="text" name="license_number" id="license_number" class="form-control" placeholder="e.g. N01-12-123456" value="<?= h($licenseNumber) ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="phone">Contact Mobile Number</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="<?= h($currentUser['phone'] ?? '+63 ') ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="customer_notes">Special Requests or Trip Destination Notes (Optional)</label>
                            <textarea name="customer_notes" id="customer_notes" class="form-control" rows="2" placeholder="e.g. Flight arrival details, out-of-town route..."><?= h($notes) ?></textarea>
                        </div>

                        <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--apex-border-subtle);">
                            <label style="display:flex; align-items:flex-start; gap:10px; font-size:13px; color:var(--apex-text-secondary); cursor:pointer;">
                                <input type="checkbox" required style="margin-top:3px; accent-color:var(--apex-primary);">
                                <span>I agree to the APEX Car Rental terms, confirm that my driver's license is active, and acknowledge that vehicle tracking during the rental is a simulated software demonstration.</span>
                            </label>
                        </div>

                        <button type="submit" name="submit_booking" value="1" class="btn btn-primary btn-lg" style="width:100%; margin-top:24px;">
                            Confirm & Submit Reservation Request &rarr;
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: Vehicle Card & Live Price Breakdown Sidebar -->
            <aside>
                <div class="card" style="position:sticky; top:90px;">
                    <div class="vehicle-thumb-wrap" style="height:190px;">
                        <img src="<?= h($vehicle['image_url']) ?>" alt="<?= h($vehicle['brand'] . ' ' . $vehicle['model']) ?>" class="vehicle-thumb">
                        <span class="vehicle-category-badge"><?= h($vehicle['category_name']) ?></span>
                        <?php if (!empty($vehicle['image_credit'])): ?>
                            <span class="vehicle-credit-tag"><?= h($vehicle['image_credit']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="card-body">
                        <h3 style="font-size:18px; font-weight:800; color:var(--apex-text-main); margin-bottom:4px;">
                            <?= h($vehicle['brand'] . ' ' . $vehicle['model']) ?>
                        </h3>
                        <div style="font-size:12px; color:var(--apex-text-muted); font-family:monospace; margin-bottom:16px;">
                            Plate: <?= h($vehicle['license_plate']) ?> &bull; <?= h($vehicle['transmission']) ?> &bull; <?= (int)$vehicle['seats'] ?> Seats
                        </div>

                        <!-- Live Price Summary -->
                        <div class="price-summary-box">
                            <h4 style="font-size:14px; font-weight:700; color:var(--apex-text-main); margin-bottom:12px;">Estimated Rental Breakdown</h4>
                            
                            <div class="summary-row">
                                <span>Duration:</span>
                                <strong><?= (int)$pricing['total_days'] ?> Days</strong>
                            </div>
                            <div class="summary-row">
                                <span>Daily Vehicle Rate:</span>
                                <strong><?= format_pesos($pricing['daily_rate']) ?> / day</strong>
                            </div>
                            <div class="summary-row">
                                <span>Base Rental Subtotal:</span>
                                <strong><?= format_pesos($pricing['base_amount']) ?></strong>
                            </div>

                            <?php if (!empty($pricing['addons_amount'])): ?>
                                <div class="summary-row">
                                    <span>Selected Add-Ons:</span>
                                    <strong>+ <?= format_pesos($pricing['addons_amount']) ?></strong>
                                </div>
                            <?php endif; ?>

                            <div class="summary-row" style="color:var(--apex-text-muted); font-size:12px;">
                                <span>Refundable Security Deposit:</span>
                                <span><?= format_pesos($pricing['deposit_amount']) ?></span>
                            </div>

                            <div class="summary-row total">
                                <span>Total Estimated:</span>
                                <span><?= format_pesos($pricing['total_amount']) ?></span>
                            </div>
                            <div style="font-size:11px; color:var(--apex-text-muted); margin-top:4px; text-align:right;">
                                Deposit settled at pickup
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
