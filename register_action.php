<?php
include 'config.php';

$name = trim($_POST['name']);
$ic = trim($_POST['ic']);
$sex = $_POST['sex'];
$address = trim($_POST['address']);
$age = intval($_POST['age']);
$mobile_no = trim($_POST['mobile_no']);
$email = trim($_POST['email']);
$username = trim($_POST['username']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Function to redirect with an error message
function redirect_with_error($message) {
    header("Location: register.php?error=" . urlencode($message));
    exit();
}

// Server-side validation
if (empty($name) || empty($ic) || empty($sex) || empty($address) || empty($age) || empty($mobile_no) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
    redirect_with_error('All fields are required.');
}

if (!preg_match('/^\d{12}$/', $ic)) {
    redirect_with_error('IC must be a 12-digit number.');
}

if ($sex !== 'Male' && $sex !== 'Female') {
    redirect_with_error('Please select a valid sex.');
}

if ($age < 1 || $age > 120) {
    redirect_with_error('Please enter a valid age.');
}

if (!preg_match('/^\d{10,11}$/', $mobile_no)) {
    redirect_with_error('Mobile number must be 10 or 11 digits.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with_error('Please enter a valid email address.');
}

if (strlen($password) < 8) {
    redirect_with_error('Password must be at least 8 characters long.');
}

if ($password !== $confirm_password) {
    redirect_with_error('Passwords do not match.');
}

// Check if username is already taken
$stmt = $conn->prepare("SELECT COUNT(*) FROM Patients WHERE Username = ?");
$stmt->execute([$username]);

if ($stmt->fetchColumn() > 0) {
    // Username already exists
    redirect_with_error('Username is already taken. Please choose a different username.');
}

$password_hashed = password_hash($password, PASSWORD_BCRYPT);

$query = "INSERT INTO Patients (Name, IC, Sex, Address, Age, MobileNo, Email, Username, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

try {
    $stmt = $conn->prepare($query);
    $stmt->execute([$name, $ic, $sex, $address, $age, $mobile_no, $email, $username, $password_hashed]);
    header('Location: login.php');
    exit;
} catch (PDOException $e) {
    redirect_with_error('Error: ' . $e->getMessage());
}
?>
