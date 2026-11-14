<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';

$today = date("Y-m-d");

$queueResult = mysqli_query(
    $conn,
    "SELECT a.no_antrian, a.status_antrian, COALESCE(NULLIF(b.nama, ''), 'Barber tersedia') AS nama_barber, l.nama_layanan, COALESCE(l.durasi, 30) AS durasi
     FROM antrian a
     LEFT JOIN barber b ON b.id = a.barber_id
     LEFT JOIN layanan l ON l.id = a.layanan_id
     WHERE a.status_antrian IN ('menunggu', 'proses')
     ORDER BY FIELD(a.status_antrian, 'proses', 'menunggu'), a.no_antrian ASC
     LIMIT 6"
);
$currentQueues = $queueResult ? mysqli_fetch_all($queueResult, MYSQLI_ASSOC) : [];
$nowServing = $currentQueues[0] ?? null;

$wRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM antrian WHERE status_antrian = 'menunggu'");
$waitingCount = $wRes ? (int)mysqli_fetch_assoc($wRes)['total'] : 0;

$pRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM antrian WHERE status_antrian = 'proses'");
$processCount = $pRes ? (int)mysqli_fetch_assoc($pRes)['total'] : 0;

$sRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM antrian WHERE (tanggal = '$today' OR tanggal = CURDATE()) AND status_antrian = 'selesai'");
$servedCount = $sRes ? (int)mysqli_fetch_assoc($sRes)['total'] : 0;

$bRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM barber WHERE LOWER(COALESCE(status, 'aktif')) = 'aktif'");
$activeBarbers = $bRes ? (int)mysqli_fetch_assoc($bRes)['total'] : 0;

$queueTotal = $waitingCount + $processCount + $servedCount;
$estimatedWait = max(5, $waitingCount * 12);
$capacity = min(100, max(12, ($waitingCount + $processCount) * 14));

echo json_encode([
    'success' => true,
    'now_serving' => $nowServing ? str_pad((string)$nowServing['no_antrian'], 3, '0', STR_PAD_LEFT) : '---',
    'queue_total' => $queueTotal,
    'waiting_count' => $waitingCount,
    'process_count' => $processCount,
    'served_count' => $servedCount,
    'active_barbers' => $activeBarbers,
    'estimated_wait' => $estimatedWait,
    'capacity' => $capacity,
    'queues' => $currentQueues
]);

