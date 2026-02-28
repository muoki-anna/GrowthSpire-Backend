<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'send_message':
        $type = $input['channel_type'] ?? '';
        $email = $input['recipient_email'] ?? '';
        $phone = $input['recipient_phone'] ?? '';
        $subject = $input['subject'] ?? '';
        $content = $input['content'] ?? '';

        if (!$type || !$content) {
            echo json_encode(['success' => false, 'message' => 'Type and content are required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO communications (id, channel_type, recipient_email, recipient_phone, subject, content, status) VALUES (UUID(), ?, ?, ?, ?, ?, 'queued')");
            $stmt->execute([$type, $email, $phone, $subject, $content]);
            echo json_encode(['success' => true, 'message' => 'Message queued for sending']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_history':
        try {
            $stmt = $pdo->query("SELECT * FROM communications ORDER BY created_at DESC LIMIT 50");
            $history = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $history]);
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
