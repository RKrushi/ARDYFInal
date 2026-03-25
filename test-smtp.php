<?php
/* Test SMTP Connection to Gmail */
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>SMTP Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #3d2a23; margin-top: 0; }
        .test-result { padding: 15px; margin: 15px 0; border-radius: 4px; }
        .success { background: #d4edda; border-left: 4px solid #28a745; color: #155724; }
        .error { background: #f8d7da; border-left: 4px solid #dc3545; color: #721c24; }
        .info { background: #d1ecf1; border-left: 4px solid #17a2b8; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .step { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 SMTP Connection Test</h1>
        
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        $smtp_host = 'smtp.gmail.com';
        $smtp_port = 587;
        $smtp_user = 'rkontam0@gmail.com';
        $smtp_pass = 'tryzncdlmofeueuy';
        
        echo '<div class="info"><strong>Testing Configuration:</strong><br>';
        echo 'Host: ' . $smtp_host . '<br>';
        echo 'Port: ' . $smtp_port . '<br>';
        echo 'User: ' . $smtp_user . '<br>';
        echo 'Pass: ' . str_repeat('*', strlen($smtp_pass)) . ' (' . strlen($smtp_pass) . ' characters)</div>';
        
        // Test 1: Socket connection
        echo '<div class="step"><strong>Step 1:</strong> Testing socket connection...</div>';
        $socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 10);
        
        if (!$socket) {
            echo '<div class="error">';
            echo '❌ <strong>Connection Failed</strong><br>';
            echo "Error #{$errno}: {$errstr}<br>";
            echo '<strong>Possible causes:</strong><br>';
            echo '- Your internet connection is down<br>';
            echo '- Firewall is blocking port 587<br>';
            echo '- Gmail SMTP is temporarily unavailable';
            echo '</div>';
            exit;
        }
        
        echo '<div class="success">✅ Socket connected successfully</div>';
        
        stream_set_timeout($socket, 10);
        
        function smtp_command($socket, $command, $expect = '250') {
            fputs($socket, $command . "\r\n");
            $response = fgets($socket, 515);
            echo '<pre>→ ' . htmlspecialchars(trim($command)) . "\n← " . htmlspecialchars(trim($response)) . '</pre>';
            return [
                'response' => $response,
                'code' => substr($response, 0, 3),
                'success' => substr($response, 0, 3) === $expect
            ];
        }
        
        // Test 2: Greeting
        echo '<div class="step"><strong>Step 2:</strong> Receiving server greeting...</div>';
        $greeting = fgets($socket, 515);
        echo '<pre>← ' . htmlspecialchars(trim($greeting)) . '</pre>';
        
        if (substr($greeting, 0, 3) !== '220') {
            echo '<div class="error">❌ Unexpected greeting: ' . htmlspecialchars($greeting) . '</div>';
            fclose($socket);
            exit;
        }
        echo '<div class="success">✅ Server greeting received</div>';
        
        // Test 3: EHLO
        echo '<div class="step"><strong>Step 3:</strong> Sending EHLO command...</div>';
        $result = smtp_command($socket, "EHLO localhost");
        if (!$result['success']) {
            echo '<div class="error">❌ EHLO failed</div>';
            fclose($socket);
            exit;
        }
        
        // Read additional EHLO responses
        while ($response = fgets($socket, 515)) {
            echo '<pre>← ' . htmlspecialchars(trim($response)) . '</pre>';
            if (substr($response, 3, 1) !== '-') break;
        }
        echo '<div class="success">✅ EHLO successful</div>';
        
        // Test 4: STARTTLS
        echo '<div class="step"><strong>Step 4:</strong> Initiating TLS encryption...</div>';
        $result = smtp_command($socket, "STARTTLS", '220');
        if (!$result['success']) {
            echo '<div class="error">❌ STARTTLS failed</div>';
            fclose($socket);
            exit;
        }
        
        // Enable TLS
        $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        if (!$crypto) {
            echo '<div class="error">❌ TLS encryption failed</div>';
            fclose($socket);
            exit;
        }
        echo '<div class="success">✅ TLS encryption enabled</div>';
        
        // Test 5: EHLO again after TLS
        echo '<div class="step"><strong>Step 5:</strong> Sending EHLO after TLS...</div>';
        $result = smtp_command($socket, "EHLO localhost");
        
        // Read additional EHLO responses
        while ($response = fgets($socket, 515)) {
            echo '<pre>← ' . htmlspecialchars(trim($response)) . '</pre>';
            if (substr($response, 3, 1) !== '-') break;
        }
        echo '<div class="success">✅ Secure connection established</div>';
        
        // Test 6: Authentication
        echo '<div class="step"><strong>Step 6:</strong> Testing authentication...</div>';
        $result = smtp_command($socket, "AUTH LOGIN", '334');
        if (!$result['success']) {
            echo '<div class="error">❌ AUTH LOGIN not accepted</div>';
            fclose($socket);
            exit;
        }
        
        // Send username
        fputs($socket, base64_encode($smtp_user) . "\r\n");
        $response = fgets($socket, 515);
        echo '<pre>→ ' . base64_encode($smtp_user) . ' (username base64)\n← ' . htmlspecialchars(trim($response)) . '</pre>';
        
        if (substr($response, 0, 3) !== '334') {
            echo '<div class="error">❌ Username not accepted</div>';
            fclose($socket);
            exit;
        }
        
        // Send password
        fputs($socket, base64_encode($smtp_pass) . "\r\n");
        $response = fgets($socket, 515);
        echo '<pre>→ ' . str_repeat('*', strlen(base64_encode($smtp_pass))) . ' (password base64)\n← ' . htmlspecialchars(trim($response)) . '</pre>';
        
        if (substr($response, 0, 3) !== '235') {
            echo '<div class="error">';
            echo '❌ <strong>Authentication Failed!</strong><br><br>';
            echo '<strong>Error:</strong> ' . htmlspecialchars(trim($response)) . '<br><br>';
            echo '<strong>Common causes:</strong><br>';
            echo '1. <strong>App Password is incorrect</strong> - Double check: tryzncdlmofeueuy<br>';
            echo '2. <strong>App Password not enabled</strong> - Visit <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a><br>';
            echo '3. <strong>2-Step Verification not enabled</strong> - Required for App Passwords<br>';
            echo '4. <strong>Spaces in password</strong> - Remove any spaces from the app password<br><br>';
            echo '<strong>To fix:</strong><br>';
            echo '1. Go to: <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a><br>';
            echo '2. Sign in with rkontam0@gmail.com<br>';
            echo '3. Generate a NEW app password<br>';
            echo '4. Copy it WITHOUT spaces<br>';
            echo '5. Update send-mail-smtp.php with the new password';
            echo '</div>';
            fclose($socket);
            exit;
        }
        
        echo '<div class="success">✅ Authentication successful!</div>';
        
        // Test 7: Send test email
        echo '<div class="step"><strong>Step 7:</strong> Sending test email...</div>';
        
        $test_to = $smtp_user;
        $result = smtp_command($socket, "MAIL FROM: <{$smtp_user}>");
        if (!$result['success']) {
            echo '<div class="error">❌ MAIL FROM failed</div>';
            fclose($socket);
            exit;
        }
        
        $result = smtp_command($socket, "RCPT TO: <{$test_to}>");
        if (!$result['success']) {
            echo '<div class="error">❌ RCPT TO failed</div>';
            fclose($socket);
            exit;
        }
        
        $result = smtp_command($socket, "DATA", '354');
        if (!$result['success']) {
            echo '<div class="error">❌ DATA command failed</div>';
            fclose($socket);
            exit;
        }
        
        $email_content = "From: ARDY Test <{$smtp_user}>\r\n";
        $email_content .= "To: {$test_to}\r\n";
        $email_content .= "Subject: SMTP Test - " . date('Y-m-d H:i:s') . "\r\n";
        $email_content .= "Content-Type: text/html; charset=UTF-8\r\n";
        $email_content .= "\r\n";
        $email_content .= "<h1>✅ SMTP Test Successful!</h1>\r\n";
        $email_content .= "<p>This is a test email from ARDY contact form SMTP configuration.</p>\r\n";
        $email_content .= "<p>Time: " . date('Y-m-d H:i:s') . "</p>\r\n";
        $email_content .= "\r\n.\r\n";
        
        fputs($socket, $email_content);
        $response = fgets($socket, 515);
        echo '<pre>← ' . htmlspecialchars(trim($response)) . '</pre>';
        
        if (substr($response, 0, 3) !== '250') {
            echo '<div class="error">❌ Email sending failed</div>';
            fclose($socket);
            exit;
        }
        
        echo '<div class="success">✅ Test email sent successfully!</div>';
        
        // Quit
        smtp_command($socket, "QUIT", '221');
        fclose($socket);
        
        echo '<div class="success">';
        echo '<h2>🎉 All Tests Passed!</h2>';
        echo '<p>Your SMTP configuration is working correctly. Check your inbox at <strong>' . $smtp_user . '</strong> for the test email.</p>';
        echo '<p><strong>Next step:</strong> Try submitting the contact form again. It should work now!</p>';
        echo '</div>';
        
        ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
            <h3>📋 Quick Actions:</h3>
            <ul>
                <li><a href="contact.html">Test Contact Form</a></li>
                <li><a href="admin-panel.php">View Admin Panel</a></li>
                <li><a href="verify-database.php">Check Database</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
