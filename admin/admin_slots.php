<?php
session_start();
require_once __DIR__ . '/../config.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin'])) {
    header('Location: ../login.php');
    exit;
}
$patients = $conn->query('SELECT PatientID, Name, MobileNo FROM Patients WHERE IsDeleted = 0 ORDER BY Name ASC')->fetchAll();
include __DIR__ . '/../header.php';
?>

<h2 class="text-3xl font-bold text-center mb-8">Manage Slots &amp; Book Patients</h2>

<div class="flex flex-col items-center w-full max-w-4xl">
    <div class="join mb-4">
        <button id="prevMonth" class="btn btn-primary join-item">Previous</button>
        <span id="monthAndYear" class="btn btn-ghost join-item font-bold"></span>
        <button id="nextMonth" class="btn btn-primary join-item">Next</button>
    </div>
    <div id="calendar" class="mb-6 w-full"></div>
    <div id="slots" class="mb-4 text-center">
        <p class="text-lg">Select a day to manage slots or book a patient into an available time.</p>
    </div>
    <button id="generate-slots-button" class="btn btn-success" style="display:none;">Generate Slots</button>
</div>
<script>
    const isAdmin = true;
    const adminPatients = <?php echo json_encode($patients, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
</script>
<script src="../js/calendar.js"></script>

<?php include __DIR__ . '/../footer.php'; ?>
