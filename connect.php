<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

$data = json_decode(file_get_contents('php://input'), true);
$host = $data['host'];
$user = $data['user'];
$password = $data['password'];
$dbName = $data['dbName'];
$dbType = $data['dbType'];

try {
    switch ($dbType) {
        case 'mysql':
            $dsn = "mysql:host=$host;dbname=$dbName";
            break;
        case 'pgsql':
            $dsn = "pgsql:host=$host;dbname=$dbName";
            break;
        case 'sqlite':
            $dsn = "sqlite:$dbName";
            break;
        case 'sqlsrv':
            $dsn = "sqlsrv:Server=$host;Database=$dbName";
            break;
        default:
            throw new Exception('Unsupported database type');
    }

    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = [];
    $query = $pdo->query("SHOW TABLES");
    while ($row = $query->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }

    echo json_encode(['success' => true, 'tables' => $tables]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
