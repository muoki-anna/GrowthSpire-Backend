<?php
require_once 'config.php';

/**
 * GrowthSpire Mailer Utility
 * 
 * Sends emails using the cloud-based PHPMailer API.
 */
class GrowthSpireMailer {
    
    /**
     * Send an email using the Cloud API
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $html_content HTML body content
     * @param bool $enable_reply Whether to enable reply-to (default: false)
     * @param string $reply_email Custom reply-to email
     * @param string $reply_name Custom reply-to name
     * @return array Result with success status and message/error
     */
    public static function send($to, $subject, $html_content, $enable_reply = false, $reply_email = '', $reply_name = '') {
        // Wrap content in GrowthSpire template
        $full_body = self::getTemplate($subject, $html_content);
        
        $data = [
            'from_email' => EMAIL_FROM,
            'from_name' => EMAIL_NAME,
            'from_password' => EMAIL_PASS,
            'to' => $to,
            'subject' => $subject,
            'html_content' => $full_body,
            'enable_reply' => $enable_reply,
            'reply_email' => $reply_email,
            'reply_name' => $reply_name
        ];

        $ch = curl_init(EMAIL_API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        // Optional: Disable SSL verification if needed (not recommended for production)
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['success' => false, 'error' => "cURL Error: " . $error];
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($http_code === 200 && isset($result['success']) && $result['success']) {
            return ['success' => true, 'message' => 'Email sent successfully'];
        } else {
            return [
                'success' => false, 
                'error' => $result['error'] ?? "Unknown API Error (HTTP $http_code)",
                'raw_response' => $response
            ];
        }
    }

    /**
     * GrowthSpire Email Template
     */
    private static function getTemplate($title, $content) {
        $year = date('Y');
        return "
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8' />
  <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
  <title>$title</title>
</head>
<body style=\"margin:0;padding:0;background-color:#f0f2f5;font-family:'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;\">
  <table width='100%' cellpadding='0' cellspacing='0' border='0' style='background-color:#f0f2f5;padding:40px 20px;'>
    <tr>
      <td align='center'>
        <table width='100%' cellpadding='0' cellspacing='0' border='0' style='max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.05);'>
          <!-- Header Gradient Strip -->
          <tr>
            <td style='height:8px;background:linear-gradient(90deg, #1a365d 0%, #2b6cb0 100%);'></td>
          </tr>
          
          <!-- Logo & Header -->
          <tr>
            <td style='padding:40px 40px 20px 40px; text-align:center;'>
               <h2 style='margin:0; color:#1a365d; font-size:28px; font-weight:800; letter-spacing:-0.5px;'>Growth<span style='color:#2b6cb0;'>Spire</span></h2>
               <p style='margin:10px 0 0 0; color:#718096; font-size:14px; text-transform:uppercase; letter-spacing:2px;'>Nurturing Potential, Accelerating Growth</p>
            </td>
          </tr>

          <!-- Main Content -->
          <tr>
            <td style='padding:20px 40px 40px 40px;'>
              <h1 style='margin:0 0 24px 0; color:#2d3748; font-size:22px; font-weight:700; line-height:1.3;'>$title</h1>
              <div style='color:#4a5568; font-size:16px; line-height:1.7;'>
                $content
              </div>
              
              <!-- CTA / Footer Link Example (Optional) -->
              <div style='margin-top:40px; padding-top:30px; border-top:1px solid #edf2f7; text-align:center;'>
                <a href='https://growthspire.org' style='display:inline-block; padding:12px 28px; background-color:#1a365d; color:#ffffff; text-decoration:none; border-radius:6px; font-weight:600; font-size:15px;'>Visit our Website</a>
              </div>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style='padding:30px 40px; background-color:#f8fafc; border-top:1px solid #edf2f7;'>
              <table width='100%' cellpadding='0' cellspacing='0' border='0'>
                <tr>
                  <td style='color:#718096; font-size:12px; line-height:1.5;'>
                    &copy; $year GrowthSpire Accelerator. All rights reserved.<br/>
                    Nairobi, Kenya &bull; Supporting the next generation of African startups.
                  </td>
                  <td align='right' valign='top'>
                    <div style='display:inline-block; color:#a0aec0; font-size:20px;'>✦</div>
                  </td>
                </tr>
              </table>
              <p style='margin:20px 0 0 0; font-size:11px; color:#a0aec0; text-align:center;'>
                This is an automated message. Please do not reply directly to this email.
              </p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>";
    }
}
?>
