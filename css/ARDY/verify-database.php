<?php
/* ══════════════════════════════════════════════════════════════
   Database Verification Script
   This script checks if the database and tables were created
══════════════════════════════════════════════════════════════ */

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Verification - ARDY Real Estate</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #3d2a23 0%, #523a31 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 300;
            margin-bottom: 8px;
        }
        .header p {
            color: #c4b693;
            font-size: 14px;
        }
        .content {
            padding: 40px;
        }
        .status-card {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .status-card.error {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        .status-card.warning {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        .status-card h3 {
            color: #333;
            margin-bottom: 12px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-card p {
            color: #666;
            line-height: 1.6;
            margin: 8px 0;
        }
        .status-card pre {
            background: white;
            padding: 12px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 10px 0;
            font-size: 13px;
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 14px;
        }
        table th {
            background: #3d2a23;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge.success { background: #d4edda; color: #155724; }
        .badge.error { background: #f8d7da; color: #721c24; }
        .icon { font-size: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Database Verification</h1>
            <p>Checking ARDY Real Estate Database Setup</p>
        </div>
        
        <div class="content">
            <?php
            // Database configuration
            $host = 'localhost';
            $username = 'root';
            $password = '';
            $database = 'ardy';
            
            $allGood = true;
            
            // Test 1: Connection
            echo '<div class="status-card">';
            echo '<h3><span class="icon">🔌</span> Database Connection</h3>';
            
            try {
                $pdo = new PDO("mysql:host={$host}", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo '<p><span class="badge success">✓ SUCCESS</span> Connected to MySQL server</p>';
            } catch (PDOException $e) {
                echo '<p><span class="badge error">✗ FAILED</span> Cannot connect to MySQL</p>';
                echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
                $allGood = false;
                echo '</div></div></body></html>';
                exit;
            }
            echo '</div>';
            
            // Test 2: Database exists
            echo '<div class="status-card">';
            echo '<h3><span class="icon">🗄️</span> Database "ardy"</h3>';
            
            try {
                $stmt = $pdo->query("SHOW DATABASES LIKE 'ardy'");
                $dbExists = $stmt->fetch();
                
                if ($dbExists) {
                    echo '<p><span class="badge success">✓ EXISTS</span> Database "ardy" is present</p>';
                    $pdo->exec("USE ardy");
                } else {
                    echo '<p><span class="badge error">✗ MISSING</span> Database "ardy" not found</p>';
                    echo '<p>Please run <code>setup-database.sql</code> in phpMyAdmin</p>';
                    $allGood = false;
                    echo '</div></div></body></html>';
                    exit;
                }
            } catch (PDOException $e) {
                echo '<p><span class="badge error">✗ ERROR</span> ' . htmlspecialchars($e->getMessage()) . '</p>';
                $allGood = false;
            }
            echo '</div>';
            
            // Test 3: Tables
            echo '<div class="status-card">';
            echo '<h3><span class="icon">📋</span> Database Tables</h3>';
            
            $requiredTables = ['contact_submissions', 'admin_users', 'activity_log'];
            
            try {
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (count($tables) > 0) {
                    echo '<p><span class="badge success">✓ FOUND</span> ' . count($tables) . ' table(s) detected</p>';
                    echo '<table>';
                    echo '<tr><th>Table Name</th><th>Status</th></tr>';
                    
                    foreach ($requiredTables as $tableName) {
                        $exists = in_array($tableName, $tables);
                        $status = $exists ? '<span class="badge success">✓ Exists</span>' : '<span class="badge error">✗ Missing</span>';
                        echo "<tr><td>{$tableName}</td><td>{$status}</td></tr>";
                        if (!$exists) $allGood = false;
                    }
                    echo '</table>';
                } else {
                    echo '<p><span class="badge error">✗ NO TABLES</span> Database is empty</p>';
                    $allGood = false;
                }
            } catch (PDOException $e) {
                echo '<p><span class="badge error">✗ ERROR</span> ' . htmlspecialchars($e->getMessage()) . '</p>';
                $allGood = false;
            }
            echo '</div>';
            
            // Test 4: Table Structure
            echo '<div class="status-card">';
            echo '<h3><span class="icon">🏗️</span> Table Structure - contact_submissions</h3>';
            
            try {
                $stmt = $pdo->query("DESCRIBE contact_submissions");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($columns) > 0) {
                    echo '<p><span class="badge success">✓ VALID</span> ' . count($columns) . ' columns found</p>';
                    echo '<table>';
                    echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Extra</th></tr>';
                    
                    foreach ($columns as $col) {
                        echo '<tr>';
                        echo '<td><strong>' . htmlspecialchars($col['Field']) . '</strong></td>';
                        echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                        echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                        echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                        echo '<td>' . htmlspecialchars($col['Extra']) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
            } catch (PDOException $e) {
                echo '<p><span class="badge error">✗ ERROR</span> ' . htmlspecialchars($e->getMessage()) . '</p>';
                $allGood = false;
            }
            echo '</div>';
            
            // Test 5: Write Test
            echo '<div class="status-card">';
            echo '<h3><span class="icon">✍️</span> Database Write Test</h3>';
            
            try {
                $testName = 'Test User ' . date('H:i:s');
                $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, phone, message) VALUES (:name, :email, :phone, :message)");
                $stmt->execute([
                    ':name' => $testName,
                    ':email' => 'test@example.com',
                    ':phone' => '+971501234567',
                    ':message' => 'Database verification test entry'
                ]);
                
                $insertId = $pdo->lastInsertId();
                echo '<p><span class="badge success">✓ SUCCESS</span> Test record inserted (ID: ' . $insertId . ')</p>';
                
                // Delete the test record
                $pdo->exec("DELETE FROM contact_submissions WHERE id = {$insertId}");
                echo '<p>Test record cleaned up successfully</p>';
                
            } catch (PDOException $e) {
                echo '<p><span class="badge error">✗ FAILED</span> Cannot write to database</p>';
                echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
                $allGood = false;
            }
            echo '</div>';
            
            // Final Status
            if ($allGood) {
                echo '<div class="status-card">';
                echo '<h3><span class="icon">🎉</span> All Systems Ready!</h3>';
                echo '<p>Your database is properly configured and ready to receive contact form submissions.</p>';
                echo '<p><strong>Next Steps:</strong></p>';
                echo '<ul style="margin-left: 20px; line-height: 2;">';
                echo '<li>Test contact form: <a href="contact.html">contact.html</a></li>';
                echo '<li>View submissions: <a href="admin-panel.php">admin-panel.php</a> (password: ardy2026)</li>';
                echo '<li>Database test: <a href="test-database.html">test-database.html</a></li>';
                echo '</ul>';
                echo '</div>';
            } else {
                echo '<div class="status-card error">';
                echo '<h3><span class="icon">⚠️</span> Setup Required</h3>';
                echo '<p>Some issues were detected. Please run <strong>setup-database.sql</strong> in phpMyAdmin to create the database structure.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
