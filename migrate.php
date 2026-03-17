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

    // 2. Schema Creation
    echo "Building database schema...\n";

    $schema = [
        "users" => "CREATE TABLE IF NOT EXISTS users (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            full_name VARCHAR(255) NOT NULL,
            username VARCHAR(255) UNIQUE,
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
            funding_amount VARCHAR(100),
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
            linkedin_profile VARCHAR(255),
            website_url VARCHAR(255),
            funding_needed_range VARCHAR(255),
            team_size VARCHAR(50),
            pitch_deck_url VARCHAR(255),
            investor_type VARCHAR(255),
            investment_range VARCHAR(255),
            focus_areas VARCHAR(255),
            reviewer_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "mentors" => "CREATE TABLE IF NOT EXISTS mentors (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            name VARCHAR(255) NOT NULL,
            role VARCHAR(255),
            company VARCHAR(255),
            specialties TEXT,
            email VARCHAR(255),
            linkedin_url VARCHAR(255),
            image_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "accelerator_programs" => "CREATE TABLE IF NOT EXISTS accelerator_programs (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            name VARCHAR(255) NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            status ENUM('Upcoming', 'Active', 'Completed') DEFAULT 'Upcoming',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "accelerator_resources" => "CREATE TABLE IF NOT EXISTS accelerator_resources (
            id VARCHAR(36) PRIMARY KEY DEFAULT (UUID()),
            title VARCHAR(255) NOT NULL,
            resource_type VARCHAR(100),
            file_format VARCHAR(50),
            size_info VARCHAR(50),
            file_url VARCHAR(255),
            category VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($schema as $tableName => $sql) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS $tableName");
            $pdo->exec($sql);
            echo "- Table '$tableName' verified.\n";
        } catch (Exception $e) {
            echo "- Error creating table '$tableName': " . $e->getMessage() . "\n";
        }
    }

    // 3. SEEDING
    echo "Seeding comprehensive data...\n";

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach (array_keys($schema) as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Seed Users
    $pdo->exec("INSERT INTO users (id, full_name, username, email, password_hash, role) VALUES 
        (UUID(), 'Dennis Mutuku', 'dennis', 'dennis@growthspire.com', '" . password_hash('admin321', PASSWORD_DEFAULT) . "', 'admin'),
        (UUID(), 'Admin User', 'admin', 'admin@growthspire.com', '" . password_hash('admin321', PASSWORD_DEFAULT) . "', 'admin')");

    // Seed Blogs
    $pdo->exec("INSERT INTO blogs (id, title, slug, excerpt, category, author_name, read_time, content, featured, published_at, image_url) VALUES 
        (UUID(), 'The 2024 Startup Guide', 'startup-guide-2024', 'Navigating the complex landscape of starting a business in 2024.', 'Strategy', 'Sarah Jenkins', '5 min', 'A comprehensive guide to launching your startup in the current economic climate. Focus on lean methodologies and customer discovery.', 1, NOW(), 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?q=80&w=1000&auto=format&fit=crop'),
        (UUID(), 'Fintech Disruptions in Africa', 'fintech-disruptions', 'How mobile money is shaping the financial future of the continent.', 'Technology', 'Mike Ross', '8 min', 'How modern payments are changing the way we do business in East Africa. The rise of interoperability and cross-border payments.', 0, NOW(), 'https://images.unsplash.com/photo-1563986768609-322da13575f3?q=80&w=1000&auto=format&fit=crop'),
        (UUID(), 'Building Sustainable Agtech', 'sustainable-agtech', 'The intersection of technology and agriculture for a better future.', 'AgriTech', 'Emma Watson', '6 min', 'Exploring how IoT and AI are helping farmers in Kenya optimize their yields while reducing waste.', 1, NOW(), 'https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?q=80&w=1000&auto=format&fit=crop'),
        (UUID(), 'Team Culture: The Foundation', 'team-culture-matters', 'Why your first 10 hires will define your company future for years to come.', 'Team', 'Emma Watson', '4 min', 'Practical tips on hiring for culture fit and building a diverse, inclusive team from day one.', 0, NOW(), 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=1000&auto=format&fit=crop')");

    // Seed Events
    $pdo->exec("INSERT INTO events (id, title, event_date, start_time, end_time, event_type, location, description, image_url, featured, registration_link) VALUES 
        (UUID(), 'GrowthSpire Demo Day', '2024-06-15', '09:00:00', '17:00:00', 'In-Person', 'Nairobi Garage, Westlands', 'Our flagship event where cohort startups pitch to global investors.', 'https://images.unsplash.com/photo-1540575861501-7ad05823c9f5?q=80&w=1000&auto=format&fit=crop', 1, 'https://lu.ma/growthspire-demo-day'),
        (UUID(), 'Investor Speed Dating', '2024-04-20', '14:00:00', '16:00:00', 'Online', 'Zoom Virtual Hub', 'Connect 1-on-1 with leading angels and VCs in this high-energy session.', 'https://images.unsplash.com/photo-1515187029135-18ee286d815b?q=80&w=1000&auto=format&fit=crop', 0, 'https://zoom.us/j/growthspire-investor'),
        (UUID(), 'HealthTech Founders Meetup', '2024-05-12', '18:00:00', '21:00:00', 'In-Person', 'iHub, Nairobi', 'Networking event for HealthTech founders and medical practitioners.', 'https://images.unsplash.com/photo-1505373630103-89bd602523f2?q=80&w=1000&auto=format&fit=crop', 0, 'https://meetup.com/healthtech-meetup'),
        (UUID(), 'Tech Innovation Workshop', '2024-03-10', '10:00:00', '15:00:00', 'Hybrid', 'Mombasa Hub', 'A hands-on workshop on building scalable architectures for SaaS startups.', 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1000&auto=format&fit=crop', 0, 'https://growthspire.com/workshop-reg')");

    // Seed Startups
    $pdo->exec("INSERT INTO portfolio_startups (id, name, slug, founder, category, description, stage, status, founded_year, joined_at, funding_amount, logo_url) VALUES 
        (UUID(), 'AgroMind', 'agromind', 'Peter Pan', 'AgriTech', 'AI-driven irrigation and soil health monitoring for small-scale farmers.', 'Seed', 'Active', 2022, '2023-06-15', 'KES 15M', 'https://logo.clearbit.com/agromind.ai'),
        (UUID(), 'HealthChain', 'healthchain', 'Dr. Strange', 'HealthTech', 'Blockchain-based medical records management for secure data sharing.', 'Pre-Seed', 'Accelerated', 2023, '2023-11-01', 'KES 5M', 'https://logo.clearbit.com/healthchain.io'),
        (UUID(), 'PayJet', 'payjet', 'Wanda Maximoff', 'Fintech', 'Real-time B2B payment platform for African SMEs.', 'Idea', 'Pending', 2024, '2024-01-10', 'N/A', 'https://logo.clearbit.com/stripe.com'),
        (UUID(), 'NexaAI', 'nexaai', 'John Smith', 'Artificial Intelligence', 'Enterprise AI solutions for automated customer support and workflows.', 'Series B', 'Active', 2021, '2022-03-20', 'KES 325M', 'https://logo.clearbit.com/nexa.ai'),
        (UUID(), 'FinFlow', 'finflow', 'Alice Cooper', 'FinTech', 'Next-generation payment infrastructure for e-commerce.', 'Series A', 'Active', 2022, '2023-01-15', 'KES 234M', 'https://logo.clearbit.com/finflow.com'),
        (UUID(), 'EduFlow', 'eduflow', 'Tony Stark', 'EdTech', 'Adaptive learning platform for primary schools in rural areas.', 'Series A', 'Active', 2021, '2023-01-10', 'KES 50M', 'https://logo.clearbit.com/coursera.org')");

    // Seed Sponsors
    $pdo->exec("INSERT INTO sponsors (id, name, website_url, logo_url, display_order) VALUES 
        (UUID(), 'Safaricom', 'https://safaricom.co.ke', 'https://logo.clearbit.com/safaricom.co.ke', 1),
        (UUID(), 'Equity Bank', 'https://equitygroupholdings.com', 'https://logo.clearbit.com/equitygroupholdings.com', 2),
        (UUID(), 'KCB Group', 'https://kcbgroup.com', 'https://logo.clearbit.com/kcbgroup.com', 3),
        (UUID(), 'Google Africa', 'https://africa.google', 'https://logo.clearbit.com/google.com', 4),
        (UUID(), 'Microsoft 4Afrika', 'https://microsoft.com/africa', 'https://logo.clearbit.com/microsoft.com', 5),
        (UUID(), 'MTN Group', 'https://mtn.com', 'https://logo.clearbit.com/mtn.com', 6),
        (UUID(), 'Mastercard Foundation', 'https://mastercardfdn.org', 'https://logo.clearbit.com/mastercard.com', 7)");

    // Seed Mentors
    $pdo->exec("INSERT INTO mentors (id, name, role, company, specialties, email, linkedin_url, image_url) VALUES 
        (UUID(), 'Elon Musk', 'CEO', 'Tesla', 'Engineering, Scaling, Fundraising', 'elon@tesla.com', 'https://linkedin.com/in/elonmusk', 'https://images.unsplash.com/photo-1543132220-4bf3de6e10ae?q=80&w=400&auto=format&fit=crop'),
        (UUID(), 'Jack Dorsey', 'Founder', 'Block', 'Product Strategy, FinTech, Branding', 'jack@block.xyz', 'https://linkedin.com/in/jackdorsey', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=400&auto=format&fit=crop'),
        (UUID(), 'Ngozi Okonjo-Iweala', 'Director General', 'WTO', 'Economics, Global Trade, Policy', 'ngozi@wto.org', 'https://linkedin.com/in/ngozi', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=400&auto=format&fit=crop'),
        (UUID(), 'Strive Masiyiwa', 'Founder', 'Econet', 'Telecomm, Philanthropy, Leadership', 'strive@econet.com', 'https://linkedin.com/in/strive', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=400&auto=format&fit=crop')");

    // Seed Accelerator Programs
    $pdo->exec("INSERT INTO accelerator_programs (id, name, start_date, end_date, status) VALUES 
        (UUID(), 'Winter Cohort 2024', '2024-01-15', '2024-04-15', 'Active'),
        (UUID(), 'Summer Cohort 2024', '2024-06-01', '2024-08-31', 'Upcoming'),
        (UUID(), 'Fall Cohort 2023', '2023-09-01', '2023-11-30', 'Completed')");

    // Seed Accelerator Resources
    $pdo->exec("INSERT INTO accelerator_resources (id, title, resource_type, file_format, size_info, file_url, category) VALUES 
        (UUID(), 'Pitch Deck Template', 'Template', 'PPTX', '2.5 MB', 'https://growthspire.com/resources/pitch-deck.pptx', 'Fundraising'),
        (UUID(), 'Term Sheet Basics', 'Document', 'PDF', '1.2 MB', 'https://growthspire.com/resources/term-sheet.pdf', 'Legal'),
        (UUID(), 'Unit Economics Calculator', 'Template', 'XLSX', '0.5 MB', 'https://growthspire.com/resources/economics.xlsx', 'Finance'),
        (UUID(), 'Growth Hacking Guide', 'Video Course', 'MP4', '45 mins', 'https://growthspire.com/resources/growth-hacking.mp4', 'Marketing')");

    echo "Seeding completed - Success.\n";

} catch (Exception $e) {
    die("Error during seed: " . $e->getMessage() . "\n");
}
?>
