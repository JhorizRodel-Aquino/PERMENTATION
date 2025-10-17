<?php
date_default_timezone_set("Asia/Manila"); // Set timezone to Manila

$conn = mysqli_connect("127.0.0.1", "root", "", "permdb");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo $ph = mysqli_real_escape_string($conn, $_GET['ph']);
$hum = mysqli_real_escape_string($conn, $_GET['hum']);
$temp = mysqli_real_escape_string($conn, $_GET['temp']);
$loadC = mysqli_real_escape_string($conn, $_GET['loadC']);
$remarks = "Off";
$dateNow = date("y-m-d G:i:s"); 
$machineStatus = "Inactive"; 

$send = "INSERT INTO datalogs (`temp`, `hum`, `lc`, `ph`, `remarks`, `datetimelog`, `machineStatus`) 
VALUES ('$temp', '$hum', '$loadC', '$ph', '$remarks', '$dateNow', '$machineStatus')";

if (mysqli_query($conn, $send)) {
    echo "Send";
} else {
    echo "Don't Send: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
