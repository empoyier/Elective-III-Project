<?php
/**
 * APEX Car Rental Management System
 * Admin Fleet Maintenance Management
 */

$pageTitle = "Fleet Maintenance Log";
$activeAdminNav = "maintenance";

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Maintenance.php';
require_once __DIR__ . '/../src/Vehicle.php';

$message = '';
$messageType = 'info';

// Handle Schedule Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_schedule'])) {
    $vehicleId = (int)$_POST['vehicle_id'];
    $serviceType = trim($_POST['service_type']);
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $cost = (float)($_POST['cost'] ?? 0.0);
    $notes = trim($_POST['notes'] ?? '');

    $res = Maintenance::schedule($vehicleId, $serviceType, $startDate, $endDate, $cost, $notes);
    $message = $res['message'];
    $messageType = $res['success'] ? 'success' : 'danger';
}

// Handle Mark Completed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_complete'])) {
    $maintId = (int)$_POST['maintenance_id'];
    if (Maintenance::complete($maintId)) {
        $message = "Maintenance service marked completed and vehicle availability restored.";
        $messageType = 'success';
    }
}

$records = Maintenance::getAll();
$vehicles = Vehicle::getAll(['admin_view' => true]);

require_once __DIR__ . '/nav.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= h($message) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span class="card-title">Scheduled & Historical Vehicle Maintenance</span>
        <button type="button" class="btn btn-primary btn-sm" onclick="openScheduleModal()">
            + Schedule Maintenance Service
        </button>
    </div>

    <div class="card-body" style="padding:0;">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Vehicle & Plate</th>
                        <th>Service Type</th>
                        <th>Service Window</th>
                        <th>Estimated Cost</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:32px; color:var(--apex-text-muted);">No maintenance logs on record.</td></tr>
                    <?php else: ?>
                        <?php foreach ($records as $m): ?>
                            <tr>
                                <td>
                                    <strong><?= h($m['brand'] . ' ' . $m['model']) ?></strong>
                                    <div style="font-size:11px; color:var(--apex-text-muted); font-family:monospace;"><?= h($m['license_plate']) ?></div>
                                </td>
                                <td><strong><?= h($m['service_type']) ?></strong></td>
                                <td style="font-size:12px;">
                                    <?= date('M d, Y', strtotime($m['start_date'])) ?> &rarr; <?= date('M d, Y', strtotime($m['end_date'])) ?>
                                </td>
                                <td><?= format_pesos($m['cost']) ?></td>
                                <td>
                                    <span class="badge badge-<?= h($m['status']) ?>">
                                        <?= strtoupper(h($m['status'])) ?>
                                    </span>
                                </td>
                                <td style="font-size:12px; color:var(--apex-text-muted);"><?= h($m['notes'] ?: '—') ?></td>
                                <td style="text-align:right;">
                                    <?php if ($m['status'] !== 'completed'): ?>
                                        <form action="<?= apex_url('admin/maintenance.php') ?>" method="POST" style="display:inline;">
                                            <input type="hidden" name="action_complete" value="1">
                                            <input type="hidden" name="maintenance_id" value="<?= (int)$m['id'] ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">Mark Completed</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="font-size:12px; color:var(--apex-success);">✓ Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Schedule Maintenance -->
<div id="scheduleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div class="card" style="width:520px; max-width:95%; margin:auto;">
        <div class="card-header">
            <span class="card-title">Schedule Vehicle Service</span>
            <button type="button" onclick="closeScheduleModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form action="<?= apex_url('admin/maintenance.php') ?>" method="POST">
                <input type="hidden" name="action_schedule" value="1">

                <div class="form-group">
                    <label class="form-label" for="m_veh">Select Vehicle *</label>
                    <select name="vehicle_id" id="m_veh" class="form-control" required>
                        <?php foreach ($vehicles as $v): ?>
                            <option value="<?= (int)$v['id'] ?>"><?= h($v['brand'] . ' ' . $v['model']) ?> (<?= h($v['license_plate']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="m_type">Service / Maintenance Description *</label>
                    <input type="text" name="service_type" id="m_type" class="form-control" required placeholder="e.g. 10,000 km PMS, Brake Pad Replacement, AC Cleaning">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:12px;">
                    <div class="form-group">
                        <label class="form-label" for="m_start">Start Date *</label>
                        <input type="date" name="start_date" id="m_start" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="m_end">End Date *</label>
                        <input type="date" name="end_date" id="m_end" class="form-control" value="<?= date('Y-m-d', strtotime('+3 days')) ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="m_cost">Estimated Cost (PHP ₱)</label>
                    <input type="number" step="0.01" name="cost" id="m_cost" class="form-control" value="0.00">
                </div>

                <div class="form-group">
                    <label class="form-label" for="m_notes">Additional Notes</label>
                    <textarea name="notes" id="m_notes" class="form-control" rows="2" placeholder="Service center details, parts ordered..."></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:20px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="closeScheduleModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save Service Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'flex';
}
function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
