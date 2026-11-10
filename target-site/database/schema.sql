-- PROJECT: BLACKOUT — TARGET DATABASE SCHEMA
-- E CORP CORPORATE DATABASE (SYNTHETIC CTF DATA)

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'user',
    department VARCHAR(100) DEFAULT 'General',
    clearance_level INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    employee_code VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    title VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    badge_id VARCHAR(50) NOT NULL,
    profile_bio TEXT NULL,
    clearance_level INT DEFAULT 1,
    salary_tier VARCHAR(20) DEFAULT 'T2',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_number VARCHAR(50) NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    contact_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    tier VARCHAR(50) DEFAULT 'Enterprise Standard',
    contract_status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    category VARCHAR(100) NOT NULL,
    classification VARCHAR(50) DEFAULT 'Confidential',
    uploaded_by INT DEFAULT 1,
    download_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    summary TEXT NOT NULL,
    content MEDIUMTEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    author VARCHAR(100) NOT NULL,
    is_published BOOLEAN DEFAULT TRUE,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL DEFAULT 1,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(150) NOT NULL,
    comment_text TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending', -- pending, approved, reviewed
    reviewed_by VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS challenge_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    challenge_id VARCHAR(20) NOT NULL UNIQUE,
    record_key VARCHAR(100) NOT NULL,
    secret_value TEXT NOT NULL,
    description VARCHAR(255) NOT NULL,
    flag_content VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    credits INT NOT NULL DEFAULT 10,
    coupons_claimed INT NOT NULL DEFAULT 0,
    vip_status VARCHAR(50) DEFAULT 'Standard',
    unlocked_secret VARCHAR(255) NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin_audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor VARCHAR(100) NOT NULL,
    action VARCHAR(255) NOT NULL,
    resource VARCHAR(255) NOT NULL,
    ip_address VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
