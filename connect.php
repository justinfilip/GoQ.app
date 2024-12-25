<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST is allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$host = $data['host'];
$user = $data['user'];
$password = $data['password'];
$dbName = $data['dbName'];
$dbType = $data['dbType'];
$sslCert = isset($data['sslCert']) ? $data['sslCert'] : '';

try {
    switch ($dbType) {
        case 'mysql':
            $dsn = "mysql:host=$host;dbname=$dbName";
            break;
        case 'pgsql':
            $dsn = "pgsql:host=$host;dbname=$dbName";
            if (!empty($sslCert)) {
                $tempCertFile = sys_get_temp_dir() . '/db_cert_' . uniqid() . '.pem';
                file_put_contents($tempCertFile, $sslCert);
                $dsn .= ";sslmode=require;sslrootcert=$tempCertFile";
            }
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

    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

    if (!empty($sslCert)) {
        switch ($dbType) {
            case 'mysql':
                $tempCertFile = sys_get_temp_dir() . '/db_cert_' . uniqid() . '.pem';
                file_put_contents($tempCertFile, $sslCert);
                $options[PDO::MYSQL_ATTR_SSL_CA] = $tempCertFile;
                break;
            case 'pgsql':
                // Additional PG SSL parameters may need DSN changes or pdo_pgsql > 1.4
                break;
        }
    }

    $pdo = new PDO($dsn, $user, $password, $options);

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
