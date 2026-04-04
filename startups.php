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
    case 'get_startups':
        try {
            $stmt = $pdo->query("SELECT * FROM portfolio_startups ORDER BY joined_at DESC");
            $startups = $stmt->fetchAll();
            // Map keys if necessary for frontend compatibility
            $formatted = array_map(function ($row) {
                return [
                'id' => $row['id'],
                'name' => $row['name'],
                'founder' => $row['founder'],
                'stage' => $row['stage'],
                'sector' => $row['category'],
                'status' => $row['status'],
                'joined' => $row['joined_at'],
                'description' => $row['description'],
                'website' => $row['website_url'],
                'logo' => $row['logo_url']
                ];
            }, $startups);
            sendSuccess('Startups retrieved', $formatted);
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'create_startup':
        $name = $input['name'] ?? '';
        $founder = $input['founder'] ?? '';
        $category = $input['sector'] ?? $input['category'] ?? '';

        if (!$name || !$founder || !$category) {
            sendError('Missing required fields: name, founder, or sector');
        }

        try {
            $id = bin2hex(random_bytes(16));
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

            $stmt = $pdo->prepare("INSERT INTO portfolio_startups (
                id, name, slug, founder, category, description, website_url, logo_url, stage, status, joined_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())");

            $stmt->execute([
                $id,
                $name,
                $slug,
                $founder,
                $category,
                $input['description'] ?? null,
                $input['website_url'] ?? null,
                $input['logo_url'] ?? null,
                $input['stage'] ?? 'Idea',
                $input['status'] ?? 'Active'
            ]);

            // NOTIFY ADMIN
            try {
                $subject = "New Startup Added: " . $name;
                $body = "
                    <p>A new startup has been added to the GrowthSpire portfolio.</p>
                    <ul>
                        <li><strong>Name:</strong> " . htmlspecialchars($name) . "</li>
                        <li><strong>Founder:</strong> " . htmlspecialchars($founder) . "</li>
                        <li><strong>Sector:</strong> " . htmlspecialchars($category) . "</li>
                        <li><strong>Stage:</strong> " . htmlspecialchars($input['stage'] ?? 'Idea') . "</li>
                    </ul>
                ";
                GrowthSpireMailer::send(ADMIN_EMAIL, $subject, $body);
            } catch (Exception $e) {
                error_log("Startup notification error: " . $e->getMessage());
            }

            sendSuccess('Startup added successfully', ['id' => $id]);
        }
        catch (Exception $e) {
            sendError('SQL Error: ' . $e->getMessage());
        }
        break;

    case 'update_startup':
        $id = $input['id'] ?? '';
        if (!$id)
            sendError('Startup ID is required');

        try {
            $fields = [];
            $params = [];

            $allowed = ['name', 'founder', 'category', 'description', 'stage', 'status', 'website_url', 'logo_url'];
            foreach ($allowed as $key) {
                if (isset($input[$key])) {
                    $fields[] = "$key = ?";
                    $params[] = $input[$key];
                }
            }

            if (empty($fields))
                sendError('No fields to update');

            $params[] = $id;
            $sql = "UPDATE portfolio_startups SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            sendSuccess('Startup updated successfully');
        }
        catch (Exception $e) {
            sendError($e->getMessage());
        }
        break;

    case 'delete_startup':
        $id = $input['id'] ?? '';
        if (!$id)
            sendError('Startup ID is required');

        try {
            $stmt = $pdo->prepare("DELETE FROM portfolio_startups WHERE id = ?");
            $stmt->execute([$id]);
            sendSuccess('Startup removed successfully');
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
