<?php
include '../public/config.php';

$date = $_GET['date'];
$query = "SELECT Time, Status FROM Slots WHERE Date='$date'";
$result = mysqli_query($conn, $query);
$slots = array();   

while ($row = mysqli_fetch_assoc($result)) {
    $slots[] = $row;
}

echo json_encode($slots);
?>


