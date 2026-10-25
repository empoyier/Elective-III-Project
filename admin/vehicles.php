<?php
/**
 * APEX Car Rental Management System
 * Admin Fleet Inventory Management
 */

$pageTitle = "Fleet Inventory Management";
$activeAdminNav = "vehicles";

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Vehicle.php';
 
$message = '';
$messageType = 'info';

// Handle Add Vehicle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_add_vehicle'])) {
    $data = [
        'category_id'        => (int)$_POST['category_id'],
        'brand'              => trim($_POST['brand']),
        'model'              => trim($_POST['model']),
        'year'               => (int)$_POST['year'],
        'license_plate'      => strtoupper(trim($_POST['license_plate'])),
        'transmission'       => $_POST['transmission'] ?? 'Automatic',
        'seats'              => (int)($_POST['seats'] ?? 5),
        'fuel_type'          => $_POST['fuel_type'] ?? 'Gasoline',
        'mileage'            => (int)($_POST['mileage'] ?? 0),
        'daily_rate'         => (float)$_POST['daily_rate'],
        'image_url'          => trim($_POST['image_url']),
        'image_credit'       => trim($_POST['image_credit'] ?? ''),
        'operational_status' => $_POST['operational_status'] ?? 'available',
        'description'        => trim($_POST['description'] ?? ''),
        'features'           => array_map('trim', explode(',', $_POST['features_csv'] ?? ''))
    ];

    try {
        $newId = Vehicle::create($data);
        $message = "Vehicle {$data['brand']} {$data['model']} ({$data['license_plate']}) created successfully!";
        $messageType = 'success';
    } catch (Exception $e) {
        $message = "Error creating vehicle: " . $e->getMessage();
        $messageType = 'danger';
    }
}

// Handle Status Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_set_status'])) {
    $vId = (int)$_POST['vehicle_id'];
    $newStat = $_POST['new_status'];
    if (Vehicle::setStatus($vId, $newStat)) {
        $message = "Vehicle operational status updated to " . strtoupper($newStat) . ".";
        $messageType = 'success';
    }
}

$categories = Vehicle::getCategories();
$vehicles = Vehicle::getAll(['admin_view' => true]);

require_once __DIR__ . '/nav.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= h($message) ?></div>
<?php endif; ?>

<div class="card glass-card">
    <div class="card-header">
        <span class="card-title">Fleet Inventory (<?= count($vehicles) ?> Vehicles)</span>
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
            + Add New Vehicle
        </button>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Vehicle & Plate</th>
                        <th>Category</th>
                        <th>Specs</th>
                        <th>Daily Rate (PHP)</th>
                        <th>Operational Status</th>
                        <th>Photo Attribution</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $v): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <img src="<?= h($v['image_url']) ?>" alt="Thumb" style="width:58px; height:38px; object-fit:cover; border-radius:2px; border:1px solid #CBD5E1;">
                                    <div>
                                        <strong><?= h($v['brand'] . ' ' . $v['model']) ?></strong>
                                        <div style="font-size:11px; color:var(--apex-text-muted); font-family:monospace;"><?= h($v['license_plate']) ?> &bull; <?= h($v['year']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><?= h($v['category_name']) ?></td>
                            <td style="font-size:12px; color:var(--apex-text-muted);">
                                <?= h($v['transmission']) ?> &bull; <?= (int)$v['seats'] ?> seats &bull; <?= h($v['fuel_type']) ?>
                            </td>
                            <td><strong style="color:var(--apex-primary);"><?= format_pesos($v['daily_rate']) ?></strong></td>
                            <td>
                                <span class="badge badge-<?= h($v['operational_status']) ?>">
                                    <?= strtoupper(h($v['operational_status'])) ?>
                                </span>
                            </td>
                            <td style="font-size:11px; color:var(--apex-text-muted);">
                                <?= h($v['image_credit'] ?: 'Staff Upload') ?>
                            </td>
                            <td style="text-align:right;">
                                <form action="<?= apex_url('admin/vehicles.php') ?>" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action_set_status" value="1">
                                    <input type="hidden" name="vehicle_id" value="<?= (int)$v['id'] ?>">
                                    
                                    <select name="new_status" onchange="this.form.submit()" style="font-size:12px; padding:4px 6px; border:1px solid #CBD5E1; border-radius:2px;">
                                        <option value="available" <?= $v['operational_status'] === 'available' ? 'selected' : '' ?>>Set Available</option>
                                        <option value="maintenance" <?= $v['operational_status'] === 'maintenance' ? 'selected' : '' ?>>Set Maintenance</option>
                                        <option value="deactivated" <?= $v['operational_status'] === 'deactivated' ? 'selected' : '' ?>>Set Deactivated</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add New Vehicle -->
<div id="addVehicleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; overflow-y:auto; padding:20px;">
    <div class="card" style="width:680px; max-width:95%; margin:auto;">
        <div class="card-header">
            <span class="card-title">Add Vehicle to Fleet Inventory</span>
            <button type="button" onclick="closeAddModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= apex_url('admin/vehicles.php') ?>" method="POST">
                <input type="hidden" name="action_add_vehicle" value="1">

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:12px;">
                    <div class="form-group">
                        <label class="form-label">Brand / Make *</label>
                        <input type="text" name="brand" class="form-control" required placeholder="e.g. Toyota">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model Name & Trim *</label>
                        <input type="text" name="model" class="form-control" required placeholder="e.g. Fortuner 2.8 4x4 LTD">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:14px; margin-bottom:12px;">
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= h($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model Year *</label>
                        <input type="number" name="year" class="form-control" value="2024" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">License Plate *</label>
                        <input type="text" name="license_plate" class="form-control" required placeholder="e.g. NBF-1234">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:14px; margin-bottom:12px;">
                    <div class="form-group">
                        <label class="form-label">Transmission</label>
                        <select name="transmission" class="form-control">
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Seats</label>
                        <input type="number" name="seats" class="form-control" value="5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fuel Type</label>
                        <select name="fuel_type" class="form-control">
                            <option value="Gasoline">Gasoline</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Hybrid">Hybrid</option>
                            <option value="Electric">Electric</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:12px;">
                    <div class="form-group">
                        <label class="form-label">Daily Rental Rate (PHP ₱) *</label>
                        <input type="number" step="0.01" name="daily_rate" class="form-control" required placeholder="e.g. 3500.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Initial Odometer (km)</label>
                        <input type="number" name="mileage" class="form-control" value="5000">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Vehicle Image Path *</label>
                    <input type="text" name="image_url" class="form-control" required placeholder="images/toyota_fortuner.jpg">
                </div>

                <div class="form-group">
                    <label class="form-label">Photo Attribution Credit (Photographer)</label>
                    <input type="text" name="image_credit" class="form-control" placeholder="Photo by [Name] on Unsplash">
                </div>

                <div class="form-group">
                    <label class="form-label">Key Features (Comma-Separated)</label>
                    <input type="text" name="features_csv" class="form-control" placeholder="Apple CarPlay, Reverse Camera, Leather Seats, Cruise Control">
                </div>

                <div class="form-group">
                    <label class="form-label">Vehicle Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Brief summary of vehicle condition and performance..."></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Add to Inventory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addVehicleModal').style.display = 'flex';
}
function closeAddModal() {
    document.getElementById('addVehicleModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
