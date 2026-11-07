<?php
session_start();
include '../public/config.php';

if (!isset($_SESSION['patient'])) {
    http_response_code(401);
    echo 'Not authenticated.';
    exit;
}

$patient_id = (int) $_SESSION['patient'];
$date = isset($_POST['date']) ? $_POST['date'] : '';
$time = isset($_POST['time']) ? $_POST['time'] : '';
$service = isset($_POST['service']) ? $_POST['service'] : '';

// Fetch SlotID based on date and time
$stmt = $conn->prepare("SELECT SlotID FROM Slots WHERE Date = ? AND Time = ? AND Status = 'available'");
$stmt->execute([$date, $time]);
$slot = $stmt->fetch();

if ($slot) {
    $slot_id = $slot['SlotID'];

    try {
        $stmt = $conn->prepare("INSERT INTO Appointments (PatientID, Date, Time, Service, Status, SlotID) VALUES (?, ?, ?, ?, 'Pending', ?)");
        $stmt->execute([$patient_id, $date, $time, $service, $slot_id]);
        header('Location: appointments.php');
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No available slot for the selected date and time.";
}
?>
