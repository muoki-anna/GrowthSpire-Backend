<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'growthspire');

// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

define('ADMIN_EMAIL', 'admin@growthspire.org'); // Set this to the actual admin email
define('EMAIL_API_URL', 'https://api.growthspire.org/email.php');
define('EMAIL_FROM', 'no-reply@growthspire.org');
define('EMAIL_PASS', 'zRNZg=8s9KkHa-+2');
define('EMAIL_NAME', 'GrowthSpire');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
