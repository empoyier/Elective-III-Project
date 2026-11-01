-- ===================================================================
-- APEX Car Rental Management System
-- Seed Data: Philippine Fleet, Routes, Add-ons & Demo Accounts
-- ===================================================================

USE `apex_rentals`;

-- Clear existing data if re-seeding
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `rental_tracking_sessions`;
TRUNCATE TABLE `return_inspections`;
TRUNCATE TABLE `booking_status_logs`;
TRUNCATE TABLE `booking_addons`;
TRUNCATE TABLE `bookings`;
TRUNCATE TABLE `maintenance_records`;
TRUNCATE TABLE `vehicles`;
TRUNCATE TABLE `vehicle_categories`;
TRUNCATE TABLE `addons`;
TRUNCATE TABLE `tracking_routes`;
TRUNCATE TABLE `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Insert Default Users
-- admin123, staff123, customer123
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `license_number`, `role`, `status`) VALUES
(1, 'Admin Operations', 'admin@apexrentals.ph', '$2y$10$aFYe2LXFgzqN./PyZsfnCuB.PLaepYsJCCTAbHIHB1b/aP4.ebBei', '+63 917 800 1100', 'N01-15-098765', 'admin', 'active'),
(2, 'Fleet Dispatch Staff', 'staff@apexrentals.ph', '$2y$10$wMCepSc6XUb4B7CEINH15.dn2HIDfaOwO9HhtzrCMX18EpE0x3h7y', '+63 918 800 2200', 'N02-18-043210', 'staff', 'active'),
(3, 'Juan Dela Cruz', 'customer@example.ph', '$2y$10$FS13ZrYLxHp5dqlFWcszzORizNO31lreRDg3f3/ey5Xs0hy8FL9mi', '+63 919 555 3344', 'N03-20-112233', 'customer', 'active'),
(4, 'Maria Santos', 'maria.santos@gmail.com', '$2y$10$FS13ZrYLxHp5dqlFWcszzORizNO31lreRDg3f3/ey5Xs0hy8FL9mi', '+63 920 777 8899', 'N04-21-445566', 'customer', 'active');

-- 2. Insert Vehicle Categories
INSERT INTO `vehicle_categories` (`id`, `name`, `slug`, `description`, `display_order`) VALUES
(1, 'Economy', 'economy', 'Fuel-efficient, compact hatchbacks and subcompact sedans perfect for Philippine city traffic and budget-conscious travel.', 1),
(2, 'Sedan', 'sedan', 'Comfortable, stylish midsize sedans ideal for corporate travel, executive commutes, and smooth expressway cruising.', 2),
(3, 'SUV', 'suv', 'Versatile, high-clearance sport utility vehicles equipped for Philippine provincial roads, rainy weather, and family vacations.', 3),
(4, 'Van & MPV', 'van-mpv', 'Spacious multi-purpose vehicles and luxury vans designed for large groups, airport transfers, and out-of-town group excursions.', 4),
(5, 'Luxury', 'luxury', 'Premium executive vehicles delivering world-class craftsmanship, whisper-quiet cabins, and prestigious design.', 5),
(6, 'Sports', 'sports', 'High-performance enthusiast coupes and convertibles delivering thrilling acceleration and head-turning styling.', 6),
(7, 'Electric', 'electric', 'Zero-emission modern electric vehicles featuring instant torque, ultra-quiet drives, and cutting-edge automotive technology.', 7);

-- 3. Insert Rental Add-Ons (Rates in PHP)
INSERT INTO `addons` (`id`, `name`, `description`, `daily_rate`, `price_type`, `is_active`) VALUES
(1, 'Comprehensive Collision Damage Waiver (CDW)', 'Zero-excess insurance protection against accidental vehicle body damage and third-party liabilities.', 450.00, 'daily', 1),
(2, 'Child Safety Seat', 'ISOFIX certified rear/forward facing child restraint seat suitable for infants and toddlers.', 200.00, 'daily', 1),
(3, 'Additional Authorized Driver', 'Authorizes a second qualified driver covered under all rental agreements and insurance provisions.', 250.00, 'daily', 1),
(4, 'Expressway RFID Toll Pass (Autosweep & Easytrip)', 'Pre-installed RFID tags for seamless contactless toll transit on SLEX, NLEX, Skyway, and CCLEX. (Tolls billed at post-rental cost).', 150.00, 'fixed', 1),
(5, 'Garmin GPS Navigation Device', 'Standalone offline GPS navigator with preloaded Philippine street and provincial highway maps.', 180.00, 'daily', 1);

-- 4. Insert 21 Philippine Fleet Vehicles with Verified Unsplash Images & Credits
INSERT INTO `vehicles` (`id`, `category_id`, `brand`, `model`, `year`, `license_plate`, `transmission`, `seats`, `fuel_type`, `mileage`, `daily_rate`, `image_url`, `image_credit`, `operational_status`, `description`, `features_json`) VALUES
-- Economy
(1, 1, 'Toyota', 'Vios 1.3 XLE Dual VVT-i', 2024, 'NBF-3891', 'Automatic', 5, 'Gasoline', 12400, 1800.00, 
'https://images.unsplash.com/photo-1614983911505-da6b8424a214?auto=format&fit=crop&w=1200&q=80', 'Photo by JavyGo on Unsplash', 'available', 
'The Philippines\' most trusted subcompact sedan. Renowned for unbeatable fuel efficiency, responsive air conditioning, and seamless urban agility.', 
'["Apple CarPlay & Android Auto", "Reverse Camera", "Dual Front Airbags", "Eco Drive Indicator", "Keyless Entry"]'),

(2, 1, 'Mitsubishi', 'Mirage G4 GLS CVT', 2023, 'NDB-7214', 'Automatic', 5, 'Gasoline', 18900, 1600.00, 
'https://images.unsplash.com/photo-1538543773971-f23d16ff0b88?auto=format&fit=crop&w=1200&q=80', 'Photo by Olena Bohovyk on Unsplash', 'available', 
'Ultra-economical 1.2L MIVEC sedan delivering remarkable gas mileage in stop-and-go Manila traffic, with surprisingly generous rear passenger legroom.', 
'["Touchscreen Infotainment", "Keyless Push Start", "Electric Power Steering", "Tire Pressure Monitor", "Rear Sensors"]'),

(3, 1, 'Honda', 'City 1.5 V DOHC i-VTEC', 2024, 'NCT-8105', 'Automatic', 5, 'Gasoline', 9500, 2100.00, 
'https://images.unsplash.com/photo-1564244411791-ce03acb576b8?auto=format&fit=crop&w=1200&q=80', 'Photo by Alvis Taurens on Unsplash', 'available', 
'Refined Japanese engineering offering Honda SENSING safety suite, punchy 121hp performance, and class-leading interior ergonomics.', 
'["Honda SENSING Suite", "LaneWatch Camera", "8-inch Display Audio", "Paddle Shifters", "Cruise Control"]'),

-- Sedan
(4, 2, 'Toyota', 'Camry 2.5 HEV Platinum Hybrid', 2024, 'NHE-9022', 'Automatic', 5, 'Hybrid', 6100, 3800.00, 
'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1200&q=80', 'Photo by Campbell on Unsplash', 'available', 
'Flagship executive hybrid sedan combining ultra-quiet electric cruising with supreme luxury, ventilated leather seats, and prestige road presence.', 
'["Self-Charging Hybrid", "Ventilated Leather Seats", "JBL 9-Speaker Audio", "Toyota Safety Sense", "Wireless Charging"]'),

(5, 2, 'Honda', 'Civic RS Turbo 1.5 VTEC', 2024, 'NHC-5541', 'Automatic', 5, 'Gasoline', 8200, 3400.00, 
'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&w=1200&q=80', 'Photo by Martin Katler on Unsplash', 'available', 
'Sporty, dynamic, and aggressively styled turbo sedan equipped with sport pedals, dual exhausts, honeycomb dashboard vents, and sharp handling.', 
'["178hp Turbo Engine", "Bose Premium Sound", "Honda SENSING", "Sport Mode with Paddle Shifters", "Full LED Headlamps"]'),

(6, 2, 'Mazda', 'Mazda 3 Sedan 2.0 Skyactiv-G', 2023, 'NDZ-3367', 'Automatic', 5, 'Gasoline', 14200, 3000.00, 
'https://images.unsplash.com/photo-1542282088-72c9c27ed0cd?auto=format&fit=crop&w=1200&q=80', 'Photo by Devon Janse on Unsplash', 'available', 
'Sculpted with Mazda\'s award-winning Kodo design language, boasting an opulent minimalist interior rivalling European luxury benchmarks.', 
'["Head-Up Display", "Bose 12-Speaker Sound", "G-Vectoring Control Plus", "Leather Upholstery", "Blind Spot Monitoring"]'),

-- SUV
(7, 3, 'Toyota', 'Fortuner 2.8 4x4 LTD Bi-Tone', 2024, 'NFG-4489', 'Automatic', 7, 'Diesel', 11300, 3800.00, 
'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1200&q=80', 'Photo by Roberto Nickson on Unsplash', 'available', 
'The undisputed king of Philippine SUVs. Heavy-duty ladder-frame chassis with a muscular 204hp 2.8L turbo-diesel engine conquering any provincial highway.', 
'["Panoramic View Monitor 360", "JBL 9-Speaker Audio", "Ventilated Front Seats", "Power Tailgate", "Differential Lock"]'),

(8, 3, 'Mitsubishi', 'Montero Sport GT 4WD', 2024, 'NMS-6721', 'Automatic', 7, 'Diesel', 15100, 3600.00, 
'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=80', 'Photo by Adrian Newell on Unsplash', 'available', 
'Sophisticated 7-seat family SUV with Super Select 4WD-II, ultra-smooth 8-speed automatic gearbox, and an advanced 8-inch digital driver instrument cluster.', 
'["Super Select 4WD-II", "Forward Collision Mitigation", "8-inch Digital Cluster", "Dual-Zone Climate Control", "Rear Seat Entertainment"]'),

(9, 3, 'Ford', 'Everest Titanium+ 2.0L Bi-Turbo 4x4', 2024, 'NEV-9932', 'Automatic', 7, 'Diesel', 7800, 4000.00, 
'https://images.unsplash.com/photo-1551830820-330a71b99659?auto=format&fit=crop&w=1200&q=80', 'Photo by Tyler Clemmensen on Unsplash', 'available', 
'Next-Gen rugged luxury SUV with massive 12-inch portrait touchscreen, panoramic sunroof, terrain management modes, and plush ride comfort.', 
'["12-inch Sync 4A Screen", "Panoramic Sunroof", "Active Park Assist", "Adaptive Cruise Control", "Wireless Apple CarPlay"]'),

-- Van & MPV
(10, 4, 'Toyota', 'Innova 2.8 V Diesel', 2024, 'NTI-7714', 'Automatic', 7, 'Diesel', 13400, 2800.00, 
'https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&w=1200&q=80', 'Photo by Darwin Vegher on Unsplash', 'available', 
'The definitive Philippine family carrier. Equipped with captain\'s chairs in the second row, robust diesel endurance, and dependable passenger comfort.', 
'["Second-Row Captain Chairs", "Ambient LED Lighting", "Automatic Climate Control", "7 Airbags", "Vehicle Stability Control"]'),

(11, 4, 'Toyota', 'HiAce Super Grandia Elite', 2024, 'NHG-1090', 'Automatic', 10, 'Diesel', 8900, 4800.00, 
'https://images.unsplash.com/photo-1535655685871-dc8158ff167e?auto=format&fit=crop&w=1200&q=80', 'Photo by Hanson Lu on Unsplash', 'available', 
'VIP executive van with quilted leather Ottoman captain chairs, dual power sliding side doors, and serene ride quality for distinguished group journeys.', 
'["Ottoman Reclining Seats", "Dual Power Sliding Doors", "Toyota Safety Sense", "Rear Independent AC", "Executive Wood Grain Trim"]'),

(12, 4, 'Hyundai', 'Staria 2.2 CRDi Premium AWD', 2024, 'NYS-5582', 'Automatic', 9, 'Diesel', 6400, 4500.00, 
'https://images.unsplash.com/photo-1688619102394-9414a07286b0?auto=format&fit=crop&w=1200&q=80', 'Photo by Julian Hochgesang on Unsplash', 'available', 
'Futuristic spaceship-inspired people mover featuring panoramic wrap-around glass, HTRAC All-Wheel Drive, and ultra-plush premium lounge seating.', 
'["HTRAC AWD", "Relaxation Comfort Seats", "Surround View Monitor", "10.25-inch Navi Display", "Smart Power Sliding Doors"]'),

-- Luxury
(13, 5, 'Mercedes-Benz', 'E-Class E 200 Avantgarde', 2024, 'NMB-2020', 'Automatic', 5, 'Gasoline', 5200, 7500.00, 
'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=80', 'Photo by Samuele Errico on Unsplash', 'available', 
'The benchmark executive saloon. Boasting MBUX digital widescreen cockpit, 64-color ambient lighting, and signature German high-speed stability.', 
'["MBUX Dual 12.3-inch Screens", "Burmester Surround Sound", "Active Brake Assist", "64-Color Ambient Light", "Agility Control Suspension"]'),

(14, 5, 'BMW', '5 Series 520i M Sport', 2024, 'NBW-5201', 'Automatic', 5, 'Gasoline', 4100, 7200.00, 
'https://images.unsplash.com/photo-1555353540-64580b51c258?auto=format&fit=crop&w=1200&q=80', 'Photo by Sven D on Unsplash', 'available', 
'The ultimate driving machine for executive rentals. Aerodynamic M Sport package, adaptive LED headlights, and pinpoint steering precision.', 
'["BMW Live Cockpit Professional", "M Sport Package", "Harman Kardon Audio", "Gesture Control", "Wireless Smartphone Integration"]'),

(15, 5, 'Lexus', 'RX 350 F Sport AWD', 2024, 'NLX-3509', 'Automatic', 5, 'Gasoline', 5900, 7800.00, 
'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80', 'Photo by Alex Suprun on Unsplash', 'available', 
'Takumi craftsmanship meets bold luxury styling. Spindle body architecture, 14-inch touchscreen, and whisper-quiet acoustic insulation.', 
'["Mark Levinson 21-Speaker Sound", "14-inch High-Res Display", "Adaptive Variable Suspension", "Color Head-Up Display", "Lexus Safety System+ 3.0"]'),

-- Sports
(16, 6, 'Ford', 'Mustang GT 5.0 V8 Fastback', 2024, 'NMS-5000', 'Automatic', 4, 'Gasoline', 3900, 8500.00, 
'https://images.unsplash.com/photo-1584345604476-8ec5e12e42dd?auto=format&fit=crop&w=1200&q=80', 'Photo by Joey Banks on Unsplash', 'available', 
'Iconic American muscle car unleashing 480 horsepower from a naturally aspirated 5.0L Coyote V8 engine with active quad-tip performance exhaust.', 
'["5.0L Coyote V8 Engine", "Active Valve Performance Exhaust", "Brembo 6-Piston Brakes", "MagneRide Damping", "Track Apps with Launch Control"]'),

(17, 6, 'Toyota', 'GR Supra 3.0 Pro Track', 2024, 'NGS-3000', 'Automatic', 2, 'Gasoline', 2800, 8900.00, 
'https://images.unsplash.com/photo-1617469767053-d3b523a0b982?auto=format&fit=crop&w=1200&q=80', 'Photo by Giorgio Trovato on Unsplash', 'available', 
'Bred on the racetrack by TOYOTA GAZOO Racing. 382hp inline-six twin-scroll turbo engine, 50:50 weight balance, and intoxicating sports coupe handling.', 
'["3.0L Inline-6 Turbo (382hp)", "Active Rear Sport Differential", "Adaptive Variable Suspension", "12-Speaker JBL Sound", "Forged Aluminum Wheels"]'),

(18, 6, 'Chevrolet', 'Corvette C8 Stingray 3LT', 2023, 'NCV-0808', 'Automatic', 2, 'Gasoline', 4200, 11000.00, 
'https://images.unsplash.com/photo-1612071078206-bc912b559e3a?auto=format&fit=crop&w=1200&q=80', 'Photo by Stefan Rodriguez on Unsplash', 'available', 
'Exotic mid-engine supercar boasting a 6.2L LT2 V8 producing 495 horsepower, capable of 0-100 km/h in 2.9 seconds with a removable targa top.', 
'["Mid-Engine 6.2L V8 (495hp)", "Z51 Performance Package", "Magnetic Selective Ride Control", "Removable Roof Panel", "Performance Data Recorder"]'),

-- Electric
(19, 7, 'Tesla', 'Model 3 Long Range Dual Motor', 2024, 'NTS-3301', 'Automatic', 5, 'Electric', 7100, 4500.00, 
'https://images.unsplash.com/photo-1560958089-b8a1929cea89?auto=format&fit=crop&w=1200&q=80', 'Photo by Austin Ramsey on Unsplash', 'available', 
'The benchmark modern EV. 629km WLTP battery range, all-glass panoramic roof, dual-motor all-wheel drive, and ultra-minimalist touchscreen control.', 
'["Dual-Motor All-Wheel Drive", "629km Battery Range", "15.4-inch Central Touchscreen", "Glass Roof", "Autopilot Driver Assistance"]'),

(20, 7, 'BYD', 'Atto 3 Superior Extended Range', 2024, 'NBD-8800', 'Automatic', 5, 'Electric', 8400, 3200.00, 
'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=1200&q=80', 'Photo by Martin Katler on Unsplash', 'available', 
'The leading global compact electric crossover featuring BYD\'s ultra-safe Blade Battery, rotating 12.8-inch infotainment display, and 480km range.', 
'["Ultra-Safe Blade Battery", "Rotating 12.8-inch Display", "DiPilot Safety Suite", "Panoramic Sunroof", "V2L Vehicle-to-Load Power"]'),

(21, 7, 'Hyundai', 'Ioniq 5 AWD Long Range', 2024, 'NHI-5050', 'Automatic', 5, 'Electric', 5600, 4200.00, 
'https://images.unsplash.com/photo-1617469767053-d3b523a0b982?auto=format&fit=crop&w=1200&q=80', 'Photo by Vlad Tchompalov on Unsplash', 'available', 
'Retro-futuristic World Car of the Year built on an 800V ultra-fast charging platform with sliding universal island console and pixel LED optics.', 
'["800V Ultra-Fast Charging", "Sliding Universal Island Console", "Relaxation Comfort Seats", "Parametric Pixel Lights", "V2L Power Supply"]');

-- 5. Insert Realistic Philippine Waypoint GPS Tracking Routes (Hub: Iba, Zambales)
INSERT INTO `tracking_routes` (`id`, `name`, `description`, `start_location`, `end_location`, `total_distance_km`, `waypoints_json`) VALUES
(1, 'Iba Central Hub to Botolan Coastal Highway & Bucao Bridge', 
'Departs from APEX Main Hub in Iba Town Center south along the Govic / Olongapo-Bugallon Highway, passing the Zambales Provincial Capitol and sports facilities, crossing the scenic Bucao River Bridge into Botolan.',
'APEX Main Hub, Iba, Zambales', 'Botolan Municipal Plaza, Zambales', 16.4,
'[[15.3265, 119.9805, 35], [15.3210, 119.9840, 50], [15.3120, 119.9910, 65], [15.3040, 119.9995, 70], [15.2965, 120.0120, 60], [15.2910, 120.0185, 55], [15.2865, 120.0240, 45], [15.2840, 120.0265, 35], [15.2865, 120.0240, 45], [15.2965, 120.0120, 60], [15.3120, 119.9910, 70], [15.3265, 119.9805, 35]]'),

(2, 'Iba Central Hub to Palauig Scenic Coastal Highway', 
'Scenic northbound coastal transit along the Zambales National Highway from Iba Central Hub through Pangolingan and Amungan beachside corridors up to Palauig Poblacion.',
'APEX Main Hub, Iba, Zambales', 'Palauig Town Overlook, Zambales', 22.8,
'[[15.3265, 119.9805, 35], [15.3340, 119.9780, 50], [15.3480, 119.9720, 65], [15.3650, 119.9650, 75], [15.3850, 119.9540, 80], [15.4050, 119.9380, 75], [15.4220, 119.9200, 60], [15.4380, 119.9050, 40], [15.4220, 119.9200, 60], [15.3850, 119.9540, 75], [15.3480, 119.9720, 65], [15.3265, 119.9805, 35]]'),

(3, 'Iba Town Center, Airport & Beachfront Resort Circuit', 
'Loop connecting the Zambales Provincial Capitol, Govic Highway, Iba Community Airport runway corridor, and the Bangantalingan coastal resort strip.',
'APEX Main Hub, Iba, Zambales', 'Iba Airport & Bangantalingan Beach Strip', 11.2,
'[[15.3265, 119.9805, 30], [15.3290, 119.9770, 40], [15.3280, 119.9690, 45], [15.3269, 119.9655, 40], [15.3320, 119.9640, 45], [15.3410, 119.9660, 50], [15.3380, 119.9730, 45], [15.3265, 119.9805, 30]]');

-- 6. Insert Sample Bookings for Demonstration of All Lifecycle States
-- Booking 1: ACTIVE (Out on Rental) with Active Simulated Tracking Session!
INSERT INTO `bookings` (`id`, `booking_ref`, `user_id`, `vehicle_id`, `pickup_datetime`, `return_datetime`, `pickup_location`, `return_location`, `total_days`, `daily_rate`, `base_amount`, `addons_amount`, `tax_amount`, `total_amount`, `deposit_amount`, `status`, `customer_notes`) VALUES
(1, 'APX-2026-8801', 3, 7, '2026-10-16 09:00:00', '2026-10-20 18:00:00', 'Iba, Zambales Hub', 'Iba, Zambales Hub', 5, 3800.00, 19000.00, 2250.00, 0.00, 21250.00, 5000.00, 'out_on_rental', 'Coastal road trip to Botolan and Zambales beaches.');

INSERT INTO `booking_addons` (`booking_id`, `addon_id`, `unit_price`, `quantity`, `subtotal`) VALUES
(1, 1, 450.00, 5, 2250.00);

INSERT INTO `booking_status_logs` (`booking_id`, `changed_by_user_id`, `previous_status`, `new_status`, `notes`) VALUES
(1, 3, NULL, 'pending', 'Customer created reservation via web portal.'),
(1, 1, 'pending', 'approved', 'Staff confirmed reservation & license validity.'),
(1, 2, 'approved', 'out_on_rental', 'Customer completed vehicle pickup inspection and dispatch.');

-- Active Tracking Session for Booking 1 (Fortuner currently on Iba - Botolan Route)
INSERT INTO `rental_tracking_sessions` (`booking_id`, `route_id`, `current_waypoint_index`, `current_lat`, `current_lng`, `current_speed`, `distance_traveled_km`, `fuel_level_pct`, `sim_status`) VALUES
(1, 1, 3, 15.3040000, 119.9995000, 68.0, 6.2, 92.0, 'active');

-- Booking 2: PENDING (Awaiting Admin Review)
INSERT INTO `bookings` (`id`, `booking_ref`, `user_id`, `vehicle_id`, `pickup_datetime`, `return_datetime`, `pickup_location`, `return_location`, `total_days`, `daily_rate`, `base_amount`, `addons_amount`, `tax_amount`, `total_amount`, `deposit_amount`, `status`, `customer_notes`) VALUES
(2, 'APX-2026-8802', 4, 4, '2026-10-22 10:00:00', '2026-10-25 10:00:00', 'Iba, Zambales Hub', 'Iba, Zambales Hub', 3, 3800.00, 11400.00, 1350.00, 0.00, 12750.00, 5000.00, 'pending', 'Executive corporate client visiting Zambales solar and provincial projects.');

INSERT INTO `booking_addons` (`booking_id`, `addon_id`, `unit_price`, `quantity`, `subtotal`) VALUES
(2, 1, 450.00, 3, 1350.00);

INSERT INTO `booking_status_logs` (`booking_id`, `changed_by_user_id`, `previous_status`, `new_status`, `notes`) VALUES
(2, 4, NULL, 'pending', 'Customer submitted reservation request.');

-- Booking 3: COMPLETED (Returned)
INSERT INTO `bookings` (`id`, `booking_ref`, `user_id`, `vehicle_id`, `pickup_datetime`, `return_datetime`, `pickup_location`, `return_location`, `total_days`, `daily_rate`, `base_amount`, `addons_amount`, `tax_amount`, `total_amount`, `deposit_amount`, `status`, `customer_notes`) VALUES
(3, 'APX-2026-8799', 3, 1, '2026-10-10 08:00:00', '2026-10-12 18:00:00', 'Iba, Zambales Hub', 'Iba, Zambales Hub', 3, 1800.00, 5400.00, 450.00, 0.00, 5850.00, 3000.00, 'returned', 'Local Iba municipality transit.');

INSERT INTO `return_inspections` (`booking_id`, `inspector_user_id`, `odometer_reading`, `fuel_level_returned`, `has_damage`, `damage_notes`, `additional_charges`) VALUES
(3, 2, 12580, 100, 0, 'Vehicle returned in spotless condition. Full tank verified.', 0.00);
