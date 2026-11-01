<?php
/**
 * APEX Car Rental Management System
 * Customer Registration Page
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Auth.php';

$error = '';

if (Auth::isLoggedIn()) {
    header("Location: " . apex_url('my-bookings.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $license = trim($_POST['license_number'] ?? '');

    $result = Auth::register($name, $email, $password, $phone, $license);
    if ($result['success']) {
        header("Location: " . apex_url('my-bookings.php'));
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = "Register Customer Account | APEX";
require_once __DIR__ . '/templates/header.php';
?>

<div class="container" style="max-width:540px; padding:50px 20px 100px;">
    <div class="card" style="border-top:4px solid var(--apex-primary);">
        <div class="card-header">
            <h1 class="card-title" style="font-size:18px;">Create an APEX Account</h1>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>

            <form action="<?= apex_url('register.php') ?>" method="POST">
                <div class="form-group">
                    <label class="form-label" for="reg_name">Full Legal Name *</label>
                    <input type="text" name="name" id="reg_name" class="form-control" required placeholder="e.g. Juan Dela Cruz">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg_email">Email Address *</label>
                    <input type="email" name="email" id="reg_email" class="form-control" required placeholder="juan@domain.ph">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg_password">Password (Minimum 6 characters) *</label>
                    <input type="password" name="password" id="reg_password" class="form-control" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg_phone">Mobile Phone *</label>
                    <input type="text" name="phone" id="reg_phone" class="form-control" required placeholder="+63 917 123 4567">
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg_license">Philippine LTO Driver's License Number</label>
                    <input type="text" name="license_number" id="reg_license" class="form-control" placeholder="e.g. N01-12-123456">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; margin-top:8px;">
                    Complete Registration &rarr;
                </button>
            </form>

            <div style="margin-top:24px; padding-top:16px; border-top:1px solid var(--apex-border-subtle); text-align:center; font-size:13px;">
                Already have an account? <a href="<?= apex_url('login.php') ?>" style="font-weight:700;">Sign In</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
