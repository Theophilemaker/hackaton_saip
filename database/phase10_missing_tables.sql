-- =====================================================================
-- SAIP — Phase 10: RBAC, Geography, Themes, Translations
-- =====================================================================

USE saip_enterprise;

-- ============ RBAC ============
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
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO role_permissions (role_id, permission_id) SELECT 7, id FROM permissions;

-- ============ RWANDA GEOGRAPHY ============
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
    lng DECIMAL(10,7),
    FOREIGN KEY (province_id) REFERENCES provinces(id)
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
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (district_id) REFERENCES districts(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cells (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sector_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (sector_id) REFERENCES sectors(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS villages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cell_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (cell_id) REFERENCES cells(id)
) ENGINE=InnoDB;

-- Add district FK to users (only if not already present)
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = 'saip_enterprise'
      AND TABLE_NAME = 'users'
      AND CONSTRAINT_NAME = 'fk_users_district'
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE users ADD CONSTRAINT fk_users_district FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE SET NULL',
    'SELECT "FK already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============ LOCALIZATION ============
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

-- ============ USER DEVICES & 2FA ============
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

-- ============ API KEYS ============
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

-- ============ DASHBOARD WIDGETS ============
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
('ai_advice','AI Recommendation','Personalized farming advice','🤖','ai','medium'),
('chat_recent','Recent Chats','Latest conversations','💬','communication','small'),
('notifications','Notifications','Recent alerts','🔔','system','small'),
('gov_heatmap','Production Heatmap','National GIS map','🗺️','analytics','large'),
('gov_nst2','NST2 Tracker','NST2 performance indicators','🎯','analytics','large');

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

SELECT '✅ Phase 10 complete' AS status;