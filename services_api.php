<?php
session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['patient']) && !isset($_SESSION['admin'])) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Not authenticated.']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
$services = $conn->query('SELECT ServiceID, Name, Price FROM Services WHERE IsActive = 1 ORDER BY Name ASC')->fetchAll();
echo json_encode($services);
