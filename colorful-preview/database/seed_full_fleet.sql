-- ===================================================================
-- APEX Car Rental Management System
-- Add the 21-vehicle demo fleet & sample data after supabase_schema.sql.
-- Safe to rerun: vehicles/bookings/maintenance are matched by stable values.
-- ===================================================================

-- 1. Insert/Update Complete 21 Philippine Vehicles
SELECT setval(pg_get_serial_sequence('public.vehicles', 'id'),
              GREATEST(COALESCE(MAX(id), 1), 1), MAX(id) IS NOT NULL)
FROM public.vehicles;

INSERT INTO public.vehicles (category_id, brand, model, year, license_plate, transmission, seats, fuel_type, mileage, daily_rate, image_url, image_credit, operational_status, description, features) VALUES
-- Economy
(1, 'Toyota', 'Vios 1.3 XLE Dual VVT-i', 2024, 'NBF-3891', 'Automatic', 5, 'Gasoline', 12400, 1800.00, 
'https://images.unsplash.com/photo-1614983911505-da6b8424a214?auto=format&fit=crop&w=1200&q=80', 'Photo by JavyGo on Unsplash', 'available', 
'The Philippines most trusted subcompact sedan. Renowned for unbeatable fuel efficiency, responsive air conditioning, and seamless urban agility.', 
'["Apple CarPlay & Android Auto", "Reverse Camera", "Dual Front Airbags", "Eco Drive Indicator", "Keyless Entry"]'::jsonb),

(1, 'Mitsubishi', 'Mirage G4 GLS CVT', 2023, 'NDB-7214', 'Automatic', 5, 'Gasoline', 18900, 1600.00, 
'https://images.unsplash.com/photo-1538543773971-f23d16ff0b88?auto=format&fit=crop&w=1200&q=80', 'Photo by Olena Bohovyk on Unsplash', 'available', 
'Highly economical subcompact sedan featuring class-leading rear legroom and light, responsive steering designed for city errands and highway cruising.', 
'["Touchscreen Audio", "Push Start Button", "Keyless Operation System", "Front Fog Lamps", "High-Efficiency INVECS-III CVT"]'::jsonb),

(1, 'Honda', 'Brio 1.2 RS Black Top', 2024, 'NHK-1902', 'Automatic', 5, 'Gasoline', 9300, 1750.00, 
'https://images.unsplash.com/photo-1590362891991-f776e747a588?auto=format&fit=crop&w=1200&q=80', 'Photo by Erik Mclean on Unsplash', 'available', 
'Sporty, youthful 5-door hatchback with punchy 1.2L i-VTEC performance, gloss black accents, and tight turning radius for effortless parking.', 
'["RS Body Kit & Spoiler", "7-inch Touchscreen Audio", "Sport Tuned Suspension", "Daytime Running Lights", "G-CON Body Structure"]'::jsonb),

-- Sedan
(2, 'Toyota', 'Corolla Altis 1.8 GR-S', 2024, 'NCA-4029', 'Automatic', 5, 'Gasoline', 11200, 2600.00, 
'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1200&q=80', 'Photo by John Paul on Unsplash', 'available', 
'Sport-injected compact executive sedan featuring Gazoo Racing tuned suspension, bolstered leather seats, and Toyota Safety Sense suite.', 
'["Toyota Safety Sense (TSS)", "Dynamic Radar Cruise Control", "GR-S Aerodynamic Package", "Wireless Charging Pad", "Paddle Shifters"]'::jsonb),

(2, 'Honda', 'Civic 1.5 V Turbo Honda SENSING', 2024, 'NHC-9150', 'Automatic', 5, 'Gasoline', 8200, 3200.00, 
'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&w=1200&q=80', 'Photo by Campbell on Unsplash', 'available', 
'Sleek fastback profile powered by a potent 178hp turbocharged VTEC engine with full Honda SENSING active driver assistance.', 
'["1.5L Turbocharged Engine (178hp)", "Honda SENSING Suite", "Digital Instrument Cluster", "Remote Engine Start", "Wireless Apple CarPlay"]'::jsonb),

(2, 'Mazda', 'Mazda 3 Sedan 2.0 e-Skyactiv G', 2023, 'NDZ-3033', 'Automatic', 5, 'Hybrid', 14500, 3100.00, 
'https://images.unsplash.com/photo-1542282088-72c9c27ed0cd?auto=format&fit=crop&w=1200&q=80', 'Photo by Adrian N on Unsplash', 'available', 
'Masterpiece of Japanese Kodo design. Whispering acoustic dampening, buttery leather appointments, and mild-hybrid efficiency.', 
'["Bose 12-Speaker Sound System", "Head-Up Active Driving Display", "M Hybrid Efficiency System", "i-Activesense Safety", "Power Moonroof"]'::jsonb),

-- SUV
(3, 'Toyota', 'Fortuner 2.8 4x4 LTD Two-Tone', 2024, 'NFE-8810', 'Automatic', 7, 'Diesel', 16500, 3800.00, 
'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1200&q=80', 'Photo by Caleb White on Unsplash', 'available', 
'The king of Philippine provincial roads. Robust ladder-frame chassis, 204hp 500Nm torque, selectable 4WD, and dual-zone climate control.', 
'["Selectable 4WD with Rear Diff Lock", "JBL 9-Speaker Audio System", "Panoramic View Monitor (360)", "Ventilated Front Seats", "Power Tailgate"]'::jsonb),

(3, 'Mitsubishi', 'Montero Sport GT 4WD Black Series', 2024, 'NMS-7041', 'Automatic', 7, 'Diesel', 14200, 3600.00, 
'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80', 'Photo by Alex Suprun on Unsplash', 'available', 
'Advanced Super Select II 4WD system, 8-inch digital cockpit, and ultra-smooth 8-speed automatic transmission for effortless long-distance travel.', 
'["Super Select II 4WD with Off-Road Modes", "Forward Collision Mitigation", "8-inch Digital Driver Display", "Blind Spot Warning", "Dual USB Ports"]'::jsonb),

(3, 'Ford', 'Everest Titanium+ 2.0 Bi-Turbo 4x4', 2024, 'NFD-9988', 'Automatic', 7, 'Diesel', 11800, 4200.00, 
'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1200&q=80', 'Photo by Dan Gold on Unsplash', 'available', 
'High-tech full-size 7-seat SUV featuring massive 12-inch portrait SYNC 4A screen, panoramic moonroof, and terrain management modes.', 
'["12-inch SYNC 4A Portrait Screen", "Dual-Panel Panoramic Sunroof", "Terrain Management System", "Active Park Assist 2.0", "Wireless Phone Charging"]'::jsonb),

-- Van & MPV
(4, 'Toyota', 'Innova 2.8 V Diesel Captain Seats', 2024, 'NIN-2800', 'Automatic', 7, 'Diesel', 21000, 2800.00, 
'https://images.unsplash.com/photo-1542282088-fe8426682b8f?auto=format&fit=crop&w=1200&q=80', 'Photo by Patrick Tomasso on Unsplash', 'available', 
'The quintessential Philippine family carrier. 2nd-row captain chairs, dependable D-4D diesel powertrain, and generous luggage capacity.', 
'["2nd Row Reclining Captain Seats", "Ambient Ceiling Lighting", "Automatic Climate Control", "7 Airbags", "Vehicle Stability Control"]'::jsonb),

(4, 'Toyota', 'HiAce Super Grandia Elite', 2024, 'NHG-1090', 'Automatic', 10, 'Diesel', 8900, 4800.00, 
'https://images.unsplash.com/photo-1535655685871-dc8158ff167e?auto=format&fit=crop&w=1200&q=80', 'Photo by Hanson Lu on Unsplash', 'available', 
'VIP executive van with quilted leather Ottoman captain chairs, dual power sliding side doors, and serene ride quality for distinguished group journeys.', 
'["Ottoman Reclining Seats", "Dual Power Sliding Doors", "Toyota Safety Sense", "Rear Independent AC", "Executive Wood Grain Trim"]'::jsonb),

(4, 'Hyundai', 'Staria 2.2 CRDi Premium AWD', 2024, 'NYS-5582', 'Automatic', 9, 'Diesel', 6400, 4500.00, 
'https://images.unsplash.com/photo-1688619102394-9414a07286b0?auto=format&fit=crop&w=1200&q=80', 'Photo by Julian Hochgesang on Unsplash', 'available', 
'Futuristic spaceship-inspired people mover featuring panoramic wrap-around glass, HTRAC All-Wheel Drive, and ultra-plush premium lounge seating.', 
'["HTRAC AWD", "Relaxation Comfort Seats", "Surround View Monitor", "10.25-inch Navi Display", "Smart Power Sliding Doors"]'::jsonb),

-- Luxury
(5, 'Mercedes-Benz', 'E-Class E 200 Avantgarde', 2024, 'NMB-2020', 'Automatic', 5, 'Gasoline', 5200, 7500.00, 
'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=80', 'Photo by Samuele Errico on Unsplash', 'available', 
'The benchmark executive saloon. Boasting MBUX digital widescreen cockpit, 64-color ambient lighting, and signature German high-speed stability.', 
'["MBUX Dual 12.3-inch Screens", "Burmester Surround Sound", "Active Brake Assist", "64-Color Ambient Light", "Agility Control Suspension"]'::jsonb),

(5, 'BMW', '5 Series 520i M Sport', 2024, 'NBW-5201', 'Automatic', 5, 'Gasoline', 4100, 7200.00, 
'https://images.unsplash.com/photo-1555353540-64580b51c258?auto=format&fit=crop&w=1200&q=80', 'Photo by Sven D on Unsplash', 'available', 
'The ultimate driving machine for executive rentals. Aerodynamic M Sport package, adaptive LED headlights, and pinpoint steering precision.', 
'["BMW Live Cockpit Professional", "M Sport Package", "Harman Kardon Audio", "Gesture Control", "Wireless Smartphone Integration"]'::jsonb),

(5, 'Lexus', 'RX 350 F Sport AWD', 2024, 'NLX-3509', 'Automatic', 5, 'Gasoline', 5900, 7800.00, 
'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=1200&q=80', 'Photo by Alex Suprun on Unsplash', 'available', 
'Takumi craftsmanship meets bold luxury styling. Spindle body architecture, 14-inch touchscreen, and whisper-quiet acoustic insulation.', 
'["Mark Levinson 21-Speaker Sound", "14-inch High-Res Display", "Adaptive Variable Suspension", "Color Head-Up Display", "Lexus Safety System+ 3.0"]'::jsonb),

-- Sports
(6, 'Ford', 'Mustang GT 5.0 V8 Fastback', 2024, 'NMS-5000', 'Automatic', 4, 'Gasoline', 3900, 8500.00, 
'https://images.unsplash.com/photo-1584345604476-8ec5e12e42dd?auto=format&fit=crop&w=1200&q=80', 'Photo by Joey Banks on Unsplash', 'available', 
'Iconic American muscle car unleashing 480 horsepower from a naturally aspirated 5.0L Coyote V8 engine with active quad-tip performance exhaust.', 
'["5.0L Coyote V8 Engine", "Active Valve Performance Exhaust", "Brembo 6-Piston Brakes", "MagneRide Damping", "Track Apps with Launch Control"]'::jsonb),

(6, 'Toyota', 'GR Supra 3.0 Pro Track', 2024, 'NGS-3000', 'Automatic', 2, 'Gasoline', 2800, 8900.00, 
'https://images.unsplash.com/photo-1617469767053-d3b523a0b982?auto=format&fit=crop&w=1200&q=80', 'Photo by Giorgio Trovato on Unsplash', 'available', 
'Bred on the racetrack by TOYOTA GAZOO Racing. 382hp inline-six twin-scroll turbo engine, 50:50 weight balance, and intoxicating sports coupe handling.', 
'["3.0L Inline-6 Turbo (382hp)", "Active Rear Sport Differential", "Adaptive Variable Suspension", "12-Speaker JBL Sound", "Forged Aluminum Wheels"]'::jsonb),

(6, 'Chevrolet', 'Corvette C8 Stingray 3LT', 2023, 'NCV-0808', 'Automatic', 2, 'Gasoline', 4200, 11000.00, 
'https://images.unsplash.com/photo-1612071078206-bc912b559e3a?auto=format&fit=crop&w=1200&q=80', 'Photo by Stefan Rodriguez on Unsplash', 'available', 
'Exotic mid-engine supercar boasting a 6.2L LT2 V8 producing 495 horsepower, capable of 0-100 km/h in 2.9 seconds with a removable targa top.', 
'["Mid-Engine 6.2L V8 (495hp)", "Z51 Performance Package", "Magnetic Selective Ride Control", "Removable Roof Panel", "Performance Data Recorder"]'::jsonb),

-- Electric
(7, 'Tesla', 'Model 3 Long Range Dual Motor', 2024, 'NTS-3301', 'Automatic', 5, 'Electric', 7100, 4500.00, 
'https://images.unsplash.com/photo-1560958089-b8a1929cea89?auto=format&fit=crop&w=1200&q=80', 'Photo by Austin Ramsey on Unsplash', 'available', 
'The benchmark modern EV. 629km WLTP battery range, all-glass panoramic roof, dual-motor all-wheel drive, and ultra-minimalist touchscreen control.', 
'["Dual-Motor All-Wheel Drive", "629km Battery Range", "15.4-inch Central Touchscreen", "Glass Roof", "Autopilot Driver Assistance"]'::jsonb),

(7, 'BYD', 'Atto 3 Superior Extended Range', 2024, 'NBD-8800', 'Automatic', 5, 'Electric', 8400, 3200.00, 
'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=1200&q=80', 'Photo by Martin Katler on Unsplash', 'available', 
'The leading global compact electric crossover featuring BYD Blade Battery, rotating 12.8-inch infotainment display, and 480km range.', 
'["Ultra-Safe Blade Battery", "Rotating 12.8-inch Display", "DiPilot Safety Suite", "Panoramic Sunroof", "V2L Vehicle-to-Load Power"]'::jsonb),

(7, 'Hyundai', 'Ioniq 5 AWD Long Range', 2024, 'NHI-5050', 'Automatic', 5, 'Electric', 5600, 4200.00, 
'https://images.unsplash.com/photo-1617469767053-d3b523a0b982?auto=format&fit=crop&w=1200&q=80', 'Photo by Vlad Tchompalov on Unsplash', 'available', 
'Retro-futuristic World Car of the Year built on an 800V ultra-fast charging platform with relaxation front seats and V2L power capabilities.', 
'["800V Ultra-Fast Charging", "V2L Exterior Power Outlet", "Parametric Pixel LED Lighting", "Relaxation Comfort Seats", "SmartSense ADAS"]'::jsonb)
ON CONFLICT (license_plate) DO UPDATE SET
    category_id = EXCLUDED.category_id,
    brand = EXCLUDED.brand,
    model = EXCLUDED.model,
    year = EXCLUDED.year,
    license_plate = EXCLUDED.license_plate,
    transmission = EXCLUDED.transmission,
    seats = EXCLUDED.seats,
    fuel_type = EXCLUDED.fuel_type,
    mileage = EXCLUDED.mileage,
    daily_rate = EXCLUDED.daily_rate,
    image_url = EXCLUDED.image_url,
    image_credit = EXCLUDED.image_credit,
    operational_status = EXCLUDED.operational_status,
    description = EXCLUDED.description,
    features = EXCLUDED.features;

-- 2. Insert Sample Demo Bookings for Admin Dashboard KPIs & Charts
SELECT setval(pg_get_serial_sequence('public.bookings', 'id'),
              GREATEST(COALESCE(MAX(id), 1), 1), MAX(id) IS NOT NULL)
FROM public.bookings;

INSERT INTO public.bookings (booking_ref, vehicle_id, pickup_datetime, return_datetime, pickup_location, return_location, total_days, daily_rate, base_amount, addons_amount, tax_amount, total_amount, deposit_amount, status, customer_notes)
SELECT seeded.booking_ref, vehicles.id, seeded.pickup_datetime, seeded.return_datetime,
       seeded.pickup_location, seeded.return_location, seeded.total_days, seeded.daily_rate,
       seeded.base_amount, seeded.addons_amount, seeded.tax_amount, seeded.total_amount,
       seeded.deposit_amount, seeded.status, seeded.customer_notes
FROM (VALUES
    ('APX-2026-8801', 'NFE-8810', NOW() - INTERVAL '1 day', NOW() + INTERVAL '3 days', 'Iba Main Hub (Zambales)', 'Iba Main Hub (Zambales)', 4, 3800.00, 15200.00, 1800.00, 0.00, 17000.00, 5000.00, 'out_on_rental', 'Coastal road trip to Botolan and Zambales beaches.'),
    ('APX-2026-8802', 'NCA-4029', NOW() + INTERVAL '2 days', NOW() + INTERVAL '5 days', 'Iba Main Hub (Zambales)', 'Subic Bay Hub', 3, 2600.00, 7800.00, 450.00, 0.00, 8250.00, 5000.00, 'pending', 'Executive corporate client visiting Zambales solar projects.'),
    ('APX-2026-8799', 'NBF-3891', NOW() - INTERVAL '5 days', NOW() - INTERVAL '2 days', 'Iba Main Hub (Zambales)', 'Iba Main Hub (Zambales)', 3, 1800.00, 5400.00, 0.00, 0.00, 5400.00, 3000.00, 'returned', 'Local Iba municipality transit.')
) AS seeded(booking_ref, vehicle_plate, pickup_datetime, return_datetime, pickup_location, return_location, total_days, daily_rate, base_amount, addons_amount, tax_amount, total_amount, deposit_amount, status, customer_notes)
JOIN public.vehicles ON vehicles.license_plate = seeded.vehicle_plate
ON CONFLICT (booking_ref) DO NOTHING;

-- 3. Insert Sample Maintenance Record
SELECT setval(pg_get_serial_sequence('public.maintenance', 'id'),
              GREATEST(COALESCE(MAX(id), 1), 1), MAX(id) IS NOT NULL)
FROM public.maintenance;

INSERT INTO public.maintenance (vehicle_id, service_type, start_date, end_date, cost, status, notes)
SELECT vehicles.id, '10,000 km Periodic Maintenance & Brake Inspection', CURRENT_DATE,
       CURRENT_DATE + INTERVAL '2 days', 4500.00, 'scheduled',
       'Routine PMS, synthetic oil change, filter replacement.'
FROM public.vehicles
WHERE vehicles.license_plate = 'NDB-7214'
  AND
  NOT EXISTS (
    SELECT 1
    FROM public.maintenance
    WHERE vehicle_id = vehicles.id
      AND service_type = '10,000 km Periodic Maintenance & Brake Inspection'
);

-- Keep identity sequences ahead of seeded IDs on fresh and existing projects.
SELECT setval(pg_get_serial_sequence('public.vehicles', 'id'),
              GREATEST(COALESCE(MAX(id), 1), 1), MAX(id) IS NOT NULL)
FROM public.vehicles;
SELECT setval(pg_get_serial_sequence('public.bookings', 'id'),
              GREATEST(COALESCE(MAX(id), 1), 1), MAX(id) IS NOT NULL)
FROM public.bookings;
SELECT setval(pg_get_serial_sequence('public.maintenance', 'id'),
              GREATEST(COALESCE(MAX(id), 1), 1), MAX(id) IS NOT NULL)
FROM public.maintenance;
