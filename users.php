<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'login':
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (!$email || !$password) {
            echo json_encode(['success' => false, 'message' => 'Email and password are required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
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
