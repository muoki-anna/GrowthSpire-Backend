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
function sendError($message, $code = 400)
{
    // http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? '';

if (!$action) {
    sendError('No action specified');
}

switch ($action) {
    case 'submit_application':
        $required = ['application_type', 'full_name', 'email', 'phone', 'company_name', 'message'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                sendError("Missing required field: $field");
            }
        }

        try {
            $id = bin2hex(random_bytes(16)); // Cleaner UUID alternative for PHP 7+

            $stmt = $pdo->prepare("INSERT INTO applications (
                id, application_type, full_name, email, phone, linkedin_profile, 
                company_name, website_url, startup_stage, industry, funding_needed_range, 
                team_size, pitch_deck_url, investor_type, investment_range, focus_areas, message, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $id,
                $input['application_type'],
                $input['full_name'],
                $input['email'],
                $input['phone'],
                $input['linkedin_profile'] ?? null,
                $input['company_name'],
                $input['website_url'] ?? null,
                $input['startup_stage'] ?? null,
                $input['industry'] ?? null,
                $input['funding_needed_range'] ?? null,
                $input['team_size'] ?? null,
                $input['pitch_deck_url'] ?? null,
                $input['investor_type'] ?? null,
                $input['investment_range'] ?? null,
                $input['focus_areas'] ?? null,
                $input['message'],
                'pending'
            ]);

            sendSuccess('Application submitted successfully', ['id' => $id]);
        }
        catch (Exception $e) {
            sendError('SQL Error: ' . $e->getMessage());
        }
        break;

    case 'get_applications':
        try {
            $stmt = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC");
            $apps = $stmt->fetchAll();
            sendSuccess('Applications retrieved', $apps);
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'get_application':
        $id = $input['id'] ?? '';
        if (!$id)
            sendError('ID required');

        try {
            $stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
            $stmt->execute([$id]);
            $app = $stmt->fetch();
            if ($app) {
                sendSuccess('Application retrieved', $app);
            }
            else {
                sendError('Application not found', 404);
            }
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'update_status':
        $id = $input['id'] ?? $input['app_id'] ?? '';
        $status = $input['status'] ?? '';
        $notes = $input['reviewer_notes'] ?? null;

        if (!$id || !$status)
            sendError('ID and status required');

        try {
            $stmt = $pdo->prepare("UPDATE applications SET status = ?, reviewer_notes = ? WHERE id = ?");
            $stmt->execute([$status, $notes, $id]);
            sendSuccess('Status updated successfully');
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'delete_application':
        $id = $input['id'] ?? '';
        if (!$id)
            sendError('ID required');

        try {
            $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
            $stmt->execute([$id]);
            sendSuccess('Application deleted');
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
