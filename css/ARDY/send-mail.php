<?php
/* ══════════════════════════════════════════════════════════════
   ARDY Real Estate — Contact Form Mail Handler with Database
   File: send-mail.php
══════════════════════════════════════════════════════════════ */

require_once 'db-config.php';

header('Content-Type: application/json');

/* ── CONFIG ──────────────────────────────────────────────── */
define('ADMIN_EMAIL',   'rkontam0@gmail.com');
define('FROM_DOMAIN',   'ardyrealestatees.com');
define('SITE_NAME',     'ARDY Real Estate');
define('ALLOWED_ORIGIN','');

/* ── CORS / Origin check ─────────────────────────────────── */
if (ALLOWED_ORIGIN !== '') {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin !== ALLOWED_ORIGIN) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
}

/* ── Allow GET for health checks ─────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  http_response_code(200);
  echo json_encode([
    'success' => true,
    'message' => 'Contact API is online. Submit the form using POST.',
  ]);
  exit;
}

/* ── Only accept POST ────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['success' => false, 'message' => 'Method not allowed']);
  exit;
}

/* ── Helper: sanitise input ──────────────────────────────── */
if (!function_exists('clean')) {
    function clean(string $val): string {
        return htmlspecialchars(trim(strip_tags($val)), ENT_QUOTES, 'UTF-8');
    }
}

/* ── Collect & sanitise fields ───────────────────────────── */
$name    = clean($_POST['name']    ?? '');
$email   = clean($_POST['email']   ?? '');
$phone   = clean($_POST['phone']   ?? '');
$message = clean($_POST['message'] ?? '');
$service = clean($_POST['service'] ?? ''); // FIX: was undefined before
$sent_at = date('d M Y, H:i:s') . ' GST';

/* ── Honeypot anti-spam ──────────────────────────────────── */
if (!empty($_POST['botcheck'])) {
    echo json_encode(['success' => true]);
    exit;
}

/* ── Validate required fields ────────────────────────────── */
$errors = [];
if (strlen($name) < 2)                            $errors[] = 'Name is required';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = 'Valid email is required';
if (strlen(preg_replace('/\D/', '', $phone)) < 6) $errors[] = 'Phone number is required';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode('. ', $errors)]);
    exit;
}

/* ── Store in Database First ─────────────────────────────── */
$submission_id = null;
$db_error      = false;
$pdo           = null;

try {
    $pdo = getDBConnection();

    if ($pdo) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

        $sql = "INSERT INTO contact_submissions
                (name, email, phone, message, ip_address, user_agent, email_sent, status)
                VALUES
                (:name, :email, :phone, :message, :ip_address, :user_agent, 0, 'new')";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name',       $name);
        $stmt->bindParam(':email',      $email);
        $stmt->bindParam(':phone',      $phone);
        $stmt->bindParam(':message',    $message);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);

        if ($stmt->execute()) {
            $submission_id = $pdo->lastInsertId();
        } else {
            $db_error = true;
            error_log("Database Insert Failed: " . implode(', ', $stmt->errorInfo()));
        }
    } else {
        $db_error = true;
        error_log("Database Connection Failed");
    }
} catch (PDOException $e) {
    $db_error = true;
    error_log("Database Error: " . $e->getMessage());
}

if ($db_error) {
    error_log("Warning: Submission not saved to database, but continuing to send email");
}

/* ── Build the admin notification email ──────────────────── */
$to      = ADMIN_EMAIL;
$subject = '=?UTF-8?B?' . base64_encode('New Enquiry from ' . $name . ' — ' . SITE_NAME) . '?=';

$bodyPlain  = "NEW ENQUIRY — " . SITE_NAME . "\n";
$bodyPlain .= str_repeat("=", 52) . "\n\n";
$bodyPlain .= "Name      : {$name}\n";
$bodyPlain .= "Email     : {$email}\n";
$bodyPlain .= "Phone     : {$phone}\n";
$bodyPlain .= "Service   : {$service}\n";
$bodyPlain .= "Submitted : {$sent_at}\n\n";
$bodyPlain .= "MESSAGE\n" . str_repeat("-", 40) . "\n";
$bodyPlain .= wordwrap($message ?: '(no message provided)', 72, "\n", false) . "\n\n";
$bodyPlain .= str_repeat("=", 52) . "\n";
$bodyPlain .= "Sent from the contact form on " . SITE_NAME . "\n";
$bodyPlain .= "Reply directly to this email to respond to the enquiry.\n";

$bodyHTML = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>New Enquiry from ' . htmlspecialchars($name) . '</title>
</head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;background-color:#f7f5f4;color:#3d2a23;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f5f4;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 24px rgba(61,42,35,0.08);">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#3d2a23 0%,#523a31 100%);padding:32px 40px;text-align:center;">
              <h1 style="margin:0;color:#f1ece8;font-size:28px;font-weight:300;letter-spacing:-0.5px;">🏢 ' . SITE_NAME . '</h1>
              <p style="margin:8px 0 0;color:#c4b693;font-size:13px;letter-spacing:0.15em;text-transform:uppercase;">New Property Enquiry</p>
            </td>
          </tr>

          <!-- Main Content -->
          <tr>
            <td style="padding:40px;">
              <p style="margin:0 0 28px;color:#635840;font-size:15px;line-height:1.6;">
                You have received a new enquiry from your website contact form. Details below:
              </p>

              <!-- Client Details Card -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f5f4;border-left:4px solid #c4b693;border-radius:4px;margin-bottom:24px;">
                <tr>
                  <td style="padding:24px;">
                    <h2 style="margin:0 0 16px;color:#3d2a23;font-size:16px;font-weight:600;">👤 Client Information</h2>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="padding:6px 0;color:#635840;font-size:13px;font-weight:600;width:140px;vertical-align:top;">Name:</td>
                        <td style="padding:6px 0;color:#3d2a23;font-size:14px;">' . htmlspecialchars($name) . '</td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0;color:#635840;font-size:13px;font-weight:600;vertical-align:top;">Email:</td>
                        <td style="padding:6px 0;color:#3d2a23;font-size:14px;"><a href="mailto:' . htmlspecialchars($email) . '" style="color:#3d2a23;text-decoration:none;">' . htmlspecialchars($email) . '</a></td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0;color:#635840;font-size:13px;font-weight:600;vertical-align:top;">Phone:</td>
                        <td style="padding:6px 0;color:#3d2a23;font-size:14px;"><a href="tel:' . htmlspecialchars($phone) . '" style="color:#3d2a23;text-decoration:none;">' . htmlspecialchars($phone) . '</a></td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0;color:#635840;font-size:13px;font-weight:600;vertical-align:top;">Service Interest:</td>
                        <td style="padding:6px 0;color:#3d2a23;font-size:14px;">' . (htmlspecialchars($service) ?: 'Not specified') . '</td>
                      </tr>
                      <tr>
                        <td style="padding:6px 0;color:#635840;font-size:13px;font-weight:600;vertical-align:top;">Submitted:</td>
                        <td style="padding:6px 0;color:#3d2a23;font-size:14px;">' . htmlspecialchars($sent_at) . '</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- Message Card -->
              ' . ($message ? '
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f5f4;border-left:4px solid #c4b693;border-radius:4px;margin-bottom:32px;">
                <tr>
                  <td style="padding:24px;">
                    <h2 style="margin:0 0 12px;color:#3d2a23;font-size:16px;font-weight:600;">💬 Client Message</h2>
                    <p style="margin:0;color:#3d2a23;font-size:14px;line-height:1.7;white-space:pre-wrap;">' . htmlspecialchars($message) . '</p>
                  </td>
                </tr>
              </table>
              ' : '') . '

              <!-- Action Button -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:24px;">
                <tr>
                  <td align="center" style="padding:20px 0;">
                    <a href="mailto:' . htmlspecialchars($email) . '?subject=RE:%20Your%20Property%20Enquiry%20%E2%80%94%20' . rawurlencode(SITE_NAME) . '"
                       style="display:inline-block;padding:14px 32px;background-color:#3d2a23;color:#f1ece8;text-decoration:none;font-size:14px;font-weight:600;letter-spacing:0.5px;text-transform:uppercase;border-radius:4px;">
                      Reply to ' . htmlspecialchars(explode(' ', $name)[0]) . '
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0;padding:16px;background-color:#fef9f3;border:1px solid #f0e1c8;border-radius:4px;color:#8b6f47;font-size:13px;line-height:1.6;">
                💡 <strong>Quick Tip:</strong> Reply to this email directly, or click the button above to respond to ' . htmlspecialchars(explode(' ', $name)[0]) . '\'s enquiry.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#f7f5f4;padding:28px 40px;text-align:center;border-top:1px solid #e5dfd7;">
              <p style="margin:0 0 8px;color:#635840;font-size:13px;line-height:1.6;">
                This message was sent from the contact form on<br>
                <strong style="color:#3d2a23;">' . SITE_NAME . '</strong> website
              </p>
              <p style="margin:0;color:#9b8972;font-size:12px;">Al Owais Building, Sheikh Zayed Road, Dubai, UAE</p>
              <p style="margin:12px 0 0;color:#9b8972;font-size:12px;">
                📞 <a href="tel:+971505761914" style="color:#9b8972;text-decoration:none;">+971 50 576 1914</a> |
                ✉️ <a href="mailto:info@ardyrealestatees.com" style="color:#9b8972;text-decoration:none;">info@ardyrealestatees.com</a>
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

$boundary = md5(uniqid(time()));

$headers  = "From: " . SITE_NAME . " <noreply@" . FROM_DOMAIN . ">\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$body  = "--{$boundary}\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$body .= $bodyPlain . "\r\n";
$body .= "--{$boundary}\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
$body .= $bodyHTML . "\r\n";
$body .= "--{$boundary}--";

/* ── Send Email ──────────────────────────────────────────── */
$sent = mail($to, $subject, $body, $headers);

// Update database with email status
if ($submission_id && $pdo) {
    try {
        $email_status = $sent ? 1 : 0;
        $sql  = "UPDATE contact_submissions SET email_sent = :email_sent WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email_sent', $email_status);
        $stmt->bindParam(':id',         $submission_id);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Failed to update email status: " . $e->getMessage());
    }
}

if ($sent) {
    sendAutoReply($name, $email, $service, $message);
    echo json_encode([
        'success'       => true,
        'message'       => 'Message sent successfully',
        'submission_id' => $submission_id,
        'saved_to_db'   => $submission_id ? true : false,
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success'       => false,
        'message'       => 'Mail server error. Your enquiry has been saved and we will contact you soon.',
        'submission_id' => $submission_id,
        'saved_to_db'   => $submission_id ? true : false,
    ]);
}

/* ── Auto-Reply Function ─────────────────────────────────── */
function sendAutoReply(string $name, string $email, string $service, string $message): void {
    $reply_subject = '=?UTF-8?B?' . base64_encode('We received your enquiry — ' . SITE_NAME) . '?=';
    $firstName     = htmlspecialchars(explode(' ', $name)[0]);

    $reply_body_plain  = "Dear {$name},\n\n";
    $reply_body_plain .= "Thank you for contacting " . SITE_NAME . ".\n\n";
    $reply_body_plain .= "We have received your enquiry and our team will be in touch within 2 business hours.\n\n";
    if ($service) {
        $reply_body_plain .= "Service Interest: {$service}\n\n";
    }
    if ($message) {
        $reply_body_plain .= "YOUR MESSAGE\n" . str_repeat("-", 40) . "\n";
        $reply_body_plain .= wordwrap($message, 68, "\n", false) . "\n";
        $reply_body_plain .= str_repeat("-", 40) . "\n\n";
    }
    $reply_body_plain .= "CONTACT US DIRECTLY\n";
    $reply_body_plain .= "Phone: +971 50 576 1914  |  +971 52 842 6615\n";
    $reply_body_plain .= "Email: info@ardyrealestatees.com\n";
    $reply_body_plain .= "WhatsApp: https://wa.me/971505761914\n\n";
    $reply_body_plain .= "Warm regards,\n";
    $reply_body_plain .= SITE_NAME . " Team\n";
    $reply_body_plain .= "Al Owais Building, Sheikh Zayed Road, Dubai, UAE\n";

    $reply_body_html = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Thank you for contacting us</title>
</head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;background-color:#f7f5f4;color:#3d2a23;">
  <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f5f4;padding:40px 20px;">
    <tr>
      <td align="center">
        <table width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 4px 24px rgba(61,42,35,0.08);">

          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#3d2a23 0%,#523a31 100%);padding:32px 40px;text-align:center;">
              <h1 style="margin:0;color:#f1ece8;font-size:28px;font-weight:300;letter-spacing:-0.5px;">🏢 ' . SITE_NAME . '</h1>
              <p style="margin:8px 0 0;color:#c4b693;font-size:13px;letter-spacing:0.15em;text-transform:uppercase;">Thank You For Your Enquiry</p>
            </td>
          </tr>

          <!-- Main Content -->
          <tr>
            <td style="padding:40px;">
              <h2 style="margin:0 0 16px;color:#3d2a23;font-size:24px;font-weight:400;">Dear ' . $firstName . ', ✅</h2>
              <p style="margin:0 0 20px;color:#3d2a23;font-size:15px;line-height:1.7;">
                Thank you for reaching out to <strong>' . SITE_NAME . '</strong>. We have successfully received your enquiry and our property specialists are reviewing your requirements.
              </p>
              <p style="margin:0 0 28px;color:#3d2a23;font-size:15px;line-height:1.7;">
                <strong>Our team will respond within 2 business hours</strong> with personalized recommendations tailored to your budget and investment goals.
              </p>

              <!-- Enquiry Summary -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f5f4;border-left:4px solid #c4b693;border-radius:4px;margin-bottom:28px;">
                <tr>
                  <td style="padding:24px;">
                    <h3 style="margin:0 0 14px;color:#3d2a23;font-size:16px;font-weight:600;">📋 Your Enquiry Summary</h3>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <td style="padding:5px 0;color:#635840;font-size:13px;font-weight:600;width:150px;vertical-align:top;">Service Interest:</td>
                        <td style="padding:5px 0;color:#3d2a23;font-size:14px;">' . (htmlspecialchars($service) ?: 'Not specified') . '</td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              ' . ($message ? '
              <!-- Your Message -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f7f5f4;border-left:4px solid #c4b693;border-radius:4px;margin-bottom:28px;">
                <tr>
                  <td style="padding:24px;">
                    <h3 style="margin:0 0 14px;color:#3d2a23;font-size:16px;font-weight:600;">📋 Your Message</h3>
                    <p style="margin:0;color:#3d2a23;font-size:14px;line-height:1.7;white-space:pre-wrap;">' . htmlspecialchars($message) . '</p>
                  </td>
                </tr>
              </table>
              ' : '') . '

              <!-- Contact Details -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#fef9f3;border:1px solid #f0e1c8;border-radius:4px;margin-bottom:28px;">
                <tr>
                  <td style="padding:24px;">
                    <h3 style="margin:0 0 14px;color:#3d2a23;font-size:16px;font-weight:600;">📞 Need Immediate Assistance?</h3>
                    <p style="margin:0 0 12px;color:#635840;font-size:14px;line-height:1.6;">Feel free to reach out to us directly:</p>
                    <p style="margin:0;color:#3d2a23;font-size:14px;line-height:1.9;">
                      <strong>Phone:</strong> <a href="tel:+971505761914" style="color:#3d2a23;text-decoration:none;">+971 50 576 1914</a> | <a href="tel:+971528426615" style="color:#3d2a23;text-decoration:none;">+971 52 842 6615</a><br>
                      <strong>Email:</strong> <a href="mailto:info@ardyrealestatees.com" style="color:#3d2a23;text-decoration:none;">info@ardyrealestatees.com</a><br>
                      <strong>WhatsApp:</strong> <a href="https://wa.me/971505761914" style="color:#3d2a23;text-decoration:none;">+971 50 576 1914</a><br>
                      <strong>Office:</strong> Al Owais Building, Sheikh Zayed Road, Dubai
                    </p>
                  </td>
                </tr>
              </table>

              <!-- CTA Button -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                <tr>
                  <td align="center" style="padding:12px 0 20px;">
                    <a href="https://wa.me/971505761914" style="display:inline-block;padding:14px 32px;background-color:#25D366;color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;letter-spacing:0.5px;border-radius:4px;">
                      💬 Chat on WhatsApp
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0;color:#635840;font-size:13px;line-height:1.6;text-align:center;">
                We look forward to helping you find the perfect property investment in Dubai.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color:#f7f5f4;padding:28px 40px;text-align:center;border-top:1px solid #e5dfd7;">
              <p style="margin:0 0 8px;color:#635840;font-size:13px;font-weight:600;">' . SITE_NAME . '</p>
              <p style="margin:0 0 4px;color:#9b8972;font-size:12px;">Professional Real Estate Services in Dubai</p>
              <p style="margin:0;color:#9b8972;font-size:12px;">Al Owais Building, Sheikh Zayed Road, Dubai, UAE</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
    ';

    $boundary = md5(uniqid(time()));

    $reply_headers  = "From: " . SITE_NAME . " <noreply@" . FROM_DOMAIN . ">\r\n";
    $reply_headers .= "MIME-Version: 1.0\r\n";
    $reply_headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";

    $reply_body  = "--{$boundary}\r\n";
    $reply_body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $reply_body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $reply_body .= $reply_body_plain . "\r\n";
    $reply_body .= "--{$boundary}\r\n";
    $reply_body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $reply_body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $reply_body .= $reply_body_html . "\r\n";
    $reply_body .= "--{$boundary}--";

    mail($email, $reply_subject, $reply_body, $reply_headers);
}