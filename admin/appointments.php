<?php
session_start();
require_once __DIR__ . '/../config.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}

// Handle status update if form is submitted
$statusError = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['appointment_id']) && isset($_POST['status'])) {
        $appointment_id = (int) $_POST['appointment_id'];
        $status = $_POST['status'];

        if (in_array($status, ['Pending', 'Approved', 'Rejected'], true)) {
            $stmt = $conn->prepare('SELECT SlotID FROM Appointments WHERE AppointmentID = ?');
            $stmt->execute([$appointment_id]);
            $appointment = $stmt->fetch();
            if ($appointment) {
                $conn->beginTransaction();
                if (in_array($status, ['Pending', 'Approved'], true) && $appointment['SlotID'] !== null) {
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE SlotID = ? AND AppointmentID != ? AND Status IN ('Pending', 'Approved')");
                    $stmt->execute([$appointment['SlotID'], $appointment_id]);
                    if ((int) $stmt->fetchColumn() > 0) {
                        $conn->rollBack();
                        $statusError = true;
                    } else {
                        $stmt = $conn->prepare("UPDATE Slots SET Status = 'unavailable' WHERE SlotID = ?");
                        $stmt->execute([$appointment['SlotID']]);
                    }
                }
                if (!$statusError) {
                    $stmt = $conn->prepare('UPDATE Appointments SET Status = ? WHERE AppointmentID = ?');
                    $stmt->execute([$status, $appointment_id]);
                }
                if (!$statusError && $status === 'Rejected' && $appointment['SlotID'] !== null) {
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE SlotID = ? AND AppointmentID != ? AND Status IN ('Pending', 'Approved')");
                    $stmt->execute([$appointment['SlotID'], $appointment_id]);
                    if ((int) $stmt->fetchColumn() === 0) {
                        $stmt = $conn->prepare("UPDATE Slots SET Status = 'available' WHERE SlotID = ?");
                        $stmt->execute([$appointment['SlotID']]);
                    }
                }
                if (!$statusError) {
                    $conn->commit();
                }
            }
        }
    }

    // Handle appointment deletion
    if (isset($_POST['delete_appointment_id'])) {
        $appointment_id = (int) $_POST['delete_appointment_id'];
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare('SELECT SlotID FROM Appointments WHERE AppointmentID = ?');
            $stmt->execute([$appointment_id]);
            $appointment = $stmt->fetch();
            $stmt = $conn->prepare('DELETE FROM Appointments WHERE AppointmentID = ?');
            $stmt->execute([$appointment_id]);
            if ($appointment && $appointment['SlotID'] !== null) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE SlotID = ? AND Status IN ('Pending', 'Approved')");
                $stmt->execute([$appointment['SlotID']]);
                if ((int) $stmt->fetchColumn() === 0) {
                    $stmt = $conn->prepare("UPDATE Slots SET Status = 'available' WHERE SlotID = ?");
                    $stmt->execute([$appointment['SlotID']]);
                }
            }
            $conn->commit();
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }
}

// Get sort parameters from URL, default to sorting by AppointmentID
$allowed_columns = ['AppointmentID', 'PatientName', 'Date', 'Time', 'Service', 'Status'];
$sort_column = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_columns, true) ? $_GET['sort'] : 'AppointmentID';
$sort_order = (isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC') ? 'DESC' : 'ASC';

// Toggle sort order
$new_order = $sort_order === 'ASC' ? 'DESC' : 'ASC';

// Join the Appointments and Patients tables to fetch patient names
$query = "SELECT Appointments.AppointmentID, Patients.Name as PatientName, Appointments.Date, Appointments.Time, Appointments.Service, Appointments.Price, Appointments.Status
          FROM Appointments 
          JOIN Patients ON Appointments.PatientID = Patients.PatientID 
          ORDER BY $sort_column $sort_order";
$result = $conn->query($query);
include __DIR__ . '/../header.php';
?>

<h2 class="text-3xl font-bold text-center mb-8">Manage Appointments</h2>
<?php if ($statusError): ?>
    <div class="alert alert-error mb-4">Another active appointment already uses that slot, so the status was not changed.</div>
<?php endif; ?>

<div class="overflow-x-auto w-full max-w-7xl">
    <table class="table table-zebra">
        <thead>
            <tr>
                <th><a href="?sort=AppointmentID&order=<?php echo $new_order; ?>" class="link link-hover">Appointment ID</a></th>   
                <th><a href="?sort=PatientName&order=<?php echo $new_order; ?>" class="link link-hover">Patient Name</a></th>
                <th><a href="?sort=Date&order=<?php echo $new_order; ?>" class="link link-hover">Date</a></th>
                <th><a href="?sort=Time&order=<?php echo $new_order; ?>" class="link link-hover">Time</a></th>
                <th><a href="?sort=Service&order=<?php echo $new_order; ?>" class="link link-hover">Service</a></th>
                <th>Price</th>
                <th><a href="?sort=Status&order=<?php echo $new_order; ?>" class="link link-hover">Status</a></th>
                <th>Change Status</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch()) {
                $statusBadge = '';
                if ($row['Status'] == 'Pending') {
                    $statusBadge = '<div class="badge badge-warning">' . htmlspecialchars($row['Status']) . '</div>';
                } elseif ($row['Status'] == 'Approved') {
                    $statusBadge = '<div class="badge badge-success">' . htmlspecialchars($row['Status']) . '</div>';
                } elseif ($row['Status'] == 'Rejected') {
                    $statusBadge = '<div class="badge badge-error">' . htmlspecialchars($row['Status']) . '</div>';
                } else {
                    $statusBadge = '<div class="badge badge-neutral">' . htmlspecialchars($row['Status']) . '</div>';
                }
                
                echo "<tr>";
                echo "<td>" . $row['AppointmentID'] . "</td>";
                echo "<td>" . htmlspecialchars($row['PatientName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Time']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Service']) . "</td>";
                echo "<td>RM " . number_format((float) $row['Price'], 2) . "</td>";
                echo "<td>" . $statusBadge . "</td>";
                echo "<td>
                        <form action='' method='post'>
                            <input type='hidden' name='appointment_id' value='" . $row['AppointmentID'] . "'>
                            <select class='select select-sm' name='status' onchange='this.form.submit()'>
                                <option value='Pending'" . ($row['Status'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                                <option value='Approved'" . ($row['Status'] == 'Approved' ? ' selected' : '') . ">Approved</option>
                                <option value='Rejected'" . ($row['Status'] == 'Rejected' ? ' selected' : '') . ">Rejected</option>
                            </select>
                        </form>
                      </td>";
                echo "<td>
                        <form action='' method='post' onsubmit='return confirm(\"Are you sure you want to delete this appointment?\");'>
                            <input type='hidden' name='delete_appointment_id' value='" . $row['AppointmentID'] . "'>
                            <button type='submit' class='btn btn-error btn-sm'>Delete</button>
                        </form>
                      </td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
