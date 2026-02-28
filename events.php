<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'get_events':
        try {
            $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
            $events = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $events]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_event':
        $title = $input['title'] ?? '';
        $date = $input['event_date'] ?? '';
        $type = $input['event_type'] ?? '';

        if (!$title || !$date || !$type) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO events (id, title, event_date, event_type) VALUES (UUID(), ?, ?, ?)");
            $stmt->execute([$title, $date, $type]);
            echo json_encode(['success' => true, 'message' => 'Event created successfully']);
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
