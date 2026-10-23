-- =====================================================================
-- SAIP — Seed Data
-- Sample farmers, farms, production, products, orders
-- Password for all test users: ChangeMe123!
-- =====================================================================

USE saip_enterprise;

-- Real bcrypt hash of "ChangeMe123!"
SET @pwd = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS7G9D9z9T4e0vB5ZWp4XGmz5.2K';

-- ============ TEST USERS ============
INSERT IGNORE INTO users (uuid, first_name, last_name, email, phone, password_hash, role_id, district_id, preferred_language, status, verification_status, gender)
VALUES
(UUID(), 'Jean',    'Mugisha',  'farmer@saip.rw',     '0788100001', @pwd, 1, 17, 'rw', 'active', 'fully_verified', 'male'),
(UUID(), 'Aline',   'Uwase',    'coop@saip.rw',       '0788100002', @pwd, 2, 7,  'rw', 'active', 'fully_verified', 'female'),
(UUID(), 'Patrick', 'Habimana', 'officer@saip.rw',    '0788100003', @pwd, 3, 17, 'rw', 'active', 'fully_verified', 'male'),
(UUID(), 'Claudine','Mukamana', 'gov@saip.rw',        '0788100004', @pwd, 4, 1,  'rw', 'active', 'fully_verified', 'female'),
(UUID(), 'Eric',    'Nkurunziza','exporter@saip.rw',  '0788100005', @pwd, 5, 1,  'en', 'active', 'fully_verified', 'male'),
(UUID(), 'Marie',   'Ingabire', 'researcher@saip.rw', '0788100006', @pwd, 6, 1,  'en', 'active', 'fully_verified', 'female'),
(UUID(), 'Admin',   'SAIP',     'admin@saip.rw',      '0788100007', @pwd, 7, 1,  'en', 'active', 'fully_verified', 'other');

-- ============ USER PREFERENCES ============
INSERT IGNORE INTO user_preferences (user_id, language_code)
SELECT id, preferred_language FROM users;

-- ============ USER PROFILES ============
INSERT IGNORE INTO user_profiles (user_id)
SELECT id FROM users;

-- ============ VERIFIED BADGES ============
INSERT IGNORE INTO verified_users (user_id, badge_type)
SELECT id, 'officer' FROM users WHERE role_id IN (3,4,7);

-- ============ FARMS ============
INSERT INTO farms (uuid, user_id, name, district_id, size_ha, soil_type, irrigation_type, lat, lng)
SELECT UUID(), u.id, CONCAT(u.first_name, '''s Farm'), u.district_id, 2.5, 'loam', 'drip', -1.9441, 30.0619
FROM users u WHERE u.role_id = 1;

-- ============ FARM CROPS ============
INSERT INTO farm_crops (farm_id, crop_id, season, year, planted_date, expected_harvest, area_ha, growth_stage, health_status)
SELECT f.id, 1, 'A', 2024, '2024-09-15', '2025-01-15', 2.5, 'vegetative', 'good'
FROM farms f LIMIT 1;

-- ============ PRODUCTION ============
INSERT INTO production (user_id, crop_id, season, year, yield_kg, revenue_rwf, harvest_date)
SELECT u.id, 1, 'A', 2024, 45000, 15750000, '2025-01-10'
FROM users u WHERE u.role_id = 1;

INSERT INTO production (user_id, crop_id, season, year, yield_kg, revenue_rwf, harvest_date)
SELECT u.id, 2, 'B', 2024, 18000, 11160000, '2025-05-20'
FROM users u WHERE u.role_id = 1;

-- ============ SELLERS ============
INSERT IGNORE INTO sellers (user_id, business_name, seller_type, district_id, is_verified, rating, total_sales)
SELECT id, CONCAT(first_name, '''s Farm'), 'farmer', district_id, 1, 4.5, 12
FROM users WHERE role_id = 1;

-- ============ PRODUCTS ============
INSERT INTO products (uuid, seller_id, crop_id, category_id, title, description, unit, quantity_available, price_per_unit, quality_grade, district_id, is_organic, status)
SELECT
    UUID(),
    (SELECT id FROM sellers LIMIT 1),
    1, 1,
    'Premium Maize — Grade A',
    'Freshly harvested maize from Bugesera. Stored in hermetic bags. Delivery available.',
    'kg', 500, 350, 'A', 17, 1, 'active';

INSERT INTO products (uuid, seller_id, crop_id, category_id, title, description, unit, quantity_available, price_per_unit, quality_grade, district_id, status)
SELECT
    UUID(),
    (SELECT id FROM sellers LIMIT 1),
    2, 4,
    'Red Beans — High Quality',
    'Fresh red beans from Season B harvest. Excellent cooking quality.',
    'kg', 300, 620, 'A', 17, 'active';

-- ============ BUYERS ============
INSERT IGNORE INTO buyers (user_id, buyer_type, district_id, is_verified)
SELECT id, 'exporter', district_id, 1 FROM users WHERE role_id = 5;

-- ============ EXPORT REQUESTS ============
INSERT INTO export_requests (uuid, buyer_id, category_id, quantity_needed, unit, target_price, destination_country, required_by, certifications, description, status)
SELECT
    UUID(),
    (SELECT id FROM users WHERE role_id = 5 LIMIT 1),
    6,
    10.00, 'ton', 500000, 'France', '2025-06-30',
    'Organic, Fair Trade',
    'Looking for Grade A coffee for European market. Must have organic certification.',
    'open';

-- ============ NOTIFICATIONS ============
INSERT INTO notifications (user_id, type, priority, title, body, icon)
SELECT id, 'system', 'green', 'Welcome to SAIP', 'Your account is ready. Explore the dashboard to get started.', '🎉'
FROM users LIMIT 5;

INSERT INTO notifications (user_id, type, priority, title, body, icon)
SELECT id, 'weather', 'yellow', 'Heavy rainfall expected', 'Rain expected in your district tomorrow. Consider delaying fertilizer application.', '🌧️'
FROM users WHERE role_id = 1;

-- ============ GOVERNMENT ALERT ============
INSERT INTO government_alerts (uuid, alert_type, severity, title, body, district_id, action_recommended, published_by, is_active)
VALUES
(UUID(), 'pest', 'high', 'Fall Armyworm outbreak detected',
 'Fall Armyworm larvae found in 12 farms in Bugesera district. Immediate action recommended.',
 17, 'Apply neem oil or Emamectin benzoate. Report any new infestations to your district officer.',
 (SELECT id FROM users WHERE role_id = 4 LIMIT 1), 1);

-- ============ NISR DATASET ROWS ============
SET @ds_id = (SELECT id FROM nisr_datasets WHERE slug = 'agri-household-2024');

INSERT INTO nisr_dataset_rows (dataset_id, row_index, data, district_id, year)
SELECT @ds_id, 0, JSON_OBJECT('district','Bugesera','crop','Maize','season','A','area_ha',120,'yield_kg',45000,'households',340,'year',2024), 17, 2024;

INSERT INTO nisr_dataset_rows (dataset_id, row_index, data, district_id, year)
SELECT @ds_id, 1, JSON_OBJECT('district','Musanze','crop','Potatoes','season','A','area_ha',85,'yield_kg',120000,'households',520,'year',2024), 7, 2024;

INSERT INTO nisr_dataset_rows (dataset_id, row_index, data, district_id, year)
SELECT @ds_id, 2, JSON_OBJECT('district','Nyagatare','crop','Maize','season','A','area_ha',200,'yield_kg',85000,'households',610,'year',2024), 22, 2024;

INSERT INTO nisr_dataset_rows (dataset_id, row_index, data, district_id, year)
SELECT @ds_id, 3, JSON_OBJECT('district','Huye','crop','Beans','season','B','area_ha',60,'yield_kg',18000,'households',180,'year',2024), 10, 2024;

INSERT INTO nisr_dataset_rows (dataset_id, row_index, data, district_id, year)
SELECT @ds_id, 4, JSON_OBJECT('district','Kigali','crop','Tomatoes','season','A','area_ha',15,'yield_kg',22500,'households',45,'year',2024), 1, 2024;

-- Update row counts
UPDATE nisr_datasets SET row_count = (SELECT COUNT(*) FROM nisr_dataset_rows WHERE dataset_id = @ds_id) WHERE id = @ds_id;

SELECT '✅ Seed complete' AS status;