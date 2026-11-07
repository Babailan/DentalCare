<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}
$patient_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM Patients WHERE PatientID = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();

if (!$patient) {
    die('Patient not found.');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $ic = trim($_POST['ic'] ?? '');
    $sex = $_POST['sex'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $age = (int) ($_POST['age'] ?? 0);
    $mobile_no = trim($_POST['mobile_no'] ?? '');

    if ($ic !== '' && !preg_match('/^\d{12}$/', $ic)) {
        $error = 'IC must be blank or 12 digits.';
    } else {
        $stmt = $conn->prepare("UPDATE Patients SET Name = ?, IC = ?, Sex = ?, Address = ?, Age = ?, MobileNo = ? WHERE PatientID = ?");
        $stmt->execute([$name, $ic === '' ? null : $ic, $sex, $address, $age, $mobile_no, $patient_id]);

        header('Location: patient_records.php');
        exit;
    }
}
include __DIR__ . '/../header.php';
?>

<h2 class="text-3xl font-bold text-center mb-8">Edit Patient</h2>

<div class="card card-bordered w-full max-w-md bg-base-100 shadow-xl">
    <div class="card-body">
        <?php if (!empty($error)): ?>
            <p role="alert"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form id="editPatientForm" action="" method="post" class="flex flex-col gap-4">
            <label class="floating-label">
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($patient['Name']); ?>" placeholder="Name" class="input w-full" required />
                <span>Name</span>
            </label>
            
            <label class="floating-label">
                <input type="text" id="ic" name="ic" value="<?php echo htmlspecialchars($_POST['ic'] ?? $patient['IC'] ?? ''); ?>" placeholder="IC (optional, 12 digits)" inputmode="numeric" pattern="\d{12}" class="input w-full" />
                <span>IC (optional, 12 digits)</span>
            </label>
            
            <div class="floating-label">
                <select id="sex" name="sex" class="select w-full" required>
                    <option value="Male" <?php if ($patient['Sex'] == 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if ($patient['Sex'] == 'Female') echo 'selected'; ?>>Female</option>
                </select>
                <span>Sex</span>
            </div>
            
            <label class="floating-label">
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($patient['Address']); ?>" placeholder="Address" class="input w-full" required />
                <span>Address</span>
            </label>
            
            <label class="floating-label">
                <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($patient['Age']); ?>" placeholder="Age" class="input w-full" required />
                <span>Age</span>
            </label>
            
            <label class="floating-label">
                <input type="text" id="mobile_no" name="mobile_no" value="<?php echo htmlspecialchars($patient['MobileNo']); ?>" placeholder="Mobile No" class="input w-full" required />
                <span>Mobile No</span>
            </label>
            
            <button type="submit" class="btn btn-primary w-full mt-2">Update</button>
        </form>
        
        <div class="card-actions justify-center mt-4">
            <button type="button" class="btn btn-ghost" onclick="window.location.href='patient_records.php';">Back</button>
        </div>
    </div>
</div>

<script>
document.getElementById('editPatientForm').addEventListener('submit', function(event) {
    const name = document.getElementById('name').value.trim();
    const ic = document.getElementById('ic').value.trim();
    const sex = document.getElementById('sex').value;
    const address = document.getElementById('address').value.trim();
    const age = document.getElementById('age').value;
    const mobile_no = document.getElementById('mobile_no').value.trim();

    if (!name || !sex || !address || !age || !mobile_no) {
        alert('All fields are required.');
        event.preventDefault();
    } else if (ic && !/^\d{12}$/.test(ic)) {
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

<?php include __DIR__ . '/../footer.php'; ?>
