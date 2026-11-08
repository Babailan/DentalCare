<?php
session_start();
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

function manage_slot_response(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['admin'])) {
    manage_slot_response(401, ['error' => 'Not authenticated.']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    manage_slot_response(405, ['error' => 'POST required.']);
}

$slotId = filter_var($_POST['slot_id'] ?? null, FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';
if (!$slotId || !in_array($action, ['block', 'unblock'], true)) {
    manage_slot_response(422, ['error' => 'Choose a valid slot and action.']);
}

try {
    $conn->beginTransaction();
    $slotStmt = $conn->prepare('SELECT SlotID, Date, Status, IsBlocked FROM Slots WHERE SlotID = ?');
    $slotStmt->execute([$slotId]);
    $slot = $slotStmt->fetch();
    if (!$slot || $slot['Date'] < date('Y-m-d')) {
        $conn->rollBack();
        manage_slot_response(422, ['error' => 'Only existing slots from today onward can be changed.']);
    }

    $appointmentStmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE SlotID = ? AND Status = 'Approved'");
    $appointmentStmt->execute([$slotId]);
    if ((int) $appointmentStmt->fetchColumn() > 0) {
        $conn->rollBack();
        manage_slot_response(409, ['error' => 'This slot has a confirmed appointment and cannot be changed.']);
    }

    if ($action === 'block') {
        if ($slot['Status'] !== 'available' || (int) $slot['IsBlocked'] === 1) {
            $conn->rollBack();
            manage_slot_response(409, ['error' => 'Only available slots can be made unavailable.']);
        }
        $update = $conn->prepare("UPDATE Slots SET Status = 'unavailable', IsBlocked = 1 WHERE SlotID = ? AND Status = 'available' AND IsBlocked = 0");
    } else {
        if ((int) $slot['IsBlocked'] !== 1) {
            $conn->rollBack();
            manage_slot_response(409, ['error' => 'This slot was not blocked by the clinic.']);
        }
        $update = $conn->prepare("UPDATE Slots SET Status = 'available', IsBlocked = 0 WHERE SlotID = ? AND Status = 'unavailable' AND IsBlocked = 1");
    }
    $update->execute([$slotId]);
    if ($update->rowCount() !== 1) {
        $conn->rollBack();
        manage_slot_response(409, ['error' => 'This slot changed; refresh and try again.']);
    }

    $conn->commit();
    manage_slot_response(200, [
        'success' => true,
        'message' => $action === 'block' ? 'Slot made unavailable.' : 'Slot is available again.',
    ]);
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    manage_slot_response(500, ['error' => 'Could not update this slot.']);
}
?>
