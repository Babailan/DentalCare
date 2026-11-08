<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_patient_id'])) {
    $patientId = filter_var($_POST['restore_patient_id'], FILTER_VALIDATE_INT);
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!is_string($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken) || !$patientId) {
        $_SESSION['patient_records_notice'] = ['error', 'Invalid request. Please try again.'];
    } else {
        try {
            $stmt = $conn->prepare('UPDATE Patients SET IsDeleted = 0, DeletedAt = NULL WHERE PatientID = ? AND IsDeleted = 1');
            $stmt->execute([$patientId]);
            $_SESSION['patient_records_notice'] = $stmt->rowCount() === 1
                ? ['success', 'Patient restored successfully.']
                : ['error', 'Deleted patient not found.'];
        } catch (PDOException $e) {
            error_log('Could not restore patient: ' . $e->getMessage());
            $_SESSION['patient_records_notice'] = ['error', 'Could not restore the patient. Please try again.'];
        }
    }

    header('Location: deleted_patients.php');
    exit;
}

$notice = $_SESSION['patient_records_notice'] ?? null;
unset($_SESSION['patient_records_notice']);

$patients = $conn->query('SELECT PatientID, Name, IC, MobileNo, DeletedAt FROM Patients WHERE IsDeleted = 1 ORDER BY DeletedAt DESC, PatientID DESC')->fetchAll();
include __DIR__ . '/../header.php';
?>

<div class="mx-auto w-full max-w-7xl px-4 py-8">
    <h2 class="mb-6 text-3xl font-bold">Deleted patients</h2>
    <p class="mb-6">These patient accounts are hidden from active records but their appointments and messages are preserved. Restore an account to make it active again.</p>
    <?php if ($notice): ?>
        <div class="alert alert-<?php echo htmlspecialchars($notice[0]); ?> mb-4" role="status"><?php echo htmlspecialchars($notice[1]); ?></div>
    <?php endif; ?>
    <div class="mb-4">
        <a class="btn" href="patient_records.php">Back to patient records</a>
    </div>
    <div class="overflow-x-auto">
        <table class="table table-zebra">
            <thead>
                <tr>
                    <th>Patient ID</th>
                    <th>Name</th>
                    <th>IC</th>
                    <th>Mobile No</th>
                    <th>Deleted at</th>
                    <th>Restore</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patients as $patient): ?>
                    <tr>
                        <td><?php echo (int) $patient['PatientID']; ?></td>
                        <td><?php echo htmlspecialchars($patient['Name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($patient['IC'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($patient['MobileNo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($patient['DeletedAt'] ?? ''); ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Restore this patient account?');">
                                <input type="hidden" name="restore_patient_id" value="<?php echo (int) $patient['PatientID']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
                                <button type="submit" class="btn btn-success btn-sm">Restore</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$patients): ?>
                    <tr><td colspan="6">There are no deleted patient accounts.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
