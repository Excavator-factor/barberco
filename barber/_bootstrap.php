<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include_once "../config/database.php";
include_once "../config/helper.php";
check_login("barber");

$userId = (int) ($_SESSION["user_id"] ?? ($_SESSION["id_user"] ?? 0));
$barberId = 0;
$barberName = $_SESSION["nama"] ?? ($_SESSION["username"] ?? "Marcus Thorne");
$barberSpecialties = "Taper Fades, Straight Razor, Beard Sculpting";
$barberStatus = "active";
$message = "";
$messageType = "success";

if ($userId > 0) {
    $profile = mysqli_prepare(
        $conn,
        "SELECT id, nama, spesialisasi, status FROM barber WHERE user_id = ? LIMIT 1",
    );
    if ($profile) {
        mysqli_stmt_bind_param($profile, "i", $userId);
        mysqli_stmt_execute($profile);
        $profileResult = mysqli_stmt_get_result($profile);
        if ($profileResult && ($row = mysqli_fetch_assoc($profileResult))) {
            $barberId = (int) $row["id"];
            $barberName = $row["nama"] ?: $barberName;
            $barberSpecialties = $row["spesialisasi"] ?: $barberSpecialties;
            $barberStatus = $row["status"] ?: $barberStatus;
        } else {
            // Auto-create barber profile if not existing yet
            $insProfile = mysqli_prepare(
                $conn,
                'INSERT INTO barber (user_id, nama, spesialisasi, status) VALUES (?, ?, ?, "active")',
            );
            if ($insProfile) {
                mysqli_stmt_bind_param(
                    $insProfile,
                    "iss",
                    $userId,
                    $barberName,
                    $barberSpecialties,
                );
                mysqli_stmt_execute($insProfile);
                $barberId = (int) mysqli_insert_id($conn);
                mysqli_stmt_close($insProfile);
            }
        }
        mysqli_stmt_close($profile);
    }
}

function barber_flash(string $text, string $type = "success"): void
{
    $_SESSION["barber_flash"] = ["text" => $text, "type" => $type];
}

$today = date("Y-m-d");
$activeQueue = null;
$waitingQueues = [];
$completedQueues = [];
$stats = ["waiting" => 0, "completed" => 0];
$revenueToday = 0;

// --- Riwayat filter logic ---
$riwayatFilter = in_array($_GET["filter"] ?? "", [
    "today",
    "week",
    "month",
    "year",
])
    ? $_GET["filter"]
    : "today";

// Build WHERE clause for the history query based on filter
switch ($riwayatFilter) {
    case "week":
        $filterCondition = "YEARWEEK(a.tanggal, 1) = YEARWEEK(CURDATE(), 1)";
        $filterLabel = "Minggu Ini";
        break;
    case "month":
        $filterCondition =
            "MONTH(a.tanggal) = MONTH(CURDATE()) AND YEAR(a.tanggal) = YEAR(CURDATE())";
        $filterLabel = "Bulan Ini (" . date("M Y") . ")";
        break;
    case "year":
        $filterCondition = "YEAR(a.tanggal) = YEAR(CURDATE())";
        $filterLabel = "Tahun " . date("Y");
        break;
    default:
        // today
        $filterCondition = "a.tanggal = '$today'";
        $filterLabel = "Hari Ini (" . date("d M Y") . ")";
        $riwayatFilter = "today";
}

if ($barberId > 0) {
    // Active queue
    $activeSql =
        "SELECT a.*, COALESCE(NULLIF(u.nama, ''), u.username, 'Pelanggan') AS nama_pelanggan, l.nama_layanan, l.harga, l.durasi FROM antrian a JOIN users u ON u.id_user = a.pelanggan_id JOIN layanan l ON l.id = a.layanan_id WHERE a.status_antrian = 'proses' AND a.barber_id = ? ORDER BY a.waktu_dibuat ASC LIMIT 1";
    $stmt = mysqli_prepare($conn, $activeSql);
    mysqli_stmt_bind_param($stmt, "i", $barberId);
    mysqli_stmt_execute($stmt);
    $activeQueue = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: null;
    mysqli_stmt_close($stmt);

    // Waiting queues
    $waitingSql =
        "SELECT a.*, COALESCE(NULLIF(u.nama, ''), u.username, 'Pelanggan') AS nama_pelanggan, l.nama_layanan, l.harga, l.durasi, b.nama AS nama_barber FROM antrian a JOIN users u ON u.id_user = a.pelanggan_id JOIN layanan l ON l.id = a.layanan_id LEFT JOIN barber b ON b.id = a.barber_id WHERE a.tanggal = ? AND a.status_antrian = 'menunggu' ORDER BY a.no_antrian ASC";
    $stmt = mysqli_prepare($conn, $waitingSql);
    mysqli_stmt_bind_param($stmt, "s", $today);
    mysqli_stmt_execute($stmt);
    $waitingQueues = mysqli_fetch_all(
        mysqli_stmt_get_result($stmt),
        MYSQLI_ASSOC,
    );
    mysqli_stmt_close($stmt);

    // Completed queues — uses dynamic filter condition
    $historySql = "SELECT a.*, COALESCE(NULLIF(u.nama, ''), u.username, 'Pelanggan') AS nama_pelanggan, l.nama_layanan, l.harga
                   FROM antrian a
                   JOIN users u ON u.id_user = a.pelanggan_id
                   JOIN layanan l ON l.id = a.layanan_id
                   WHERE $filterCondition AND a.status_antrian = 'selesai' AND a.barber_id = $barberId
                   ORDER BY a.waktu_dibuat DESC";
    $historyResult = mysqli_query($conn, $historySql);
    $completedQueues = $historyResult
        ? mysqli_fetch_all($historyResult, MYSQLI_ASSOC)
        : [];

    $stats = [
        "waiting" => count($waitingQueues),
        "completed" => count($completedQueues),
    ];

    // Calculate revenue for current filter
    $revSql = "SELECT COALESCE(SUM(l.harga), 0) AS total
               FROM antrian a
               JOIN layanan l ON l.id = a.layanan_id
               WHERE $filterCondition AND a.status_antrian = 'selesai' AND a.barber_id = $barberId";
    $resRev = mysqli_fetch_assoc(mysqli_query($conn, $revSql));
    $revenueToday = (int) ($resRev["total"] ?? 0);
}

if (!empty($_SESSION["barber_flash"])) {
    $message = $_SESSION["barber_flash"]["text"];
    $messageType = $_SESSION["barber_flash"]["type"];
    unset($_SESSION["barber_flash"]);
}
