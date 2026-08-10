<?php
include '../config/database.php';
include '../config/helper.php';
check_login('admin');

require_once __DIR__ . '/_bootstrap.php';
admin_ensure_layanan_image_column($conn);

$serviceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
if ($serviceId > 0) {
    $cekSql = "SELECT COUNT(*) as jml FROM antrian WHERE layanan_id = ?";
    $stmtCek = mysqli_prepare($conn, $cekSql);
    mysqli_stmt_bind_param($stmtCek, 'i', $serviceId);
    mysqli_stmt_execute($stmtCek);
    $cekRes = mysqli_stmt_get_result($stmtCek);
    $cekRow = $cekRes ? mysqli_fetch_assoc($cekRes) : null;
    mysqli_stmt_close($stmtCek);
    
    if ($cekRow && $cekRow['jml'] > 0) {
        $msg = urlencode("Layanan tidak dapat dihapus karena masih terkait dengan data antrian atau transaksi.");
        header("Location: layanan.php?error=$msg");
        exit;
    }

    $stmt = mysqli_prepare($conn, 'SELECT gambar FROM layanan WHERE id = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $serviceId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $service = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);

        if (!empty($service['gambar'])) {
            $imageFile = __DIR__ . '/../' . ltrim((string) $service['gambar'], '/');
            if (is_file($imageFile)) {
                @unlink($imageFile);
            }
        }
    }

    try {
        $stmt = mysqli_prepare($conn, 'DELETE FROM layanan WHERE id = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $serviceId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        $msg = urlencode("Layanan berhasil dihapus.");
        header("Location: layanan.php?success=$msg");
        exit;
    } catch (mysqli_sql_exception $e) {
        $msg = urlencode("Gagal menghapus layanan: " . $e->getMessage());
        header("Location: layanan.php?error=$msg");
        exit;
    }
}

header('Location: layanan.php');
exit;
