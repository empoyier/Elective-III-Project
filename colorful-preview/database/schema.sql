-- ===================================================================
-- APEX Car Rental Management System
-- Relational Database Schema (MySQL / MariaDB)
-- ===================================================================

CREATE DATABASE IF NOT EXISTS `apex_rentals` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `apex_rentals`;

-- 1. Users & Authentication
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `license_number` VARCHAR(50) NULL COMMENT 'LTO Driver License Number',
    `role` ENUM('customer', 'admin', 'staff') NOT NULL DEFAULT 'customer',
    `status` ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Vehicle Categories
CREATE TABLE IF NOT EXISTS `vehicle_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `display_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Vehicle Inventory
CREATE TABLE IF NOT EXISTS `vehicles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `brand` VARCHAR(50) NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `year` INT NOT NULL,
    `license_plate` VARCHAR(20) NOT NULL UNIQUE,
    `transmission` ENUM('Automatic', 'Manual') NOT NULL DEFAULT 'Automatic',
    `seats` INT NOT NULL DEFAULT 5,
    `fuel_type` ENUM('Gasoline', 'Diesel', 'Hybrid', 'Electric') NOT NULL DEFAULT 'Gasoline',
    `mileage` INT NOT NULL DEFAULT 0,
    `daily_rate` DECIMAL(10,2) NOT NULL COMMENT 'Rate in Philippine Pesos (PHP)',
    `image_url` TEXT NOT NULL,
    `image_credit` VARCHAR(255) NULL COMMENT 'Photographer attribution credit',
    `operational_status` ENUM('available', 'maintenance', 'deactivated') NOT NULL DEFAULT 'available',
    `description` TEXT NULL,
    `features_json` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `vehicle_categories`(`id`) ON DELETE RESTRICT,
    INDEX `idx_vehicles_status` (`operational_status`),
    INDEX `idx_vehicles_category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Rental Add-Ons
CREATE TABLE IF NOT EXISTS `addons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `daily_rate` DECIMAL(10,2) NOT NULL,
    `price_type` ENUM('daily', 'fixed') NOT NULL DEFAULT 'daily',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Reservations & Bookings
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_ref` VARCHAR(20) NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `vehicle_id` INT NOT NULL,
    `pickup_datetime` DATETIME NOT NULL,
    `return_datetime` DATETIME NOT NULL,
    `pickup_location` VARCHAR(150) NOT NULL,
    `return_location` VARCHAR(150) NOT NULL,
    `total_days` INT NOT NULL DEFAULT 1,
    `daily_rate` DECIMAL(10,2) NOT NULL,
    `base_amount` DECIMAL(10,2) NOT NULL,
    `addons_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `deposit_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('pending', 'approved', 'rejected', 'out_on_rental', 'returned', 'cancelled') NOT NULL DEFAULT 'pending',
    `rejection_reason` TEXT NULL,
    `cancellation_reason` TEXT NULL,
    `customer_notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE RESTRICT,
    INDEX `idx_bookings_vehicle_dates` (`vehicle_id`, `pickup_datetime`, `return_datetime`),
    INDEX `idx_bookings_status` (`status`),
    INDEX `idx_bookings_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Booking Add-Ons Relational Pivot
CREATE TABLE IF NOT EXISTS `booking_addons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `addon_id` INT NOT NULL,
    `unit_price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `subtotal` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`addon_id`) REFERENCES `addons`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Audit Trail / Status Logs
CREATE TABLE IF NOT EXISTS `booking_status_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL,
    `changed_by_user_id` INT NULL,
    `previous_status` VARCHAR(50) NULL,
    `new_status` VARCHAR(50) NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`changed_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Vehicle Maintenance Records
CREATE TABLE IF NOT EXISTS `maintenance_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vehicle_id` INT NOT NULL,
    `service_type` VARCHAR(100) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('scheduled', 'in_progress', 'completed') NOT NULL DEFAULT 'scheduled',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE,
    INDEX `idx_maintenance_dates` (`vehicle_id`, `start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Return Inspections
CREATE TABLE IF NOT EXISTS `return_inspections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL UNIQUE,
    `inspector_user_id` INT NOT NULL,
    `odometer_reading` INT NOT NULL,
    `fuel_level_returned` INT NOT NULL COMMENT 'Percentage 0-100',
    `has_damage` TINYINT(1) NOT NULL DEFAULT 0,
    `damage_notes` TEXT NULL,
    `additional_charges` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`inspector_user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Simulated GPS Waypoint Routes (Philippine Expressways)
CREATE TABLE IF NOT EXISTS `tracking_routes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `start_location` VARCHAR(150) NOT NULL,
    `end_location` VARCHAR(150) NOT NULL,
    `total_distance_km` DECIMAL(6,2) NOT NULL,
    `waypoints_json` LONGTEXT NOT NULL COMMENT 'Array of [lat, lng, speed_limit_kph]',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Rental Simulated Tracking Sessions
CREATE TABLE IF NOT EXISTS `rental_tracking_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `booking_id` INT NOT NULL UNIQUE,
    `route_id` INT NOT NULL,
    `current_waypoint_index` INT NOT NULL DEFAULT 0,
    `current_lat` DECIMAL(10,7) NOT NULL,
    `current_lng` DECIMAL(10,7) NOT NULL,
    `current_speed` DECIMAL(5,1) NOT NULL DEFAULT 0.0 COMMENT 'Speed in km/h',
    `distance_traveled_km` DECIMAL(6,2) NOT NULL DEFAULT 0.0,
    `fuel_level_pct` DECIMAL(5,1) NOT NULL DEFAULT 100.0,
    `sim_status` ENUM('active', 'paused', 'completed') NOT NULL DEFAULT 'active',
    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`route_id`) REFERENCES `tracking_routes`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
