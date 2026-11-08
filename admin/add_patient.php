<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}

$error = '';
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$middleName = trim($_POST['middle_name'] ?? '');
$suffix = trim($_POST['suffix'] ?? '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = implode(' ', array_filter([$firstName, $middleName, $lastName, $suffix], static fn ($part) => $part !== ''));
    $ic = trim($_POST['ic'] ?? '');
    $sex = $_POST['sex'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $age = filter_var($_POST['age'] ?? null, FILTER_VALIDATE_INT);
    $mobile = trim($_POST['mobile_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($firstName === '' || $lastName === '' || $address === '' || $username === '' || !in_array($sex, ['Male', 'Female'], true)
        || ($ic !== '' && !preg_match('/^\d{12}$/', $ic)) || $age === false || $age < 1 || $age > 120
        || !preg_match('/^\d{10,11}$/', $mobile) || !filter_var($email, FILTER_VALIDATE_EMAIL)
        || strlen($password) < 8) {
        $error = 'Check all fields. IC must be blank or 12 digits, age 1–120, mobile 10–11 digits, and password at least 8 characters.';
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO Patients (Name, IC, Sex, Address, Age, MobileNo, Email, Username, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $ic === '' ? null : $ic, $sex, $address, $age, $mobile, $email, $username, password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: patient_records.php?created=1');
            exit;
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $error = 'That username is already in use.';
            } else {
                throw $e;
            }
        }
    }
}

include __DIR__ . '/../header.php';
?>

<div class="mx-auto w-full max-w-3xl px-4 py-8">
    <h1 class="mb-6 text-3xl font-bold">Create patient account</h1>
    <p class="mb-6">Only the doctor can create patient accounts. Give the patient their username and temporary password securely.</p>
    <?php if ($error !== ''): ?>
        <div class="alert alert-error mb-6" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" class="card card-border bg-base-100 shadow-sm">
        <div class="card-body grid grid-cols-1 gap-4 md:grid-cols-2">
            <label><span class="mb-1 block text-sm font-medium">First name</span><input class="input w-full" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required></label>
            <label><span class="mb-1 block text-sm font-medium">Last name</span><input class="input w-full" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" required></label>
            <label><span class="mb-1 block text-sm font-medium">Middle name</span><input class="input w-full" name="middle_name" value="<?php echo htmlspecialchars($middleName); ?>"></label>
            <label><span class="mb-1 block text-sm font-medium">Suffix (optional)</span><input class="input w-full" name="suffix" value="<?php echo htmlspecialchars($suffix); ?>"></label>
            <label><span class="mb-1 block text-sm font-medium">IC (optional, 12 digits)</span><input class="input w-full" name="ic" inputmode="numeric" pattern="\d{12}"></label>
            <label><span class="mb-1 block text-sm font-medium">Sex</span><select class="select w-full" name="sex" required><option value="">Select</option><option>Male</option><option>Female</option></select></label>
            <label><span class="mb-1 block text-sm font-medium">Age</span><input class="input w-full" type="number" name="age" min="1" max="120" required></label>
            <label><span class="mb-1 block text-sm font-medium">Mobile number</span><input class="input w-full" name="mobile_no" pattern="\d{10,11}" required></label>
            <label><span class="mb-1 block text-sm font-medium">Email</span><input class="input w-full" type="email" name="email" required></label>
            <label class="md:col-span-2"><span class="mb-1 block text-sm font-medium">Address</span><input class="input w-full" name="address" required></label>
            <label><span class="mb-1 block text-sm font-medium">Username</span><input class="input w-full" name="username" required></label>
            <label><span class="mb-1 block text-sm font-medium">Temporary password (8+ characters)</span><input class="input w-full" type="password" name="password" minlength="8" required></label>
            <div class="card-actions md:col-span-2">
                <button class="btn btn-primary" type="submit">Create account</button>
                <a class="btn btn-ghost" href="patient_records.php">Cancel</a>
            </div>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../footer.php'; ?>
