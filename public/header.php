<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Appointment System</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="wrapper">
        <header>
            <h1>Dental Appointment System</h1>
            <nav>
                <?php
                if (isset($_SESSION['admin'])) {
                    echo '<a href="../admin/dashboard.php">Dashboard</a> | ';
                    echo '<a href="../admin/patient_records.php">Patient Records</a> | ';
                    echo '<a href="../admin/appointments.php">Manage Appointments</a> | ';
                    echo '<a href="../admin/admin_slots.php">Manage Slots</a> | ';
                    echo '<a href="../public/logout.php" onclick="return confirmLogout();">Logout</a>';
                } elseif (isset($_SESSION['patient'])) {
                    echo '<a href="../patient/home.php">Home</a> | ';
                    echo '<a href="../patient/appointment_slots.php">Book Appointment</a> | ';
                    echo '<a href="../patient/appointments.php">My Appointments</a> | ';
                    echo '<a href="../patient/profile.php">Profile</a> | ';
                    echo '<a href="../public/logout.php" onclick="return confirmLogout();">Logout</a>';
                } else {
                    echo '<a href="../public/login.php">Login</a> | ';
                    echo '<a href="../public/register.php">Register</a>';
                }
                ?>
            </nav>
        </header>
        <main>
            <script>
                function confirmLogout() {
                    return confirm("Are you sure you want to logout?");
                }
            </script>
