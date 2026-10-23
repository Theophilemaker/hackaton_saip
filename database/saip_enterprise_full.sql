-- =====================================================================
-- SAIP — Smart Agriculture Intelligence Platform
-- Complete Enterprise Database — All Phases Consolidated
-- Version: 2.0 Enterprise Edition
-- Engine: InnoDB  |  Charset: utf8mb4  |  Collation: utf8mb4_unicode_ci
--
-- Usage:
--   mysql -u root -p < saip_enterprise_full.sql
--
-- Or in phpMyAdmin:
--   Create DB "saip_enterprise" → Import → Select this file → Go
--
-- Default test accounts (password: ChangeMe123!):
--   farmer@saip.rw · coop@saip.rw · officer@saip.rw · gov@saip.rw
--   exporter@saip.rw · researcher@saip.rw · admin@saip.rw
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+02:00";

CREATE DATABASE IF NOT EXISTS saip_enterprise
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE saip_enterprise;

-- =====================================================================
-- MODULE 1: RBAC & USER MANAGEMENT
-- =====================================================================

CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO roles (id, name, description) VALUES
(1,'Farmer','Individual farmer'),
(2,'Cooperative Leader','Manages a cooperative'),
(3,'Agricultural Officer','District monitoring officer'),
(4,'Government Officer','National analytics access'),
(5,'Exporter','Bulk buyer / exporter'),
(6,'Researcher','Data analyst / university'),
(7,'Admin','Full system administrator');

CREATE TABLE IF NOT EXISTS permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    permission_name VARCHAR(150) UNIQUE NOT NULL,
    module VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO permissions (permission_name, module, description) VALUES
('manage_users','users','Create, edit, suspend users'),
('manage_roles','users','Assign roles and permissions'),
('view_dashboard','dashboard','Access personal dashboard'),
('upload_dataset','data','Upload NISR or custom datasets'),
('manage_marketplace','marketplace','Moderate marketplace listings'),
('verify_posts','chat','Verify answers in Farmer Connect'),
('manage_cooperatives','cooperative','Manage cooperatives'),
('view_national_analytics','government','Access national KPI dashboard'),
('generate_reports','government','Generate PDF/Excel reports'),
('publish_alerts','government','Publish government alerts'),
('manage_storage','storage','Manage warehouses and bins'),
('manage_logistics','logistics','Manage vehicles and transport'),
('manage_ai_models','ai','Manage AI model versions'),
('view_audit_logs','security','View audit and login logs'),
('manage_system','system','Full system administration');

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id)
) ENGINE=InnoDB;

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions WHERE permission_name IN
('view_dashboard','manage_marketplace','verify_posts','manage_storage','manage_logistics');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE permission_name IN
('view_dashboard','manage_marketplace','verify_posts','manage_cooperatives','manage_storage','manage_logistics','generate_reports');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE permission_name IN
('view_dashboard','verify_posts','manage_cooperatives','view_national_analytics','generate_reports','publish_alerts','view_audit_logs');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE permission_name IN
('view_dashboard','view_national_analytics','generate_reports','publish_alerts','view_audit_logs','upload_dataset');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE permission_name IN
('view_dashboard','manage_marketplace','generate_reports','manage_logistics');
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 6, id FROM permissions WHERE permission_name IN
('view_dashboard','upload_dataset','view_national_analytics','generate_reports');
INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT 7, id FROM permissions;

-- =====================================================================
-- MODULE 2: RWANDA GEOGRAPHY
-- =====================================================================

CREATE TABLE IF NOT EXISTS provinces (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) UNIQUE,
    name VARCHAR(100) NOT NULL,
    name_rw VARCHAR(100),
    lat DECIMAL(10,7),
    lng DECIMAL(10,7)
) ENGINE=InnoDB;

INSERT IGNORE INTO provinces (id, code, name, name_rw, lat, lng) VALUES
(1,'KGL','Kigali City','Umujyi wa Kigali',-1.9441,30.0619),
(2,'NOR','Northern Province','Intara y\'Amajyaruguru',-1.5000,29.9000),
(3,'SOU','Southern Province','Intara y\'Amajyepfo',-2.3000,29.7000),
(4,'EAS','Eastern Province','Intara y\'Iburasirazuba',-1.9000,30.6000),
(5,'WES','Western Province','Intara y\'Iburengerazuba',-2.0000,29.3000);

CREATE TABLE IF NOT EXISTS districts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    province_id INT NOT NULL,
    code VARCHAR(10) UNIQUE,
    name VARCHAR(100) NOT NULL,
    name_rw VARCHAR(100),
    lat DECIMAL(10,7),
    lng DECIMAL(10,7)
) ENGINE=InnoDB;

INSERT IGNORE INTO districts (id, province_id, code, name, lat, lng) VALUES
(1,1,'KGL01','Gasabo',-1.9167,30.1333),
(2,1,'KGL02','Kicukiro',-1.9833,30.1000),
(3,1,'KGL03','Nyarugenge',-1.9500,30.0500),
(4,2,'NOR01','Burera',-1.4667,29.8333),
(5,2,'NOR02','Gakenke',-1.7000,29.7833),
(6,2,'NOR03','Gicumbi',-1.5833,30.1000),
(7,2,'NOR04','Musanze',-1.4997,29.6349),
(8,2,'NOR05','Rulindo',-1.7333,30.0667),
(9,3,'SOU01','Gisagara',-2.5500,29.8333),
(10,3,'SOU02','Huye',-2.5967,29.7394),
(11,3,'SOU03','Kamonyi',-2.0000,29.9000),
(12,3,'SOU04','Muhanga',-2.0833,29.7500),
(13,3,'SOU05','Nyamagabe',-2.4667,29.4667),
(14,3,'SOU06','Nyanza',-2.3500,29.7500),
(15,3,'SOU07','Nyaruguru',-2.6167,29.5333),
(16,3,'SOU08','Ruhango',-2.2333,29.7833),
(17,4,'EAS01','Bugesera',-2.2167,30.1500),
(18,4,'EAS02','Gatsibo',-1.6000,30.4333),
(19,4,'EAS03','Kayonza',-1.8833,30.6167),
(20,4,'EAS04','Kirehe',-2.2833,30.7167),
(21,4,'EAS05','Ngoma',-2.1500,30.4667),
(22,4,'EAS06','Nyagatare',-1.3000,30.3333),
(23,4,'EAS07','Rwamagana',-1.9500,30.4333),
(24,5,'WES01','Karongi',-2.0000,29.3833),
(25,5,'WES02','Ngororero',-1.8667,29.6167),
(26,5,'WES03','Nyabihu',-1.6667,29.5000),
(27,5,'WES04','Nyamasheke',-2.3333,29.1333),
(28,5,'WES05','Rubavu',-1.6833,29.2667),
(29,5,'WES06','Rusizi',-2.4833,29.0000),
(30,5,'WES07','Rutsiro',-1.9333,29.3333);

CREATE TABLE IF NOT EXISTS sectors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    district_id INT NOT NULL,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cells (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sector_id INT NOT NULL,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS villages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cell_id INT NOT NULL,
    name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 3: LOCALIZATION
-- =====================================================================

CREATE TABLE IF NOT EXISTS languages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(10) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    native_name VARCHAR(100),
    flag_icon VARCHAR(255),
    is_default TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    is_rtl TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

INSERT IGNORE INTO languages (code, name, native_name, flag_icon, is_default) VALUES
('rw','Kinyarwanda','Ikinyarwanda','🇷🇼',1),
('en','English','English','🇬🇧',0),
('fr','French','Français','🇫🇷',0),
('sw','Kiswahili','Kiswahili','🇹🇿',0);

CREATE TABLE IF NOT EXISTS translation_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100)
) ENGINE=InnoDB;

INSERT IGNORE INTO translation_categories (slug, name) VALUES
('navigation','Navigation'),('dashboard','Dashboard'),('auth','Authentication'),
('weather','Weather'),('crops','Crops'),('chat','Chat'),('marketplace','Marketplace'),
('cooperative','Cooperative'),('government','Government'),('storage','Storage'),
('logistics','Logistics'),('notifications','Notifications'),('common','Common'),
('nisr','NISR'),('forum','Forum'),('admin','Admin');

CREATE TABLE IF NOT EXISTS translations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    translation_key VARCHAR(200) NOT NULL,
    category_id INT NULL,
    lang_code VARCHAR(10) NOT NULL,
    translated_text TEXT,
    updated_by BIGINT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_key_lang (translation_key, lang_code),
    INDEX idx_key (translation_key)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS auto_translation_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    source_text TEXT,
    source_lang VARCHAR(5),
    target_lang VARCHAR(5),
    translated_text TEXT,
    provider VARCHAR(50),
    confidence DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 4: USERS
-- =====================================================================

CREATE TABLE IF NOT EXISTS users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    role_id INT DEFAULT 1,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    gender ENUM('male','female','other','prefer_not_to_say') DEFAULT 'prefer_not_to_say',
    date_of_birth DATE,
    district_id INT,
    sector_id INT,
    cell_id INT,
    village_id INT,
    preferred_language VARCHAR(10) DEFAULT 'en',
    profile_photo VARCHAR(500),
    verification_status ENUM('unverified','email_verified','phone_verified','fully_verified') DEFAULT 'unverified',
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_role (role_id),
    INDEX idx_district (district_id),
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_profiles (
    user_id BIGINT PRIMARY KEY,
    occupation VARCHAR(150),
    years_experience INT DEFAULT 0,
    cooperative_id BIGINT NULL,
    biography TEXT,
    website VARCHAR(255),
    social_links JSON,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_preferences (
    user_id BIGINT PRIMARY KEY,
    language_code VARCHAR(5) DEFAULT 'en',
    theme_name VARCHAR(50) DEFAULT 'green-harvest',
    primary_color VARCHAR(10) DEFAULT '#16A34A',
    secondary_color VARCHAR(10) DEFAULT '#22C55E',
    accent_color VARCHAR(10) DEFAULT '#84CC16',
    background_color VARCHAR(10) DEFAULT '#081C15',
    card_style ENUM('glass','material','flat') DEFAULT 'glass',
    icon_style ENUM('outline','filled','rounded','duotone') DEFAULT 'outline',
    border_radius INT DEFAULT 20,
    animation_level ENUM('none','low','medium','high') DEFAULT 'medium',
    font_size ENUM('small','medium','large','xlarge') DEFAULT 'medium',
    dashboard_layout JSON,
    dark_mode TINYINT(1) DEFAULT 1,
    high_contrast TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_sessions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    session_token CHAR(64) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent VARCHAR(300),
    device_type VARCHAR(50),
    is_active TINYINT(1) DEFAULT 1,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_devices (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    device_name VARCHAR(150),
    device_type ENUM('desktop','mobile','tablet','other') DEFAULT 'desktop',
    fingerprint CHAR(64),
    last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    trusted TINYINT(1) DEFAULT 0,
    UNIQUE KEY uq_device (user_id, fingerprint),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_2fa (
    user_id BIGINT PRIMARY KEY,
    secret VARCHAR(100),
    is_enabled TINYINT(1) DEFAULT 0,
    backup_codes JSON,
    enabled_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_2fa_backup_codes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    code_hash CHAR(64) NOT NULL,
    used TINYINT(1) DEFAULT 0,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_hash (token_hash)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS email_verifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    email VARCHAR(150) NOT NULL,
    token_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    UNIQUE KEY uq_hash (token_hash),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS phone_verifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    attempts INT DEFAULT 0,
    verified_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS remember_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    selector CHAR(32) UNIQUE NOT NULL,
    validator_hash CHAR(64) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS login_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NULL,
    email_attempted VARCHAR(150),
    ip_address VARCHAR(45),
    user_agent VARCHAR(300),
    device_fingerprint VARCHAR(64),
    status ENUM('success','failed','locked','logout') DEFAULT 'success',
    failure_reason VARCHAR(200),
    login_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_time (user_id, login_at),
    INDEX idx_email_time (email_attempted, login_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS failed_logins (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(150),
    attempts INT DEFAULT 1,
    first_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_attempt_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    locked_until DATETIME NULL,
    INDEX idx_ip_time (ip_address, last_attempt_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS api_keys (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    key_name VARCHAR(100) NOT NULL,
    api_key CHAR(64) UNIQUE NOT NULL,
    permissions JSON,
    rate_limit_per_min INT DEFAULT 60,
    is_active TINYINT(1) DEFAULT 1,
    last_used_at TIMESTAMP NULL,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rate_limits (
    rate_key VARCHAR(150) PRIMARY KEY,
    hits INT DEFAULT 0,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_window (window_start)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS verified_users (
    user_id BIGINT PRIMARY KEY,
    verified_by BIGINT,
    badge_type ENUM('officer','expert','coop_leader','researcher') DEFAULT 'officer',
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 5: AGRICULTURE
-- =====================================================================

CREATE TABLE IF NOT EXISTS crops (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    scientific_name VARCHAR(200),
    category VARCHAR(100),
    icon VARCHAR(20) DEFAULT '🌾',
    growing_days INT DEFAULT 90,
    optimal_temp_min DECIMAL(5,2),
    optimal_temp_max DECIMAL(5,2),
    optimal_rainfall_mm DECIMAL(8,2),
    image_url VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT IGNORE INTO crops (id, name, scientific_name, category, icon, growing_days, optimal_temp_min, optimal_temp_max, optimal_rainfall_mm) VALUES
(1,'Maize','Zea mays','Cereal','🌽',120,18,32,500),
(2,'Beans','Phaseolus vulgaris','Legume','🫘',90,15,28,400),
(3,'Rice','Oryza sativa','Cereal','🌾',130,20,35,1200),
(4,'Wheat','Triticum aestivum','Cereal','🌾',110,12,25,450),
(5,'Potatoes','Solanum tuberosum','Tuber','🥔',90,10,22,600),
(6,'Cassava','Manihot esculenta','Tuber','🍠',270,20,30,800),
(7,'Banana','Musa spp.','Fruit','🍌',300,20,32,1200),
(8,'Coffee','Coffea arabica','Beverage','☕',730,15,24,1500),
(9,'Tea','Camellia sinensis','Beverage','🍵',1095,12,25,1800),
(10,'Tomatoes','Solanum lycopersicum','Vegetable','🍅',75,15,28,500),
(11,'Onions','Allium cepa','Vegetable','🧅',120,10,25,500),
(12,'Sorghum','Sorghum bicolor','Cereal','🌾',120,20,35,500);

CREATE TABLE IF NOT EXISTS farms (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    user_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    district_id INT,
    sector VARCHAR(100),
    cell VARCHAR(100),
    village VARCHAR(100),
    size_ha DECIMAL(8,2),
    soil_type ENUM('clay','sandy','loam','silt','peat','chalk') DEFAULT 'loam',
    irrigation_type ENUM('none','drip','sprinkler','furrow','flood') DEFAULT 'none',
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS farm_images (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    image_url VARCHAR(500),
    caption VARCHAR(300),
    taken_at DATE,
    uploaded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS farm_crops (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    crop_id INT NOT NULL,
    season VARCHAR(20),
    year INT,
    planted_date DATE,
    expected_harvest DATE,
    area_ha DECIMAL(8,2),
    quantity_planted_kg DECIMAL(10,2),
    growth_stage ENUM('planting','germination','vegetative','flowering','fruiting','maturity','harvested') DEFAULT 'planting',
    health_status ENUM('excellent','good','fair','poor','critical') DEFAULT 'good',
    expected_yield_kg DECIMAL(12,2),
    actual_yield_kg DECIMAL(12,2) NULL,
    notes TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crops(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS growth_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_crop_id BIGINT NOT NULL,
    stage VARCHAR(50),
    note TEXT,
    image_url VARCHAR(500),
    recorded_by BIGINT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_crop_id) REFERENCES farm_crops(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS production (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    farm_id BIGINT,
    crop_id INT NOT NULL,
    season VARCHAR(20),
    year INT,
    yield_kg DECIMAL(12,2),
    revenue_rwf DECIMAL(14,2),
    harvest_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_crop (user_id, crop_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crops(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS irrigation (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    irrigation_type ENUM('drip','sprinkler','furrow','flood','manual') NOT NULL,
    water_volume_m3 DECIMAL(10,2),
    start_time DATETIME,
    end_time DATETIME,
    duration_minutes INT,
    cost_rwf DECIMAL(10,2),
    notes TEXT,
    recorded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS soil_tests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_id BIGINT NOT NULL,
    ph DECIMAL(4,2),
    nitrogen_ppm DECIMAL(8,2),
    phosphorus_ppm DECIMAL(8,2),
    potassium_ppm DECIMAL(8,2),
    organic_matter_pct DECIMAL(5,2),
    moisture_pct DECIMAL(5,2),
    tested_at DATE,
    notes TEXT,
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fertilizers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('organic','inorganic','bio','mixed') DEFAULT 'inorganic',
    npk_ratio VARCHAR(20),
    description TEXT,
    recommended_crops JSON,
    price_per_kg DECIMAL(10,2)
) ENGINE=InnoDB;

INSERT IGNORE INTO fertilizers (name, type, npk_ratio, description) VALUES
('NPK 17-17-17','inorganic','17-17-17','Balanced fertilizer for all crops'),
('Urea','inorganic','46-0-0','High nitrogen for vegetative growth'),
('DAP','inorganic','18-46-0','Phosphorus-rich for root development'),
('Compost','organic','VAR','Organic matter for soil health'),
('Lime','inorganic','VAR','Corrects soil acidity');

CREATE TABLE IF NOT EXISTS crop_calendar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    season VARCHAR(20),
    activity ENUM('land_prep','planting','weeding','fertilizing','pest_control','harvesting') NOT NULL,
    start_month INT,
    end_month INT,
    description TEXT,
    FOREIGN KEY (crop_id) REFERENCES crops(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS diseases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    crop_id INT,
    pathogen_type ENUM('fungal','bacterial','viral','pest','deficiency') DEFAULT 'fungal',
    symptoms TEXT,
    treatment TEXT,
    prevention TEXT,
    severity ENUM('low','medium','high') DEFAULT 'medium',
    image_url VARCHAR(500)
) ENGINE=InnoDB;

INSERT IGNORE INTO diseases (name, crop_id, pathogen_type, symptoms, treatment, prevention, severity) VALUES
('Fall Armyworm',1,'pest','Ragged holes in leaves, sawdust-like frass.','Apply neem oil or Emamectin benzoate.','Rotate crops, plant early.','high'),
('Potato Blight',5,'fungal','Dark water-soaked spots, white mold.','Copper fungicide every 7-10 days.','Avoid overhead irrigation.','high'),
('Banana Wilt',7,'fungal','Yellowing older leaves, wilting.','Remove infected plants. Apply Trichoderma.','Use clean suckers.','high'),
('Maize Streak Virus',1,'viral','Yellow streaks parallel to veins.','Remove infected plants.','Plant resistant varieties.','high'),
('Tomato Early Blight',10,'fungal','Brown concentric rings on lower leaves.','Apply chlorothalonil.','Mulch, rotate crops.','medium'),
('Coffee Leaf Rust',8,'fungal','Orange-yellow powdery spots.','Copper-based fungicide.','Resistant varieties.','high'),
('Bean Anthracnose',2,'fungal','Dark sunken lesions on pods.','Treat seeds. Apply fungicide.','Crop rotation.','medium');

CREATE TABLE IF NOT EXISTS pests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    crop_id INT NULL,
    pest_type ENUM('insect','rodent','bird','nematode','weed') DEFAULT 'insect',
    symptoms TEXT,
    control_methods TEXT,
    severity ENUM('low','medium','high') DEFAULT 'medium',
    image_url VARCHAR(500)
) ENGINE=InnoDB;

INSERT IGNORE INTO pests (name, crop_id, pest_type, symptoms, control_methods, severity) VALUES
('Fall Armyworm',1,'insect','Ragged holes, frass in whorl','Neem oil, Emamectin benzoate','high'),
('Maize Weevil',1,'insect','Bore holes in stored grain','Hermetic storage, fumigation','medium'),
('Coffee Berry Borer',8,'insect','Holes in coffee cherries','Field sanitation, Beauveria bassiana','high'),
('Banana Weevil',7,'insect','Tunnels in pseudostem','Clean suckers, trapping','high');

CREATE TABLE IF NOT EXISTS storage_facilities (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    district_id INT,
    facility_type ENUM('cooperative','private','shared','government') DEFAULT 'private',
    capacity_kg DECIMAL(14,2),
    temperature_c DECIMAL(5,2),
    moisture_pct DECIMAL(5,2),
    pest_risk ENUM('low','medium','high') DEFAULT 'low',
    manager_id BIGINT,
    status ENUM('active','maintenance','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS extension_visits (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    officer_id BIGINT NOT NULL,
    farm_id BIGINT,
    cooperative_id BIGINT,
    visit_date DATE,
    purpose VARCHAR(200),
    findings TEXT,
    recommendations TEXT,
    follow_up_date DATE,
    status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (officer_id) REFERENCES users(id),
    FOREIGN KEY (farm_id) REFERENCES farms(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS district_production (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    district_id INT NOT NULL,
    crop_id INT NOT NULL,
    season VARCHAR(20),
    year INT,
    yield_tons DECIMAL(12,2) DEFAULT 0,
    area_ha DECIMAL(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_district_crop (district_id, crop_id)
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 6: WEATHER & GIS
-- =====================================================================

CREATE TABLE IF NOT EXISTS weather_data (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    district_id INT NOT NULL,
    temperature DECIMAL(5,2),
    feels_like DECIMAL(5,2),
    humidity INT,
    wind_speed DECIMAL(5,2),
    rainfall_mm DECIMAL(8,2) DEFAULT 0,
    condition_main VARCHAR(50),
    condition_desc VARCHAR(150),
    icon VARCHAR(20),
    uv_index DECIMAL(4,2) DEFAULT 0,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    forecast_date DATE,
    is_forecast TINYINT(1) DEFAULT 0,
    INDEX idx_district_date (district_id, forecast_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS weather_alerts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    district_id INT,
    alert_type ENUM('flood','drought','heavy_rain','frost','heatwave') NOT NULL,
    severity ENUM('low','medium','high') DEFAULT 'medium',
    message TEXT,
    starts_at DATETIME,
    expires_at DATETIME,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rainfall_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    district_id INT NOT NULL,
    record_date DATE NOT NULL,
    rainfall_mm DECIMAL(8,2),
    source VARCHAR(50) DEFAULT 'manual',
    INDEX idx_district_date (district_id, record_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS climate_alerts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    district_id INT NULL,
    alert_type ENUM('flood','drought','heavy_rain','frost','heatwave','strong_wind') NOT NULL,
    severity ENUM('low','medium','high','critical') DEFAULT 'medium',
    description TEXT,
    expected_from DATE,
    expected_to DATE,
    source VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gis_layers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    layer_name VARCHAR(100) NOT NULL,
    layer_type ENUM('production','rainfall','irrigation','pest','soil') NOT NULL,
    geojson_url VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS satellite_images (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    district_id INT NULL,
    image_url VARCHAR(500) NOT NULL,
    image_type ENUM('ndvi','rgb','thermal','radar') DEFAULT 'ndvi',
    ndvi_avg DECIMAL(5,3),
    captured_at DATE,
    cloud_coverage_pct DECIMAL(5,2),
    source VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 7: AI & ANALYTICS
-- =====================================================================

CREATE TABLE IF NOT EXISTS model_versions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(100) NOT NULL,
    model_type ENUM('yield','price','disease','weather','translation','risk') NOT NULL,
    version VARCHAR(20) NOT NULL,
    accuracy DECIMAL(5,2),
    trained_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    notes TEXT
) ENGINE=InnoDB;

INSERT IGNORE INTO model_versions (model_name, model_type, version, accuracy, is_active) VALUES
('Maize Yield RF','yield','v1.0',87.20,1),
('Price Prophet','price','v1.0',81.50,1),
('Disease CNN','disease','v1.0',92.30,1),
('Weather ARIMA','weather','v1.0',78.90,1);

CREATE TABLE IF NOT EXISTS ai_predictions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NULL,
    model_id INT NULL,
    prediction_type ENUM('yield','price','disease','weather','risk','recommendation') NOT NULL,
    scope JSON,
    input_data JSON,
    output_data JSON,
    confidence DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type_time (prediction_type, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS prediction_history (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    prediction_id BIGINT NOT NULL,
    actual_value DECIMAL(14,2),
    accuracy_pct DECIMAL(5,2),
    evaluated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prediction_id) REFERENCES ai_predictions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS disease_detections (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    user_id BIGINT NOT NULL,
    farm_crop_id BIGINT NULL,
    image_url VARCHAR(500) NOT NULL,
    detected_disease_id INT NULL,
    detected_name VARCHAR(200),
    confidence DECIMAL(5,2),
    severity ENUM('low','medium','high') DEFAULT 'medium',
    treatment TEXT,
    recommendation TEXT,
    status ENUM('pending','confirmed','resolved','false_positive') DEFAULT 'pending',
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS yield_predictions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_crop_id BIGINT NOT NULL,
    predicted_yield_kg DECIMAL(12,2),
    confidence DECIMAL(5,2),
    model_version VARCHAR(20) DEFAULT 'v1.0',
    features JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS price_forecasts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    district_id INT NULL,
    forecast_date DATE NOT NULL,
    predicted_price DECIMAL(12,2),
    lower_bound DECIMAL(12,2),
    upper_bound DECIMAL(12,2),
    confidence DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_crop_date (crop_id, forecast_date)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fertilizer_recommendations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    farm_crop_id BIGINT NOT NULL,
    fertilizer_type VARCHAR(100),
    quantity_kg DECIMAL(10,2),
    application_method VARCHAR(200),
    timing VARCHAR(100),
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recommendation_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NULL,
    recommendation_type VARCHAR(100),
    input_context JSON,
    recommendation TEXT,
    accepted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_time (user_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS price_recommendations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    crop_id INT NOT NULL,
    district_id INT,
    suggested_price DECIMAL(12,2),
    min_price DECIMAL(12,2),
    max_price DECIMAL(12,2),
    confidence DECIMAL(4,2),
    basis VARCHAR(200),
    computed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_crop_district (crop_id, district_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_conversations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    user_id BIGINT NOT NULL,
    title VARCHAR(200) DEFAULT 'New Chat',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ai_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT NOT NULL,
    role ENUM('user','assistant') NOT NULL,
    content TEXT,
    language VARCHAR(5) DEFAULT 'en',
    tokens_used INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 8: COMMUNICATION (Chat + Feed + Forums)
-- =====================================================================

CREATE TABLE IF NOT EXISTS conversations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    type ENUM('direct','group') DEFAULT 'direct',
    title VARCHAR(150) NULL,
    avatar VARCHAR(255) NULL,
    created_by BIGINT,
    last_message_id BIGINT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS conversation_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role ENUM('owner','admin','member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_read_at TIMESTAMP NULL,
    is_muted TINYINT(1) DEFAULT 0,
    UNIQUE KEY unique_member (conversation_id, user_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    conversation_id BIGINT NOT NULL,
    sender_id BIGINT NOT NULL,
    message_type ENUM('text','image','video','audio','file','location','system') DEFAULT 'text',
    content TEXT,
    media_url VARCHAR(500) NULL,
    media_meta JSON NULL,
    location_lat DECIMAL(10,7) NULL,
    location_lng DECIMAL(10,7) NULL,
    reply_to_id BIGINT NULL,
    is_translated TINYINT(1) DEFAULT 0,
    original_language VARCHAR(5) NULL,
    status ENUM('sent','delivered','read') DEFAULT 'sent',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    edited_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    INDEX idx_conv_time (conversation_id, sent_at),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS message_reads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_read (message_id, user_id),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS message_reactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    emoji VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_reaction (message_id, user_id, emoji),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS message_translations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT NOT NULL,
    target_lang VARCHAR(5) NOT NULL,
    translated_text TEXT,
    provider VARCHAR(50) DEFAULT 'local',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_translation (message_id, target_lang),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS voice_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT NOT NULL,
    audio_url VARCHAR(500) NOT NULL,
    duration_seconds INT,
    waveform JSON,
    transcript TEXT,
    language VARCHAR(5),
    UNIQUE KEY uq_message (message_id),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS voice_translations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT NOT NULL,
    source_language VARCHAR(5),
    target_language VARCHAR(5),
    original_transcript TEXT,
    translated_transcript TEXT,
    provider VARCHAR(50) DEFAULT 'browser_api',
    confidence DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_msg_lang (message_id, target_language),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_groups (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    group_name VARCHAR(150) NOT NULL,
    description TEXT,
    group_type ENUM('district','crop','cooperative','officer','announcement','custom') DEFAULT 'custom',
    reference_id INT NULL,
    created_by BIGINT NOT NULL,
    profile_picture_url VARCHAR(500),
    is_public TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_group_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    group_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role ENUM('admin','moderator','member') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_group_user (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES chat_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_presence (
    user_id BIGINT PRIMARY KEY,
    status ENUM('online','away','offline') DEFAULT 'offline',
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS typing_indicators (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    conversation_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_typing (conversation_id, user_id),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feed_posts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    user_id BIGINT NOT NULL,
    post_type ENUM('text','image','video','question','tip','announcement') DEFAULT 'text',
    title VARCHAR(200) NULL,
    content TEXT,
    media_url VARCHAR(500) NULL,
    district_id INT NULL,
    crop_id INT NULL,
    is_verified_answer TINYINT(1) DEFAULT 0,
    verified_by BIGINT NULL,
    likes_count INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_feed_time (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feed_likes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES feed_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feed_comments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    content TEXT,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES feed_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_channels (
    id INT PRIMARY KEY AUTO_INCREMENT,
    channel_type ENUM('district','crop','cooperative','announcement') NOT NULL,
    reference_id INT NULL,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(300),
    icon VARCHAR(20) DEFAULT '💬',
    conversation_id BIGINT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS officer_verifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    verifier_id BIGINT NOT NULL,
    entity_type ENUM('feed_post','feed_comment','forum_answer','product') NOT NULL,
    entity_id BIGINT NOT NULL,
    note VARCHAR(500),
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_verify (entity_type, entity_id, verifier_id),
    INDEX idx_entity (entity_type, entity_id),
    FOREIGN KEY (verifier_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS announcements (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    audience ENUM('all','farmers','coops','officers','district') DEFAULT 'all',
    district_id INT NULL,
    priority ENUM('normal','high','urgent') DEFAULT 'normal',
    created_by BIGINT,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 9: MARKETPLACE
-- =====================================================================

CREATE TABLE IF NOT EXISTS product_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(20) DEFAULT '🌾',
    parent_id INT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO product_categories (name, slug, icon) VALUES
('Grains','grains','🌾'),
('Vegetables','vegetables','🥬'),
('Fruits','fruits','🍎'),
('Legumes','legumes','🫘'),
('Tubers','tubers','🥔'),
('Coffee & Tea','coffee-tea','☕'),
('Livestock','livestock','🐄'),
('Dairy','dairy','🥛'),
('Seeds & Inputs','inputs','🌱'),
('Other','other','📦');

CREATE TABLE IF NOT EXISTS sellers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    business_name VARCHAR(200),
    seller_type ENUM('farmer','cooperative','trader','exporter') DEFAULT 'farmer',
    district_id INT,
    description TEXT,
    is_verified TINYINT(1) DEFAULT 0,
    verified_at TIMESTAMP NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    total_sales INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS buyers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,
    buyer_type ENUM('individual','restaurant','hotel','exporter','processor','retailer') DEFAULT 'individual',
    district_id INT,
    preferred_products JSON,
    total_purchases INT DEFAULT 0,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    seller_id BIGINT NOT NULL,
    crop_id INT NULL,
    category_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    unit ENUM('kg','ton','bag','piece','liter','bunch') DEFAULT 'kg',
    quantity_available DECIMAL(12,2) NOT NULL DEFAULT 0,
    min_order_qty DECIMAL(12,2) DEFAULT 1,
    price_per_unit DECIMAL(12,2) NOT NULL,
    currency VARCHAR(5) DEFAULT 'RWF',
    quality_grade ENUM('A','B','C','Organic','Standard') DEFAULT 'Standard',
    district_id INT,
    main_image VARCHAR(500),
    images JSON,
    is_organic TINYINT(1) DEFAULT 0,
    is_export_ready TINYINT(1) DEFAULT 0,
    status ENUM('active','sold_out','hidden','pending_review') DEFAULT 'active',
    views_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_cat (status, category_id),
    INDEX idx_district (district_id),
    FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    order_number VARCHAR(30) UNIQUE NOT NULL,
    buyer_id BIGINT NOT NULL,
    seller_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total_amount DECIMAL(14,2) NOT NULL,
    currency VARCHAR(5) DEFAULT 'RWF',
    delivery_method ENUM('pickup','delivery') DEFAULT 'delivery',
    delivery_address VARCHAR(300),
    delivery_district INT,
    buyer_notes TEXT,
    status ENUM('pending','confirmed','paid','ready','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
    payment_status ENUM('unpaid','paid','escrow','refunded') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_buyer (buyer_id, status),
    INDEX idx_seller (seller_id, status),
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES sellers(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(14,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    order_id BIGINT NOT NULL,
    method ENUM('mtn_momo','airtel_money','bank_transfer','cash_on_delivery','escrow') NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    currency VARCHAR(5) DEFAULT 'RWF',
    transaction_ref VARCHAR(100),
    phone_number VARCHAR(20),
    status ENUM('initiated','pending','success','failed','refunded') DEFAULT 'initiated',
    payload JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS deliveries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    order_id BIGINT UNIQUE NOT NULL,
    transporter_id BIGINT NULL,
    vehicle_info VARCHAR(200),
    pickup_location VARCHAR(300),
    dropoff_location VARCHAR(300),
    pickup_lat DECIMAL(10,7),
    pickup_lng DECIMAL(10,7),
    dropoff_lat DECIMAL(10,7),
    dropoff_lng DECIMAL(10,7),
    current_lat DECIMAL(10,7),
    current_lng DECIMAL(10,7),
    status ENUM('pending','assigned','picked_up','in_transit','delivered','failed') DEFAULT 'pending',
    distance_km DECIMAL(8,2),
    fee DECIMAL(12,2),
    estimated_at DATETIME,
    delivered_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS delivery_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    delivery_id BIGINT NOT NULL,
    event_type VARCHAR(50),
    description TEXT,
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (delivery_id) REFERENCES deliveries(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNIQUE NOT NULL,
    reviewer_id BIGINT NOT NULL,
    seller_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id),
    FOREIGN KEY (seller_id) REFERENCES sellers(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS negotiations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL,
    buyer_id BIGINT NOT NULL,
    seller_id BIGINT NOT NULL,
    conversation_id BIGINT NOT NULL,
    offered_price DECIMAL(12,2),
    quantity DECIMAL(12,2),
    status ENUM('open','accepted','rejected','expired') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS export_requests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    buyer_id BIGINT NOT NULL,
    crop_id INT,
    category_id INT,
    quantity_needed DECIMAL(12,2),
    unit ENUM('kg','ton','bag','piece','liter','bunch') DEFAULT 'ton',
    target_price DECIMAL(12,2),
    destination_country VARCHAR(100),
    required_by DATE,
    certifications VARCHAR(300),
    description TEXT,
    status ENUM('open','fulfilled','closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wishlists (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_wish (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 10: COOPERATIVES
-- =====================================================================

CREATE TABLE IF NOT EXISTS cooperatives (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    registration_no VARCHAR(50) UNIQUE,
    district_id INT,
    sector VARCHAR(100),
    cell VARCHAR(100),
    village VARCHAR(100),
    chairperson_id BIGINT,
    manager_id BIGINT,
    founded_year INT,
    member_count INT DEFAULT 0,
    description TEXT,
    logo_url VARCHAR(500),
    bank_account VARCHAR(50),
    momo_code VARCHAR(20),
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chairperson_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cooperative_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    member_no VARCHAR(30),
    role ENUM('member','secretary','treasurer','vice_chair','chair') DEFAULT 'member',
    joined_at DATE,
    shares INT DEFAULT 0,
    status ENUM('active','inactive','suspended') DEFAULT 'active',
    UNIQUE KEY uq_member (cooperative_id, user_id),
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS coop_harvests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id BIGINT NOT NULL,
    crop_id INT NOT NULL,
    season VARCHAR(20),
    year INT,
    total_yield_kg DECIMAL(14,2),
    total_area_ha DECIMAL(10,2),
    quality_grade ENUM('A','B','C','Organic','Standard') DEFAULT 'Standard',
    stored_quantity_kg DECIMAL(14,2) DEFAULT 0,
    sold_quantity_kg DECIMAL(14,2) DEFAULT 0,
    harvest_date DATE,
    notes TEXT,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crops(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS coop_contributions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    amount_rwf DECIMAL(14,2) NOT NULL,
    purpose VARCHAR(200),
    contribution_date DATE,
    payment_method ENUM('cash','momo','bank','in_kind') DEFAULT 'cash',
    status ENUM('pending','paid','overdue') DEFAULT 'pending',
    receipt_no VARCHAR(50),
    recorded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS coop_equipment (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id BIGINT NOT NULL,
    name VARCHAR(150) NOT NULL,
    category ENUM('tractor','irrigation','harvesting','processing','transport','tool','other') DEFAULT 'other',
    quantity INT DEFAULT 1,
    condition_status ENUM('excellent','good','fair','needs_repair','broken') DEFAULT 'good',
    purchase_date DATE,
    purchase_value_rwf DECIMAL(14,2),
    assigned_to BIGINT,
    location VARCHAR(200),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS coop_meetings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cooperative_id BIGINT NOT NULL,
    title VARCHAR(200),
    agenda TEXT,
    meeting_date DATETIME,
    location VARCHAR(200),
    minutes TEXT,
    decisions TEXT,
    status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS coop_meeting_attendance (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    meeting_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    attended TINYINT(1) DEFAULT 0,
    notes VARCHAR(200),
    UNIQUE KEY uq_attendance (meeting_id, user_id),
    FOREIGN KEY (meeting_id) REFERENCES coop_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 11: LOGISTICS
-- =====================================================================

CREATE TABLE IF NOT EXISTS vehicles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    owner_id BIGINT NOT NULL,
    plate_number VARCHAR(20) UNIQUE NOT NULL,
    vehicle_type ENUM('truck','van','pickup','motorcycle','bicycle') DEFAULT 'truck',
    capacity_kg DECIMAL(10,2),
    driver_name VARCHAR(150),
    driver_phone VARCHAR(20),
    is_available TINYINT(1) DEFAULT 1,
    current_lat DECIMAL(10,7),
    current_lng DECIMAL(10,7),
    last_ping TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS transport_requests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    requested_by BIGINT NOT NULL,
    vehicle_id BIGINT NULL,
    order_id BIGINT NULL,
    pickup_location VARCHAR(300),
    pickup_lat DECIMAL(10,7),
    pickup_lng DECIMAL(10,7),
    dropoff_location VARCHAR(300),
    dropoff_lat DECIMAL(10,7),
    dropoff_lng DECIMAL(10,7),
    cargo_description VARCHAR(300),
    weight_kg DECIMAL(10,2),
    scheduled_at DATETIME,
    distance_km DECIMAL(8,2),
    fee_rwf DECIMAL(12,2),
    status ENUM('pending','assigned','picked_up','in_transit','delivered','cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requested_by) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicle_positions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    vehicle_id BIGINT NOT NULL,
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    speed_kmh DECIMAL(6,2),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vehicle_time (vehicle_id, recorded_at),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 12: STORAGE & IOT
-- =====================================================================

CREATE TABLE IF NOT EXISTS warehouses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    owner_id BIGINT NOT NULL,
    cooperative_id BIGINT NULL,
    district_id INT,
    address VARCHAR(300),
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    capacity_kg DECIMAL(14,2),
    capacity_m3 DECIMAL(10,2),
    warehouse_type ENUM('dry','cold','controlled','hermetic') DEFAULT 'dry',
    has_ventilation TINYINT(1) DEFAULT 1,
    has_cooling TINYINT(1) DEFAULT 0,
    status ENUM('active','maintenance','full','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS storage_bins (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    warehouse_id BIGINT NOT NULL,
    bin_code VARCHAR(30),
    crop_id INT NULL,
    quantity_kg DECIMAL(14,2) DEFAULT 0,
    fill_percentage DECIMAL(5,2) DEFAULT 0,
    moisture_pct DECIMAL(5,2),
    temperature_c DECIMAL(5,2),
    pest_risk ENUM('low','medium','high') DEFAULT 'low',
    entry_date DATE,
    expiry_date DATE,
    status ENUM('active','empty','quarantine','expired') DEFAULT 'active',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE,
    FOREIGN KEY (crop_id) REFERENCES crops(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS storage_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bin_id BIGINT NOT NULL,
    event_type ENUM('in','out','check','alert','expired') NOT NULL,
    quantity_kg DECIMAL(14,2),
    note TEXT,
    recorded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bin_id) REFERENCES storage_bins(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS iot_sensors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    device_id VARCHAR(100) UNIQUE NOT NULL,
    warehouse_id BIGINT NULL,
    farm_id BIGINT NULL,
    sensor_type ENUM('temperature','humidity','soil_moisture','rainfall','ph','co2','light') NOT NULL,
    unit VARCHAR(20),
    last_reading_value DECIMAL(10,2),
    last_reading TIMESTAMP NULL,
    battery_pct INT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS iot_readings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    sensor_id BIGINT NOT NULL,
    value DECIMAL(10,2),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sensor_time (sensor_id, recorded_at),
    FOREIGN KEY (sensor_id) REFERENCES iot_sensors(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 13: GOVERNMENT & REPORTS
-- =====================================================================

CREATE TABLE IF NOT EXISTS government_alerts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    alert_type ENUM('flood','drought','pest','price_shock','food_security','frost','disease_outbreak') NOT NULL,
    severity ENUM('low','medium','high','critical') DEFAULT 'medium',
    title VARCHAR(200) NOT NULL,
    body TEXT,
    district_id INT NULL,
    affected_districts JSON,
    affected_area_ha DECIMAL(12,2),
    affected_population INT,
    source VARCHAR(100),
    action_recommended TEXT,
    published_by BIGINT,
    is_active TINYINT(1) DEFAULT 1,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active_sev (is_active, severity)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS report_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    report_type ENUM('district','provincial','national','export','ai_insight','custom') NOT NULL,
    layout JSON,
    description VARCHAR(300),
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT IGNORE INTO report_templates (name, report_type, description) VALUES
('Weekly District Report', 'district', 'Weekly production + weather + alerts'),
('Monthly Provincial Report', 'provincial', 'Consolidated province KPIs'),
('National Quarterly Report', 'national', 'Executive summary for MINAGRI'),
('Export Performance Report', 'export', 'NAEB-style export analytics'),
('AI Insight Report', 'ai_insight', 'AI-generated insights with confidence');

CREATE TABLE IF NOT EXISTS reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    report_type ENUM('district','provincial','national','export','ai_insight','custom') NOT NULL,
    template_id INT,
    scope JSON,
    file_format ENUM('pdf','excel','csv','json') DEFAULT 'pdf',
    file_url VARCHAR(500),
    generated_by BIGINT,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_scheduled TINYINT(1) DEFAULT 0,
    FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS scheduled_reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    template_id INT NOT NULL,
    frequency ENUM('daily','weekly','monthly','quarterly') NOT NULL,
    day_of_week INT NULL,
    day_of_month INT NULL,
    time_of_day TIME DEFAULT '08:00:00',
    recipients JSON,
    scope JSON,
    is_active TINYINT(1) DEFAULT 1,
    last_run_at DATETIME,
    next_run_at DATETIME,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES report_templates(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS scheduled_report_runs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    schedule_id INT NOT NULL,
    run_at DATETIME NOT NULL,
    status ENUM('pending','running','success','failed') DEFAULT 'pending',
    output_path VARCHAR(500),
    recipients JSON,
    error_message TEXT,
    completed_at DATETIME NULL,
    INDEX idx_run (run_at, status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dashboard_snapshots (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    scope_key VARCHAR(100) UNIQUE,
    payload JSON,
    computed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS analytics_cache (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    cache_key VARCHAR(150) UNIQUE,
    cache_value JSON,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nst2_indicators (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE,
    name VARCHAR(200),
    pillar ENUM('economic','social','governance') NOT NULL,
    unit VARCHAR(50),
    baseline DECIMAL(14,2),
    target_2029 DECIMAL(14,2),
    current_value DECIMAL(14,2),
    trend ENUM('up','flat','down') DEFAULT 'flat',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO nst2_indicators (code, name, pillar, unit, baseline, target_2029, current_value, trend) VALUES
('AGRI.YIELD.MAIZE', 'Maize yield', 'economic', 't/ha', 2.0, 3.5, 2.6, 'up'),
('AGRI.YIELD.POTATO', 'Potato yield', 'economic', 't/ha', 10.0, 16.0, 12.3, 'up'),
('AGRI.IRRIG.COVER', 'Irrigation coverage', 'economic', '%', 5.0, 15.0, 8.2, 'up'),
('AGRI.EXPORT.VAL', 'Agri export value', 'economic', 'USD M', 500, 1000, 780, 'up'),
('AGRI.POSTHARVEST', 'Post-harvest loss', 'economic', '%', 25, 10, 18, 'down'),
('SOC.FARMER.DIG', 'Farmer digitalization', 'social', '%', 15, 60, 38, 'up'),
('SOC.JOBS.AGRI', 'Agri-jobs created', 'social', 'count', 0, 500000, 245000, 'up'),
('GOV.EVIDENCE.PLAN', 'Evidence-based planning', 'governance', '%', 40, 90, 72, 'up');

-- =====================================================================
-- MODULE 14: NOTIFICATIONS & MESSAGING
-- =====================================================================

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    type ENUM('weather','price','government','cooperative','chat','disease','transport','storage','system','order') NOT NULL,
    priority ENUM('green','yellow','red') DEFAULT 'green',
    title VARCHAR(200) NOT NULL,
    body TEXT,
    link VARCHAR(300),
    icon VARCHAR(20) DEFAULT '🔔',
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME,
    delivered_sms TINYINT(1) DEFAULT 0,
    delivered_email TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_id, is_read),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS email_queue (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    to_email VARCHAR(150) NOT NULL,
    subject VARCHAR(300) NOT NULL,
    body TEXT NOT NULL,
    html TINYINT(1) DEFAULT 1,
    user_id BIGINT NULL,
    status ENUM('pending','sent','failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    last_error VARCHAR(500),
    scheduled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status, scheduled_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sms_queue (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    user_id BIGINT NULL,
    provider ENUM('mtn','airtel','other') DEFAULT 'mtn',
    status ENUM('pending','sent','failed') DEFAULT 'pending',
    attempts INT DEFAULT 0,
    last_attempt_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 15: SECURITY & AUDIT
-- =====================================================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    action VARCHAR(100),
    entity VARCHAR(100),
    entity_id BIGINT,
    meta JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(300),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_time (user_id, created_at),
    INDEX idx_entity (entity, entity_id)
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 16: NISR DATA ANALYSIS PORTAL
-- =====================================================================

CREATE TABLE IF NOT EXISTS nisr_datasets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    survey_type ENUM('EICV','RPHC','agricultural_survey','household_survey','census','custom','external') DEFAULT 'custom',
    source VARCHAR(200) DEFAULT 'NISR',
    source_url VARCHAR(500),
    version VARCHAR(20) DEFAULT '1.0',
    year INT,
    geographic_coverage VARCHAR(100) DEFAULT 'national',
    columns_schema JSON,
    row_count INT DEFAULT 0,
    file_path VARCHAR(500),
    is_public TINYINT(1) DEFAULT 0,
    status ENUM('draft','published','archived','processing') DEFAULT 'processing',
    uploaded_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_survey_type (survey_type),
    INDEX idx_year (year)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nisr_dataset_rows (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    dataset_id BIGINT NOT NULL,
    row_index INT,
    data JSON NOT NULL,
    district_id INT NULL,
    sector_id INT NULL,
    crop_id INT NULL,
    year INT,
    season VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dataset (dataset_id),
    INDEX idx_district (district_id),
    INDEX idx_year (year),
    FOREIGN KEY (dataset_id) REFERENCES nisr_datasets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nisr_data_quality (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    dataset_id BIGINT NOT NULL,
    column_name VARCHAR(150) NOT NULL,
    data_type VARCHAR(50),
    total_values INT,
    missing_count INT DEFAULT 0,
    missing_pct DECIMAL(5,2) DEFAULT 0,
    unique_count INT DEFAULT 0,
    min_value DECIMAL(20,4),
    max_value DECIMAL(20,4),
    mean_value DECIMAL(20,4),
    median_value DECIMAL(20,4),
    std_dev DECIMAL(20,4),
    outlier_count INT DEFAULT 0,
    computed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_dataset_col (dataset_id, column_name),
    FOREIGN KEY (dataset_id) REFERENCES nisr_datasets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nisr_saved_analyses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    analysis_type ENUM('pivot','chart','comparison','timeseries','custom_query') NOT NULL,
    dataset_id BIGINT,
    config JSON,
    filters JSON,
    is_public TINYINT(1) DEFAULT 0,
    created_by BIGINT NOT NULL,
    last_run_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dataset_id) REFERENCES nisr_datasets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nisr_chart_configs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    title VARCHAR(200),
    chart_type ENUM('line','bar','pie','doughnut','area','radar','scatter','heatmap') NOT NULL,
    dataset_id BIGINT,
    x_field VARCHAR(150),
    y_fields JSON,
    group_by VARCHAR(150),
    filters JSON,
    palette VARCHAR(50) DEFAULT 'green',
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dataset_id) REFERENCES nisr_datasets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nisr_publications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    title VARCHAR(300) NOT NULL,
    subtitle VARCHAR(300),
    publication_type ENUM('statistical_yearbook','survey_report','thematic_brief','policy_brief','quarterly') NOT NULL,
    dataset_ids JSON,
    sections JSON,
    cover_image VARCHAR(500),
    status ENUM('draft','review','published') DEFAULT 'draft',
    published_at DATETIME,
    is_public TINYINT(1) DEFAULT 1,
    created_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nisr_api_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    token CHAR(64) UNIQUE NOT NULL,
    label VARCHAR(100),
    permissions JSON,
    rate_limit_per_hour INT DEFAULT 1000,
    calls_made INT DEFAULT 0,
    last_used_at TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    expires_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS nisr_analysis_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    analysis_type VARCHAR(50),
    dataset_id BIGINT,
    query_params JSON,
    execution_time_ms INT,
    row_count INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_time (user_id, created_at),
    INDEX idx_dataset (dataset_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS api_request_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    token_id BIGINT NULL,
    user_id BIGINT NULL,
    endpoint VARCHAR(200),
    method VARCHAR(10),
    ip_address VARCHAR(45),
    status_code INT,
    execution_ms INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token_id, created_at),
    INDEX idx_endpoint (endpoint, created_at)
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 17: DASHBOARD BUILDER
-- =====================================================================

CREATE TABLE IF NOT EXISTS widget_catalog (
    id INT PRIMARY KEY AUTO_INCREMENT,
    widget_key VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(300),
    icon VARCHAR(20),
    category ENUM('weather','agriculture','marketplace','ai','communication','analytics','system') NOT NULL,
    default_size ENUM('small','medium','large','full') DEFAULT 'medium',
    is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT IGNORE INTO widget_catalog (widget_key, name, description, icon, category, default_size) VALUES
('weather_now','Current Weather','Live weather at your location','🌤️','weather','small'),
('weather_forecast','5-Day Forecast','Extended weather forecast','🌧️','weather','medium'),
('crop_health','Crop Health','AI-monitored crop status','🌱','agriculture','medium'),
('crop_yield','Yield Prediction','AI yield forecast','📈','agriculture','medium'),
('market_prices','Market Prices','Live crop prices','💰','marketplace','small'),
('market_orders','Recent Orders','Your marketplace orders','🛒','marketplace','medium'),
('ai_advice','AI Recommendation','Personalized farming advice','🤖','ai','medium'),
('ai_insights','AI Insights','Trending insights from data','💡','ai','medium'),
('chat_recent','Recent Chats','Latest conversations','💬','communication','small'),
('chat_groups','Community Groups','District & crop forums','👥','communication','medium'),
('analytics_yield','Yield Analytics','Production trends','📊','analytics','large'),
('analytics_kpi','KPI Cards','Key performance indicators','📉','analytics','medium'),
('calendar','Calendar','Meetings & tasks','📅','system','medium'),
('notifications','Notifications','Recent alerts','🔔','system','small'),
('gov_heatmap','Production Heatmap','National GIS map','🗺️','analytics','large'),
('gov_nst2','NST2 Tracker','NST2 performance indicators','🎯','analytics','large'),
('coop_members','Members','Cooperative members list','👥','agriculture','medium'),
('coop_harvest','Harvest Records','Cooperative harvests','🌾','agriculture','medium'),
('logistics_fleet','Fleet Status','Vehicles & deliveries','🚚','system','medium'),
('storage_bins','Storage Bins','Warehouse bin status','🏭','system','medium');

CREATE TABLE IF NOT EXISTS dashboard_layouts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    dashboard_type ENUM('farmer','cooperative','officer','government','exporter','researcher','admin') NOT NULL,
    layout_data JSON NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_dash (user_id, dashboard_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- MODULE 18: NISR SEED DATASETS
-- =====================================================================

INSERT IGNORE INTO nisr_datasets
(uuid, name, slug, description, survey_type, source, year, columns_schema, row_count, is_public, status)
VALUES
(UUID(), 'Agricultural Household Survey 2024', 'agri-household-2024',
 'National agricultural household survey covering crop production, livestock, and land use.',
 'agricultural_survey', 'NISR', 2024,
 JSON_ARRAY(
    JSON_OBJECT('name','district','type','string'),
    JSON_OBJECT('name','crop','type','string'),
    JSON_OBJECT('name','season','type','string'),
    JSON_OBJECT('name','area_ha','type','number'),
    JSON_OBJECT('name','yield_kg','type','number'),
    JSON_OBJECT('name','households','type','number')
 ), 0, 1, 'published'),
(UUID(), 'EICV 5 — Household Consumption', 'eicv5-consumption',
 'Household consumption patterns and poverty indicators across Rwanda.',
 'EICV', 'NISR', 2023,
 JSON_ARRAY(
    JSON_OBJECT('name','district','type','string'),
    JSON_OBJECT('name','consumption_rwf','type','number'),
    JSON_OBJECT('name','poverty_rate','type','number'),
    JSON_OBJECT('name','households','type','number')
 ), 0, 1, 'published'),
(UUID(), 'RPHC 5 — Population by District', 'rphc5-population',
 'Population and housing census aggregated by district and sector.',
 'RPHC', 'NISR', 2022,
 JSON_ARRAY(
    JSON_OBJECT('name','district','type','string'),
    JSON_OBJECT('name','population','type','number'),
    JSON_OBJECT('name','male','type','number'),
    JSON_OBJECT('name','female','type','number'),
    JSON_OBJECT('name','urban_pct','type','number')
 ), 0, 1, 'published');

-- =====================================================================
-- SEED DATA: USERS, FARMS, PRODUCTS, ORDERS
-- =====================================================================
-- Password for all test users: ChangeMe123!
-- Hash generated with: password_hash('ChangeMe123!', PASSWORD_DEFAULT)

SET @pwd = '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1HlCS7G9D9z9T4e0vB5ZWp4XGmz5.2K';

INSERT IGNORE INTO users (uuid, first_name, last_name, email, phone, password_hash, role_id, district_id, preferred_language, status, verification_status, gender)
VALUES
(UUID(), 'Jean',    'Mugisha',   'farmer@saip.rw',     '0788100001', @pwd, 1, 17, 'rw', 'active', 'fully_verified', 'male'),
(UUID(), 'Aline',   'Uwase',     'coop@saip.rw',       '0788100002', @pwd, 2, 7,  'rw', 'active', 'fully_verified', 'female'),
(UUID(), 'Patrick', 'Habimana',  'officer@saip.rw',    '0788100003', @pwd, 3, 17, 'rw', 'active', 'fully_verified', 'male'),
(UUID(), 'Claudine','Mukamana',  'gov@saip.rw',        '0788100004', @pwd, 4, 1,  'rw', 'active', 'fully_verified', 'female'),
(UUID(), 'Eric',    'Nkurunziza','exporter@saip.rw',   '0788100005', @pwd, 5, 1,  'en', 'active', 'fully_verified', 'male'),
(UUID(), 'Marie',   'Ingabire',  'researcher@saip.rw', '0788100006', @pwd, 6, 1,  'en', 'active', 'fully_verified', 'female'),
(UUID(), 'Admin',   'SAIP',      'admin@saip.rw',      '0788100007', @pwd, 7, 1,  'en', 'active', 'fully_verified', 'other');

INSERT IGNORE INTO user_preferences (user_id, language_code)
SELECT id, preferred_language FROM users;

INSERT IGNORE INTO user_profiles (user_id) SELECT id FROM users;

INSERT IGNORE INTO verified_users (user_id, badge_type)
SELECT id, 'officer' FROM users WHERE role_id IN (3,4,7);

INSERT INTO farms (uuid, user_id, name, district_id, size_ha, soil_type, irrigation_type, lat, lng)
SELECT UUID(), u.id, CONCAT(u.first_name, '''s Farm'), u.district_id, 2.5, 'loam', 'drip', -1.9441, 30.0619
FROM users u WHERE u.role_id = 1;

INSERT INTO farm_crops (farm_id, crop_id, season, year, planted_date, expected_harvest, area_ha, growth_stage, health_status)
SELECT f.id, 1, 'A', 2024, '2024-09-15', '2025-01-15', 2.5, 'vegetative', 'good'
FROM farms f LIMIT 1;

INSERT INTO production (user_id, crop_id, season, year, yield_kg, revenue_rwf, harvest_date)
SELECT u.id, 1, 'A', 2024, 45000, 15750000, '2025-01-10'
FROM users u WHERE u.role_id = 1;

INSERT INTO production (user_id, crop_id, season, year, yield_kg, revenue_rwf, harvest_date)
SELECT u.id, 2, 'B', 2024, 18000, 11160000, '2025-05-20'
FROM users u WHERE u.role_id = 1;

INSERT IGNORE INTO sellers (user_id, business_name, seller_type, district_id, is_verified, rating, total_sales)
SELECT id, CONCAT(first_name, '''s Farm'), 'farmer', district_id, 1, 4.5, 12
FROM users WHERE role_id = 1;

INSERT INTO products (uuid, seller_id, crop_id, category_id, title, description, unit, quantity_available, price_per_unit, quality_grade, district_id, is_organic, status)
SELECT UUID(), (SELECT id FROM sellers LIMIT 1), 1, 1,
    'Premium Maize — Grade A',
    'Freshly harvested maize from Bugesera. Stored in hermetic bags. Delivery available.',
    'kg', 500, 350, 'A', 17, 1, 'active';

INSERT INTO products (uuid, seller_id, crop_id, category_id, title, description, unit, quantity_available, price_per_unit, quality_grade, district_id, status)
SELECT UUID(), (SELECT id FROM sellers LIMIT 1), 2, 4,
    'Red Beans — High Quality',
    'Fresh red beans from Season B harvest. Excellent cooking quality.',
    'kg', 300, 620, 'A', 17, 'active';

INSERT IGNORE INTO buyers (user_id, buyer_type, district_id, is_verified)
SELECT id, 'exporter', district_id, 1 FROM users WHERE role_id = 5;

INSERT INTO export_requests (uuid, buyer_id, category_id, quantity_needed, unit, target_price, destination_country, required_by, certifications, description, status)
SELECT UUID(), (SELECT id FROM users WHERE role_id = 5 LIMIT 1), 6,
    10.00, 'ton', 500000, 'France', '2025-06-30',
    'Organic, Fair Trade',
    'Looking for Grade A coffee for European market. Must have organic certification.',
    'open';

INSERT INTO notifications (user_id, type, priority, title, body, icon)
SELECT id, 'system', 'green', 'Welcome to SAIP', 'Your account is ready. Explore the dashboard to get started.', '🎉'
FROM users LIMIT 5;

INSERT INTO notifications (user_id, type, priority, title, body, icon)
SELECT id, 'weather', 'yellow', 'Heavy rainfall expected', 'Rain expected in your district tomorrow. Consider delaying fertilizer application.', '🌧️'
FROM users WHERE role_id = 1;

INSERT INTO government_alerts (uuid, alert_type, severity, title, body, district_id, action_recommended, published_by, is_active)
VALUES
(UUID(), 'pest', 'high', 'Fall Armyworm outbreak detected',
 'Fall Armyworm larvae found in 12 farms in Bugesera district. Immediate action recommended.',
 17, 'Apply neem oil or Emamectin benzoate. Report any new infestations to your district officer.',
 (SELECT id FROM users WHERE role_id = 4 LIMIT 1), 1);

-- NISR dataset rows
SET @ds_id = (SELECT id FROM nisr_datasets WHERE slug = 'agri-household-2024' LIMIT 1);
INSERT INTO nisr_dataset_rows (dataset_id, row_index, data, district_id, year) VALUES
(@ds_id, 0, JSON_OBJECT('district','Bugesera','crop','Maize','season','A','area_ha',120,'yield_kg',45000,'households',340,'year',2024), 17, 2024),
(@ds_id, 1, JSON_OBJECT('district','Musanze','crop','Potatoes','season','A','area_ha',85,'yield_kg',120000,'households',520,'year',2024), 7, 2024),
(@ds_id, 2, JSON_OBJECT('district','Nyagatare','crop','Maize','season','A','area_ha',200,'yield_kg',85000,'households',610,'year',2024), 22, 2024),
(@ds_id, 3, JSON_OBJECT('district','Huye','crop','Beans','season','B','area_ha',60,'yield_kg',18000,'households',180,'year',2024), 10, 2024),
(@ds_id, 4, JSON_OBJECT('district','Kigali','crop','Tomatoes','season','A','area_ha',15,'yield_kg',22500,'households',45,'year',2024), 1, 2024);

UPDATE nisr_datasets SET row_count = (SELECT COUNT(*) FROM nisr_dataset_rows WHERE dataset_id = @ds_id) WHERE id = @ds_id;

-- Forum channels
INSERT IGNORE INTO conversations (uuid, type, title, created_by) VALUES
(UUID(), 'group', 'Bugesera District Forum', 1),
(UUID(), 'group', 'Maize Growers Community', 1);

INSERT IGNORE INTO forum_channels (channel_type, reference_id, name, icon, conversation_id) VALUES
('district', 17, 'Bugesera District Forum', '📍', 1),
('crop',     1,  'Maize Growers Community', '🌽', 2);

-- =====================================================================
-- FINALIZE
-- =====================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- VERIFICATION
-- =====================================================================

SELECT
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'saip_enterprise') AS total_tables,
    (SELECT COUNT(*) FROM roles)          AS roles,
    (SELECT COUNT(*) FROM permissions)    AS permissions,
    (SELECT COUNT(*) FROM users)          AS users,
    (SELECT COUNT(*) FROM provinces)      AS provinces,
    (SELECT COUNT(*) FROM districts)      AS districts,
    (SELECT COUNT(*) FROM crops)          AS crops,
    (SELECT COUNT(*) FROM nst2_indicators) AS nst2_indicators,
    (SELECT COUNT(*) FROM widget_catalog) AS widgets,
    (SELECT COUNT(*) FROM nisr_datasets)  AS nisr_datasets;

SELECT '✅ SAIP Enterprise Database — Complete' AS status;