<?php
require_once 'db.php';
require_once 'mailer.php';


header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'login':
        $username = $input['username'] ?? $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (!$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'Username and password are required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // NOTIFY ADMIN ON LOGIN
                try {
                    $login_time = date('Y-m-d H:i:s');
                    $user_agent = $_SERVER['HTTP_USER_AGENT'];
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $subject = "Security Alert: Login Detected - " . ($user['full_name'] ?? $user['username']);
                    $body = "
                        <p>A login was detected on the GrowthSpire platform.</p>
                        <ul>
                            <li><strong>User:</strong> " . htmlspecialchars($user['full_name'] ?? $user['username']) . "</li>
                            <li><strong>Email:</strong> " . htmlspecialchars($user['email']) . "</li>
                            <li><strong>Role:</strong> " . htmlspecialchars($user['role'] ?? 'user') . "</li>
                            <li><strong>Time:</strong> $login_time</li>
                            <li><strong>IP Address:</strong> $ip</li>
                        </ul>
                        <p>If this was not you, please secure the account immediately.</p>
                    ";
                    GrowthSpireMailer::send(ADMIN_EMAIL, $subject, $body);
                } catch (Exception $e) {
                    error_log("Login notification error: " . $e->getMessage());
                }

                // Remove password hash from response
                unset($user['password_hash']);
                echo json_encode(['success' => true, 'data' => $user]);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'register':
        $name = $input['full_name'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (!$name || !$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        try {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (id, full_name, email, password_hash) VALUES (UUID(), ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed]);

            // SEND WELCOME EMAIL
            try {
                $subject = "Welcome to GrowthSpire, $name!";
                $body = "
                    <p>Hello " . htmlspecialchars($name) . ",</p>
                    <p>Welcome to <strong>GrowthSpire</strong>! Your account has been successfully created.</p>
                    <p>You can now access our platform to manage your applications, join programs, and connect with mentors.</p>
                    <p><strong>Your login details:</strong></p>
                    <ul>
                        <li><strong>Email:</strong> " . htmlspecialchars($email) . "</li>
                    </ul>
                    <p>We're excited to have you on board!</p>
                    <p>Best regards,<br/>The GrowthSpire Team</p>
                ";
                GrowthSpireMailer::send($email, $subject, $body);
            } catch (Exception $e) {
                error_log("Welcome email error: " . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => 'User registered successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
?>
