<?php
require_once 'db.php';

header('Content-Type: application/json');

/**
 * Handle successful response
 */
function sendSuccess($message, $data = null)
{
    echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
    exit;
}

/**
 * Handle error response
 */
function sendError($message)
{
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? '';

if (!$action) {
    sendError('No action specified');
}

switch ($action) {
    case 'get_blogs':
        try {
            $stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
            $blogs = $stmt->fetchAll();
            sendSuccess('Blogs retrieved', $blogs);
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'get_blog':
        $id = $_GET['id'] ?? $input['id'] ?? '';
        if (!$id) sendError('Blog ID or slug required');
        try {
            $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ? OR slug = ? LIMIT 1");
            $stmt->execute([$id, $id]);
            $blog = $stmt->fetch();
            if (!$blog) sendError('Blog not found');
            sendSuccess('Blog retrieved', $blog);
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'create_blog':
        $required = ['title', 'content', 'category'];
        foreach ($required as $field) {
            if (empty($input[$field]))
                sendError("Missing required field: $field");
        }

        try {
            $id = bin2hex(random_bytes(16));
            $title = $input['title'];
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

            $stmt = $pdo->prepare("INSERT INTO blogs (
                id, slug, title, content, excerpt, category, author_name, image_url, published_at, read_time, featured
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");

            $stmt->execute([
                $id,
                $slug,
                $title,
                $input['content'],
                $input['excerpt'] ?? substr(strip_tags($input['content']), 0, 160),
                $input['category'],
                $input['author_name'] ?? 'GrowthSpire Team',
                $input['image_url'] ?? null,
                $input['read_time'] ?? '5 min read',
                $input['featured'] ?? 0
            ]);

            sendSuccess('Blog published successfully', ['id' => $id]);
        }
        catch (Exception $e) {
            sendError('SQL Error: ' . $e->getMessage());
        }
        break;

    case 'update_blog':
        $id = $input['id'] ?? '';
        if (!$id)
            sendError('Blog ID required');

        try {
            $fields = [];
            $params = [];
            $allowed = ['title', 'content', 'excerpt', 'category', 'author_name', 'image_url', 'read_time', 'featured'];

            foreach ($allowed as $key) {
                if (isset($input[$key])) {
                    $fields[] = "$key = ?";
                    $params[] = $input[$key];
                }
            }

            if (isset($input['title'])) {
                $fields[] = "slug = ?";
                $params[] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['title'])));
            }

            if (empty($fields))
                sendError('No fields to update');

            $params[] = $id;
            $stmt = $pdo->prepare("UPDATE blogs SET " . implode(', ', $fields) . " WHERE id = ?");
            $stmt->execute($params);

            sendSuccess('Blog updated successfully');
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'delete_blog':
        $id = $input['id'] ?? '';
        if (!$id)
            sendError('Blog ID required');
        try {
            $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
            $stmt->execute([$id]);
            sendSuccess('Blog deleted');
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    default:
        sendError('Invalid action: ' . $action);
        break;
}
?>
