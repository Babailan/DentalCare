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

    // Predefined time slots from 9 AM to 5 PM
    $timeSlots = [
        '09:00:00', '10:00:00', '11:00:00', '12:00:00',
        '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'
    ];

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
