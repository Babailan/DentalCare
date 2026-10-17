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
            <div class="navbar bg-base-200">
                <div class="navbar-start">
                    <h1 class="text-xl font-bold">Dental Care</h1>
                </div>
                <div class="navbar-end">
                    <nav class="flex flex-wrap items-center gap-2">
                        <?php
                        if (isset($_SESSION['admin'])) {
                            echo '<a href="../admin/dashboard.php" class="btn btn-ghost btn-sm">Dashboard</a>';
                            echo '<a href="../admin/patient_records.php" class="btn btn-ghost btn-sm">Patient Records</a>';
                            echo '<a href="../admin/appointments.php" class="btn btn-ghost btn-sm">Manage Appointments</a>';
                            echo '<a href="../admin/admin_slots.php" class="btn btn-ghost btn-sm">Manage Slots</a>';
                            echo '<a href="../public/logout.php" class="btn btn-ghost btn-sm" onclick="return confirmLogout();">Logout</a>';
                        } elseif (isset($_SESSION['patient'])) {
                            echo '<a href="../patient/home.php" class="btn btn-ghost btn-sm">Home</a>';
                            echo '<a href="../patient/appointment_slots.php" class="btn btn-ghost btn-sm">Book Appointment</a>';
                            echo '<a href="../patient/appointments.php" class="btn btn-ghost btn-sm">My Appointments</a>';
                            echo '<a href="../patient/profile.php" class="btn btn-ghost btn-sm">Profile</a>';
                            echo '<a href="../public/logout.php" class="btn btn-ghost btn-sm" onclick="return confirmLogout();">Logout</a>';
                        } else {
                            echo '<a href="../public/login.php" class="btn btn-ghost btn-sm">Login</a>';
                        }
                        ?>
                    </nav>
                </div>
            </div>
        </header>
        <main class="flex-1 flex flex-col items-center justify-center p-4">
            <script>
                function confirmLogout() {
                    return confirm("Are you sure you want to logout?");
                }
            </script>
