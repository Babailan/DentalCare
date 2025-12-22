<?php
session_start();
include 'config.php';

$username = $_POST['username'];
$password = $_POST['password'];

// Function to redirect with an error message
function redirect_with_error($message) {
    header("Location: login.php?error=" . urlencode($message));
    exit();
}

// Check if user is admin
$query = "SELECT * FROM Admin WHERE Username=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $admin = $result->fetch_assoc();
    if ($password === $admin['Password']) {
        $_SESSION['admin'] = $admin['AdminID'];
        header('Location: ../admin/dashboard.php');
        exit;
    } else {
        redirect_with_error('Invalid credentials. Please try again.');
    }
}

// Check if user is patient
$query = "SELECT * FROM Patients WHERE Username=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $patient = $result->fetch_assoc();
    if (password_verify($password, $patient['Password'])) {
        $_SESSION['patient'] = $patient['PatientID'];
        header('Location: ../patient/home.php');
        exit;
    } else {
        redirect_with_error('Invalid credentials. Please try again.');
    }
}

// Invalid login
redirect_with_error('Invalid credentials. Please try again.');
?>
