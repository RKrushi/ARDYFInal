<?php
/* ══════════════════════════════════════════════════════════════
   Database Connection Test Backend
══════════════════════════════════════════════════════════════ */

require_once 'db-config.php';

header('Content-Type: application/json');

try {
    // Test connection
    $pdo = getDBConnection();
    
    if (!$pdo) {
        echo json_encode([
            'success' => false,
            'message' => 'Could not connect to database. Check your credentials in db-config.php',
            'error_code' => 'CONNECTION_FAILED'
        ]);
        exit;
    }
    
    // Check if database exists
    $dbname = DB_NAME;
    $result = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbname}'");
    
    if ($result->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => "Database '{$dbname}' does not exist. Please run setup-database.sql in phpMyAdmin.",
            'error_code' => 'DB_NOT_FOUND'
        ]);
        exit;
    }
    
    // Get list of tables
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    // Get submission count
    $submission_count = 0;
    if (in_array('contact_submissions', $tables)) {
        $count_result = $pdo->query("SELECT COUNT(*) as count FROM contact_submissions");
        $submission_count = $count_result->fetch()['count'];
    }
    
    // Success
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'database' => $dbname,
        'tables' => $tables,
        'submission_count' => $submission_count
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'error_code' => 'PDO_ERROR'
    ]);
}
