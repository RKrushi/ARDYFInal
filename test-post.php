<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode([
        'success' => true,
        'message' => 'POST request received successfully!',
        'data' => $_POST,
        'method' => $_SERVER['REQUEST_METHOD']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST allowed. Current method: ' . $_SERVER['REQUEST_METHOD']
    ]);
}
