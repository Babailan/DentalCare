<?php
session_start();
include '../public/config.php';
include '../public/header.php';

if (!isset($_SESSION['patient'])) {
    header('Location: ../public/login.php');
    exit;
}

$patient_id = (int) $_SESSION['patient'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointment_id = (int) $_POST['id'];

    try {
        // Fetch SlotID for the appointment
        $stmt = $conn->prepare("SELECT SlotID FROM Appointments WHERE AppointmentID = ? AND PatientID = ?");
        $stmt->execute([$appointment_id, $patient_id]);
        $appointment = $stmt->fetch();

        if ($appointment) {
            // Delete appointment
            $stmt = $conn->prepare("DELETE FROM Appointments WHERE AppointmentID = ?");
            $stmt->execute([$appointment_id]);

            // Update slot status
            if ($appointment['SlotID'] !== null) {
                $stmt = $conn->prepare("UPDATE Slots SET Status = 'available' WHERE SlotID = ?");
                $stmt->execute([$appointment['SlotID']]);
            }

            echo "<script>alert('Appointment cancelled successfully');</script>";
        } else {
            echo "<script>alert('Appointment not found.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "');</script>";
    }
}

$stmt = $conn->prepare("SELECT * FROM Appointments WHERE PatientID = ?");
$stmt->execute([$patient_id]);
$result = $stmt->fetchAll();
?>

<h2>My Appointments</h2>
<table>
    <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Service</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Time']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Service']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
        if ($row['Status'] == 'Pending') {
            echo "<td>
                    <form class='cell' action='' method='post' onsubmit='return confirm(\"Are you sure you want to cancel this appointment?\");'>
                        <input type='hidden' name='id' value='" . $row['AppointmentID'] . "'>
                        <input type='hidden' name='cancel_appointment' value='true'>
                        <button class='cell' type='submit'>Cancel</button>
                    </form>
                  </td>";
        } else {
            echo "<td></td>";
        }
        echo "</tr>";
    }
    ?>
</table>

<?php include '../public/footer.php'; ?>
