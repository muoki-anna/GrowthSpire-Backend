-- Database Schema for GrowthSpire V2
-- This schema consolidates requirements from both Admin and User applications

-- Users Table (for Admins/Authors)
CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin', -- 'admin', 'editor', 'viewer'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blogs Table
CREATE TABLE IF NOT EXISTS blogs (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    slug VARCHAR(255) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT,
    content TEXT NOT NULL,
    category VARCHAR(100) NOT NULL, -- 'Fundraising', 'Product', 'Growth', 'Team', 'Strategy', 'Case Study', 'Technology'
    author_name VARCHAR(100) NOT NULL,
    published_at TIMESTAMP NULL,
    read_time VARCHAR(50), 
    featured BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Events Table
CREATE TABLE IF NOT EXISTS events (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    timezone VARCHAR(50) DEFAULT 'EAT',
    location VARCHAR(255),
    event_type VARCHAR(50) NOT NULL, -- 'In-Person', 'Online'
    attendees_count INT DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    registration_link VARCHAR(255),
    is_past BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- FAQs Table
CREATE TABLE IF NOT EXISTS faqs (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    category VARCHAR(100) NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sponsors Table
CREATE TABLE IF NOT EXISTS sponsors (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    logo_url VARCHAR(255),
    website_url VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Portfolio / Startups Table
CREATE TABLE IF NOT EXISTS portfolio_startups (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    founder VARCHAR(255),
    category VARCHAR(100) NOT NULL,
    description TEXT,
    funding_amount VARCHAR(255),
    founded_year INT,
    stage VARCHAR(50), -- 'Idea', 'Pre-Seed', 'Seed', 'Series A', 'Series B', 'Exit'
    status VARCHAR(50) DEFAULT 'Active', -- 'Active', 'Accelerated', 'Pending', 'Inactive'
    website_url VARCHAR(255),
    logo_url VARCHAR(255),
    joined_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tales / Success Stories Table
CREATE TABLE IF NOT EXISTS tales (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    subtitle VARCHAR(255),
    content TEXT NOT NULL,
    startup_id VARCHAR(36) NULL,
    author_name VARCHAR(100),
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    featured BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (startup_id) REFERENCES portfolio_startups(id) ON DELETE SET NULL
);

-- Applications Table
CREATE TABLE IF NOT EXISTS applications (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    application_type VARCHAR(50) NOT NULL, -- 'startup' or 'sponsor'
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    linkedin_profile VARCHAR(255),
    company_name VARCHAR(255) NOT NULL,
    website_url VARCHAR(255),
    startup_stage VARCHAR(50),
    industry VARCHAR(100),
    funding_needed_range VARCHAR(50),
    team_size VARCHAR(50),
    pitch_deck_url VARCHAR(255),
    investor_type VARCHAR(50),
    investment_range VARCHAR(50),
    focus_areas VARCHAR(255),
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'pending', -- 'pending', 'under_review', 'interview', 'accepted', 'rejected'
    reviewer_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Privacy Policies Table
CREATE TABLE IF NOT EXISTS privacy_policies (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    type VARCHAR(50) NOT NULL, -- 'privacy', 'terms', 'data_policy'
    version VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    type ENUM('received', 'disbursed') NOT NULL,
    method VARCHAR(50) NOT NULL, -- 'M-Pesa', 'Card', 'Bank Transfer', etc.
    transaction_code VARCHAR(100) UNIQUE NOT NULL,
    reference VARCHAR(100),
    amount DECIMAL(15, 2) NOT NULL,
    transaction_cost DECIMAL(15, 2) DEFAULT 0.00,
    net_amount DECIMAL(15, 2) NOT NULL,
    customer_name VARCHAR(255),
    customer_phone VARCHAR(50),
    recipient_name VARCHAR(255),
    mobile_number VARCHAR(50),
    status ENUM('completed', 'pending', 'failed') DEFAULT 'pending',
    description TEXT,
    plan_details TEXT,
    transaction_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Packages Table
CREATE TABLE IF NOT EXISTS packages (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    speed VARCHAR(50) NOT NULL, -- e.g., '10 Mbps'
    price DECIMAL(15, 2) NOT NULL,
    customer_count INT DEFAULT 0,
    monthly_revenue DECIMAL(15, 2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Communications Table (for Emails, SMS, Notifications)
CREATE TABLE IF NOT EXISTS communications (
    id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
    channel_type ENUM('email', 'sms', 'notification') NOT NULL,
    recipient_email VARCHAR(255),
    recipient_phone VARCHAR(50),
    subject VARCHAR(255),
    content TEXT NOT NULL,
    status ENUM('queued', 'sent', 'failed') DEFAULT 'queued',
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX idx_blogs_slug ON blogs(slug);
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_applications_email ON applications(email);
CREATE INDEX idx_portfolio_category ON portfolio_startups(category);
CREATE INDEX idx_faqs_category ON faqs(category);
CREATE INDEX idx_blogs_category ON blogs(category);
CREATE INDEX idx_blogs_published_at ON blogs(published_at);
CREATE INDEX idx_tales_slug ON tales(slug);
CREATE INDEX idx_payments_transaction_code ON payments(transaction_code);
CREATE INDEX idx_packages_active ON packages(is_active);

-- Routers Table
CREATE TABLE IF NOT EXISTS routers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(100),
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    package_id CHAR(36),
    router_id INT,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    next_payment_date DATE,
    account_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    FOREIGN KEY (router_id) REFERENCES routers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Add indexes for customers and routers
CREATE INDEX idx_customers_username ON customers(username);
CREATE INDEX idx_customers_phone ON customers(phone);
CREATE INDEX idx_customers_status ON customers(status);
CREATE INDEX idx_routers_name ON routers(name);
