<?php
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
$scriptDirectory = str_replace('\\', '/', dirname($scriptName));
$scriptFolder = basename($scriptDirectory);
if (in_array($scriptFolder, ['admin', 'patient'], true)) {
    $scriptDirectory = str_replace('\\', '/', dirname($scriptDirectory));
}
$appBasePath = rtrim($scriptDirectory, '/');
if ($appBasePath === '.' || $appBasePath === '/') {
    $appBasePath = '';
}

$isAdminPortal = isset($_SESSION['admin']) && $scriptFolder === 'admin';
$isPatientPortal = isset($_SESSION['patient']) && $scriptFolder === 'patient';
$isPortal = $isAdminPortal || $isPatientPortal;
$currentPage = basename($scriptName);
$pageTitles = [
    'dashboard.php' => 'Doctor dashboard',
    'appointments.php' => $isAdminPortal ? 'Manage appointments' : 'My appointments',
    'patient_records.php' => 'Patient records',
    'add_patient.php' => 'Create patient account',
    'edit_patient.php' => 'Edit patient',
    'add_appointment.php' => 'Add appointment',
    'admin_slots.php' => 'Manage appointment slots',
    'appointment_slots.php' => 'Book an appointment',
    'messages.php' => 'Messages',
    'profile.php' => 'My profile',
    'services.php' => 'Services and prices',
    'home.php' => 'Your care, all in one place',
];
$portalTitle = $pageTitles[$currentPage] ?? 'CIAT Dental Clinic';
$brandUrl = htmlspecialchars($appBasePath . '/index.php', ENT_QUOTES, 'UTF-8');
$loginUrl = htmlspecialchars($appBasePath . '/login.php', ENT_QUOTES, 'UTF-8');
$contactUrl = htmlspecialchars($appBasePath . '/contact.php', ENT_QUOTES, 'UTF-8');
$logoutUrl = htmlspecialchars($appBasePath . '/logout.php', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($portalTitle); ?> | CIAT Dental Clinic</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($appBasePath . '/css/portal.css', ENT_QUOTES, 'UTF-8'); ?>">
</head>
<body class="app-shell<?php echo $isPortal ? ' portal-shell' : ' public-shell'; ?>">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <?php if ($isPortal): ?>
        <?php
        $navigation = $isAdminPortal
            ? [
                ['dashboard.php', 'Dashboard', '◫'],
                ['appointments.php', 'Appointments', '▣'],
                ['patient_records.php', 'Patients', '♙'],
                ['add_patient.php', 'Create patient', '+'],
                ['admin_slots.php', 'Appointment slots', '▦'],
                ['services.php', 'Services & prices', '◇'],
                ['messages.php', 'Messages', '✉'],
            ]
            : [
                ['home.php', 'Home', '⌂'],
                ['appointment_slots.php', 'Book appointment', '+'],
                ['appointments.php', 'My appointments', '▣'],
                ['messages.php', 'Messages', '✉'],
                ['profile.php', 'My profile', '♙'],
            ];
        ?>
        <div class="portal-layout">
            <input id="portal-menu-toggle" class="portal-menu-toggle" type="checkbox" aria-label="Toggle navigation">
            <aside class="portal-sidebar">
                <a class="portal-brand" href="<?php echo $brandUrl; ?>">
                    <span class="portal-brand-mark" aria-hidden="true">✳</span>
                    <span>CIAT<span class="portal-brand-subtitle">Dental Clinic</span></span>
                </a>
                <div class="portal-role"><?php echo $isAdminPortal ? 'CLINIC ADMIN' : 'PATIENT PORTAL'; ?></div>
                <nav class="portal-nav" aria-label="<?php echo $isAdminPortal ? 'Admin' : 'Patient'; ?> navigation">
                    <?php foreach ($navigation as [$page, $label, $icon]): ?>
                        <?php
                        $href = htmlspecialchars(($isAdminPortal ? $appBasePath . '/admin/' : $appBasePath . '/patient/') . $page, ENT_QUOTES, 'UTF-8');
                        $active = $currentPage === $page;
                        ?>
                        <a class="portal-nav-link<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo $href; ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
                            <span class="portal-nav-icon" aria-hidden="true"><?php echo $icon; ?></span>
                            <span><?php echo htmlspecialchars($label); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <div class="portal-sidebar-bottom">
                    <a class="portal-nav-link portal-logout" href="<?php echo $logoutUrl; ?>" onclick="return confirmLogout();">
                        <span class="portal-nav-icon" aria-hidden="true">↗</span>
                        <span>Sign out</span>
                    </a>
                    <p>Care that starts with listening.</p>
                </div>
            </aside>
            <label class="portal-sidebar-overlay" for="portal-menu-toggle" aria-label="Close navigation"></label>
            <div class="portal-workspace">
                <header class="portal-topbar">
                    <label class="portal-menu-button" for="portal-menu-toggle" aria-label="Open navigation">
                        <span></span><span></span><span></span>
                    </label>
                    <div class="portal-topbar-title">
                        <span><?php echo $isAdminPortal ? 'CLINIC WORKSPACE' : 'YOUR DENTAL CARE'; ?></span>
                        <h1><?php echo htmlspecialchars($portalTitle); ?></h1>
                    </div>
                    <a class="portal-contact-link" href="<?php echo $contactUrl; ?>">Contact clinic <span aria-hidden="true">↗</span></a>
                </header>
                <main id="main-content" class="portal-main">
    <?php else: ?>
        <header class="site-header">
            <nav class="site-navbar" aria-label="Main navigation">
                <a class="portal-brand" href="<?php echo $brandUrl; ?>">
                    <span class="portal-brand-mark" aria-hidden="true">✳</span>
                    <span>CIAT<span class="portal-brand-subtitle">Dental Clinic</span></span>
                </a>
                <div class="public-nav">
                    <a href="<?php echo $contactUrl; ?>">Contact</a>
                    <?php if (isset($_SESSION['admin'])): ?>
                        <a class="btn btn-primary" href="<?php echo htmlspecialchars($appBasePath . '/admin/dashboard.php', ENT_QUOTES, 'UTF-8'); ?>">Dashboard</a>
                    <?php elseif (isset($_SESSION['patient'])): ?>
                        <a class="btn btn-primary" href="<?php echo htmlspecialchars($appBasePath . '/patient/home.php', ENT_QUOTES, 'UTF-8'); ?>">My portal</a>
                    <?php else: ?>
                        <a class="btn btn-primary" href="<?php echo $loginUrl; ?>">Patient sign in</a>
                    <?php endif; ?>
                </div>
            </nav>
        </header>
        <main id="main-content" class="public-main">
    <?php endif; ?>
    <script>
        function confirmLogout() {
            return confirm("Are you sure you want to logout?");
        }
    </script>
