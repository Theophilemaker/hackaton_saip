-- =====================================================================
-- SAIP — Phase 1: Core User Management
-- Roles, Users, Sessions
-- =====================================================================

CREATE DATABASE IF NOT EXISTS saip_enterprise
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE saip_enterprise;

-- ============ ROLES ============
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO roles (id, name, description) VALUES
(1, 'Farmer', 'Individual farmer'),
(2, 'Cooperative Leader', 'Manages a cooperative'),
(3, 'Agricultural Officer', 'District monitoring officer'),
(4, 'Government Officer', 'National analytics access'),
(5, 'Exporter', 'Bulk buyer / exporter'),
(6, 'Researcher', 'Data analyst / university'),
(7, 'Admin', 'Full system administrator');

-- ============ USERS ============
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
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ============ USER SESSIONS ============
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

-- ============ USER PREFERENCES ============
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

-- ============ USER PROFILES ============
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

-- ============ PASSWORD RESETS ============
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

-- ============ LOGIN HISTORY ============
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

-- ============ FAILED LOGINS ============
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

-- ============ AUDIT LOGS ============
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

-- ============ RATE LIMITS ============
CREATE TABLE IF NOT EXISTS rate_limits (
    rate_key VARCHAR(150) PRIMARY KEY,
    hits INT DEFAULT 0,
    window_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_window (window_start)
) ENGINE=InnoDB;

-- ============ NOTIFICATIONS ============
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

SELECT '✅ Phase 1 complete' AS status;