<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}
include __DIR__ . '/../header.php';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_patient_id'])) {
    $patient_id = (int) $_POST['delete_patient_id'];

    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare('SELECT SlotID FROM Appointments WHERE PatientID = ? AND SlotID IS NOT NULL');
        $stmt->execute([$patient_id]);
        $slotIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt = $conn->prepare("DELETE FROM Patients WHERE PatientID = ?");
        $stmt->execute([$patient_id]);
        foreach (array_unique($slotIds) as $slotId) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE SlotID = ? AND Status IN ('Pending', 'Approved')");
            $stmt->execute([$slotId]);
            if ((int) $stmt->fetchColumn() === 0) {
                $stmt = $conn->prepare("UPDATE Slots SET Status = 'available' WHERE SlotID = ?");
                $stmt->execute([$slotId]);
            }
        }
        $conn->commit();
        echo "<script>alert('Patient deleted successfully');</script>";
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo "<script>alert('Error deleting patient: " . htmlspecialchars($e->getMessage(), ENT_QUOTES) . "');</script>";
    }
}

// Get sort parameters from URL, default to sorting by PatientID
$allowed_columns = ['PatientID', 'Name', 'IC', 'Sex', 'Address', 'Age', 'MobileNo'];
$sort_column = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_columns, true) ? $_GET['sort'] : 'PatientID';
$sort_order = (isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC') ? 'DESC' : 'ASC';

// Toggle sort order
$new_order = $sort_order === 'ASC' ? 'DESC' : 'ASC';

// Fetch all patients with sorting
$query = "SELECT * FROM Patients ORDER BY $sort_column $sort_order";
$result = $conn->query($query);
?>

<h2 class="text-3xl font-bold text-center mb-8">Patient Records</h2>
<div class="mb-4 w-full max-w-2xl"><a class="btn btn-primary" href="add_patient.php">Create patient account</a></div>

<div class="mb-4 w-full max-w-2xl">
    <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for names, IC, etc.." title="Type in a name or IC" class="input w-full">
</div>

<div class="overflow-x-auto w-full max-w-7xl">
    <table id="patientTable" class="table table-zebra">
        <thead>
            <tr>
                <th><a href="?sort=PatientID&order=<?php echo $new_order; ?>" class="link link-hover">Patient ID</a></th>
                <th><a href="?sort=Name&order=<?php echo $new_order; ?>" class="link link-hover">Name</a></th>
                <th><a href="?sort=IC&order=<?php echo $new_order; ?>" class="link link-hover">IC</a></th>
                <th><a href="?sort=Sex&order=<?php echo $new_order; ?>" class="link link-hover">Sex</a></th>
                <th><a href="?sort=Address&order=<?php echo $new_order; ?>" class="link link-hover">Address</a></th>
                <th><a href="?sort=Age&order=<?php echo $new_order; ?>" class="link link-hover">Age</a></th>
                <th><a href="?sort=MobileNo&order=<?php echo $new_order; ?>" class="link link-hover">Mobile No</a></th>
                <th>Edit</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
        <?php
        while ($row = $result->fetch()) {
            echo "<tr>";
            echo "<td>" . $row['PatientID'] . "</td>";
            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['IC']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Sex']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Address']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Age']) . "</td>";
            echo "<td>" . htmlspecialchars($row['MobileNo']) . "</td>";
            echo "<td>
                    <form action='edit_patient.php' method='get'>
                        <input type='hidden' name='id' value='" . $row['PatientID'] . "'>
                        <button type='submit' class='btn btn-warning btn-sm'>Edit</button>
                    </form>
                  </td>";
            echo "<td>
                    <form action='' method='post' onsubmit='return confirm(\"Are you sure you want to delete this patient? All data regarding this patient will be deleted permanently\");'>
                        <input type='hidden' name='delete_patient_id' value='" . $row['PatientID'] . "'>
                        <button type='submit' class='btn btn-error btn-sm'>Delete</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<script>
// search bar function - filter rows by value
function searchTable() {
    // Get the input element and its value, convert the value to lowercase
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();

    // Get the table element and all the rows within it
    const table = document.getElementById("patientTable");
    const tr = table.getElementsByTagName("tr");

    // Loop through all table rows 
    for (let i = 1; i < tr.length; i++) {
        // Get all the cells in the current row
        const tdArray = tr[i].getElementsByTagName("td");  
        let match = false;

        // Loop through all cells in the current row
        for (let j = 0; j < tdArray.length; j++) {
            if (tdArray[j]) {
                // Get the text content of the current cell and convert it to lowercase
                const tdText = tdArray[j].textContent || tdArray[j].innerText;
                // Check if the cell's text contains the search query (filter)
                if (tdText.toLowerCase().indexOf(filter) > -1) {
                    // If a match is found, set match to true and break out of the loop
                    match = true;
                    break;
                }
            }
        }
        // If a match was found in any cell of the row, display the row; otherwise, hide it
        tr[i].style.display = match ? "" : "none";
    }
}
</script>

<?php include __DIR__ . '/../footer.php'; ?>
