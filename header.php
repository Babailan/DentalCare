<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Appointment System</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>

<body>
    <div class="min-h-screen flex flex-col bg-base-100">
        <header>
            <div class="navbar  p-10">
                <div class="navbar-start">
                    <h1 class="text-xl font-bold">IDentalCare</h1>
                </div>
                <div class="navbar-end">
                    <nav class="flex flex-wrap items-center gap-2 text-sm">
                        <?php
                        if (isset($_SESSION['admin'])) {
                            echo '<a href="../admin/dashboard.php" class="btn btn-ghost btn-sm">Dashboard</a>';
                            echo '<a href="../admin/patient_records.php" class="btn btn-ghost btn-sm">Patient Records</a>';
                            echo '<a href="../admin/appointments.php" class="btn btn-ghost btn-sm">Manage Appointments</a>';
                            echo '<a href="../admin/messages.php" class="btn btn-ghost btn-sm">Messages</a>';
                            echo '<a href="../admin/admin_slots.php" class="btn btn-ghost btn-sm">Manage Slots</a>';
                            echo '<a href="../public/logout.php" class="btn btn-ghost btn-sm" onclick="return confirmLogout();">Logout</a>';
                        } elseif (isset($_SESSION['patient'])) {
                            echo '<a href="../patient/home.php" class="btn btn-ghost btn-sm">Home</a>';
                            echo '<a href="../patient/appointment_slots.php" class="btn btn-ghost btn-sm">Book Appointment</a>';
                            echo '<a href="../patient/appointments.php" class="btn btn-ghost btn-sm">My Appointments</a>';
                            echo '<a href="../patient/messages.php" class="btn btn-ghost btn-sm">Messages</a>';
                            echo '<a href="../patient/profile.php" class="btn btn-ghost btn-sm">Profile</a>';
                            echo '<a href="../public/logout.php" class="btn btn-ghost btn-sm" onclick="return confirmLogout();">Logout</a>';
                        } 
                        ?>
                        <a href="../contact.php">FAQ</a>
                        <a href="../contact.php" class="mr-5 text-primary">GET A QUOTE</a>
                        <?php 
                            echo '<a href="./login.php">Sign In</a>';
                        ?>
                    </nav>
                </div>
            </div>
        </header>
        <main class="">
            <script>
                function confirmLogout() {
                    return confirm("Are you sure you want to logout?");
                }
            </script>