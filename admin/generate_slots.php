<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin'])) {
    http_response_code(401);
    exit('Not authenticated.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $parsedDate = DateTime::createFromFormat('!Y-m-d', $date);
    if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date || $date < date('Y-m-d')) {
        http_response_code(422);
        exit('Choose a valid date from today onward.');
    }

    $timeSlots = [];
    for ($minutes = 9 * 60; $minutes <= 17 * 60; $minutes += 30) {
        $timeSlots[] = sprintf('%02d:%02d:00', intdiv($minutes, 60), $minutes % 60);
    }

    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO Slots (Date, Time, Status) SELECT ?, ?, 'available' WHERE NOT EXISTS (SELECT 1 FROM Slots WHERE Date = ? AND Time = ?)");

        foreach ($timeSlots as $timeSlot) {
            $stmt->execute([$date, $timeSlot, $date, $timeSlot]);
        }

        $conn->commit();
        echo "Slots added successfully";
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        http_response_code(500);
        echo 'Could not generate appointment slots.';
    }
}
?>
