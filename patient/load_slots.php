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

$select = isset($_SESSION['admin'])
    ? "SELECT Slots.SlotID, Slots.Time, Slots.Status, Slots.IsBlocked,
              (SELECT Patients.Name
               FROM Appointments
               JOIN Patients ON Appointments.PatientID = Patients.PatientID
               WHERE Appointments.SlotID = Slots.SlotID
                 AND Appointments.Status = 'Approved'
               ORDER BY Appointments.AppointmentID DESC
               LIMIT 1) AS BookedBy
              ,(SELECT group_concat(Patients.Name, ', ')
                FROM Appointments
                JOIN Patients ON Appointments.PatientID = Patients.PatientID
                WHERE Appointments.SlotID = Slots.SlotID
                  AND Appointments.Status = 'Pending') AS PendingRequests
       FROM Slots
       WHERE Slots.Date = ?
       ORDER BY Slots.Time ASC"
    : 'SELECT Time, Status FROM Slots WHERE Date = ? ORDER BY Time ASC';
$stmt = $conn->prepare($select);
$stmt->execute([$date]);
echo json_encode($stmt->fetchAll());
