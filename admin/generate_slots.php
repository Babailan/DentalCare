<?php
include '../public/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];

    // Predefined time slots from 9 AM to 5 PM
    $timeSlots = [
        '09:00:00', '10:00:00', '11:00:00', '12:00:00', 
        '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'
    ];

    $values = [];
    foreach ($timeSlots as $timeSlot) {
        $values[] = "('$date', '$timeSlot', 'available')";
    }

    $valuesString = implode(', ', $values);

    $query = "INSERT INTO Slots (Date, Time, Status) VALUES $valuesString";
    if (mysqli_query($conn, $query)) {
        echo "Slots added successfully";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
