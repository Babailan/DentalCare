<?php
session_start();
include '../public/config.php';
include '../public/header.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin'])) {
    header('Location: ../public/login.php');
    exit;
}

// Handle status update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['appointment_id']) && isset($_POST['status'])) {
        $appointment_id = $_POST['appointment_id'];
        $status = $_POST['status'];

        // Update appointment status
        $query = "UPDATE Appointments SET Status = '$status' WHERE AppointmentID = $appointment_id";
        if (!mysqli_query($conn, $query)) {
            echo "Error updating record: " . mysqli_error($conn);
        } else {
            // If the status is approved, update the corresponding slot status to unavailable
            if ($status == 'Approved') {
                $slot_query = "SELECT SlotID FROM Appointments WHERE AppointmentID = $appointment_id";
                $slot_result = mysqli_query($conn, $slot_query);
                if ($slot_result && mysqli_num_rows($slot_result) > 0) {
                    $slot_row = mysqli_fetch_assoc($slot_result);
                    $slot_id = $slot_row['SlotID'];
                    $update_slot_query = "UPDATE Slots SET Status = 'unavailable' WHERE SlotID = $slot_id";
                    mysqli_query($conn, $update_slot_query);
                }
            }
        }
    }

    // Handle appointment deletion
    if (isset($_POST['delete_appointment_id'])) {
        $appointment_id = $_POST['delete_appointment_id'];

        $delete_query = "DELETE FROM Appointments WHERE AppointmentID = $appointment_id";
        if (!mysqli_query($conn, $delete_query)) {
            echo "Error deleting record: " . mysqli_error($conn);
        }
    }
}

// Get sort parameters from URL, default to sorting by AppointmentID
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'AppointmentID';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Toggle sort order
$new_order = $sort_order === 'ASC' ? 'DESC' : 'ASC';

// Join the Appointments and Patients tables to fetch patient names
$query = "SELECT Appointments.AppointmentID, Patients.Name as PatientName, Appointments.Date, Appointments.Time, Appointments.Service, Appointments.Status 
          FROM Appointments 
          JOIN Patients ON Appointments.PatientID = Patients.PatientID 
          ORDER BY $sort_column $sort_order";
$result = mysqli_query($conn, $query);
?>

<h2>Manage Appointments</h2>
<table>
    <tr>
        <!--Add sorting to every column-->
        <th><a class='cell' href="?sort=AppointmentID&order=<?php echo $new_order; ?>">Appointment ID</a></th>   
        <th><a class='cell' href="?sort=PatientName&order=<?php echo $new_order; ?>">Patient Name</a></th>
        <th><a class='cell' href="?sort=Date&order=<?php echo $new_order; ?>">Date</a></th>
        <th><a class='cell' href="?sort=Time&order=<?php echo $new_order; ?>">Time</a></th>
        <th><a class='cell' href="?sort=Service&order=<?php echo $new_order; ?>">Service</a></th>
        <th><a class='cell' href="?sort=Status&order=<?php echo $new_order; ?>">Status</a></th>
        <th>Change Status</th>
        <th>Delete</th>
    </tr>
    <?php
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['AppointmentID'] . "</td>";
        echo "<td>" . $row['PatientName'] . "</td>";
        echo "<td>" . $row['Date'] . "</td>";
        echo "<td>" . $row['Time'] . "</td>";
        echo "<td>" . $row['Service'] . "</td>";
        echo "<td>" . $row['Status'] . "</td>";
        echo "<td>
                <form class='cell' action='' method='post'>
                    <input type='hidden' name='appointment_id' value='" . $row['AppointmentID'] . "'>
                    <select class='cell' name='status' onchange='this.form.submit()'>
                        <option value='Pending'" . ($row['Status'] == 'Pending' ? ' selected' : '') . ">Pending</option>
                        <option value='Approved'" . ($row['Status'] == 'Approved' ? ' selected' : '') . ">Approved</option>
                        <option value='Rejected'" . ($row['Status'] == 'Rejected' ? ' selected' : '') . ">Rejected</option>
                    </select>
                </form>
              </td>";
        echo "<td>
                <form class='cell' action='' method='post' onsubmit='return confirm(\"Are you sure you want to delete this appointment?\");'>
                    <input type='hidden' name='delete_appointment_id' value='" . $row['AppointmentID'] . "'>
                    <button type='submit' class='delete'>Delete</button>
                </form>
              </td>";
        echo "</tr>";
    }
    ?>
</table>

<?php include '../public/footer.php'; ?>
