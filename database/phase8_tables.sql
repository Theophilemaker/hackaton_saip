-- =====================================================================
-- SAIP — Phase 8: Cooperatives, Logistics, Storage, IoT
-- =====================================================================

USE saip_enterprise;

-- ============ COOPERATIVES ============
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
    FOREIGN KEY (cooperative_id) REFERENCES cooperatives(id) ON DELETE CASCADE
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

-- ============ LOGISTICS ============
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

-- ============ STORAGE & IOT ============
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
    FOREIGN KEY (warehouse_id) REFERENCES warehouses(id) ON DELETE CASCADE
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

-- ============ SMS & EMAIL QUEUES ============
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

SELECT '✅ Phase 8 complete' AS status;