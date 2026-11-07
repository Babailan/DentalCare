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
$stmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE Date = ?");
$stmt->execute([$todays_date]);
$todays_appointments_count = $stmt->fetchColumn();

// Fetch today's available slots
$stmt = $conn->prepare("SELECT COUNT(*) FROM Slots WHERE Date = ? AND Status = 'available'");
$stmt->execute([$todays_date]);
$todays_slots_count = $stmt->fetchColumn();

// Fetch pending appointments
$pending_appointments_count = $conn->query("SELECT COUNT(*) FROM Appointments WHERE Status = 'Pending'")->fetchColumn();
?>

<h2 class="text-3xl font-bold text-center mb-8">Admin Dashboard</h2>

<div class="stats stats-vertical lg:stats-horizontal shadow w-full max-w-4xl mb-8">
    <div class="stat">
        <div class="stat-title">Today's Appointments</div>
        <div class="stat-value text-primary"><?php echo $todays_appointments_count; ?></div>
    </div>
    
    <div class="stat">
        <div class="stat-title">Pending Appointments</div>
        <div class="stat-value text-warning"><?php echo $pending_appointments_count; ?></div>
    </div>
    
    <div class="stat">
        <div class="stat-title">Today's Available Slots</div>
        <div class="stat-value text-success"><?php echo $todays_slots_count; ?></div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body items-center text-center">
            <h3 class="card-title">Manage Appointments</h3>
            <div class="card-actions">
                <button class="btn btn-primary" onclick="window.location.href='appointments.php';">Go to Appointments</button>
            </div>
        </div>
    </div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body items-center text-center">
            <h3 class="card-title">Patient Records</h3>
            <div class="card-actions">
                <button class="btn btn-primary" onclick="window.location.href='patient_records.php';">Go to Patients</button>
            </div>
        </div>
    </div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body items-center text-center">
            <h3 class="card-title">Manage Slots</h3>
            <div class="card-actions">
                <button class="btn btn-primary" onclick="window.location.href='admin_slots.php';">Go to Slots</button>
            </div>
        </div>
    </div>
</div>

<?php include '../public/footer.php'; ?>