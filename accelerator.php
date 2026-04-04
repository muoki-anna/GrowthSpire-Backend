<?php
require_once 'db.php';
require_once 'mailer.php';


header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Support both JSON input and $_GET for actions
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    // Programs
    case 'get_programs':
        try {
            $stmt = $pdo->query("SELECT * FROM accelerator_programs ORDER BY created_at DESC");
            $programs = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $programs]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_program':
        if ($method !== 'POST')
            exit;
        try {
            $stmt = $pdo->prepare("INSERT INTO accelerator_programs (id, name, start_date, end_date, status) VALUES (UUID(), ?, ?, ?, ?)");
            $stmt->execute([$input['name'], $input['start_date'], $input['end_date'], $input['status'] ?? 'Upcoming']);
            echo json_encode(['success' => true]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_program':
        if ($method !== 'POST')
            exit;
        try {
            $stmt = $pdo->prepare("UPDATE accelerator_programs SET name = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
            $stmt->execute([$input['name'], $input['start_date'], $input['end_date'], $input['status'], $input['id']]);
            echo json_encode(['success' => true]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_program':
        try {
            $id = $input['id'] ?? $_GET['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM accelerator_programs WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // Mentors
    case 'get_mentors':
        try {
            $stmt = $pdo->query("SELECT * FROM mentors ORDER BY created_at DESC");
            $mentors = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $mentors]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_mentor':
        if ($method !== 'POST')
            exit;
        try {
            $stmt = $pdo->prepare("INSERT INTO mentors (id, name, role, company, specialties, email, linkedin_url) VALUES (UUID(), ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$input['name'], $input['role'], $input['company'], $input['specialties'], $input['email'], $input['linkedin_url']]);

            // NOTIFY MENTOR
            try {
                if (!empty($input['email'])) {
                    $subject = "Welcome to GrowthSpire Network, " . $input['name'];
                    $body = "
                        <p>Hello " . htmlspecialchars($input['name']) . ",</p>
                        <p>We are delighted to inform you that you have been added to the <strong>GrowthSpire Mentor Network</strong>!</p>
                        <p>As a mentor with " . htmlspecialchars($input['company'] ?? 'GrowthSpire') . ", your expertise in <strong>" . htmlspecialchars($input['specialties'] ?? 'innovation') . "</strong> will be invaluable to the startups in our programs.</p>
                        <p>We'll reach out soon with more details about upcoming sessions and startup pairings.</p>
                        <p>Best regards,<br/>The GrowthSpire Team</p>
                    ";
                    GrowthSpireMailer::send($input['email'], $subject, $body);
                }
            } catch (Exception $e) {
                error_log("Mentor notification error: " . $e->getMessage());
            }

            echo json_encode(['success' => true]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update_mentor':
        if ($method !== 'POST')
            exit;
        try {
            $stmt = $pdo->prepare("UPDATE mentors SET name = ?, role = ?, company = ?, specialties = ?, email = ?, linkedin_url = ? WHERE id = ?");
            $stmt->execute([$input['name'], $input['role'], $input['company'], $input['specialties'], $input['email'], $input['linkedin_url'], $input['id']]);
            echo json_encode(['success' => true]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_mentor':
        try {
            $id = $input['id'] ?? $_GET['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM mentors WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    // Resources
    case 'get_resources':
        try {
            $stmt = $pdo->query("SELECT * FROM accelerator_resources ORDER BY created_at DESC");
            $resources = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $resources]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create_resource':
        if ($method !== 'POST')
            exit;
        try {
            $stmt = $pdo->prepare("INSERT INTO accelerator_resources (id, title, resource_type, file_format, size_info, file_url, category) VALUES (UUID(), ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$input['title'], $input['resource_type'], $input['file_format'], $input['size_info'], $input['file_url'], $input['category']]);
            echo json_encode(['success' => true]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_resource':
        try {
            $id = $input['id'] ?? $_GET['id'] ?? '';
            $stmt = $pdo->prepare("DELETE FROM accelerator_resources WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
