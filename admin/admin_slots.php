<?php
session_start();
include '../public/config.php';
include '../public/header.php';

// Ensure only admin can access this page
if (!isset($_SESSION['admin'])) {
    header('Location: ../public/login.php');
    exit;
}
?>

<h2>Manage Slots</h2>
<div id="calendar-controls">
    <button id="prevMonth">Previous</button>
    <span id="monthAndYear"></span>
    <button id="nextMonth">Next</button>
</div>
<div id="calendar"></div>
<div id="slots">
    <p>Select a day to book appointment</p>
</div>
<button id="generate-slots-button" style="display:none;">Generate Slots</button>
<script>
    const isAdmin = true;
</script>
<script src="../js/calendar.js"></script>

<?php include '../public/footer.php'; ?>
