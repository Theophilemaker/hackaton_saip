-- =====================================================================
-- SAIP — Phase 12: Complete Missing Modules
-- Chat, Marketplace, Agriculture, AI, Weather, Government
-- =====================================================================

USE saip_enterprise;

-- ============ COMMUNICATION ============
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

-- ============ FEED & FORUMS ============
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

CREATE TABLE IF NOT EXISTS verified_users (
    user_id BIGINT PRIMARY KEY,
    verified_by BIGINT,
    badge_type ENUM('officer','expert','coop_leader','researcher') DEFAULT 'officer',
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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

-- ============ AGRICULTURE ============
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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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
('Fall Armyworm',1,'pest','Ragged holes, frass.','Neem oil or Emamectin benzoate.','Rotate crops.','high'),
('Potato Blight',5,'fungal','Dark spots, white mold.','Copper fungicide.','Avoid overhead irrigation.','high'),
('Banana Wilt',7,'fungal','Yellowing leaves.','Trichoderma.','Clean suckers.','high'),
('Coffee Leaf Rust',8,'fungal','Orange-yellow spots.','Copper fungicide.','Resistant varieties.','high');

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
('Fall Armyworm',1,'insect','Ragged holes','Neem oil','high'),
('Coffee Berry Borer',8,'insect','Holes in cherries','Beauveria bassiana','high'),
('Banana Weevil',7,'insect','Pseudostem tunnels','Clean suckers','high');

-- ============ WEATHER ============
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

-- ============ AI ============
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
('Disease CNN','disease','v1.0',92.30,1);

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

-- ============ GOVERNMENT ============
CREATE TABLE IF NOT EXISTS government_alerts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid CHAR(36) UNIQUE NOT NULL,
    alert_type ENUM('flood','drought','pest','price_shock','food_security','frost','disease_outbreak') NOT NULL,
    severity ENUM('low','medium','high','critical') DEFAULT 'medium',
    title VARCHAR(200) NOT NULL,
    body TEXT,
    district_id INT NULL,
    affected_districts JSON,
    source VARCHAR(100),
    action_recommended TEXT,
    published_by BIGINT,
    is_active TINYINT(1) DEFAULT 1,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active_sev (is_active, severity)
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
('SOC.FARMER.DIG', 'Farmer digitalization', 'social', '%', 15, 60, 38, 'up'),
('GOV.EVIDENCE.PLAN', 'Evidence-based planning', 'governance', '%', 40, 90, 72, 'up');

-- ============ MARKETPLACE ============
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

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNIQUE NOT NULL,
    reviewer_id BIGINT NOT NULL,
    seller_id BIGINT NOT NULL,
    product_id BIGINT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
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

-- ============ FORUM SEEDS ============
INSERT IGNORE INTO conversations (uuid, type, title, created_by) VALUES
(UUID(), 'group', 'Bugesera District Forum', 1),
(UUID(), 'group', 'Maize Growers Community', 1);

INSERT IGNORE INTO forum_channels (channel_type, reference_id, name, icon, conversation_id) VALUES
('district', 17, 'Bugesera District Forum', '📍', 1),
('crop',     1,  'Maize Growers Community', '🌽', 2);

SELECT '✅ Phase 12 complete' AS status;