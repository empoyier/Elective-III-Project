<?php
/**
 * APEX Car Rental Management System
 * Logout Handler
 */

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/src/Auth.php';

Auth::logout();
header("Location: " . apex_url('login.php?message=logged_out'));
exit;
