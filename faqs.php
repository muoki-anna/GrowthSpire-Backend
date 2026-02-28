<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'get_faqs':
        try {
            $stmt = $pdo->query("SELECT * FROM faqs ORDER BY category, display_order");
            $faqs = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $faqs]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_faq':
        $category = $input['category'] ?? '';
        $question = $input['question'] ?? '';
        $answer = $input['answer'] ?? '';

        if (!$category || !$question || !$answer) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO faqs (id, category, question, answer) VALUES (UUID(), ?, ?, ?)");
            $stmt->execute([$category, $question, $answer]);
            echo json_encode(['success' => true, 'message' => 'FAQ created successfully']);
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
