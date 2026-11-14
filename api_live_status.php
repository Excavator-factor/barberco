<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helper.php';

$today = date("Y-m-d");

// Antrean yang sedang dicukur (proses) hari ini
$nowServingRes = mysqli_query(
    $conn,
    "SELECT a.no_antrian, l.nama_layanan, COALESCE(NULLIF(b.nama, ''), 'Barber tersedia') AS nama_barber
     FROM antrian a
     LEFT JOIN barber b ON b.id = a.barber_id
     LEFT JOIN layanan l ON l.id = a.layanan_id
     WHERE (a.tanggal = '$today' OR a.tanggal = CURDATE()) AND a.status_antrian = 'proses'
     ORDER BY a.no_antrian ASC
     LIMIT 1"
);
$nowServing = $nowServingRes ? mysqli_fetch_assoc($nowServingRes) : null;

// Antrean berikutnya yang sedang menunggu hari ini
$nextQueueRes = mysqli_query(
    $conn,
    "SELECT a.no_antrian, l.nama_layanan, COALESCE(NULLIF(b.nama, ''), 'Barber tersedia') AS nama_barber
     FROM antrian a
     LEFT JOIN barber b ON b.id = a.barber_id
     LEFT JOIN layanan l ON l.id = a.layanan_id
     WHERE (a.tanggal = '$today' OR a.tanggal = CURDATE()) AND a.status_antrian = 'menunggu'
     ORDER BY a.no_antrian ASC
     LIMIT 1"
);
$nextQueue = $nextQueueRes ? mysqli_fetch_assoc($nextQueueRes) : null;

// Total antrean menunggu hari ini
$wRes = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM antrian WHERE (tanggal = '$today' OR tanggal = CURDATE()) AND status_antrian = 'menunggu'"
);
$waitingCount = $wRes ? (int)mysqli_fetch_assoc($wRes)['total'] : 0;

if ($waitingCount > 0) {
    $estimatedWait = ($waitingCount * 20) . " Min";
} elseif ($nowServing) {
    $estimatedWait = "15 Min";
} else {
    $estimatedWait = "Langsung Siap";
}

$nowServingNum = $nowServing ? str_pad((string)$nowServing['no_antrian'], 3, '0', STR_PAD_LEFT) : '---';
$nowServingDesc = $nowServing ? ($nowServing['nama_layanan'] . ' · ' . $nowServing['nama_barber']) : 'Kursi Kosong · Siap Melayani';
$nextQueueNum = $nextQueue ? ('#' . str_pad((string)$nextQueue['no_antrian'], 3, '0', STR_PAD_LEFT)) : '---';

echo json_encode([
    'success' => true,
    'now_serving' => $nowServingNum,
    'now_serving_desc' => $nowServingDesc,
    'next_queue' => $nextQueueNum,
    'waiting_count' => $waitingCount . ' Orang',
    'estimated_wait' => $estimatedWait
]);
