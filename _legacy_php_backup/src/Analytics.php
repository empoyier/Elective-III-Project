<?php
/**
 * APEX Car Rental Management System
 * Operational Analytics, Revenue Calculation & Fleet Metrics
 */

require_once __DIR__ . '/Database.php';

class Analytics {
    /**
     * Get primary dashboard KPI metrics
     */
    public static function getSummaryKpis(): array {
        $totalVehicles = (int)Database::fetchOne("SELECT COUNT(*) AS c FROM `vehicles` WHERE `operational_status` != 'deactivated'")['c'];
        $availableVehicles = (int)Database::fetchOne("SELECT COUNT(*) AS c FROM `vehicles` WHERE `operational_status` = 'available'")['c'];
        $inMaintenance = (int)Database::fetchOne("SELECT COUNT(*) AS c FROM `vehicles` WHERE `operational_status` = 'maintenance'")['c'];

        $pendingBookings = (int)Database::fetchOne("SELECT COUNT(*) AS c FROM `bookings` WHERE `status` = 'pending'")['c'];
        $activeRentals = (int)Database::fetchOne("SELECT COUNT(*) AS c FROM `bookings` WHERE `status` = 'out_on_rental'")['c'];
        $returnedRentals = (int)Database::fetchOne("SELECT COUNT(*) AS c FROM `bookings` WHERE `status` = 'returned'")['c'];

        // Recognized revenue from completed and active rentals
        $revRow = Database::fetchOne("SELECT COALESCE(SUM(`total_amount`), 0) AS total_rev FROM `bookings` WHERE `status` IN ('out_on_rental', 'returned')");
        $totalRevenue = (float)($revRow['total_rev'] ?? 0.0);

        // Fleet utilization: active rentals divided by total active fleet
        $utilizationRate = ($totalVehicles > 0) ? round(($activeRentals / $totalVehicles) * 100, 1) : 0.0;

        return [
            'total_vehicles'        => $totalVehicles,
            'available_vehicles'    => $availableVehicles,
            'maintenance_vehicles'  => $inMaintenance,
            'pending_bookings'      => $pendingBookings,
            'active_rentals'        => $activeRentals,
            'returned_rentals'      => $returnedRentals,
            'total_revenue'         => $totalRevenue,
            'fleet_utilization'     => $utilizationRate
        ];
    }

    /**
     * Category distribution for charts
     */
    public static function getFleetByCategory(): array {
        $sql = "SELECT c.name, COUNT(v.id) AS vehicle_count 
                FROM `vehicle_categories` c 
                LEFT JOIN `vehicles` v ON v.category_id = c.id AND v.operational_status != 'deactivated'
                GROUP BY c.id 
                ORDER BY c.display_order ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Bookings status breakdown for charts
     */
    public static function getBookingsByStatus(): array {
        $sql = "SELECT `status`, COUNT(*) AS count 
                FROM `bookings` 
                GROUP BY `status`";
        return Database::fetchAll($sql);
    }

    /**
     * Recent bookings for admin overview
     */
    public static function getRecentBookings(int $limit = 6): array {
        $sql = "SELECT b.*, v.brand, v.model, v.license_plate, c.name AS category_name, u.name AS customer_name 
                FROM `bookings` b 
                JOIN `vehicles` v ON b.vehicle_id = v.id 
                JOIN `vehicle_categories` c ON v.category_id = c.id 
                JOIN `users` u ON b.user_id = u.id 
                ORDER BY b.created_at DESC 
                LIMIT {$limit}";
        return Database::fetchAll($sql);
    }
}
