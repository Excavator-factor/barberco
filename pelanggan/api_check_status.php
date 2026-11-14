<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION["role"]) || strtolower($_SESSION["role"]) !== "pelanggan") {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once "../config/database.php";
require_once "../config/helper.php";

$user_id = (int)($_SESSION["id_user"] ?? ($_SESSION["user_id"] ?? ($_SESSION["id"] ?? 0)));
$queue_id = (int)($_GET['queue_id'] ?? 0);

if ($user_id <= 0 || $queue_id <= 0) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT a.status_antrian, t.status_pembayaran 
    FROM antrian a 
    LEFT JOIN transaksi t ON t.antrian_id = a.id 
    WHERE a.id = ? AND a.pelanggan_id = ? LIMIT 1");

if (!$stmt) {
    echo json_encode(['error' => 'Database error']);
    exit();
}

mysqli_stmt_bind_param($stmt, "ii", $queue_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
    echo json_encode(['found' => false]);
    exit();
}

$status_antrian = strtolower((string)$row['status_antrian']);
$status_pembayaran = strtolower((string)($row['status_pembayaran'] ?? 'pending'));

echo json_encode([
    'found' => true,
    'status_antrian' => $status_antrian,
    'status_pembayaran' => $status_pembayaran,
    'need_payment' => ($status_antrian === 'selesai' && $status_pembayaran !== 'lunas')
]);
