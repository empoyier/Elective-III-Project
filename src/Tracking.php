<?php
/**
 * APEX Car Rental Management System
 * Simulated Real-Time Vehicle Tracking & Waypoint Telemetry Engine
 */

require_once __DIR__ . '/Database.php';

class Tracking {
    /**
     * Get active tracking session for a booking
     */
    public static function getSessionForBooking(int $bookingId): ?array {
        $sql = "SELECT ts.*, r.name AS route_name, r.description AS route_description,
                       r.start_location, r.end_location, r.total_distance_km, r.waypoints_json,
                       b.booking_ref, b.status AS booking_status, b.pickup_datetime, b.return_datetime,
                       v.brand, v.model, v.year, v.license_plate, v.fuel_type, v.image_url,
                       c.name AS category_name
                FROM `rental_tracking_sessions` ts
                JOIN `tracking_routes` r ON ts.route_id = r.id
                JOIN `bookings` b ON ts.booking_id = b.id
                JOIN `vehicles` v ON b.vehicle_id = v.id
                JOIN `vehicle_categories` c ON v.category_id = c.id
                WHERE ts.booking_id = ? LIMIT 1";

        $session = Database::fetchOne($sql, [$bookingId]);
        if ($session) {
            $session['waypoints'] = json_decode($session['waypoints_json'], true) ?: [];
        }
        return $session;
    }

    /**
     * Advance simulation telemetry by one step (called periodically via AJAX or polling)
     */
    public static function advanceSimulation(int $bookingId): array {
        $session = self::getSessionForBooking($bookingId);
        if (!$session) {
            return ['active' => false, 'message' => 'No active tracking session found for this booking.'];
        }

        if ($session['booking_status'] !== 'out_on_rental' || $session['sim_status'] !== 'active') {
            return [
                'active' => false,
                'status' => $session['sim_status'],
                'message' => 'Vehicle is not currently out on rental or simulation has ended.'
            ];
        }

        $waypoints = $session['waypoints'];
        $numWaypoints = count($waypoints);

        if ($numWaypoints === 0) {
            return ['active' => false, 'message' => 'Route has no waypoints defined.'];
        }

        $currentIndex = (int)$session['current_waypoint_index'];
        $nextIndex = ($currentIndex + 1) % $numWaypoints;

        $targetWp = $waypoints[$nextIndex];
        $lat = (float)$targetWp[0];
        $lng = (float)$targetWp[1];
        $baseSpeed = (float)($targetWp[2] ?? 50.0);

        // Add subtle realistic fluctuation (±4 km/h)
        $speed = max(15.0, round($baseSpeed + (mt_rand(-40, 40) / 10), 1));

        // Distance delta step (~0.3 km to 0.7 km per tick)
        $stepKm = round(($speed / 3600) * 15, 2); // approx 15s interval equivalent
        $distanceTraveled = min((float)$session['total_distance_km'], (float)$session['distance_traveled_km'] + $stepKm);

        // Fuel decay: ~0.08% per km traveled
        $fuelPct = max(8.0, round((float)$session['fuel_level_pct'] - ($stepKm * 0.12), 1));

        // Update session in database
        Database::query(
            "UPDATE `rental_tracking_sessions` SET 
                `current_waypoint_index` = ?, 
                `current_lat` = ?, 
                `current_lng` = ?, 
                `current_speed` = ?, 
                `distance_traveled_km` = ?, 
                `fuel_level_pct` = ?, 
                `updated_at` = NOW() 
             WHERE `booking_id` = ?",
            [$nextIndex, $lat, $lng, $speed, $distanceTraveled, $fuelPct, $bookingId]
        );

        return [
            'active'               => true,
            'booking_ref'          => $session['booking_ref'],
            'vehicle'              => $session['brand'] . ' ' . $session['model'] . " ({$session['license_plate']})",
            'fuel_type'            => $session['fuel_type'],
            'route_name'           => $session['route_name'],
            'current_lat'          => $lat,
            'current_lng'          => $lng,
            'current_speed'        => $speed,
            'distance_traveled_km' => $distanceTraveled,
            'total_distance_km'    => (float)$session['total_distance_km'],
            'fuel_level_pct'       => $fuelPct,
            'waypoint_index'       => $nextIndex,
            'total_waypoints'      => $numWaypoints,
            'updated_at'           => date('Y-m-d H:i:s'),
            'all_waypoints'        => $waypoints
        ];
    }

    /**
     * Admin: Get all currently active tracking sessions across the entire fleet
     */
    public static function getAllActiveSessions(): array {
        $sql = "SELECT ts.*, r.name AS route_name, r.total_distance_km,
                       b.booking_ref, b.pickup_datetime, b.return_datetime,
                       v.id AS vehicle_id, v.brand, v.model, v.license_plate, v.image_url,
                       c.name AS category_name, u.name AS customer_name
                FROM `rental_tracking_sessions` ts
                JOIN `tracking_routes` r ON ts.route_id = r.id
                JOIN `bookings` b ON ts.booking_id = b.id
                JOIN `vehicles` v ON b.vehicle_id = v.id
                JOIN `vehicle_categories` c ON v.category_id = c.id
                JOIN `users` u ON b.user_id = u.id
                WHERE b.status = 'out_on_rental' AND ts.sim_status = 'active'
                ORDER BY ts.updated_at DESC";
        return Database::fetchAll($sql);
    }
}
