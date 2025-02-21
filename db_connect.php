<?php
$servername = "sql210.infinityfree.com";
$username = "if0_38181546";
$password = "F8sCvelamea";
$dbname = "if0_38181546_cb_community";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Enable error reporting for database queries
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
?>

