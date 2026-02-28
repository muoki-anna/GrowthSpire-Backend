<?php
require_once 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch ($action) {
    case 'submit_application':
        $type = $input['application_type'] ?? '';
        $name = $input['full_name'] ?? '';
        $email = $input['email'] ?? '';
        $phone = $input['phone'] ?? '';
        $company = $input['company_name'] ?? '';
        $message = $input['message'] ?? '';

        if (!$type || !$name || !$email || !$phone || !$company || !$message) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO applications (id, application_type, full_name, email, phone, company_name, message) VALUES (UUID(), ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$type, $name, $email, $phone, $company, $message]);
            echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get_applications':
        $user_id = $input['user_id'] ?? '';
        if (!$user_id) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit();
        }

        try {
            $stmt = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC");
            $apps = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $apps]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_status':
        $user_id = $input['user_id'] ?? '';
        $app_id = $input['app_id'] ?? '';
        $status = $input['status'] ?? '';

        if (!$user_id || !$app_id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing required data']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
            $stmt->execute([$status, $app_id]);
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
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
