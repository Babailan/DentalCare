<?php
session_start();
include '../public/config.php';

$patient_id = $_SESSION['patient'];
$name = trim($_POST['name']);
$ic = trim($_POST['ic']);
$sex = $_POST['sex'];
$address = trim($_POST['address']);
$age = intval($_POST['age']);
$mobile_no = trim($_POST['mobile_no']);
$email = trim($_POST['email']);

// Server-side validation
if (empty($name) || empty($ic) || empty($sex) || empty($address) || empty($age) || empty($mobile_no) || empty($email)) {
    die('All fields are required.');
}

if (!preg_match('/^\d{12}$/', $ic)) {
    die('IC must be a 12-digit number.');
}

if ($sex !== 'Male' && $sex !== 'Female') {
    die('Please select a valid sex.');
}

if ($age < 1 || $age > 120) {
    die('Please enter a valid age.');
}

if (!preg_match('/^\d{10,11}$/', $mobile_no)) {
    die('Mobile number must be 10 or 11 digits.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Please enter a valid email address.');
}

$query = "UPDATE Patients SET name='$name', ic='$ic', sex='$sex', address='$address', age=$age, mobileNo='$mobile_no', email='$email' WHERE patientID=$patient_id";
if (mysqli_query($conn, $query)) {
    header('Location: profile.php');
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
