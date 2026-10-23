-- =====================================================
-- RECOMMENDATION ENGINE — Core Tables
-- =====================================================

-- Farmer profile (extends users)
CREATE TABLE IF NOT EXISTS farmer_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    district VARCHAR(100) NULL,
    sector VARCHAR(100) NULL,
    cell VARCHAR(100) NULL,
    province VARCHAR(50) NULL,
    land_size_ha DECIMAL(10,2) DEFAULT 1.00,
    primary_crops JSON NULL,
    soil_type VARCHAR(50) DEFAULT 'loam',
    has_irrigation TINYINT(1) DEFAULT 0,
    farming_experience_years INT DEFAULT 0,
    cooperative_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_location (district, sector)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cached market prices (from e-Soko or seeded)
CREATE TABLE IF NOT EXISTS market_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop VARCHAR(100) NOT NULL,
    market VARCHAR(100) NOT NULL,
    district VARCHAR(100) NOT NULL,
    price_per_kg DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'RWF',
    recorded_at DATE NOT NULL,
    source VARCHAR(50) DEFAULT 'manual',
    INDEX idx_crop_market (crop, market),
    INDEX idx_date (recorded_at),
    INDEX idx_district (district)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Recommendation history
CREATE TABLE IF NOT EXISTS recommendations_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recommendation_type VARCHAR(50) NOT NULL,
    input_context JSON NULL,
    output_data JSON NULL,
    user_action VARCHAR(50) DEFAULT 'shown',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_type (user_id, recommendation_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Farmer alerts
CREATE TABLE IF NOT EXISTS farmer_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alert_type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    severity ENUM('info','warning','critical') DEFAULT 'info',
    channel ENUM('app','sms','email') DEFAULT 'app',
    is_read TINYINT(1) DEFAULT 0,
    action_url VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- SEED: Sample market prices (for testing)
-- =====================================================
INSERT IGNORE INTO market_prices (crop, market, district, price_per_kg, recorded_at, source) VALUES
('Maize',        'Kigali (Nyabugogo)', 'Kigali',   480, CURDATE(), 'seed'),
('Maize',        'Rubavu Market',      'Rubavu',   460, CURDATE(), 'seed'),
('Maize',        'Musanze Local',      'Musanze',  420, CURDATE(), 'seed'),
('Maize',        'Huye Market',        'Huye',     445, CURDATE(), 'seed'),
('Potatoes',     'Kigali (Nyabugogo)', 'Kigali',   395, CURDATE(), 'seed'),
('Potatoes',     'Rubavu Market',      'Rubavu',   380, CURDATE(), 'seed'),
('Potatoes',     'Musanze Local',      'Musanze',  365, CURDATE(), 'seed'),
('Beans',        'Kigali (Nyabugogo)', 'Kigali',   875, CURDATE(), 'seed'),
('Beans',        'Huye Market',        'Huye',     850, CURDATE(), 'seed'),
('Rice',         'Kigali (Nyabugogo)', 'Kigali',   720, CURDATE(), 'seed'),
('Tomatoes',     'Kigali (Nyabugogo)', 'Kigali',   650, CURDATE(), 'seed'),
('Coffee',       'Kigali (Nyabugogo)', 'Kigali',  2400, CURDATE(), 'seed'),
('Tea',          'Kigali (Nyabugogo)', 'Kigali',   950, CURDATE(), 'seed'),
('Cassava',      'Huye Market',        'Huye',     320, CURDATE(), 'seed'),
('Bananas',      'Kigali (Nyabugogo)', 'Kigali',   280, CURDATE(), 'seed');