<?php
session_start();
include '../public/config.php';
include '../public/header.php';

$patient_id = $_SESSION['patient'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_appointment'])) {
    $appointment_id = $_POST['id'];

    // Fetch SlotID for the appointment
    $appointment_query = "SELECT SlotID FROM Appointments WHERE AppointmentID=$appointment_id";
    $appointment_result = mysqli_query($conn, $appointment_query);
    $appointment = mysqli_fetch_assoc($appointment_result);
    $slot_id = $appointment['SlotID'];

    // Delete appointment
    $query = "DELETE FROM Appointments WHERE AppointmentID=$appointment_id";
    if (mysqli_query($conn, $query)) {
        // Update slot status
        $slot_query = "UPDATE Slots SET Status='available' WHERE SlotID=$slot_id";
        mysqli_query($conn, $slot_query);

        echo "<script>alert('Appointment cancelled successfully');</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

$query = "SELECT * FROM Appointments WHERE PatientID=$patient_id";
$result = mysqli_query($conn, $query);
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
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Date'] . "</td>";
        echo "<td>" . $row['Time'] . "</td>";
        echo "<td>" . $row['Service'] . "</td>";
        echo "<td>" . $row['Status'] . "</td>";
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
