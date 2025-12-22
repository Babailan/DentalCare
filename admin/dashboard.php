<?php
session_start();
include '../public/config.php';
include '../public/header.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin'])) {
    header('Location: ../public/login.php');
    exit;
}

// Fetch today's appointments
$todays_date = date('Y-m-d');
$todays_appointments_query = "SELECT COUNT(*) AS count FROM Appointments WHERE Date = '$todays_date'";
$todays_appointments_result = mysqli_query($conn, $todays_appointments_query);
$todays_appointments_count = mysqli_fetch_assoc($todays_appointments_result)['count'];

// Fetch today's available slots
$todays_slots_query = "SELECT COUNT(*) AS count FROM Slots WHERE Date='$todays_date' AND Status='available'";
$todays_slots_result = mysqli_query($conn, $todays_slots_query);
$todays_slots_count = mysqli_fetch_assoc($todays_slots_result)['count'];

// Fetch pending appointments
$pending_appointments_query = "SELECT COUNT(*) AS count FROM Appointments WHERE Status = 'Pending'";
$pending_appointments_result = mysqli_query($conn, $pending_appointments_query);
$pending_appointments_count = mysqli_fetch_assoc($pending_appointments_result)['count'];
?>

<h2>Admin Dashboard</h2>

<div class="dashboard-overview">
    <div class="overview-card">
        <h3>Today's Appointments</h3>
        <p><?php echo $todays_appointments_count; ?></p>
    </div>
    <div class="overview-card">
        <h3>Pending Appointments</h3>
        <p><?php echo $pending_appointments_count; ?></p>
    </div>
    <div class="overview-card">
        <h3>Today Available Slot</h3>
        <p><?php echo $todays_slots_count; ?></p>
</div>

<div class="dashboard-sections">
    <div class="section">
        <h3>Manage Appointments</h3>
        <button onclick="window.location.href='appointments.php';">Go to Appointments</button>
    </div>
    <div class="section">
        <h3>Patient Records</h3>
        <button onclick="window.location.href='patient_records.php';">Go to Patients</button>
    </div>
    <div class="section">
        <h3>Manage Slots</h3>
        <button onclick="window.location.href='admin_slots.php';">Go to Slots</button>
    </div>
</div>

<?php include '../public/footer.php'; ?>
