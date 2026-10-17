<?php
session_start();
include '../public/config.php';
include '../public/header.php';
?>

<h2 class="text-3xl font-bold text-center mb-8">Book Appointment</h2>

<div class="flex flex-col items-center w-full max-w-4xl">
    <div class="join mb-4">
        <button id="prevMonth" class="btn btn-primary join-item">Previous</button>
        <span id="monthAndYear" class="btn btn-ghost join-item font-bold"></span>
        <button id="nextMonth" class="btn btn-primary join-item">Next</button>
    </div>
    <div id="calendar" class="mb-6 w-full"></div>
    <div id="slots">
        <p class="text-lg">Select a day to book an appointment</p>
    </div>
</div>
<script>
    const isAdmin = false;
</script>
<script src="../js/calendar.js"></script>

<?php include '../public/footer.php'; ?>
