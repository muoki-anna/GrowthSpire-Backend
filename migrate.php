<?php
// migrate.php - Comprehensive database migration and seeder
require_once 'config.php';

function columnExists($pdo, $table, $column) {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE ?");
        $stmt->execute([$column]);
        return $stmt->fetch() !== false;
    } catch (Exception $e) {
        return false;
    }
}

try {
    // 1. Database Creation
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Ensuring database exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);

    // Reconnect with DB name
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Schema Creation (Based on database.sql)
    echo "Building database schema...\n";

    $schema = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role VARCHAR(50) DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "blogs" => "CREATE TABLE IF NOT EXISTS blogs (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            slug VARCHAR(255) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            excerpt TEXT,
            content TEXT NOT NULL,
            category VARCHAR(100) NOT NULL,
            author_name VARCHAR(100) NOT NULL,
            published_at TIMESTAMP NULL,
            read_time VARCHAR(50), 
            featured BOOLEAN DEFAULT FALSE,
            image_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "events" => "CREATE TABLE IF NOT EXISTS events (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            title VARCHAR(255) NOT NULL,
            description TEXT,
            event_date DATE NOT NULL,
            start_time TIME,
            end_time TIME,
            location VARCHAR(255),
            event_type VARCHAR(50) NOT NULL,
            featured BOOLEAN DEFAULT FALSE,
            image_url VARCHAR(255),
            registration_link VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "portfolio_startups" => "CREATE TABLE IF NOT EXISTS portfolio_startups (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            founder VARCHAR(255),
            category VARCHAR(100) NOT NULL,
            description TEXT,
            stage VARCHAR(50),
            status VARCHAR(50) DEFAULT 'Active',
            website_url VARCHAR(255),
            logo_url VARCHAR(255),
            founded_year INT,
            joined_at DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "sponsors" => "CREATE TABLE IF NOT EXISTS sponsors (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            name VARCHAR(255) NOT NULL,
            logo_url VARCHAR(255),
            website_url VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "applications" => "CREATE TABLE IF NOT EXISTS applications (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            application_type VARCHAR(50) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            company_name VARCHAR(255) NOT NULL,
            industry VARCHAR(100),
            startup_stage VARCHAR(50),
            message TEXT NOT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
         "mentors" => "CREATE TABLE IF NOT EXISTS mentors (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            name VARCHAR(255) NOT NULL,
            role VARCHAR(255),
            company VARCHAR(255),
            specialties TEXT,
            email VARCHAR(255),
            image_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    foreach ($schema as $tableName => $sql) {
        $pdo->exec($sql);
        echo "- Table '$tableName' verified.\n";
    }

    // 3. SEEDING (Truncate and reload for a "whole" seed)
    echo "Seeding comprehensive data...\n";

    // Disable foreign key checks for truncation
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach (array_keys($schema) as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Seed Users
    $pdo->exec("INSERT INTO users (id, full_name, email, password_hash, role) VALUES 
        (UUID(), 'System Admin', 'admin@growthspire.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')");

    // Seed Blogs
    $pdo->exec("INSERT INTO blogs (id, title, slug, category, author_name, read_time, content, featured, published_at, image_url) VALUES 
        (UUID(), 'The 2024 Startup Guide', 'startup-guide-2024', 'Strategy', 'Sarah Jenkins', '5 min', 'A comprehensive guide to launching your startup in the current economic climate...', 1, NOW(), 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?q=80&w=1000&auto=format&fit=crop'),
        (UUID(), 'Fintech Disruptions', 'fintech-disruptions', 'Technology', 'Mike Ross', '8 min', 'How modern payments are changing the way we do business in East Africa...', 0, NOW(), 'https://images.unsplash.com/photo-1563986768609-322da13575f3?q=80&w=1000&auto=format&fit=crop'),
        (UUID(), 'Team Culture Matters', 'team-culture-matters', 'Team', 'Emma Watson', '4 min', 'Why your first 10 hires will define your company future for years to come...', 0, NOW(), 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=1000&auto=format&fit=crop')");

    // Seed Events
    $pdo->exec("INSERT INTO events (id, title, event_date, event_type, location, description, image_url, featured) VALUES 
        (UUID(), 'GrowthSpire Demo Day', '2024-06-15', 'In-Person', 'Nairobi Garage, Westlands', 'Our flagship event where cohort startups pitch to global investors.', 'https://images.unsplash.com/photo-1540575861501-7ad05823c9f5?q=80&w=1000&auto=format&fit=crop', 1),
        (UUID(), 'Investor Speed Dating', '2024-04-20', 'Online', 'Zoom Virtual Hub', 'Connect 1-on-1 with leading angels and VCs in this high-energy session.', 'https://images.unsplash.com/photo-1515187029135-18ee286d815b?q=80&w=1000&auto=format&fit=crop', 0),
        (UUID(), 'Tech Innovation Workshop', '2024-03-10', 'Hybrid', 'Mombasa Hub', 'A hands-on workshop on building scalable architectures for SaaS startups.', 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1000&auto=format&fit=crop', 0)");

    // Seed Startups
    $pdo->exec("INSERT INTO portfolio_startups (id, name, slug, founder, category, stage, status, founded_year, joined_at, logo_url) VALUES 
        (UUID(), 'AgroMind', 'agromind', 'Peter Pan', 'AgriTech', 'Seed', 'Active', 2022, CURDATE(), 'https://logo.clearbit.com/agromind.ai'),
        (UUID(), 'HealthChain', 'healthchain', 'Dr. Strange', 'HealthTech', 'Pre-Seed', 'Accelerated', 2023, CURDATE(), 'https://logo.clearbit.com/healthchain.io'),
        (UUID(), 'PayJet', 'payjet', 'Wanda Maximoff', 'Fintech', 'Idea', 'Pending', 2024, CURDATE(), 'https://logo.clearbit.com/stripe.com'),
        (UUID(), 'EduFlow', 'eduflow', 'Tony Stark', 'EdTech', 'Series A', 'Active', 2021, '2023-01-10', 'https://logo.clearbit.com/coursera.org')");

    // Seed Sponsors
    $pdo->exec("INSERT INTO sponsors (id, name, website_url, logo_url, display_order) VALUES 
        (UUID(), 'Safaricom', 'https://safaricom.co.ke', 'https://logo.clearbit.com/safaricom.co.ke', 1),
        (UUID(), 'Equity Bank', 'https://equitygroupholdings.com', 'https://logo.clearbit.com/equitygroupholdings.com', 2),
        (UUID(), 'Microsoft', 'https://microsoft.com', 'https://logo.clearbit.com/microsoft.com', 3),
        (UUID(), 'Google for Startups', 'https://startups.google.com', 'https://logo.clearbit.com/google.com', 4)");

    // Seed Applications
    $pdo->exec("INSERT INTO applications (id, application_type, full_name, email, phone, company_name, industry, status, message) VALUES 
        (UUID(), 'startup', 'Bruce Wayne', 'bruce@waynecorp.com', '+254700111222', 'WayneTech Solutions', 'Smart Cities', 'pending', 'Looking to scale our AI-driven security systems across the region.'),
        (UUID(), 'startup', 'Clark Kent', 'clark@dailyplanet.com', '+254700333444', 'Krypto Media', 'Digital Media', 'under_review', 'Building a decentralized news platform for unbiased reporting.'),
        (UUID(), 'sponsor', 'Lex Luthor', 'lex@luthorcorp.com', '+254700555666', 'LuthorCorp', 'Conglomerate', 'accepted', 'We want to support the next generation of innovators through equity grants.')");

    // Seed Mentors
    $pdo->exec("INSERT INTO mentors (id, name, role, company, specialties, email, image_url) VALUES 
        (UUID(), 'Elon Musk', 'CEO', 'Tesla', 'Engineering, Scaling', 'elon@tesla.com', 'https://images.unsplash.com/photo-1543132220-4bf3de6e10ae?q=80&w=400&auto=format&fit=crop'),
        (UUID(), 'Jack Dorsey', 'Founder', 'Block', 'Product, FinTech', 'jack@block.xyz', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=400&auto=format&fit=crop')");

    echo "Seeding completed - Success.\n";

} catch (Exception $e) {
    die("Error during seed: " . $e->getMessage() . "\n");
}
?>
