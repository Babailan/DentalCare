<?php
session_start();
include '../public/header.php';
include '../public/config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../public/login.php');
    exit;
}

$patient_id = $_GET['id'];
$query = "SELECT * FROM Patients WHERE PatientID=$patient_id";
$result = mysqli_query($conn, $query);
$patient = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $ic = $_POST['ic'];
    $sex = $_POST['sex'];
    $address = $_POST['address'];
    $age = $_POST['age'];
    $mobile_no = $_POST['mobile_no'];

    $update_query = "UPDATE Patients SET Name='$name', IC='$ic', Sex='$sex', Address='$address', Age=$age, MobileNo='$mobile_no' WHERE PatientID=$patient_id";
    if (mysqli_query($conn, $update_query)) {
        header('Location: patient_records.php');
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<h2>Edit Patient</h2>
<form id="editPatientForm" action="" method="post">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($patient['Name']); ?>" required>
    
    <label for="ic">IC:</label>
    <input type="text" id="ic" name="ic" value="<?php echo htmlspecialchars($patient['IC']); ?>" required>
    
    <label for="sex">Sex:</label>
    <select id="sex" name="sex" required>
        <option value="Male" <?php if ($patient['Sex'] == 'Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if ($patient['Sex'] == 'Female') echo 'selected'; ?>>Female</option>
    </select>
    
    <label for="address">Address:</label>
    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($patient['Address']); ?>" required>
    
    <label for="age">Age:</label>
    <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($patient['Age']); ?>" required>
    
    <label for="mobile_no">Mobile No:</label>
    <input type="text" id="mobile_no" name="mobile_no" value="<?php echo htmlspecialchars($patient['MobileNo']); ?>" required>
    
    <input type="submit" value="Update">
</form>
<div class='back'>
    <button type="button" onclick="window.location.href='patient_records.php';">Back</button>
</div>

<script>
document.getElementById('editPatientForm').addEventListener('submit', function(event) {
    const name = document.getElementById('name').value.trim();
    const ic = document.getElementById('ic').value.trim();
    const sex = document.getElementById('sex').value;
    const address = document.getElementById('address').value.trim();
    const age = document.getElementById('age').value;
    const mobile_no = document.getElementById('mobile_no').value.trim();

    if (!name || !ic || !sex || !address || !age || !mobile_no) {
        alert('All fields are required.');
        event.preventDefault();
    } else if (!/^\d{12}$/.test(ic)) {
        alert('IC must be a 12-digit number.');
        event.preventDefault();
    } else if (!['Male', 'Female'].includes(sex)) {
        alert('Sex must be either "Male" or "Female".');
        event.preventDefault();
    } else if (age < 1 || age > 120) {
        alert('Please enter a valid age.');
        event.preventDefault();
    } else if (!/^\d{10,11}$/.test(mobile_no)) {
        alert('Mobile number must be 10 or 11 digits.');
        event.preventDefault();
    }
});
</script>

<?php include '../public/footer.php'; ?>
