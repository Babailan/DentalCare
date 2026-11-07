<?php
include '../public/config.php';

$date = isset($_GET['date']) ? $_GET['date'] : '';

$stmt = $conn->prepare("SELECT Time, Status FROM Slots WHERE Date = ?");
$stmt->execute([$date]);
$slots = $stmt->fetchAll();

echo json_encode($slots);
?>
