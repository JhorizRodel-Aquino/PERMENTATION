<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = mysqli_connect("127.0.0.1", "root", "", "permdb");

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed: " . mysqli_connect_error()
    ]);
    exit;
}

$sql = "SELECT intervals, temperature FROM control WHERE id = 1";
$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500); // Query error (server issue)
    echo json_encode([
        "status" => "error",
        "message" => "Database query failed: " . mysqli_error($conn)
    ]);
} elseif (mysqli_num_rows($result) === 0) {
    http_response_code(404); // No rows found
    echo json_encode([
        "status" => "error",
        "message" => "No data found"
    ]);
} else {
    $data = mysqli_fetch_assoc($result);
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => [
            "intervals" => floatval($data['intervals']),
            "temperature" => floatval($data['temperature'])
        ]
    ]);
}

mysqli_close($conn);
?>
