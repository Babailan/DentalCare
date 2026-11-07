<?php
session_start();
require_once __DIR__ . '/../config.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch today's appointments
$todays_date = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE Date = ? AND Status IN ('Pending', 'Approved')");
$stmt->execute([$todays_date]);
$todays_appointments_count = $stmt->fetchColumn();

// Fetch today's available slots
$stmt = $conn->prepare("SELECT COUNT(*) FROM Slots WHERE Date = ? AND Status = 'available'");
$stmt->execute([$todays_date]);
$todays_slots_count = $stmt->fetchColumn();

// Fetch pending appointments
$pending_appointments_count = $conn->query("SELECT COUNT(*) FROM Appointments WHERE Status = 'Pending'")->fetchColumn();

$upcoming = $conn->prepare("
    SELECT a.Date, a.Time, a.Service, a.Price, p.Name AS PatientName, a.Status
    FROM Appointments a
    JOIN Patients p ON p.PatientID = a.PatientID
    WHERE (a.Date > ? OR (a.Date = ? AND a.Time >= ?))
      AND a.Status IN ('Pending', 'Approved')
    ORDER BY a.Date ASC, a.Time ASC
    LIMIT 5
");
$upcoming->execute([$todays_date, $todays_date, date('H:i:s')]);
$upcoming_appointments = $upcoming->fetchAll();
$current_appointments_stmt = $conn->prepare("SELECT COUNT(*) FROM Appointments WHERE (Date > ? OR (Date = ? AND Time >= ?)) AND Status IN ('Pending', 'Approved')");
$current_appointments_stmt->execute([$todays_date, $todays_date, date('H:i:s')]);
$current_appointments_count = (int) $current_appointments_stmt->fetchColumn();

include __DIR__ . '/../header.php';
?>

<div class="mx-auto w-full max-w-6xl px-4 py-8">
<h2 class="text-3xl font-bold text-center mb-8">Doctor Dashboard</h2>

<div class="stats stats-vertical lg:stats-horizontal shadow w-full max-w-4xl mb-8">
    <div class="stat">
        <div class="stat-title">Today's Appointments</div>
        <div class="stat-value text-primary"><?php echo $todays_appointments_count; ?></div>
    </div>
    
    <div class="stat">
        <div class="stat-title">All Upcoming Appointments</div>
        <div class="stat-value text-info"><?php echo $current_appointments_count; ?></div>
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

<section class="card card-border mb-8 w-full bg-base-100 shadow-sm">
    <div class="card-body">
        <h3 class="card-title">Earliest upcoming appointments</h3>
        <?php if (!$upcoming_appointments): ?>
            <p>No upcoming appointments are scheduled.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Service</th><th>Price</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($upcoming_appointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['Date']); ?></td>
                            <td><?php echo htmlspecialchars(substr($appointment['Time'], 0, 5)); ?></td>
                            <td><?php echo htmlspecialchars($appointment['PatientName']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['Service']); ?></td>
                            <td>RM <?php echo number_format((float) $appointment['Price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($appointment['Status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full max-w-4xl">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body items-center text-center">
            <h3 class="card-title">Manage Appointments</h3>
            <div class="card-actions">
                <a class="btn btn-primary" href="appointments.php">Go to Appointments</a>
            </div>
        </div>
    </div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body items-center text-center">
            <h3 class="card-title">Patient Records</h3>
            <div class="card-actions">
                <a class="btn btn-primary" href="patient_records.php">Go to Patients</a>
            </div>
        </div>
    </div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body items-center text-center">
            <h3 class="card-title">Manage Slots</h3>
            <div class="card-actions">
                <a class="btn btn-primary" href="admin_slots.php">Go to Slots</a>
            </div>
        </div>
    </div>
</div>
<div class="mt-6"><a class="btn" href="services.php">Manage services and prices</a></div>
</div>
<?php include __DIR__ . '/../footer.php'; ?>