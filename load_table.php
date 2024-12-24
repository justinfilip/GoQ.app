<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$tableName = $data['tableName'];

$conn = pg_connect("host=$host port=$port dbname=$dbName user=$user password=$password");

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit;
}

$result = pg_query($conn, "SELECT * FROM $tableName");
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve table data']);
    exit;
}

$rows = [];
while ($row = pg_fetch_assoc($result)) {
    $rows[] = $row;
}

echo json_encode(['success' => true, 'data' => $rows]);
pg_close($conn);
?>
