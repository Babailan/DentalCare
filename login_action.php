<?php
session_start();
include 'config.php';

$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Function to redirect with an error message
function redirect_with_error($message) {
    header("Location: login.php?error=" . urlencode($message));
    exit();
}

// Check if user is admin
$stmt = $conn->prepare("SELECT * FROM Admin WHERE Username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin) {
    $adminPasswordMatches = password_verify($password, $admin['Password'])
        || hash_equals((string) $admin['Password'], $password);
    if ($adminPasswordMatches) {
        $_SESSION['admin'] = $admin['AdminID'];
        header('Location: ../admin/dashboard.php');
        exit;
    }
    redirect_with_error('Invalid credentials. Please try again.');
}

// Check if user is patient
$stmt = $conn->prepare("SELECT * FROM Patients WHERE Username = ?");
$stmt->execute([$username]);
$patient = $stmt->fetch();

if ($patient) {
    if (password_verify($password, $patient['Password'])) {
        $_SESSION['patient'] = $patient['PatientID'];
        header('Location: ../patient/home.php');
        exit;
    }
    redirect_with_error('Invalid credentials. Please try again.');
}

// Invalid login
redirect_with_error('Invalid credentials. Please try again.');
?>
