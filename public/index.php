<?php
session_start();    
if (isset($_SESSION['admin'])) {
    header('Location: ../admin/dashboard.php');
    exit;
} elseif (isset($_SESSION['patient'])) {
    header('Location: ../patient/home.php');
    exit;
} else {
    header('Location: login.php');
    exit;
}
?>
