<?php
// adminseeder.php - Admin User Seeder
require_once 'config.php';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting admin user seeding...\n";
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute(['admin', 'admin@growthspire.com']);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "Admin user already exists. Updating password...\n";
        
        // Update existing admin user
        $passwordHash = password_hash('123admin321', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
        $stmt->execute([$passwordHash, 'admin']);
        
        echo "Admin user password updated successfully!\n";
    } else {
        echo "Creating new admin user...\n";
        
        // Create new admin user
        $passwordHash = password_hash('123admin321', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (id, full_name, username, email, password_hash, role) VALUES 
            (UUID(), 'Administrator', 'admin', 'admin@growthspire.com', ?, 'admin')");
        $stmt->execute([$passwordHash]);
        
        echo "Admin user created successfully!\n";
    }
    
    // Verify the user was created/updated
    $stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "\n✅ Admin user details:\n";
        echo "   - Username: {$user['username']}\n";
        echo "   - Email: {$user['email']}\n";
        echo "   - Role: {$user['role']}\n";
        echo "   - Password: 123admin321\n";
        echo "\nYou can now login with:\n";
        echo "   Username: admin\n";
        echo "   Password: 123admin321\n";
    } else {
        echo "❌ Error: Admin user not found after seeding.\n";
    }
    
} catch (Exception $e) {
    die("Error during admin seeding: " . $e->getMessage() . "\n");
}
?>