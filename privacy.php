<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'get_policies':
        try {
            $stmt = $pdo->query("SELECT * FROM privacy_policies WHERE is_active = 1 ORDER BY effective_date DESC");
            $policies = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $policies]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_policy':
        $user_id = $input['user_id'] ?? '';
        $type = $input['type'] ?? '';
        $content = $input['content'] ?? '';

        if (!$user_id || !$type || !$content) {
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO privacy_policies (id, type, version, content, is_active, effective_date) VALUES (UUID(), ?, ?, ?, 1, CURDATE())");
            $stmt->execute([$type, date('Y-m'), $content]);
            echo json_encode(['success' => true, 'message' => 'Policy updated successfully']);
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
