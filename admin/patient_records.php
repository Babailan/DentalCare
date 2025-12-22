<?php
session_start();
include '../public/config.php';
include '../public/header.php';

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_patient_id'])) {
    $patient_id = $_POST['delete_patient_id'];
    $delete_query = "DELETE FROM Patients WHERE PatientID = $patient_id";

    if (mysqli_query($conn, $delete_query)) {
        echo "<script>alert('Patient deleted successfully');</script>";
    } else {
        echo "<script>alert('Error deleting patient: " . mysqli_error($conn) . "');</script>";
    }
}

// Get sort parameters from URL, default to sorting by PatientID
$sort_column = isset($_GET['sort']) ? $_GET['sort'] : 'PatientID';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Toggle sort order
$new_order = $sort_order === 'ASC' ? 'DESC' : 'ASC';

// Fetch all patients with sorting
$query = "SELECT * FROM Patients ORDER BY $sort_column $sort_order";
$result = mysqli_query($conn, $query);
?>

<h2>Patient Records</h2>

<input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for names, IC, etc.." title="Type in a name or IC">

<table id="patientTable">
    <thead>
        <tr>
            <th><a class='cell' href="?sort=PatientID&order=<?php echo $new_order; ?>">Patient ID</a></th>
            <th><a class='cell' href="?sort=Name&order=<?php echo $new_order; ?>">Name</a></th>
            <th><a class='cell' href="?sort=IC&order=<?php echo $new_order; ?>">IC</a></th>
            <th><a class='cell' href="?sort=Sex&order=<?php echo $new_order; ?>">Sex</a></th>
            <th><a class='cell' href="?sort=Address&order=<?php echo $new_order; ?>">Address</a></th>
            <th><a class='cell' href="?sort=Age&order=<?php echo $new_order; ?>">Age</a></th>
            <th><a class='cell' href="?sort=MobileNo&order=<?php echo $new_order; ?>">Mobile No</a></th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>
    <?php
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['PatientID'] . "</td>";
        echo "<td>" . $row['Name'] . "</td>";
        echo "<td>" . $row['IC'] . "</td>";
        echo "<td>" . $row['Sex'] . "</td>";
        echo "<td>" . $row['Address'] . "</td>";
        echo "<td>" . $row['Age'] . "</td>";
        echo "<td>" . $row['MobileNo'] . "</td>";
        echo "<td>
                <form class='cell' action='edit_patient.php' method='get'>
                    <input type='hidden' name='id' value='" . $row['PatientID'] . "'>
                    <button type='submit'>Edit</button>
                </form>
              </td>";
        echo "<td>
                <form class='cell' action='' method='post' onsubmit='return confirm(\"Are you sure you want to delete this patient? All data regarding this patient will be deleted permanently\");'>
                    <input type='hidden' name='delete_patient_id' value='" . $row['PatientID'] . "'>
                    <button type='submit'>Delete</button>
                </form>
              </td>";
        echo "</tr>";
    }
    ?>
    </tbody>
</table>

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

<?php include '../public/footer.php'; ?>
