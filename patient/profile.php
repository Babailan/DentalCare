<?php
session_start();
include '../public/config.php';
include '../public/header.php';

$patient_id = (int) $_SESSION['patient'];
$stmt = $conn->prepare("SELECT * FROM Patients WHERE PatientID = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    die('Error: No patient data found.');
}
?>

<h2>Profile</h2>
<form id="profileForm" action="update_profile.php" method="post">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" value="<?php echo isset($patient['Name']) ? htmlspecialchars($patient['Name']) : ''; ?>" required>
      
    <label for="ic">IC:</label>
    <input type="text" id="ic" name="ic" value="<?php echo isset($patient['IC']) ? htmlspecialchars($patient['IC']) : ''; ?>" required>
      
    <label for="sex">Sex:</label>
    <select id="sex" name="sex" required>
        <option value="Male" <?php if (isset($patient['Sex']) && $patient['Sex'] == 'Male') echo 'selected'; ?>>Male</option>
        <option value="Female" <?php if (isset($patient['Sex']) && $patient['Sex']   == 'Female') echo 'selected'; ?>>Female</option>
    </select>
      
    <label for="address">Address:</label>
    <input type="text" id="address" name="address" value="<?php echo isset($patient['Address']) ? htmlspecialchars($patient['Address']) : ''; ?>" required>
      
    <label for="age">Age:</label>
    <input type="number" id="age" name="age" value="<?php echo isset($patient['Age']) ? htmlspecialchars($patient['Age']) : ''; ?>" required>
      
    <label for="mobile_no">Mobile No:</label>
    <input type="text" id="mobile_no" name="mobile_no" value="<?php echo isset($patient['MobileNo']) ? htmlspecialchars($patient['MobileNo']) : ''; ?>" required>
      
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?php echo isset($patient['Email']) ? htmlspecialchars($patient['Email']) : ''; ?>" required>
      
    <input type="submit" value="Update Profile">
</form>

<!--Validate all the profile fields-->
<script>
    document.getElementById('profileForm').addEventListener('submit', function(event) {
        const name = document.getElementById('name').value;
        const ic = document.getElementById('ic').value;
        const sex = document.getElementById('sex').value;
        const address = document.getElementById('address').value;
        const age = document.getElementById('age').value;
        const mobileNo = document.getElementById('mobile_no').value;
        const email = document.getElementById('email').value;

        // Validate Name
        if (name.trim() === '') {
            alert('Name is required.');
            event.preventDefault();
            return false;
        }

        // Validate IC
        if (!/^\d{12}$/.test(ic)) {
            alert('IC must be a 12-digit number.');
            event.preventDefault();
            return false;
        }

        // Validate Sex
        if (sex !== 'Male' && sex !== 'Female') {
            alert('Please select a valid sex.');
            event.preventDefault();
            return false;
        }

        // Validate Address
        if (address.trim() === '') {
            alert('Address is required.');
            event.preventDefault();
            return false;
        }

        // Validate Age
        if (age < 1 || age > 120) {
            alert('Please enter a valid age.');
            event.preventDefault();
            return false;
        }

        // Validate Mobile Number
        if (!/^\d{10,11}$/.test(mobileNo)) {
            alert('Mobile number must be 10 or 11 digits.');
            event.preventDefault();
            return false;
        }

        // Validate Email
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            alert('Please enter a valid email address.');
            event.preventDefault();
            return false;
        }

        alert ('Successfully Updated Profile Information ')
        return true;
    });
</script>

<?php include '../public/footer.php'; ?>
