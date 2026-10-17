<?php
/**
 * APEX Car Rental Management System
 * Simulated Telemetry JSON Feed Endpoint
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Tracking.php';
require_once __DIR__ . '/../src/Booking.php';

$bookingId = (int)($_GET['booking_id'] ?? 0);
if ($bookingId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    exit;
}

$booking = Booking::getById($bookingId);
if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Booking not found']);
    exit;
}

// Allow access if staff, or owner, or if this is the active public demonstration booking (Booking #1)
$isDemoBooking = ($bookingId === 1 && $booking['status'] === 'out_on_rental');

if (!Auth::isStaff() && !$isDemoBooking && (!Auth::isLoggedIn() || $booking['user_id'] != Auth::id())) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access to telemetry session']);
    exit;
}

// Advance simulation and fetch current telemetry
$telemetry = Tracking::advanceSimulation($bookingId);

echo json_encode([
    'success'   => true,
    'telemetry' => $telemetry
]);
