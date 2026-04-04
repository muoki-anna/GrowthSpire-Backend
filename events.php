<?php
require_once 'db.php';
require_once 'mailer.php';


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
$action = $_GET['action'] ?? $input['action'] ?? '';

switch ($action) {
    case 'get_events':
        try {
            $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
            $events = $stmt->fetchAll();
            sendSuccess('Events retrieved', $events);
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'create_event':
        $title = $input['title'] ?? '';
        $date = $input['event_date'] ?? '';
        $type = $input['event_type'] ?? '';

        if (!$title || !$date || !$type) {
            sendError('Missing required fields');
        }

        try {
            $id = bin2hex(random_bytes(16));
            $stmt = $pdo->prepare("INSERT INTO events (
                id, title, description, event_date, start_time, end_time, location, event_type, image_url, registration_link, featured
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $id,
                $title,
                $input['description'] ?? '',
                $date,
                $input['start_time'] ?? '09:00:00',
                $input['end_time'] ?? '17:00:00',
                $input['location'] ?? 'Online',
                $type,
                $input['image_url'] ?? null,
                $input['registration_link'] ?? null,
                $input['featured'] ?? 0
            ]);

            // NOTIFY ADMIN
            try {
                $subject = "New Event Created: " . $title;
                $body = "
                    <p>A new event has been scheduled on GrowthSpire.</p>
                    <ul>
                        <li><strong>Title:</strong> $title</li>
                        <li><strong>Date:</strong> $date</li>
                        <li><strong>Type:</strong> $type</li>
                        <li><strong>Location:</strong> " . ($input['location'] ?? 'Online') . "</li>
                    </ul>
                ";
                GrowthSpireMailer::send(ADMIN_EMAIL, $subject, $body);
            } catch (Exception $e) {
                error_log("Event notification error: " . $e->getMessage());
            }

            sendSuccess('Event created successfully', ['id' => $id]);
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'update_event':
        $id = $input['id'] ?? '';
        if (!$id) sendError('Event ID required');

        try {
            $fields = [];
            $params = [];
            $allowed = ['title', 'description', 'event_date', 'start_time', 'end_time', 'location', 'event_type', 'image_url', 'registration_link', 'featured'];

            foreach ($allowed as $key) {
                if (isset($input[$key])) {
                    $fields[] = "$key = ?";
                    $params[] = $input[$key];
                }
            }

            if (empty($fields)) sendError('No fields to update');

            $params[] = $id;
            $stmt = $pdo->prepare("UPDATE events SET " . implode(', ', $fields) . " WHERE id = ?");
            $stmt->execute($params);

            sendSuccess('Event updated successfully');
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'delete_event':
        $id = $input['id'] ?? '';
        if (!$id) sendError('Event ID required');
        try {
            $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
            $stmt->execute([$id]);
            sendSuccess('Event deleted');
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
