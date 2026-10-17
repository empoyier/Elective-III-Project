<?php
/**
 * APEX Car Rental Management System
 * Booking Lifecycle, Concurrency Locking, Pricing Engine & Workflow
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Vehicle.php';

class Booking {
    /**
     * Get all active add-ons
     */
    public static function getAddons(): array {
        return Database::fetchAll("SELECT * FROM `addons` WHERE `is_active` = 1 ORDER BY `id` ASC");
    }

    /**
     * Calculate itemized pricing breakdown in Philippine Pesos (PHP)
     */
    public static function calculatePricing(int $vehicleId, string $pickupDatetime, string $returnDatetime, array $addonIds = []): array {
        $vehicle = Vehicle::getById($vehicleId);
        if (!$vehicle) {
            throw new InvalidArgumentException("Vehicle not found.");
        }

        $pickupTs = strtotime($pickupDatetime);
        $returnTs = strtotime($returnDatetime);
        if ($pickupTs >= $returnTs) {
            throw new InvalidArgumentException("Return date must be strictly after pickup date.");
        }

        // Calculate rental days (minimum 1 day)
        $diffSeconds = $returnTs - $pickupTs;
        $totalDays = max(1, (int)ceil($diffSeconds / 86400));

        $dailyRate = (float)$vehicle['daily_rate'];
        $baseAmount = $totalDays * $dailyRate;

        // Process add-ons
        $addonsBreakdown = [];
        $addonsAmount = 0.0;

        if (!empty($addonIds)) {
            $inClause = implode(',', array_fill(0, count($addonIds), '?'));
            $addons = Database::fetchAll("SELECT * FROM `addons` WHERE `id` IN ({$inClause}) AND `is_active` = 1", $addonIds);

            foreach ($addons as $addon) {
                $unitPrice = (float)$addon['daily_rate'];
                $subtotal = ($addon['price_type'] === 'daily') ? ($unitPrice * $totalDays) : $unitPrice;
                $addonsAmount += $subtotal;

                $addonsBreakdown[] = [
                    'id'          => $addon['id'],
                    'name'        => $addon['name'],
                    'price_type'  => $addon['price_type'],
                    'unit_price'  => $unitPrice,
                    'quantity'    => ($addon['price_type'] === 'daily' ? $totalDays : 1),
                    'subtotal'    => $subtotal
                ];
            }
        }

        // Refundable security deposit based on category
        $depositAmount = match($vehicle['category_slug']) {
            'economy'        => 3000.00,
            'luxury', 'sports' => 10000.00,
            default          => 5000.00
        };

        $totalAmount = $baseAmount + $addonsAmount;

        return [
            'vehicle_id'      => $vehicleId,
            'vehicle_model'   => $vehicle['brand'] . ' ' . $vehicle['model'],
            'total_days'      => $totalDays,
            'daily_rate'      => $dailyRate,
            'base_amount'     => $baseAmount,
            'addons'          => $addonsBreakdown,
            'addons_amount'   => $addonsAmount,
            'tax_amount'      => 0.00,
            'total_amount'    => $totalAmount,
            'deposit_amount'  => $depositAmount,
            'grand_total'     => $totalAmount + $depositAmount // Including refundable deposit
        ];
    }

    /**
     * Generate unique booking reference number (e.g. APX-2026-7B92)
     */
    public static function generateReference(): string {
        $year = date('Y');
        $randomHex = strtoupper(bin2hex(random_bytes(2)));
        return "APX-{$year}-{$randomHex}";
    }

    /**
     * Create reservation inside a concurrency-safe transaction with row-locking
     */
    public static function createBooking(
        int $userId,
        int $vehicleId,
        string $pickupDatetime,
        string $returnDatetime,
        string $pickupLocation,
        string $returnLocation,
        array $addonIds = [],
        string $customerNotes = ''
    ): array {
        // Verify user account status
        $user = Database::fetchOne("SELECT `status` FROM `users` WHERE `id` = ?", [$userId]);
        if (!$user || $user['status'] === 'blocked') {
            return ['success' => false, 'message' => 'Your account is suspended and cannot submit bookings.'];
        }

        // Calculate pricing
        $pricing = self::calculatePricing($vehicleId, $pickupDatetime, $returnDatetime, $addonIds);

        Database::beginTransaction();
        try {
            // Lock vehicle row for transaction to prevent concurrent race condition
            $lockSql = "SELECT `id`, `operational_status` FROM `vehicles` WHERE `id` = ? FOR UPDATE";
            $vehLocked = Database::fetchOne($lockSql, [$vehicleId]);

            if (!$vehLocked || $vehLocked['operational_status'] !== 'available') {
                Database::rollBack();
                return ['success' => false, 'message' => 'Vehicle is currently unavailable for reservations.'];
            }

            // Re-validate temporal conflict within the locked transaction
            $conflictCheck = Vehicle::checkAvailability($vehicleId, $pickupDatetime, $returnDatetime);
            if (!$conflictCheck['available']) {
                Database::rollBack();
                return ['success' => false, 'message' => $conflictCheck['reason']];
            }

            $bookingRef = self::generateReference();

            // Insert booking
            $sqlBooking = "INSERT INTO `bookings` 
                (`booking_ref`, `user_id`, `vehicle_id`, `pickup_datetime`, `return_datetime`, 
                 `pickup_location`, `return_location`, `total_days`, `daily_rate`, `base_amount`, 
                 `addons_amount`, `tax_amount`, `total_amount`, `deposit_amount`, `status`, `customer_notes`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)";

            Database::query($sqlBooking, [
                $bookingRef,
                $userId,
                $vehicleId,
                $pickupDatetime,
                $returnDatetime,
                $pickupLocation,
                $returnLocation,
                $pricing['total_days'],
                $pricing['daily_rate'],
                $pricing['base_amount'],
                $pricing['addons_amount'],
                $pricing['tax_amount'],
                $pricing['total_amount'],
                $pricing['deposit_amount'],
                trim($customerNotes)
            ]);

            $bookingId = (int)Database::lastInsertId();

            // Insert selected add-ons
            if (!empty($pricing['addons'])) {
                $sqlAddon = "INSERT INTO `booking_addons` (`booking_id`, `addon_id`, `unit_price`, `quantity`, `subtotal`) VALUES (?, ?, ?, ?, ?)";
                foreach ($pricing['addons'] as $addon) {
                    Database::query($sqlAddon, [
                        $bookingId,
                        $addon['id'],
                        $addon['unit_price'],
                        $addon['quantity'],
                        $addon['subtotal']
                    ]);
                }
            }

            // Log status creation
            Database::query(
                "INSERT INTO `booking_status_logs` (`booking_id`, `changed_by_user_id`, `previous_status`, `new_status`, `notes`) VALUES (?, ?, NULL, 'pending', 'Reservation submitted by customer.')",
                [$bookingId, $userId]
            );

            Database::commit();

            return [
                'success'      => true,
                'booking_id'   => $bookingId,
                'booking_ref'  => $bookingRef,
                'total_amount' => $pricing['total_amount']
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Booking creation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get booking by reference code
     */
    public static function getByRef(string $ref): ?array {
        $sql = "SELECT b.*, v.brand, v.model, v.year, v.license_plate, v.image_url, v.image_credit,
                       c.name AS category_name, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                       u.license_number AS user_license
                FROM `bookings` b
                JOIN `vehicles` v ON b.vehicle_id = v.id
                JOIN `vehicle_categories` c ON v.category_id = c.id
                JOIN `users` u ON b.user_id = u.id
                WHERE b.booking_ref = ? LIMIT 1";
        $booking = Database::fetchOne($sql, [trim($ref)]);
        if ($booking) {
            $booking['addons'] = Database::fetchAll(
                "SELECT ba.*, a.name, a.description, a.price_type 
                 FROM `booking_addons` ba 
                 JOIN `addons` a ON ba.addon_id = a.id 
                 WHERE ba.booking_id = ?",
                [$booking['id']]
            );
        }
        return $booking;
    }

    /**
     * Get booking by ID
     */
    public static function getById(int $id): ?array {
        $sql = "SELECT b.*, v.brand, v.model, v.year, v.license_plate, v.image_url, v.image_credit,
                       c.name AS category_name, u.name AS user_name, u.email AS user_email, u.phone AS user_phone,
                       u.license_number AS user_license
                FROM `bookings` b
                JOIN `vehicles` v ON b.vehicle_id = v.id
                JOIN `vehicle_categories` c ON v.category_id = c.id
                JOIN `users` u ON b.user_id = u.id
                WHERE b.id = ? LIMIT 1";
        $booking = Database::fetchOne($sql, [$id]);
        if ($booking) {
            $booking['addons'] = Database::fetchAll(
                "SELECT ba.*, a.name, a.description, a.price_type 
                 FROM `booking_addons` ba 
                 JOIN `addons` a ON ba.addon_id = a.id 
                 WHERE ba.booking_id = ?",
                [$booking['id']]
            );
        }
        return $booking;
    }

    /**
     * Get all bookings for a specific customer
     */
    public static function getUserBookings(int $userId): array {
        $sql = "SELECT b.*, v.brand, v.model, v.year, v.image_url, v.license_plate, c.name AS category_name,
                       ts.id AS tracking_session_id, ts.sim_status
                FROM `bookings` b
                JOIN `vehicles` v ON b.vehicle_id = v.id
                JOIN `vehicle_categories` c ON v.category_id = c.id
                LEFT JOIN `rental_tracking_sessions` ts ON ts.booking_id = b.id
                WHERE b.user_id = ?
                ORDER BY b.created_at DESC";
        return Database::fetchAll($sql, [$userId]);
    }

    /**
     * Admin: Get all bookings with filtering
     */
    public static function getAllBookings(array $filters = []): array {
        $sql = "SELECT b.*, v.brand, v.model, v.license_plate, c.name AS category_name,
                       u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
                FROM `bookings` b
                JOIN `vehicles` v ON b.vehicle_id = v.id
                JOIN `vehicle_categories` c ON v.category_id = c.id
                JOIN `users` u ON b.user_id = u.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $sql .= " AND (b.booking_ref LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR v.brand LIKE ? OR v.model LIKE ?)";
            $params = array_merge($params, [$term, $term, $term, $term, $term]);
        }

        $sql .= " ORDER BY b.created_at DESC";
        return Database::fetchAll($sql, $params);
    }

    /**
     * Update booking status with strict state machine verification & tracking session activation
     */
    public static function updateStatus(int $bookingId, string $newStatus, int $userId, string $notes = '', array $additional = []): array {
        $booking = Database::fetchOne("SELECT * FROM `bookings` WHERE `id` = ? LIMIT 1", [$bookingId]);
        if (!$booking) {
            return ['success' => false, 'message' => 'Booking not found.'];
        }

        $curr = $booking['status'];
        $valid = false;

        // State Machine Rules
        switch ($curr) {
            case 'pending':
                $valid = in_array($newStatus, ['approved', 'rejected', 'cancelled'], true);
                break;
            case 'approved':
                $valid = in_array($newStatus, ['out_on_rental', 'cancelled'], true);
                break;
            case 'out_on_rental':
                $valid = ($newStatus === 'returned');
                break;
            default:
                $valid = false;
        }

        if (!$valid) {
            return ['success' => false, 'message' => "Invalid status change from '{$curr}' to '{$newStatus}'."];
        }

        Database::beginTransaction();
        try {
            // If approving, re-check conflict with other approved bookings
            if ($newStatus === 'approved') {
                $conflict = Vehicle::checkAvailability($booking['vehicle_id'], $booking['pickup_datetime'], $booking['return_datetime'], $bookingId);
                if (!$conflict['available']) {
                    Database::rollBack();
                    return ['success' => false, 'message' => "Cannot approve: " . $conflict['reason']];
                }
            }

            // Update booking status
            $updateSql = "UPDATE `bookings` SET `status` = ?, `updated_at` = NOW()";
            $params = [$newStatus];

            if ($newStatus === 'rejected') {
                $updateSql .= ", `rejection_reason` = ?";
                $params[] = $notes;
            } else if ($newStatus === 'cancelled') {
                $updateSql .= ", `cancellation_reason` = ?";
                $params[] = $notes;
            }
            $updateSql .= " WHERE `id` = ?";
            $params[] = $bookingId;

            Database::query($updateSql, $params);

            // Log status transition
            Database::query(
                "INSERT INTO `booking_status_logs` (`booking_id`, `changed_by_user_id`, `previous_status`, `new_status`, `notes`) VALUES (?, ?, ?, ?, ?)",
                [$bookingId, $userId, $curr, $newStatus, $notes]
            );

            // Lifecycle Hook 1: Moving to OUT ON RENTAL -> Initialize Simulated Tracking Session!
            if ($newStatus === 'out_on_rental') {
                // Select a route (e.g. Route 1 or Route 2)
                $route = Database::fetchOne("SELECT * FROM `tracking_routes` ORDER BY RAND() LIMIT 1");
                if ($route) {
                    $waypoints = json_decode($route['waypoints_json'], true) ?: [];
                    $firstWp = $waypoints[0] ?? [14.5208, 121.0152, 40];

                    Database::query(
                        "INSERT INTO `rental_tracking_sessions` 
                        (`booking_id`, `route_id`, `current_waypoint_index`, `current_lat`, `current_lng`, `current_speed`, `distance_traveled_km`, `fuel_level_pct`, `sim_status`) 
                        VALUES (?, ?, 0, ?, ?, ?, 0.0, 100.0, 'active')
                        ON DUPLICATE KEY UPDATE `sim_status` = 'active', `updated_at` = NOW()",
                        [$bookingId, $route['id'], $firstWp[0], $firstWp[1], $firstWp[2] ?? 45.0]
                    );
                }
            }

            // Lifecycle Hook 2: Moving to RETURNED -> End Tracking & Save Inspection
            if ($newStatus === 'returned') {
                // Terminate tracking session
                Database::query("UPDATE `rental_tracking_sessions` SET `sim_status` = 'completed', `updated_at` = NOW() WHERE `booking_id` = ?", [$bookingId]);

                // Record Inspection
                $odometer = (int)($additional['odometer_reading'] ?? 0);
                $fuelReturned = (int)($additional['fuel_level_returned'] ?? 100);
                $hasDamage = !empty($additional['has_damage']) ? 1 : 0;
                $damageNotes = trim($additional['damage_notes'] ?? '');
                $extraCharges = (float)($additional['additional_charges'] ?? 0.00);

                Database::query(
                    "INSERT INTO `return_inspections` 
                    (`booking_id`, `inspector_user_id`, `odometer_reading`, `fuel_level_returned`, `has_damage`, `damage_notes`, `additional_charges`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE `odometer_reading` = VALUES(`odometer_reading`), `fuel_level_returned` = VALUES(`fuel_level_returned`)",
                    [$bookingId, $userId, $odometer, $fuelReturned, $hasDamage, $damageNotes, $extraCharges]
                );

                // Update vehicle mileage
                if ($odometer > 0) {
                    Database::query("UPDATE `vehicles` SET `mileage` = ? WHERE `id` = ?", [$odometer, $booking['vehicle_id']]);
                }
            }

            Database::commit();
            return ['success' => true, 'message' => "Booking status updated to " . strtoupper($newStatus)];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => "Failed to update booking status: " . $e->getMessage()];
        }
    }
}
