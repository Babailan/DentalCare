<?php
session_start();
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

function booking_response(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['patient'])) {
    booking_response(401, ['error' => 'Sign in to book an appointment.']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    booking_response(405, ['error' => 'POST required.']);
}

$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$serviceId = filter_var($_POST['service_id'] ?? null, FILTER_VALIDATE_INT);
$dateValue = DateTime::createFromFormat('!Y-m-d', $date);
if (!$dateValue || $dateValue->format('Y-m-d') !== $date || $date < date('Y-m-d')
    || !preg_match('/^\d{2}:\d{2}$/', $time) || !$serviceId) {
    booking_response(422, ['error' => 'Choose a valid date, time, and appointment type.']);
}

$serviceStmt = $conn->prepare('SELECT ServiceID, Name, Price FROM Services WHERE ServiceID = ? AND IsActive = 1');
$serviceStmt->execute([$serviceId]);
$service = $serviceStmt->fetch();
if (!$service) {
    booking_response(422, ['error' => 'That appointment type is no longer available.']);
}

try {
    $conn->beginTransaction();
    $slotStmt = $conn->prepare("SELECT SlotID FROM Slots WHERE Date = ? AND Time LIKE ? AND Status = 'available' LIMIT 1");
    $slotStmt->execute([$date, $time . '%']);
    $slot = $slotStmt->fetch();
    if (!$slot) {
        $conn->rollBack();
        booking_response(409, ['error' => 'That appointment time is no longer available.']);
    }

    $reserve = $conn->prepare("UPDATE Slots SET Status = 'unavailable' WHERE SlotID = ? AND Status = 'available'");
    $reserve->execute([$slot['SlotID']]);
    if ($reserve->rowCount() !== 1) {
        $conn->rollBack();
        booking_response(409, ['error' => 'That appointment time was just booked. Please choose another slot.']);
    }

    $appointment = $conn->prepare("INSERT INTO Appointments (PatientID, Date, Time, Service, ServiceID, Price, Status, SlotID) VALUES (?, ?, ?, ?, ?, ?, 'Pending', ?)");
    $appointment->execute([
        (int) $_SESSION['patient'],
        $date,
        $time . ':00',
        $service['Name'],
        $service['ServiceID'],
        $service['Price'],
        $slot['SlotID'],
    ]);
    $conn->commit();
    booking_response(201, ['success' => true, 'message' => 'Appointment request sent to the clinic.']);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    booking_response(500, ['error' => 'The appointment could not be saved. Please try again.']);
}
