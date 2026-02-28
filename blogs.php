<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'get_blogs':
        try {
            $stmt = $pdo->query("SELECT * FROM blogs ORDER BY published_at DESC");
            $blogs = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $blogs]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_blog':
        $user_id = $input['user_id'] ?? '';
        $title = $input['title'] ?? '';
        $content = $input['content'] ?? '';
        $category = $input['category'] ?? '';

        if (!$user_id || !$title || !$content || !$category) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO blogs (id, slug, title, content, category, author_name, published_at) VALUES (UUID(), ?, ?, ?, ?, ?, NOW())");
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            // Fetch author name from user_id if needed, but for now using a placeholder
            $stmt->execute([$slug, $title, $content, $category, 'Admin']);
            echo json_encode(['success' => true, 'message' => 'Blog published successfully']);
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
