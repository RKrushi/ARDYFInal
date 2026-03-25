<?php
/* Simple diagnostic to check if PHP and send-mail.php are accessible */
header('Content-Type: application/json');

$diagnostics = [
    'php_working' => true,
    'php_version' => phpversion(),
    'server_time' => date('Y-m-d H:i:s'),
    'post_method' => $_SERVER['REQUEST_METHOD'],
    'send_mail_exists' => file_exists(__DIR__ . '/send-mail.php'),
    'send_mail_path' => __DIR__ . '/send-mail.php',
    'db_config_exists' => file_exists(__DIR__ . '/db-config.php'),
    'mail_function' => function_exists('mail'),
    'pdo_available' => class_exists('PDO')
];

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
