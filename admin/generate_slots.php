<?php
include '../public/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = isset($_POST['date']) ? $_POST['date'] : '';

    // Predefined time slots from 9 AM to 5 PM
    $timeSlots = [
        '09:00:00', '10:00:00', '11:00:00', '12:00:00',
        '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'
    ];

    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO Slots (Date, Time, Status) VALUES (?, ?, 'available')");

        foreach ($timeSlots as $timeSlot) {
            $stmt->execute([$date, $timeSlot]);
        }

        $conn->commit();
        echo "Slots added successfully";
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo "Error: " . $e->getMessage();
    }
}
?>
