<?php
session_start();
include '../public/config.php';

$patient_id = $_SESSION['patient'];
$date = $_POST['date'];
$time = $_POST['time'];
$service = $_POST['service'];

// Fetch SlotID based on date and time
$slot_query = "SELECT SlotID FROM Slots WHERE Date='$date' AND Time='$time' AND Status='available'";
$slot_result = mysqli_query($conn, $slot_query);
$slot = mysqli_fetch_assoc($slot_result);
$slot_id = $slot['SlotID'];

if ($slot_id) {
    $query = "INSERT INTO Appointments (PatientID, Date, Time, Service, Status, SlotID) VALUES ($patient_id, '$date', '$time', '$service', 'Pending', $slot_id)";
    if (mysqli_query($conn, $query)) {
        header('Location: appointments.php');
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    echo "No available slot for the selected date and time.";
}
?>
