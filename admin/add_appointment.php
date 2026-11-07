<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}

$message = '';
$messageType = 'error';
$date = $_POST['date'] ?? $_GET['date'] ?? '';
$time = $_POST['time'] ?? $_GET['time'] ?? '';
$serviceId = (int) ($_POST['service_id'] ?? $_GET['service_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patientId = filter_var($_POST['patient_id'] ?? null, FILTER_VALIDATE_INT);
    $dateValid = DateTime::createFromFormat('!Y-m-d', $date);
    $timeValid = preg_match('/^\d{2}:\d{2}$/', $time) === 1;

    $patientStmt = $conn->prepare('SELECT PatientID FROM Patients WHERE PatientID = ?');
    $patientStmt->execute([$patientId]);
    $patient = $patientStmt->fetch();
    $serviceStmt = $conn->prepare('SELECT ServiceID, Name, Price FROM Services WHERE ServiceID = ? AND IsActive = 1');
    $serviceStmt->execute([$serviceId]);
    $service = $serviceStmt->fetch();
    $slotStmt = $conn->prepare("SELECT SlotID FROM Slots WHERE Date = ? AND Time LIKE ? AND Status = 'available'");
    $slotStmt->execute([$date, $time . '%']);
    $slot = $slotStmt->fetch();

    if (!$patient || !$service || !$slot || !$dateValid || $dateValid->format('Y-m-d') !== $date || !$timeValid || $date < date('Y-m-d')) {
        $message = 'Choose a valid patient and service, and verify that the selected slot is still available.';
    } else {
        try {
            $conn->beginTransaction();
            $reserve = $conn->prepare("UPDATE Slots SET Status = 'unavailable' WHERE SlotID = ? AND Status = 'available'");
            $reserve->execute([$slot['SlotID']]);
            if ($reserve->rowCount() !== 1) {
                throw new RuntimeException('The selected slot is no longer available.');
            }
            $stmt = $conn->prepare("INSERT INTO Appointments (PatientID, Date, Time, Service, ServiceID, Price, Status, SlotID) VALUES (?, ?, ?, ?, ?, ?, 'Approved', ?)");
            $stmt->execute([$patient['PatientID'], $date, $time . ':00', $service['Name'], $service['ServiceID'], $service['Price'], $slot['SlotID']]);
            $conn->commit();
            $message = 'Appointment added and confirmed.';
            $messageType = 'success';
        } catch (RuntimeException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $message = $e->getMessage();
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }
}

$patients = $conn->query('SELECT PatientID, Name, MobileNo FROM Patients ORDER BY Name ASC')->fetchAll();
$services = $conn->query('SELECT ServiceID, Name, Price FROM Services WHERE IsActive = 1 ORDER BY Name ASC')->fetchAll();
include __DIR__ . '/../header.php';
?>

<div class="mx-auto w-full max-w-2xl px-4 py-8">
    <h1 class="mb-6 text-3xl font-bold">Add appointment</h1>
    <?php if ($message !== ''): ?>
        <div class="alert alert-<?php echo $messageType; ?> mb-4" role="status"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form id="appointmentForm" action="add_appointment.php" method="post" class="card card-border bg-base-100 shadow-sm">
        <div class="card-body flex flex-col gap-4">
            <label>
                <span class="mb-1 block text-sm font-medium">Patient</span>
                <select class="select w-full" name="patient_id" required>
                    <option value="">Select a patient</option>
                    <?php foreach ($patients as $patient): ?>
                        <option value="<?php echo (int) $patient['PatientID']; ?>"><?php echo htmlspecialchars($patient['Name'] . ' (' . $patient['MobileNo'] . ')'); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Appointment type</span>
                <select class="select w-full" name="service_id" required>
                    <option value="">Select a service</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?php echo (int) $service['ServiceID']; ?>" <?php echo (int) $service['ServiceID'] === $serviceId ? 'selected' : ''; ?>><?php echo htmlspecialchars($service['Name']); ?> — RM <?php echo number_format((float) $service['Price'], 2); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Date</span>
                <input class="input w-full" type="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required readonly>
            </label>
            <label>
                <span class="mb-1 block text-sm font-medium">Time</span>
                <input class="input w-full" type="time" name="time" value="<?php echo htmlspecialchars(substr($time, 0, 5)); ?>" required readonly>
            </label>
            <div class="card-actions">
                <button class="btn btn-primary" type="submit">Add appointment</button>
                <a class="btn btn-ghost" href="admin_slots.php">Back to slots</a>
            </div>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
