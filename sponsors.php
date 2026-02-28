<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'get_sponsors':
        try {
            $stmt = $pdo->query("SELECT * FROM sponsors WHERE is_active = 1 ORDER BY display_order");
            $sponsors = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $sponsors]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_sponsor':
        $name = $input['name'] ?? '';
        $logo = $input['logo_url'] ?? '';
        $website = $input['website_url'] ?? '';

        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO sponsors (id, name, logo_url, website_url) VALUES (UUID(), ?, ?, ?)");
            $stmt->execute([$name, $logo, $website]);
            echo json_encode(['success' => true, 'message' => 'Sponsor added successfully']);
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
