-- =====================================================================
-- SAIP — Phase 11: NISR Data Analysis Portal
-- =====================================================================

USE saip_enterprise;

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
    INDEX idx_year (year),
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
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

-- Seed sample datasets (metadata only)
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
 ),
 0, 1, 'published'),
(UUID(), 'EICV 5 — Household Consumption', 'eicv5-consumption',
 'Household consumption patterns and poverty indicators across Rwanda.',
 'EICV', 'NISR', 2023,
 JSON_ARRAY(
    JSON_OBJECT('name','district','type','string'),
    JSON_OBJECT('name','consumption_rwf','type','number'),
    JSON_OBJECT('name','poverty_rate','type','number'),
    JSON_OBJECT('name','households','type','number')
 ),
 0, 1, 'published'),
(UUID(), 'RPHC 5 — Population by District', 'rphc5-population',
 'Population and housing census aggregated by district and sector.',
 'RPHC', 'NISR', 2022,
 JSON_ARRAY(
    JSON_OBJECT('name','district','type','string'),
    JSON_OBJECT('name','population','type','number'),
    JSON_OBJECT('name','male','type','number'),
    JSON_OBJECT('name','female','type','number'),
    JSON_OBJECT('name','urban_pct','type','number')
 ),
 0, 1, 'published');

SELECT '✅ Phase 11 complete' AS status;