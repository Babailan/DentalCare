<?php
session_start();
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['patient']) && !isset($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated.']);
    exit;
}

$date = $_GET['date'] ?? '';
$parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid date.']);
    exit;
}

$stmt = $conn->prepare('SELECT Time, Status FROM Slots WHERE Date = ? ORDER BY Time ASC');
$stmt->execute([$date]);
echo json_encode($stmt->fetchAll());
