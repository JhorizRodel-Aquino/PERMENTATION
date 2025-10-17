
<?php
header('Content-Type: application/json');
$conn = mysqli_connect("127.0.0.1", "root", "", "permdb");

if (!$conn) {
    die(json_encode(["error" => "Connection failed: " . mysqli_connect_error()]));
}

$query = "SELECT temp, hum, ph, lc,datetimelog FROM datalogs ORDER BY datetimelog DESC LIMIT 7";
$result = mysqli_query($conn, $query);

$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
mysqli_close($conn);
?>