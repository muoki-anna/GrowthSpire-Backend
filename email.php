<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function getEmailTemplate($title, $content, $footer_note = '') {
    return '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>' . htmlspecialchars($title) . '</title>
</head>
<body style="margin:0;padding:0;background-color:#f7f7fb;font-family:Georgia,\'Times New Roman\',serif;">

  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f7fb;padding:48px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:580px;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 32px rgba(100,80,200,0.08);">

          <tr>
            <td style="height:5px;background:linear-gradient(90deg,#7c5cbf 0%,#5bb8a8 100%);"></td>
          </tr>

          <tr>
            <td style="padding:40px 48px 32px 48px;border-bottom:1px solid #eeecf8;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td>
                    <span style="font-family:Georgia,serif;font-size:13px;font-weight:400;letter-spacing:0.15em;text-transform:uppercase;color:#9d86d4;">Little People</span><br/>
                    <span style="font-family:Georgia,serif;font-size:22px;font-weight:700;color:#2d2650;letter-spacing:-0.02em;">in Tech</span>
                  </td>
                  <td align="right" valign="middle">
                    <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#7c5cbf,#5bb8a8);display:inline-block;text-align:center;line-height:44px;font-size:20px;">✦</div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="padding:40px 48px;">
              <h1 style="margin:0 0 16px 0;font-family:Georgia,serif;font-size:26px;font-weight:700;color:#1e1840;letter-spacing:-0.02em;line-height:1.3;">
                ' . htmlspecialchars($title) . '
              </h1>
              <div style="width:40px;height:3px;background:linear-gradient(90deg,#7c5cbf,#5bb8a8);border-radius:2px;margin-bottom:28px;"></div>
              <div style="font-family:Georgia,serif;font-size:16px;line-height:1.8;color:#3d3660;">
                ' . $content . '
              </div>
            </td>
          </tr>

          <tr>
            <td style="padding:24px 48px 36px 48px;border-top:1px solid #eeecf8;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td>
                    <p style="margin:0 0 6px 0;font-family:Georgia,serif;font-size:12px;color:#a89fcf;">
                      ' . ($footer_note ? htmlspecialchars($footer_note) : 'This is a no-reply email. Please do not reply to this message.') . '
                    </p>
                    <p style="margin:0;font-family:Georgia,serif;font-size:12px;color:#c4bde8;">
                      &copy; ' . date('Y') . ' Little People in Tech &mdash;
                      <a href="https://littlepeopleintech.org" style="color:#7c5cbf;text-decoration:none;">littlepeopleintech.org</a>
                    </p>
                  </td>
                  <td align="right" valign="bottom">
                    <span style="font-family:Georgia,serif;font-size:11px;color:#d4cef0;letter-spacing:0.1em;text-transform:uppercase;">No Reply</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="height:3px;background:linear-gradient(90deg,#5bb8a8 0%,#7c5cbf 100%);"></td>
          </tr>

        </table>

        <p style="margin:24px 0 0 0;font-family:Georgia,serif;font-size:12px;color:#b0a8d0;text-align:center;">
          Sent by Little People in Tech &bull; Nairobi, Kenya
        </p>

      </td>
    </tr>
  </table>

</body>
</html>';
}

function sendEmail($from_email, $from_name, $from_password, $to, $subject, $html_content, $enable_reply = false, $reply_email = '', $reply_name = '') {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration - Using sender's provided credentials
        $mail->isSMTP();
        $mail->Host       = 'mail.littlepeopleintech.org'; // Change this to your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username   = $from_email;
        $mail->Password   = $from_password;
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        // Sender
        $mail->setFrom($from_email, $from_name);
        
        // Reply-to configuration
        if ($enable_reply) {
            $reply_to_email = !empty($reply_email) ? $reply_email : $from_email;
            $reply_to_name = !empty($reply_name) ? $reply_name : $from_name;
            $mail->addReplyTo($reply_to_email, $reply_to_name);
        } else {
            $mail->addReplyTo('no-reply@littlepeopleintech.org', 'No Reply');
        }
        
        // Recipient
        $mail->addAddress($to);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $html_content;
        $mail->AltBody = strip_tags($html_content);

        $mail->send();
        return ['success' => true, 'message' => 'Email sent successfully'];

    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

// API Endpoint Handler
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If JSON parsing failed, try POST data
    if (!$input) {
        $input = $_POST;
    }
    
    // Validate required fields
    $required_fields = ['from_email', 'from_name', 'from_password', 'to', 'subject', 'html_content'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Missing required fields',
            'missing_fields' => $missing_fields
        ]);
        exit();
    }
    
    // Extract parameters
    $from_email = $input['from_email'];
    $from_name = $input['from_name'];
    $from_password = $input['from_password'];
    $to = $input['to'];
    $subject = $input['subject'];
    $html_content = $input['html_content'];
    $enable_reply = isset($input['enable_reply']) ? filter_var($input['enable_reply'], FILTER_VALIDATE_BOOLEAN) : false;
    $reply_email = $input['reply_email'] ?? '';
    $reply_name = $input['reply_name'] ?? '';
    
    // Validate email formats
    if (!filter_var($from_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid sender email format']);
        exit();
    }
    
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid recipient email format']);
        exit();
    }
    
    if ($enable_reply && !empty($reply_email) && !filter_var($reply_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid reply-to email format']);
        exit();
    }
    
    // Send email
    $result = sendEmail(
        $from_email,
        $from_name,
        $from_password,
        $to,
        $subject,
        $html_content,
        $enable_reply,
        $reply_email,
        $reply_name
    );
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(500);
        echo json_encode($result);
    }
    
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST method.'
    ]);
}
?>