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

            // EMAIL NOTIFICATIONS
            try {
                // 1. Notify Admin
                $admin_subject = "New Application: " . $input['application_type'] . " - " . $input['company_name'];
                $admin_body = "
                    <p>A new application has been submitted to GrowthSpire.</p>
                    <p><strong>Type:</strong> " . htmlspecialchars($input['application_type']) . "</p>
                    <p><strong>Company:</strong> " . htmlspecialchars($input['company_name']) . "</p>
                    <p><strong>Name:</strong> " . htmlspecialchars($input['full_name']) . "</p>
                    <p><strong>Email:</strong> " . htmlspecialchars($input['email']) . "</p>
                    <p><strong>Phone:</strong> " . htmlspecialchars($input['phone']) . "</p>
                    <p><strong>Message:</strong><br/>" . nl2br(htmlspecialchars($input['message'])) . "</p>
                    <p>You can view this application in the admin dashboard.</p>
                ";
                GrowthSpireMailer::send(ADMIN_EMAIL, $admin_subject, $admin_body);

                // 2. Notify Applicant (Confirmation)
                $app_subject = "Application Received - " . $input['company_name'];
                $app_body = "
                    <p>Hello " . htmlspecialchars($input['full_name']) . ",</p>
                    <p>Thank you for applying to GrowthSpire! We have received your application for the <strong>" . htmlspecialchars($input['application_type']) . "</strong> program.</p>
                    <p>Our team will review your submission and get back to you shortly regarding the next steps.</p>
                    <p><strong>Application Details Summary:</strong></p>
                    <ul>
                        <li><strong>Company:</strong> " . htmlspecialchars($input['company_name']) . "</li>
                        <li><strong>Stage:</strong> " . htmlspecialchars($input['startup_stage'] ?? 'N/A') . "</li>
                        <li><strong>Field:</strong> " . htmlspecialchars($input['industry'] ?? 'N/A') . "</li>
                    </ul>
                    <p>Best regards,<br/>The GrowthSpire Team</p>
                ";
                GrowthSpireMailer::send($input['email'], $app_subject, $app_body);
            } catch (Exception $e) {
                // Log error but don't fail the response
                error_log("Email notification error: " . $e->getMessage());
            }

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

            // NOTIFY USER ON STATUS UPDATE
            try {
                // Fetch application details for email
                $stmt_app = $pdo->prepare("SELECT full_name, email, company_name FROM applications WHERE id = ?");
                $stmt_app->execute([$id]);
                $app = $stmt_app->fetch();

                if ($app) {
                    $status_display = ucfirst(str_replace('_', ' ', $status));
                    $subject = "Application Status Updated - " . $app['company_name'];
                    $body = "
                        <p>Hello " . htmlspecialchars($app['full_name']) . ",</p>
                        <p>We are writing to inform you that the status of your application for <strong>" . htmlspecialchars($app['company_name']) . "</strong> has been updated to: <strong style='color:#2b6cb0;'>$status_display</strong>.</p>
                    ";

                    if (!empty($notes)) {
                        $body .= "<p><strong>Reviewer feedback:</strong><br/>" . nl2br(htmlspecialchars($notes)) . "</p>";
                    }

                    $body .= "
                        <p>If you have any questions, please feel free to reach out to us.</p>
                        <p>Best regards,<br/>The GrowthSpire Team</p>
                    ";
                    
                    GrowthSpireMailer::send($app['email'], $subject, $body);
                }
            } catch (Exception $e) {
                error_log("Email notification error on status update: " . $e->getMessage());
            }

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
