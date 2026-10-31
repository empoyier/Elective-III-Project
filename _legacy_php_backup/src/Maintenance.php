<?php
/**
 * APEX Car Rental Management System
 * Fleet Maintenance Management & Operational Restriction Controls
 */

require_once __DIR__ . '/Database.php';

class Maintenance {
    /**
     * Get all maintenance records with vehicle details
     */
    public static function getAll(): array {
        $sql = "SELECT m.*, v.brand, v.model, v.license_plate, v.image_url, c.name AS category_name 
                FROM `maintenance_records` m 
                JOIN `vehicles` v ON m.vehicle_id = v.id 
                JOIN `vehicle_categories` c ON v.category_id = c.id 
                ORDER BY m.start_date DESC";
        return Database::fetchAll($sql);
    }

    /**
     * Schedule a maintenance window for a vehicle
     */
    public static function schedule(int $vehicleId, string $serviceType, string $startDate, string $endDate, float $cost = 0.0, string $notes = ''): array {
        if (strtotime($startDate) > strtotime($endDate)) {
            return ['success' => false, 'message' => 'End date cannot be earlier than start date.'];
        }

        // Check if there are overlapping approved or active bookings
        $bookingConflict = Database::fetchOne(
            "SELECT `booking_ref`, `pickup_datetime`, `return_datetime` 
             FROM `bookings` 
             WHERE `vehicle_id` = ? 
             AND `status` IN ('approved', 'out_on_rental') 
             AND (`pickup_datetime` <= ? AND `return_datetime` >= ?)",
            [$vehicleId, $endDate . ' 23:59:59', $startDate . ' 00:00:00']
        );

        if ($bookingConflict) {
            return [
                'success' => false,
                'message' => "Cannot schedule maintenance: Conflicting booking {$bookingConflict['booking_ref']} exists from " . 
                             date('M d, Y', strtotime($bookingConflict['pickup_datetime'])) . " to " . 
                             date('M d, Y', strtotime($bookingConflict['return_datetime'])) . "."
            ];
        }

        Database::beginTransaction();
        try {
            Database::query(
                "INSERT INTO `maintenance_records` (`vehicle_id`, `service_type`, `start_date`, `end_date`, `cost`, `status`, `notes`) VALUES (?, ?, ?, ?, ?, 'scheduled', ?)",
                [$vehicleId, trim($serviceType), $startDate, $endDate, $cost, trim($notes)]
            );

            // If maintenance starts today or earlier, set vehicle operational status to 'maintenance'
            if (strtotime($startDate) <= time() && strtotime($endDate) >= time()) {
                Database::query("UPDATE `vehicles` SET `operational_status` = 'maintenance' WHERE `id` = ?", [$vehicleId]);
            }

            Database::commit();
            return ['success' => true, 'message' => 'Maintenance successfully scheduled.'];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Failed to schedule maintenance: ' . $e->getMessage()];
        }
    }

    /**
     * Mark maintenance record as completed and restore vehicle availability if no other active maintenance
     */
    public static function complete(int $maintenanceId): bool {
        $record = Database::fetchOne("SELECT * FROM `maintenance_records` WHERE `id` = ?", [$maintenanceId]);
        if (!$record) {
            return false;
        }

        Database::beginTransaction();
        try {
            Database::query("UPDATE `maintenance_records` SET `status` = 'completed' WHERE `id` = ?", [$maintenanceId]);

            // Check if vehicle has any other active or scheduled maintenance
            $otherMaint = Database::fetchOne(
                "SELECT `id` FROM `maintenance_records` WHERE `vehicle_id` = ? AND `status` IN ('scheduled', 'in_progress') AND `id` != ?",
                [$record['vehicle_id'], $maintenanceId]
            );

            if (!$otherMaint) {
                Database::query("UPDATE `vehicles` SET `operational_status` = 'available' WHERE `id` = ?", [$record['vehicle_id']]);
            }

            Database::commit();
            return true;
        } catch (Exception $e) {
            Database::rollBack();
            return false;
        }
    }
}
