<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['patient'])) {
    header('Location: ../login.php');
    exit;
}

$patientId = (int) $_SESSION['patient'];
$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointmentId = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($appointmentId) {
        $conn->beginTransaction();
        $stmt = $conn->prepare("SELECT SlotID FROM Appointments WHERE AppointmentID = ? AND PatientID = ? AND Status = 'Pending'");
        $stmt->execute([$appointmentId, $patientId]);
        $appointment = $stmt->fetch();
        if ($appointment) {
            $stmt = $conn->prepare("DELETE FROM Appointments WHERE AppointmentID = ? AND PatientID = ? AND Status = 'Pending'");
            $stmt->execute([$appointmentId, $patientId]);
            if ($appointment['SlotID'] !== null) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE SlotID = ? AND Status IN ('Pending', 'Approved')");
                $stmt->execute([$appointment['SlotID']]);
                if ((int) $stmt->fetchColumn() === 0) {
                    $stmt = $conn->prepare("UPDATE Slots SET Status = 'available' WHERE SlotID = ?");
                    $stmt->execute([$appointment['SlotID']]);
                }
            }
            $message = 'Pending appointment request cancelled.';
        } else {
            $message = 'Only pending appointments can be cancelled here.';
            $messageType = 'error';
        }
        $conn->commit();
    } else {
        $message = 'Invalid appointment.';
        $messageType = 'error';
    }
}

$stmt = $conn->prepare('SELECT Date, Time, Service, Price, Status, AppointmentID FROM Appointments WHERE PatientID = ? ORDER BY Date DESC, Time DESC, AppointmentID DESC');
$stmt->execute([$patientId]);
$appointments = $stmt->fetchAll();

include __DIR__ . '/../header.php';
?>

<div class="mx-auto w-full max-w-6xl px-4 py-8">
    <h1 class="mb-6 text-3xl font-bold">My appointment records</h1>
    <p class="mb-6">Your appointment history and upcoming visits are shown here. You can message the doctor if you need help.</p>
    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo $messageType; ?> mb-4" role="status"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <div class="overflow-x-auto">
        <table class="table table-zebra">
            <thead><tr><th>Date</th><th>Time</th><th>Appointment type</th><th>Price</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['Date']); ?></td>
                        <td><?php echo htmlspecialchars(substr($appointment['Time'], 0, 5)); ?></td>
                        <td><?php echo htmlspecialchars($appointment['Service'] ?? ''); ?></td>
                        <td>RM <?php echo number_format((float) $appointment['Price'], 2); ?></td>
                        <td><span class="badge <?php echo $appointment['Status'] === 'Approved' ? 'badge-success' : ($appointment['Status'] === 'Rejected' ? 'badge-error' : 'badge-warning'); ?>"><?php echo htmlspecialchars($appointment['Status']); ?></span></td>
                        <td>
                            <?php if ($appointment['Status'] === 'Pending'): ?>
                                <form method="post" onsubmit="return confirm('Cancel this appointment request?');">
                                    <input type="hidden" name="id" value="<?php echo (int) $appointment['AppointmentID']; ?>">
                                    <button class="btn btn-error btn-sm" type="submit" name="cancel_appointment" value="1">Cancel request</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$appointments): ?>
                    <tr><td colspan="6">You do not have any appointment records yet. <a class="link link-primary" href="appointment_slots.php">View available appointments</a>.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
