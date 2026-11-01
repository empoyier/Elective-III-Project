<?php
/**
 * APEX Car Rental Management System
 * Vehicle Catalog, Specifications, Availability & Fleet Management
 */

require_once __DIR__ . '/Database.php';

class Vehicle {
    /**
     * Get all vehicle categories with vehicle counts
     */
    public static function getCategories(): array {
        $sql = "SELECT c.*, COUNT(v.id) AS vehicle_count 
                FROM `vehicle_categories` c 
                LEFT JOIN `vehicles` v ON v.category_id = c.id AND v.operational_status != 'deactivated'
                GROUP BY c.id 
                ORDER BY c.display_order ASC";
        return Database::fetchAll($sql);
    }

    /**
     * Get featured vehicles for the front page
     */
    public static function getFeatured(int $limit = 6): array {
        $sql = "SELECT v.*, c.name AS category_name, c.slug AS category_slug 
                FROM `vehicles` v 
                JOIN `vehicle_categories` c ON v.category_id = c.id 
                WHERE v.operational_status = 'available' 
                ORDER BY v.daily_rate ASC, v.id ASC 
                LIMIT {$limit}";
        return Database::fetchAll($sql);
    }

    /**
     * Get vehicle by ID
     */
    public static function getById(int $id): ?array {
        $sql = "SELECT v.*, c.name AS category_name, c.slug AS category_slug 
                FROM `vehicles` v 
                JOIN `vehicle_categories` c ON v.category_id = c.id 
                WHERE v.id = ? LIMIT 1";
        $vehicle = Database::fetchOne($sql, [$id]);
        if ($vehicle && !empty($vehicle['features_json'])) {
            $vehicle['features'] = json_decode($vehicle['features_json'], true) ?: [];
        } else if ($vehicle) {
            $vehicle['features'] = [];
        }
        return $vehicle;
    }

    /**
     * Check temporal availability for a vehicle over a given date/time interval
     */
    public static function checkAvailability(int $vehicleId, string $pickupDatetime, string $returnDatetime, ?int $excludeBookingId = null): array {
        $vehicle = Database::fetchOne("SELECT * FROM `vehicles` WHERE `id` = ? LIMIT 1", [$vehicleId]);
        if (!$vehicle) {
            return ['available' => false, 'reason' => 'Vehicle not found.'];
        }

        if ($vehicle['operational_status'] === 'deactivated') {
            return ['available' => false, 'reason' => 'This vehicle is currently deactivated and not available for rental.'];
        }

        if ($vehicle['operational_status'] === 'maintenance') {
            return ['available' => false, 'reason' => 'This vehicle is currently undergoing scheduled maintenance.'];
        }

        // Validate datetime order
        if (strtotime($pickupDatetime) >= strtotime($returnDatetime)) {
            return ['available' => false, 'reason' => 'Return date/time must be strictly after pickup date/time.'];
        }

        // 1. Check conflicting approved or active bookings
        $bookingSql = "SELECT `id`, `booking_ref`, `pickup_datetime`, `return_datetime`, `status` 
                       FROM `bookings` 
                       WHERE `vehicle_id` = ? 
                       AND `status` IN ('approved', 'out_on_rental') 
                       AND (`pickup_datetime` < ? AND `return_datetime` > ?)";
        $params = [$vehicleId, $returnDatetime, $pickupDatetime];
        if ($excludeBookingId !== null) {
            $bookingSql .= " AND `id` != ?";
            $params[] = $excludeBookingId;
        }

        $conflictingBooking = Database::fetchOne($bookingSql, $params);
        if ($conflictingBooking) {
            return [
                'available' => false,
                'reason' => 'Vehicle is already reserved or out on rental during the requested period (' . 
                            date('M d, Y', strtotime($conflictingBooking['pickup_datetime'])) . ' to ' . 
                            date('M d, Y', strtotime($conflictingBooking['return_datetime'])) . ').'
            ];
        }

        // 2. Check conflicting maintenance records
        $pickupDate = date('Y-m-d', strtotime($pickupDatetime));
        $returnDate = date('Y-m-d', strtotime($returnDatetime));
        $maintSql = "SELECT * FROM `maintenance_records` 
                     WHERE `vehicle_id` = ? 
                     AND `status` IN ('scheduled', 'in_progress') 
                     AND (`start_date` <= ? AND `end_date` >= ?)";
        $conflictingMaint = Database::fetchOne($maintSql, [$vehicleId, $returnDate, $pickupDate]);
        if ($conflictingMaint) {
            return [
                'available' => false,
                'reason' => 'Vehicle has scheduled maintenance during these dates (' . 
                            date('M d, Y', strtotime($conflictingMaint['start_date'])) . ' to ' . 
                            date('M d, Y', strtotime($conflictingMaint['end_date'])) . ').'
            ];
        }

        return ['available' => true, 'reason' => 'Vehicle is available for the requested dates.'];
    }

    /**
     * Get all vehicles with multi-facet filters & date-based availability
     */
    public static function getAll(array $filters = []): array {
        $sql = "SELECT v.*, c.name AS category_name, c.slug AS category_slug 
                FROM `vehicles` v 
                JOIN `vehicle_categories` c ON v.category_id = c.id 
                WHERE 1=1";
        $params = [];

        // Operational status filter (customers only see available)
        if (!empty($filters['status'])) {
            $sql .= " AND v.operational_status = ?";
            $params[] = $filters['status'];
        } else if (empty($filters['admin_view'])) {
            $sql .= " AND v.operational_status != 'deactivated'";
        }

        // Category filter
        if (!empty($filters['category'])) {
            if (is_numeric($filters['category'])) {
                $sql .= " AND v.category_id = ?";
                $params[] = (int)$filters['category'];
            } else {
                $sql .= " AND c.slug = ?";
                $params[] = $filters['category'];
            }
        }

        // Price range
        if (!empty($filters['price_min'])) {
            $sql .= " AND v.daily_rate >= ?";
            $params[] = (float)$filters['price_min'];
        }
        if (!empty($filters['price_max'])) {
            $sql .= " AND v.daily_rate <= ?";
            $params[] = (float)$filters['price_max'];
        }

        // Transmission
        if (!empty($filters['transmission'])) {
            $sql .= " AND v.transmission = ?";
            $params[] = $filters['transmission'];
        }

        // Seats
        if (!empty($filters['seats'])) {
            $sql .= " AND v.seats >= ?";
            $params[] = (int)$filters['seats'];
        }

        // Fuel type
        if (!empty($filters['fuel_type'])) {
            $sql .= " AND v.fuel_type = ?";
            $params[] = $filters['fuel_type'];
        }

        // Keyword search
        if (!empty($filters['search'])) {
            $term = '%' . trim($filters['search']) . '%';
            $sql .= " AND (v.brand LIKE ? OR v.model LIKE ? OR v.license_plate LIKE ? OR c.name LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sort = $filters['sort'] ?? 'rate_asc';
        $sql .= match($sort) {
            'rate_desc' => " ORDER BY v.daily_rate DESC",
            'name_asc'  => " ORDER BY v.brand ASC, v.model ASC",
            'year_desc' => " ORDER BY v.year DESC",
            default     => " ORDER BY v.daily_rate ASC, v.id ASC"
        };

        $vehicles = Database::fetchAll($sql, $params);

        // If date filter provided, compute availability flag for each vehicle
        if (!empty($filters['pickup_datetime']) && !empty($filters['return_datetime'])) {
            $p = $filters['pickup_datetime'];
            $r = $filters['return_datetime'];

            foreach ($vehicles as &$veh) {
                $check = self::checkAvailability($veh['id'], $p, $r);
                $veh['is_date_available'] = $check['available'];
                $veh['availability_reason'] = $check['reason'];
            }
            unset($veh);

            // If filter asks to hide unavailable
            if (!empty($filters['only_available'])) {
                $vehicles = array_values(array_filter($vehicles, fn($v) => !empty($v['is_date_available'])));
            }
        }

        // Parse features JSON for each
        foreach ($vehicles as &$veh) {
            $veh['features'] = !empty($veh['features_json']) ? (json_decode($veh['features_json'], true) ?: []) : [];
        }
        unset($veh);

        return $vehicles;
    }

    /**
     * Admin: Create new vehicle
     */
    public static function create(array $data): int {
        $sql = "INSERT INTO `vehicles` 
                (`category_id`, `brand`, `model`, `year`, `license_plate`, `transmission`, `seats`, `fuel_type`, `mileage`, `daily_rate`, `image_url`, `image_credit`, `operational_status`, `description`, `features_json`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $featuresJson = is_array($data['features']) ? json_encode(array_values(array_filter($data['features']))) : '[]';

        Database::query($sql, [
            (int)$data['category_id'],
            trim($data['brand']),
            trim($data['model']),
            (int)$data['year'],
            strtoupper(trim($data['license_plate'])),
            $data['transmission'] ?? 'Automatic',
            (int)($data['seats'] ?? 5),
            $data['fuel_type'] ?? 'Gasoline',
            (int)($data['mileage'] ?? 0),
            (float)$data['daily_rate'],
            trim($data['image_url']),
            trim($data['image_credit'] ?? ''),
            $data['operational_status'] ?? 'available',
            trim($data['description'] ?? ''),
            $featuresJson
        ]);

        return (int)Database::lastInsertId();
    }

    /**
     * Admin: Update vehicle
     */
    public static function update(int $id, array $data): bool {
        $featuresJson = isset($data['features']) && is_array($data['features']) 
            ? json_encode(array_values(array_filter($data['features']))) 
            : null;

        $sql = "UPDATE `vehicles` SET 
                `category_id` = ?, `brand` = ?, `model` = ?, `year` = ?, `license_plate` = ?, 
                `transmission` = ?, `seats` = ?, `fuel_type` = ?, `mileage` = ?, `daily_rate` = ?, 
                `image_url` = ?, `image_credit` = ?, `operational_status` = ?, `description` = ?" .
                ($featuresJson !== null ? ", `features_json` = ?" : "") . 
                " WHERE `id` = ?";

        $params = [
            (int)$data['category_id'],
            trim($data['brand']),
            trim($data['model']),
            (int)$data['year'],
            strtoupper(trim($data['license_plate'])),
            $data['transmission'],
            (int)$data['seats'],
            $data['fuel_type'],
            (int)$data['mileage'],
            (float)$data['daily_rate'],
            trim($data['image_url']),
            trim($data['image_credit'] ?? ''),
            $data['operational_status'],
            trim($data['description'] ?? ''),
        ];

        if ($featuresJson !== null) {
            $params[] = $featuresJson;
        }
        $params[] = $id;

        Database::query($sql, $params);
        return true;
    }

    /**
     * Admin: Set operational status (available, maintenance, deactivated)
     */
    public static function setStatus(int $id, string $status): bool {
        if (!in_array($status, ['available', 'maintenance', 'deactivated'], true)) {
            return false;
        }
        Database::query("UPDATE `vehicles` SET `operational_status` = ? WHERE `id` = ?", [$status, $id]);
        return true;
    }
}
