<?php
/* ══════════════════════════════════════════════════════════════
   ARDY Real Estate — Contact Form Mail Handler (SMTP Version)
   Uses Gmail SMTP for reliable email delivery
══════════════════════════════════════════════════════════════ */

require_once 'db-config.php';

/* ── CORS Headers ────────────────────────────────────────── */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

/* ── Handle preflight requests ───────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
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

/* ── CONFIG ──────────────────────────────────────────────── */
define('ADMIN_EMAIL', 'rkontam0@gmail.com');
define('SITE_NAME',   'ARDY Real Estate');
define('SMTP_HOST',   'smtp.gmail.com');
define('SMTP_PORT',   587);
define('SMTP_USER',   'rkontam0@gmail.com');
define('SMTP_PASS',   'xetyjenqqnameiqh');   // Gmail App Password

/* ── Always return JSON, even on fatal errors ────────────── */
set_exception_handler(function($e) {
    error_log("Uncaught Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error. Please contact us directly.']);
    exit;
});

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
$service = clean($_POST['service'] ?? '');
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

/* ── Store in Database ───────────────────────────────────── */
$submission_id = null;
$pdo           = null;

try {
    $pdo = getDBConnection();
    if ($pdo) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);

        $stmt = $pdo->prepare(
            "INSERT INTO contact_submissions
             (name, email, phone, message, ip_address, user_agent, email_sent, status)
             VALUES (:name, :email, :phone, :message, :ip_address, :user_agent, 0, 'new')"
        );
        $stmt->bindParam(':name',       $name);
        $stmt->bindParam(':email',      $email);
        $stmt->bindParam(':phone',      $phone);
        $stmt->bindParam(':message',    $message);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);

        if ($stmt->execute()) {
            $submission_id = $pdo->lastInsertId();
        } else {
            error_log("DB Insert Failed: " . implode(', ', $stmt->errorInfo()));
        }
    }
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
}

/* ── Send Email via SMTP ─────────────────────────────────── */
$emailSent = false;

try {
    $emailSent = sendViaSMTP(
        ADMIN_EMAIL,
        'New Enquiry from ' . $name . ' — ' . SITE_NAME,
        buildEmailHTML($name, $email, $phone, $service, $message, $sent_at),
        $email,
        $name
    );

    // Update DB with email status
    if ($submission_id && $pdo) {
        $stmt = $pdo->prepare("UPDATE contact_submissions SET email_sent = :s WHERE id = :id");
        $stmt->bindValue(':s',  $emailSent ? 1 : 0);
        $stmt->bindValue(':id', $submission_id);
        $stmt->execute();
    }

    // Send auto-reply
    if ($emailSent) {
        sendViaSMTP(
            $email,
            'We received your enquiry — ' . SITE_NAME,
            buildAutoReplyHTML(explode(' ', $name)[0], $message),
            ADMIN_EMAIL,
            SITE_NAME
        );
    }

} catch (Exception $e) {
    error_log("Email Error: " . $e->getMessage());
}

echo json_encode([
    'success'       => true,
    'message'       => 'Thank you! Your enquiry has been received. We\'ll be in touch within 2 hours.',
    'submission_id' => $submission_id,
    'email_sent'    => $emailSent,
]);
exit;

/* ══════════════════════════════════════════════════════════
   SMTP SEND FUNCTION
══════════════════════════════════════════════════════════ */

/**
 * Reads all lines of a multi-line SMTP response, returning the last one.
 * Prevents the EHLO read-loop from hanging.
 */
function smtpRead($socket): string {
    $last = '';
    while ($line = fgets($socket, 515)) {
        $last = $line;
        // A space after the 3-digit code signals the final line
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    return $last;
}

function sendViaSMTP(string $to, string $subject, string $htmlBody, string $replyTo, string $replyName): bool {

    $context = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ]);

    $socket = @stream_socket_client(
        'tcp://' . SMTP_HOST . ':' . SMTP_PORT,
        $errno, $errstr, 15,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        error_log("SMTP Connect Failed ({$errno}): {$errstr}");
        return false;
    }

    stream_set_timeout($socket, 15);

    try {
        // Greeting
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '220') { error_log("SMTP Greeting: {$r}"); fclose($socket); return false; }

        // EHLO
        fputs($socket, "EHLO localhost\r\n");
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '250') { error_log("SMTP EHLO: {$r}"); fclose($socket); return false; }

        // STARTTLS
        fputs($socket, "STARTTLS\r\n");
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '220') { error_log("SMTP STARTTLS: {$r}"); fclose($socket); return false; }

        // Upgrade to TLS
        $crypto = @stream_socket_enable_crypto(
            $socket, true,
            STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT
        );
        if (!$crypto) { error_log("SMTP TLS upgrade failed"); fclose($socket); return false; }

        // EHLO again after TLS
        fputs($socket, "EHLO localhost\r\n");
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '250') { error_log("SMTP EHLO2: {$r}"); fclose($socket); return false; }

        // AUTH LOGIN
        fputs($socket, "AUTH LOGIN\r\n");
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '334') { error_log("SMTP AUTH: {$r}"); fclose($socket); return false; }

        fputs($socket, base64_encode(SMTP_USER) . "\r\n");
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '334') { error_log("SMTP USER: {$r}"); fclose($socket); return false; }

        fputs($socket, base64_encode(SMTP_PASS) . "\r\n");
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '235') { error_log("SMTP PASS rejected: {$r}"); fclose($socket); return false; }

        // MAIL FROM
        fputs($socket, "MAIL FROM: <" . SMTP_USER . ">\r\n");
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '250') { error_log("SMTP MAIL FROM: {$r}"); fclose($socket); return false; }

        // RCPT TO
        fputs($socket, "RCPT TO: <{$to}>\r\n");
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '250') { error_log("SMTP RCPT TO: {$r}"); fclose($socket); return false; }

        // DATA
        fputs($socket, "DATA\r\n");
        $r = smtpRead($socket);
        if (substr($r, 0, 3) !== '354') { error_log("SMTP DATA: {$r}"); fclose($socket); return false; }

        // Build message (subject encoded for UTF-8)
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $msg  = "From: " . SITE_NAME . " <" . SMTP_USER . ">\r\n";
        $msg .= "To: {$to}\r\n";
        $msg .= "Reply-To: {$replyName} <{$replyTo}>\r\n";
        $msg .= "Subject: {$encodedSubject}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n";
        $msg .= "\r\n";
        $msg .= chunk_split(base64_encode($htmlBody));
        $msg .= "\r\n.\r\n";

        fputs($socket, $msg);
        $r = smtpRead($socket);
        $success = substr($r, 0, 3) === '250';

        if (!$success) {
            error_log("SMTP Send Failed: {$r}");
        }

        fputs($socket, "QUIT\r\n");
        smtpRead($socket);
        fclose($socket);

        return $success;

    } catch (Exception $e) {
        error_log("SMTP Exception: " . $e->getMessage());
        @fclose($socket);
        return false;
    }
}

/* ══════════════════════════════════════════════════════════
   EMAIL TEMPLATES
══════════════════════════════════════════════════════════ */

function buildEmailHTML(string $name, string $email, string $phone, string $service, string $message, string $sent_at): string {
    $s = $service ?: 'Not specified';
    return '<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f7f5f4;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f5f4;padding:40px 20px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1);">
        <tr>
          <td style="background:linear-gradient(135deg,#3d2a23,#523a31);padding:32px;text-align:center;">
            <h1 style="margin:0;color:#f1ece8;font-size:26px;">🏢 ' . SITE_NAME . '</h1>
            <p style="margin:8px 0 0;color:#c4b693;font-size:12px;letter-spacing:.15em;">NEW CONTACT ENQUIRY</p>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f5f4;border-left:4px solid #c4b693;border-radius:4px;">
              <tr><td style="padding:24px;">
                <table width="100%">
                  <tr>
                    <td style="padding:5px 0;color:#635840;font-size:13px;font-weight:600;width:110px;">Name:</td>
                    <td style="padding:5px 0;color:#3d2a23;font-size:14px;">' . htmlspecialchars($name) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:5px 0;color:#635840;font-size:13px;font-weight:600;">Email:</td>
                    <td style="padding:5px 0;font-size:14px;"><a href="mailto:' . htmlspecialchars($email) . '" style="color:#3d2a23;">' . htmlspecialchars($email) . '</a></td>
                  </tr>
                  <tr>
                    <td style="padding:5px 0;color:#635840;font-size:13px;font-weight:600;">Phone:</td>
                    <td style="padding:5px 0;font-size:14px;"><a href="tel:' . htmlspecialchars($phone) . '" style="color:#3d2a23;">' . htmlspecialchars($phone) . '</a></td>
                  </tr>
                  <tr>
                    <td style="padding:5px 0;color:#635840;font-size:13px;font-weight:600;">Service:</td>
                    <td style="padding:5px 0;color:#3d2a23;font-size:14px;">' . htmlspecialchars($s) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:5px 0;color:#635840;font-size:13px;font-weight:600;">Submitted:</td>
                    <td style="padding:5px 0;color:#635840;font-size:13px;">' . htmlspecialchars($sent_at) . '</td>
                  </tr>
                </table>
              </td></tr>
            </table>
            ' . ($message ? '
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f5f4;border-left:4px solid #c4b693;border-radius:4px;margin-top:20px;">
              <tr><td style="padding:24px;">
                <h3 style="margin:0 0 12px;color:#3d2a23;font-size:15px;">💬 Message</h3>
                <p style="margin:0;color:#3d2a23;font-size:14px;line-height:1.7;white-space:pre-wrap;">' . htmlspecialchars($message) . '</p>
              </td></tr>
            </table>' : '') . '
            <table width="100%"><tr>
              <td align="center" style="padding:24px 0 0;">
                <a href="mailto:' . htmlspecialchars($email) . '" style="display:inline-block;padding:13px 30px;background:#3d2a23;color:#f1ece8;text-decoration:none;font-size:13px;font-weight:600;border-radius:4px;">
                  Reply to ' . htmlspecialchars(explode(' ', $name)[0]) . '
                </a>
              </td>
            </tr></table>
          </td>
        </tr>
        <tr>
          <td style="background:#f7f5f4;padding:20px;text-align:center;border-top:1px solid #e5dfd7;">
            <p style="margin:0;color:#635840;font-size:12px;">Sent from ' . SITE_NAME . ' contact form</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body></html>';
}

function buildAutoReplyHTML(string $firstName, string $message): string {
    return '<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:Arial,sans-serif;background:#f7f5f4;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f5f4;padding:40px 20px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.1);">
        <tr>
          <td style="background:linear-gradient(135deg,#3d2a23,#523a31);padding:32px;text-align:center;">
            <h1 style="margin:0;color:#f1ece8;font-size:26px;">🏢 ' . SITE_NAME . '</h1>
            <p style="margin:8px 0 0;color:#c4b693;font-size:12px;letter-spacing:.15em;">THANK YOU FOR YOUR ENQUIRY</p>
          </td>
        </tr>
        <tr>
          <td style="padding:40px;">
            <h2 style="margin:0 0 16px;color:#3d2a23;font-size:22px;">Dear ' . htmlspecialchars($firstName) . ', ✅</h2>
            <p style="margin:0 0 18px;color:#3d2a23;font-size:15px;line-height:1.7;">
              Thank you for reaching out to <strong>' . SITE_NAME . '</strong>. We have received your enquiry and our team will respond <strong>within 2 business hours</strong>.
            </p>
            ' . ($message ? '
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f7f5f4;border-left:4px solid #c4b693;border-radius:4px;margin:20px 0;">
              <tr><td style="padding:24px;">
                <h3 style="margin:0 0 12px;color:#3d2a23;font-size:15px;">📋 Your Message</h3>
                <p style="margin:0;color:#3d2a23;font-size:14px;line-height:1.7;white-space:pre-wrap;">' . htmlspecialchars($message) . '</p>
              </td></tr>
            </table>' : '') . '
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#fef9f3;border:1px solid #f0e1c8;border-radius:4px;margin:20px 0;">
              <tr><td style="padding:24px;">
                <h3 style="margin:0 0 12px;color:#3d2a23;font-size:15px;">📞 Need Immediate Assistance?</h3>
                <p style="margin:0;color:#3d2a23;font-size:14px;line-height:1.9;">
                  <strong>Phone:</strong> <a href="tel:+971505761914" style="color:#3d2a23;">+971 50 576 1914</a><br>
                  <strong>Email:</strong> <a href="mailto:info@ardyrealestatees.com" style="color:#3d2a23;">info@ardyrealestatees.com</a><br>
                  <strong>WhatsApp:</strong> <a href="https://wa.me/971505761914" style="color:#3d2a23;">+971 50 576 1914</a>
                </p>
              </td></tr>
            </table>
            <table width="100%"><tr>
              <td align="center" style="padding:8px 0 0;">
                <a href="https://wa.me/971505761914" style="display:inline-block;padding:13px 30px;background:#25D366;color:#fff;text-decoration:none;font-size:13px;font-weight:600;border-radius:4px;">
                  💬 Chat on WhatsApp
                </a>
              </td>
            </tr></table>
          </td>
        </tr>
        <tr>
          <td style="background:#f7f5f4;padding:20px;text-align:center;border-top:1px solid #e5dfd7;">
            <p style="margin:0;color:#635840;font-size:12px;">' . SITE_NAME . ' — Dubai, UAE</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body></html>';
}