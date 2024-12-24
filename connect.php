<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$host = $data['host'];
$port = $data['port'];
$user = $data['user'];
$password = $data['password'];
$dbName = $data['dbName'];

$conn = pg_connect("host=$host port=$port dbname=$dbName user=$user password=$password");

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit;
}

$result = pg_query($conn, "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve tables']);
    exit;
}

$tables = [];
while ($row = pg_fetch_assoc($result)) {
    $tables[] = $row['table_name'];
}

echo json_encode(['success' => true, 'tables' => $tables]);
pg_close($conn);
?>
