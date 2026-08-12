<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../config/database.php';
include_once '../config/helper.php';
check_login('barber');

$userId = (int) ($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? 0);
$barberId = 0;
$barberName = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Marcus Thorne';
$barberSpecialties = 'Taper Fades, Straight Razor, Beard Sculpting';
$barberStatus = 'active';
$message = '';
$messageType = 'success';

if ($userId > 0) {
    $profile = mysqli_prepare($conn, 'SELECT id, nama, spesialisasi, status FROM barber WHERE user_id = ? LIMIT 1');
    if ($profile) {
        mysqli_stmt_bind_param($profile, 'i', $userId);
        mysqli_stmt_execute($profile);
        $profileResult = mysqli_stmt_get_result($profile);
        if ($profileResult && ($row = mysqli_fetch_assoc($profileResult))) {
            $barberId = (int) $row['id'];
            $barberName = $row['nama'] ?: $barberName;
            $barberSpecialties = $row['spesialisasi'] ?: $barberSpecialties;
            $barberStatus = $row['status'] ?: $barberStatus;
        } else {
            // Auto-create barber profile if not existing yet
            $insProfile = mysqli_prepare($conn, 'INSERT INTO barber (user_id, nama, spesialisasi, status) VALUES (?, ?, ?, "active")');
            if ($insProfile) {
                mysqli_stmt_bind_param($insProfile, 'iss', $userId, $barberName, $barberSpecialties);
                mysqli_stmt_execute($insProfile);
                $barberId = (int) mysqli_insert_id($conn);
                mysqli_stmt_close($insProfile);
            }
        }
        mysqli_stmt_close($profile);
    }
}

function barber_flash(string $text, string $type = 'success'): void
{
    $_SESSION['barber_flash'] = ['text' => $text, 'type' => $type];
}

// Handle form POST actions globally or in page controller
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $queueId = filter_input(INPUT_POST, 'queue_id', FILTER_VALIDATE_INT);

    if ($action === 'toggle_status') {
        $newStatus = ($_POST['status'] ?? '') === 'off_duty' ? 'off_duty' : 'active';
        if ($barberId > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE barber SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'si', $newStatus, $barberId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $barberStatus = $newStatus;
            barber_flash("Status barber berhasil diperbarui ke " . strtoupper($newStatus) . ".");
        }
        $redirect = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
        header("Location: $redirect");
        exit;
    } elseif ($action === 'start' && $queueId) {
        if ($barberId <= 0) {
            barber_flash('Profil barber belum tersedia.', 'error');
        } else {
            $activeCheck = mysqli_prepare($conn, "SELECT id FROM antrian WHERE barber_id = ? AND status_antrian = 'proses' LIMIT 1");
            mysqli_stmt_bind_param($activeCheck, 'i', $barberId);
            mysqli_stmt_execute($activeCheck);
            $hasActiveQueue = mysqli_num_rows(mysqli_stmt_get_result($activeCheck)) > 0;
            mysqli_stmt_close($activeCheck);

            if ($hasActiveQueue) {
                barber_flash('Selesaikan antrean aktif terlebih dahulu.', 'error');
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE antrian SET status_antrian = 'proses', barber_id = ? WHERE id = ? AND status_antrian = 'menunggu' AND (barber_id IS NULL OR barber_id = ?)");
                mysqli_stmt_bind_param($stmt, 'iii', $barberId, $queueId, $barberId);
                mysqli_stmt_execute($stmt);
                $changed = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);
                if ($changed) {
                    barber_flash('Antrean berhasil dimulai!');
                } else {
                    barber_flash('Antrean tidak dapat dimulai. Mungkin sudah diambil barber lain.', 'error');
                }
            }
        }
        $redirect = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
        header("Location: $redirect");
        exit;
    } elseif ($action === 'finish' && $queueId) {
        if ($barberId <= 0) {
            barber_flash('Profil barber belum tersedia.', 'error');
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE antrian SET status_antrian = 'selesai' WHERE id = ? AND status_antrian = 'proses' AND barber_id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $queueId, $barberId);
            mysqli_stmt_execute($stmt);
            $changed = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($changed) {
                // Ensure a transaction record is logged
                $chkTrx = mysqli_prepare($conn, "SELECT id FROM transaksi WHERE antrian_id = ? LIMIT 1");
                mysqli_stmt_bind_param($chkTrx, 'i', $queueId);
                mysqli_stmt_execute($chkTrx);
                $resTrx = mysqli_stmt_get_result($chkTrx);
                $trxRow = mysqli_fetch_assoc($resTrx);
                $transaksi_id = $trxRow ? (int)$trxRow['id'] : 0;
                mysqli_stmt_close($chkTrx);

                if (!$transaksi_id) {
                    $layananRes = mysqli_query($conn, "SELECT l.harga FROM antrian a JOIN layanan l ON l.id = a.layanan_id WHERE a.id = $queueId LIMIT 1");
                    if ($layananRes && ($lRow = mysqli_fetch_assoc($layananRes))) {
                        $price = (int) $lRow['harga'];
                        $insTrx = mysqli_prepare($conn, "INSERT INTO transaksi (antrian_id, total_harga, metode_pembayaran, status_pembayaran, waktu_bayar) VALUES (?, ?, 'cash', 'lunas', NOW(6))");
                        mysqli_stmt_bind_param($insTrx, 'ii', $queueId, $price);
                        mysqli_stmt_execute($insTrx);
                        $transaksi_id = mysqli_insert_id($conn);
                        mysqli_stmt_close($insTrx);
                    }
                }
                barber_flash('Antrean telah diselesaikan. Sesi berhasil ditutup!');
                
                if ($transaksi_id > 0) {
                    header("Location: struk.php?id=" . $transaksi_id . "&auto_print=1");
                    exit;
                }
            } else {
                barber_flash('Antrean tidak dapat diselesaikan.', 'error');
            }
        }
        $redirect = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
        header("Location: $redirect");
        exit;
    }
}

$today = date('Y-m-d');
$activeQueue = null;
$waitingQueues = [];
$completedQueues = [];
$stats = ['waiting' => 0, 'completed' => 0];
$revenueToday = 0;

// --- Riwayat filter logic ---
$riwayatFilter = in_array($_GET['filter'] ?? '', ['today', 'week', 'month', 'year'])
    ? ($_GET['filter'])
    : 'today';

// Build WHERE clause for the history query based on filter
switch ($riwayatFilter) {
    case 'week':
        $filterCondition = "YEARWEEK(a.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
        $filterLabel = 'Minggu Ini';
        break;
    case 'month':
        $filterCondition = "MONTH(a.tanggal) = MONTH(CURDATE()) AND YEAR(a.tanggal) = YEAR(CURDATE())";
        $filterLabel = 'Bulan Ini (' . date('M Y') . ')';
        break;
    case 'year':
        $filterCondition = "YEAR(a.tanggal) = YEAR(CURDATE())";
        $filterLabel = 'Tahun ' . date('Y');
        break;
    default: // today
        $filterCondition = "a.tanggal = '$today'";
        $filterLabel = 'Hari Ini (' . date('d M Y') . ')';
        $riwayatFilter = 'today';
}

if ($barberId > 0) {
    // Active queue
    $activeSql = "SELECT a.*, COALESCE(NULLIF(u.nama, ''), u.username, 'Pelanggan') AS nama_pelanggan, l.nama_layanan, l.harga, l.durasi FROM antrian a JOIN users u ON u.id_user = a.pelanggan_id JOIN layanan l ON l.id = a.layanan_id WHERE a.status_antrian = 'proses' AND a.barber_id = ? ORDER BY a.waktu_dibuat ASC LIMIT 1";
    $stmt = mysqli_prepare($conn, $activeSql);
    mysqli_stmt_bind_param($stmt, 'i', $barberId);
    mysqli_stmt_execute($stmt);
    $activeQueue = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    mysqli_stmt_close($stmt);

    // Waiting queues
    $waitingSql = "SELECT a.*, COALESCE(NULLIF(u.nama, ''), u.username, 'Pelanggan') AS nama_pelanggan, l.nama_layanan, l.harga, l.durasi, b.nama AS nama_barber FROM antrian a JOIN users u ON u.id_user = a.pelanggan_id JOIN layanan l ON l.id = a.layanan_id LEFT JOIN barber b ON b.id = a.barber_id WHERE a.tanggal = ? AND a.status_antrian = 'menunggu' ORDER BY a.no_antrian ASC";
    $stmt = mysqli_prepare($conn, $waitingSql);
    mysqli_stmt_bind_param($stmt, 's', $today);
    mysqli_stmt_execute($stmt);
    $waitingQueues = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    // Completed queues — uses dynamic filter condition
    $historySql = "SELECT a.*, COALESCE(NULLIF(u.nama, ''), u.username, 'Pelanggan') AS nama_pelanggan, l.nama_layanan, l.harga
                   FROM antrian a
                   JOIN users u ON u.id_user = a.pelanggan_id
                   JOIN layanan l ON l.id = a.layanan_id
                   WHERE $filterCondition AND a.status_antrian = 'selesai' AND a.barber_id = $barberId
                   ORDER BY a.waktu_dibuat DESC";
    $historyResult = mysqli_query($conn, $historySql);
    $completedQueues = $historyResult ? mysqli_fetch_all($historyResult, MYSQLI_ASSOC) : [];

    $stats = ['waiting' => count($waitingQueues), 'completed' => count($completedQueues)];

    // Calculate revenue for current filter
    $revSql = "SELECT COALESCE(SUM(l.harga), 0) AS total
               FROM antrian a
               JOIN layanan l ON l.id = a.layanan_id
               WHERE $filterCondition AND a.status_antrian = 'selesai' AND a.barber_id = $barberId";
    $resRev = mysqli_fetch_assoc(mysqli_query($conn, $revSql));
    $revenueToday = (int) ($resRev['total'] ?? 0);
}

if (!empty($_SESSION['barber_flash'])) {
    $message = $_SESSION['barber_flash']['text'];
    $messageType = $_SESSION['barber_flash']['type'];
    unset($_SESSION['barber_flash']);
}
