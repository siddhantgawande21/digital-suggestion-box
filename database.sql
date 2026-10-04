-- =========================================================
-- DIGITAL SUGGESTION BOX
-- Database: digital_suggestion_box
-- =========================================================



-- =========================================================
-- 1. ADMIN TABLE
-- =========================================================

CREATE TABLE admin (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    email VARCHAR(255) NOT NULL UNIQUE,

    password_hash VARCHAR(255) NOT NULL,

    reset_token_hash VARCHAR(255) DEFAULT NULL,

    reset_token_expires_at DATETIME DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =========================================================
-- 2. COLLEGE CODE TABLE
-- =========================================================

CREATE TABLE college_code (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    code VARCHAR(100) NOT NULL UNIQUE,

    is_active BOOLEAN NOT NULL DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- =========================================================
-- 3. SUGGESTIONS TABLE
-- =========================================================

CREATE TABLE suggestions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    suggestion_id VARCHAR(30) NOT NULL UNIQUE,

    category VARCHAR(100) NOT NULL,

    suggestion TEXT NOT NULL,

    status ENUM(
        'Pending',
        'Under Review',
        'Accepted',
        'Rejected',
        'Implemented'
    ) NOT NULL DEFAULT 'Pending',

    admin_response TEXT DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


-- =========================================================
-- INSERT COLLEGE CODE
-- =========================================================

INSERT INTO college_code (code, is_active)
VALUES ('MCOE-DSB-2026', TRUE);


-- =========================================================
-- OPTIONAL INDEXES FOR FASTER SEARCH
-- =========================================================

CREATE INDEX idx_suggestions_status
ON suggestions(status);

CREATE INDEX idx_suggestions_category
ON suggestions(category);

CREATE INDEX idx_suggestions_created_at
ON suggestions(created_at);