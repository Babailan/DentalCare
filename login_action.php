<?php
session_start();
include 'config.php';

$usernameInput = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

function redirect_with_error($message, $username = '') {
    $_SESSION['login_error'] = $message;
    $_SESSION['login_username'] = $username;
    header('Location: login.php');
    exit();
}

if (!is_string($usernameInput) || !is_string($password)) {
    redirect_with_error('Please enter a valid username and password.');
}

$username = trim($usernameInput);
if ($username === '' || $password === '') {
    redirect_with_error('Please enter your username and password.', $username);
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
    redirect_with_error('Invalid credentials. Please try again.', $username);
}

// Check if user is patient
$stmt = $conn->prepare("SELECT * FROM Patients WHERE Username = ? AND IsDeleted = 0");
$stmt->execute([$username]);
$patient = $stmt->fetch();

if ($patient) {
    if (password_verify($password, $patient['Password'])) {
        $_SESSION['patient'] = $patient['PatientID'];
        header('Location: ../patient/home.php');
        exit;
    }
    redirect_with_error('Invalid credentials. Please try again.', $username);
}

// Invalid login
redirect_with_error('Invalid credentials. Please try again.', $username);
?>
