<?php
/* ══════════════════════════════════════════════════════════════
   PHP Mail Test Script
   This simple script tests if PHP mail() function is working
══════════════════════════════════════════════════════════════ */

header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$to_email = filter_var($_POST['to_email'] ?? '', FILTER_VALIDATE_EMAIL);
$to_name = htmlspecialchars(trim($_POST['to_name'] ?? ''));
$test_message = htmlspecialchars(trim($_POST['test_message'] ?? 'This is a test email'));

// Validate
if (!$to_email) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address']);
    exit;
}

if (empty($to_name)) {
    echo json_encode(['success' => false, 'message' => 'Please provide your name']);
    exit;
}

// Prepare email
$subject = '=?UTF-8?B?' . base64_encode('PHP Mail Test - ARDY Real Estate') . '?=';
$sent_at = date('d M Y, H:i:s');

// HTML Email Body
$body_html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PHP Mail Test</title>
</head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background-color:#f4f4f4;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f4f4f4;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:30px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:26px;">
                                ✅ PHP Mail Test Successful!
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding:40px 30px;">
                            <p style="margin:0 0 20px;color:#333;font-size:16px;line-height:1.6;">
                                Hello <strong>' . htmlspecialchars($to_name) . '</strong>,
                            </p>
                            <p style="margin:0 0 20px;color:#333;font-size:15px;line-height:1.6;">
                                If you\'re reading this, congratulations! Your PHP <code style="background:#f5f5f5;padding:2px 6px;border-radius:3px;">mail()</code> function is working correctly.
                            </p>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f9f9f9;border-left:4px solid #667eea;border-radius:4px;margin:20px 0;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="margin:0 0 10px;color:#666;font-size:13px;font-weight:600;">Test Message:</p>
                                        <p style="margin:0;color:#333;font-size:14px;line-height:1.6;">' . nl2br($test_message) . '</p>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0;color:#666;font-size:13px;line-height:1.6;">
                                <strong>Sent at:</strong> ' . $sent_at . '<br>
                                <strong>To:</strong> ' . htmlspecialchars($to_email) . '<br>
                                <strong>From:</strong> ARDY Real Estate Contact Form Test
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f9f9f9;padding:20px 30px;text-align:center;border-top:1px solid #e0e0e0;">
                            <p style="margin:0;color:#999;font-size:12px;">
                                This is an automated test email from<br>
                                <strong style="color:#666;">ARDY Real Estate PHP Mail System</strong>
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
';

// Plain text fallback
$body_plain = "PHP Mail Test - ARDY Real Estate\n\n";
$body_plain .= "Hello {$to_name},\n\n";
$body_plain .= "If you're reading this, your PHP mail() function is working correctly!\n\n";
$body_plain .= "Test Message:\n{$test_message}\n\n";
$body_plain .= "Sent at: {$sent_at}\n";
$body_plain .= "To: {$to_email}\n\n";
$body_plain .= "This is an automated test email from ARDY Real Estate PHP Mail System.\n";

// Create multipart boundary
$boundary = md5(uniqid(time()));

// Headers
$headers  = "From: ARDY Real Estate <noreply@ardyrealestatees.com>\r\n";
$headers .= "Reply-To: noreply@ardyrealestatees.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";

// Build multipart body
$body  = "--{$boundary}\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$body .= $body_plain . "\r\n";
$body .= "--{$boundary}\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$body .= $body_html . "\r\n";
$body .= "--{$boundary}--";

// Send email
$sent = mail($to_email, $subject, $body, $headers);

if ($sent) {
    echo json_encode([
        'success' => true,
        'message' => 'Test email sent successfully! Check your inbox.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'PHP mail() function returned false. Check your server configuration.'
    ]);
}
