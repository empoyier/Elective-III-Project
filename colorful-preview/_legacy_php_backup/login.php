<?php
/**
 * APEX Car Rental Management System
 * Unified Login Page with Quick Demo Credentials
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Auth.php';

$error = '';
$redirect = $_GET['redirect'] ?? '';

if (Auth::isLoggedIn()) {
    header("Location: " . (Auth::isStaff() ? apex_url('admin/index.php') : apex_url('my-bookings.php')));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $result = Auth::login($email, $password);
    if ($result['success']) {
        if (!empty($redirect) && !str_contains($redirect, 'login.php')) {
            header("Location: " . $redirect);
        } else if (Auth::isStaff()) {
            header("Location: " . apex_url('admin/index.php'));
        } else {
            header("Location: " . apex_url('my-bookings.php'));
        }
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = "Sign In | APEX Car Rental Services";
require_once __DIR__ . '/templates/header.php';
?>

<div class="container" style="max-width:480px; padding:60px 20px 100px;">
    <div class="card" style="border-top:4px solid var(--apex-primary);">
        <div class="card-header">
            <h1 class="card-title" style="font-size:18px;">Sign In to APEX Services</h1>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>

            <form action="<?= apex_url('login.php' . ($redirect ? '?redirect=' . urlencode($redirect) : '')) ?>" method="POST">
                <div class="form-group">
                    <label class="form-label" for="login_email">Email Address</label>
                    <input type="email" name="email" id="login_email" class="form-control" required placeholder="name@domain.ph">
                </div>

                <div class="form-group">
                    <label class="form-label" for="login_password">Password</label>
                    <input type="password" name="password" id="login_password" class="form-control" required placeholder="Enter your password">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; margin-top:8px;">
                    Sign In &rarr;
                </button>
            </form>

            <div style="margin-top:24px; padding-top:16px; border-top:1px solid var(--apex-border-subtle); text-align:center; font-size:13px;">
                Don't have an account yet? <a href="<?= apex_url('register.php') ?>" style="font-weight:700;">Create Customer Account</a>
            </div>

            <!-- One-Click Demo Credentials Quick Fill -->
            <div style="margin-top:24px; background:#F8FAFC; border:1px solid var(--apex-border-crisp); padding:16px; border-radius:4px;">
                <h4 style="font-size:12px; font-weight:700; color:var(--apex-text-muted); text-transform:uppercase; margin-bottom:10px;">One-Click Demo Accounts</h4>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('admin@apexrentals.ph', 'admin123')">Administrator (admin@apexrentals.ph)</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('staff@apexrentals.ph', 'staff123')">Fleet Staff (staff@apexrentals.ph)</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="fillCredentials('customer@example.ph', 'customer123')">Customer Juan (customer@example.ph)</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillCredentials(email, pass) {
    document.getElementById('login_email').value = email;
    document.getElementById('login_password').value = pass;
}
</script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
