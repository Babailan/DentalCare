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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_patient_id'])) {
    $patient_id = filter_var($_POST['delete_patient_id'], FILTER_VALIDATE_INT);
    try {
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!is_string($csrfToken) || !hash_equals($_SESSION['csrf_token'], $csrfToken) || !$patient_id) {
            $_SESSION['patient_records_notice'] = ['error', 'Invalid request. Please try again.'];
        } else {
            $stmt = $conn->prepare('UPDATE Patients SET IsDeleted = 1, DeletedAt = CURRENT_TIMESTAMP WHERE PatientID = ? AND IsDeleted = 0');
            $stmt->execute([$patient_id]);
            $_SESSION['patient_records_notice'] = $stmt->rowCount() === 1
                ? ['success', 'Patient moved to deleted records. You can restore the account at any time.']
                : ['error', 'Patient not found or already deleted.'];
        }
    } catch (PDOException $e) {
        error_log('Could not soft-delete patient: ' . $e->getMessage());
        $_SESSION['patient_records_notice'] = ['error', 'Could not move the patient to deleted records.'];
    }
    header('Location: patient_records.php');
    exit;
}

$notice = $_SESSION['patient_records_notice'] ?? null;
unset($_SESSION['patient_records_notice']);

// Get sort parameters from URL, default to sorting by PatientID
$allowed_columns = ['PatientID', 'Name', 'IC', 'Sex', 'Address', 'Age', 'MobileNo'];
$sort_column = isset($_GET['sort']) && in_array($_GET['sort'], $allowed_columns, true) ? $_GET['sort'] : 'PatientID';
$sort_order = (isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC') ? 'DESC' : 'ASC';

// Toggle sort order
$new_order = $sort_order === 'ASC' ? 'DESC' : 'ASC';

// Deleted patient records are managed on the recovery page.
$query = "SELECT * FROM Patients WHERE IsDeleted = 0 ORDER BY $sort_column $sort_order";
$result = $conn->query($query);
include __DIR__ . '/../header.php';
?>

<h2 class="text-3xl font-bold text-center mb-8">Patient Records</h2>
<?php if ($notice): ?>
    <div class="alert alert-<?php echo htmlspecialchars($notice[0]); ?> mb-4" role="status"><?php echo htmlspecialchars($notice[1]); ?></div>
<?php endif; ?>
<div class="mb-4 flex flex-wrap gap-3">
    <a class="btn btn-primary" href="add_patient.php">Create patient account</a>
    <a class="btn" href="deleted_patients.php">View deleted patients</a>
</div>

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
                <th>Remove</th>
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
                    <form action='patient_records.php' method='post' onsubmit='return confirm(\"Move this patient to deleted records? You can restore them later.\");'>
                        <input type='hidden' name='delete_patient_id' value='" . $row['PatientID'] . "'>
                        <input type='hidden' name='csrf_token' value='" . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) . "'>
                        <button type='submit' class='btn btn-warning btn-sm'>Soft delete</button>
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
