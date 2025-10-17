<?php
header('Content-Type: application/json');
$conn = mysqli_connect("127.0.0.1", "root", "", "permdb");

if (!$conn) {
    die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
}

$query = "SELECT temp, hum, ph, lc, datetimelog FROM datalogs ORDER BY datetimelog DESC LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result) {
    die(json_encode(["error" => "Query failed: " . mysqli_error($conn)]));
}

$data = mysqli_fetch_assoc($result);
echo json_encode($data ?: ["error" => "No data found"]);

mysqli_close($conn);
?>
