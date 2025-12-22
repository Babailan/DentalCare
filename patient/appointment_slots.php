<?php
session_start();
include '../public/config.php';
include '../public/header.php';
?>

<h2>Book Appointment</h2>
<div id="calendar-controls">
    <button id="prevMonth">Previous</button>
    <span id="monthAndYear"></span>
    <button id="nextMonth">Next</button>
</div>
<div id="calendar"></div>
<div id="slots">
    <p>Select a day to book appointment</p>
</div>
<script>
    const isAdmin = false;
</script>
<script src="../js/calendar.js"></script>

<?php include '../public/footer.php'; ?>
