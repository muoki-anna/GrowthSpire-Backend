<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$user_id = $input['user_id'] ?? '';

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

switch ($action) {
    case 'get_startups':
        try {
            $stmt = $pdo->query("SELECT * FROM portfolio_startups ORDER BY joined_at DESC");
            $startups = [];
            while ($row = $stmt->fetch()) {
                $startups[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'founder' => $row['founder'],
                    'stage' => $row['stage'],
                    'sector' => $row['category'],
                    'status' => $row['status'],
                    'joined' => $row['joined_at']
                ];
            }
            echo json_encode(['success' => true, 'data' => $startups]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_startup':
        $name = $input['name'] ?? '';
        $founder = $input['founder'] ?? '';
        $category = $input['sector'] ?? '';

        if (!$name || !$founder || !$category) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO portfolio_startups (id, name, slug, founder, category, joined_at) VALUES (UUID(), ?, ?, ?, ?, CURDATE())");
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            $stmt->execute([$name, $slug, $founder, $category]);
            echo json_encode(['success' => true, 'message' => 'Startup added successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_startup':
        $id = $input['id'] ?? '';
        $stage = $input['stage'] ?? '';
        $status = $input['status'] ?? '';

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Startup ID is required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE portfolio_startups SET stage = ?, status = ? WHERE id = ?");
            $stmt->execute([$stage, $status, $id]);
            echo json_encode(['success' => true, 'message' => 'Startup updated successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_startup':
        $id = $input['id'] ?? '';
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Startup ID is required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM portfolio_startups WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Startup removed successfully']);
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
