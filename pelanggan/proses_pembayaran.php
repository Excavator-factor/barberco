<?php
include '../config/database.php';
include '../config/helper.php';
check_login('pelanggan');

$pelanggan_id = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$antrian_id = isset($_POST['antrian_id']) ? (int) $_POST['antrian_id'] : 0;
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate antrian exists for this customer
    $check = mysqli_query($conn, "SELECT a.id, l.harga FROM antrian a JOIN layanan l ON a.layanan_id = l.id WHERE a.id = '$antrian_id' AND a.pelanggan_id = '$pelanggan_id' LIMIT 1");
    $data = mysqli_fetch_assoc($check);
    
    if (!$data) {
        header('Location: dashboard.php?status=error&msg=' . urlencode("Antrean tidak valid."));
        exit();
    }

    $metode = isset($_POST['metode_pembayaran']) ? mysqli_real_escape_string($conn, $_POST['metode_pembayaran']) : 'Tunai';
    $nama_file_bukti = null;

    if (!in_array($metode, ['QRIS', 'Transfer Bank', 'Tunai'], true)) {
        $error_msg = 'Metode pembayaran tidak valid.';
    }

    if ($error_msg === '' && $metode !== 'Tunai' && (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK)) {
        $error_msg = 'Unggah bukti pembayaran untuk QRIS atau transfer bank.';
    }

    if ($error_msg === '' && $metode !== 'Tunai' && isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['bukti_pembayaran']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if ($_FILES['bukti_pembayaran']['size'] > 2 * 1024 * 1024) {
            $error_msg = 'Ukuran bukti pembayaran maksimal 2MB.';
        } elseif (!in_array($ext, $allowed, true)) {
            $error_msg = 'Format bukti pembayaran harus JPG, PNG, atau PDF.';
        } elseif (is_uploaded_file($tmp_name)) {
            $folder_upload = '../uploads/bukti/';
            if (!file_exists($folder_upload)) {
                mkdir($folder_upload, 0755, true);
            }

            $nama_file_bukti = 'BUKTI_' . $antrian_id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (!move_uploaded_file($tmp_name, $folder_upload . $nama_file_bukti)) {
                $error_msg = 'Bukti pembayaran gagal disimpan. Silakan coba lagi.';
            }
        } else {
            $error_msg = 'Berkas bukti pembayaran tidak valid.';
        }
    }

    if ($error_msg === '') {
        $status_pembayaran = 'lunas';
        $harga = (int) $data['harga'];
        $existing = mysqli_query($conn, "SELECT id FROM transaksi WHERE antrian_id = '$antrian_id' LIMIT 1");
        $existingRow = $existing ? mysqli_fetch_assoc($existing) : null;

        if ($existingRow) {
            $transactionId = (int) $existingRow['id'];
            $buktiSql = $nama_file_bukti ? ", bukti_pembayaran = '" . mysqli_real_escape_string($conn, $nama_file_bukti) . "'" : '';
            $paymentQuery = "UPDATE transaksi SET total_harga = '$harga', metode_pembayaran = '$metode', status_pembayaran = '$status_pembayaran', waktu_bayar = NOW(6)$buktiSql WHERE id = '$transactionId'";
        } else {
            $buktiSql = $nama_file_bukti ? "'" . mysqli_real_escape_string($conn, $nama_file_bukti) . "'" : 'NULL';
            $paymentQuery = "INSERT INTO transaksi (antrian_id, total_harga, metode_pembayaran, bukti_pembayaran, status_pembayaran, waktu_bayar) VALUES ('$antrian_id', '$harga', '$metode', $buktiSql, '$status_pembayaran', NOW(6))";
        }

        if (mysqli_query($conn, $paymentQuery)) {
            $transactionId = $existingRow ? (int) $existingRow['id'] : (int) mysqli_insert_id($conn);
            header('Location: struk.php?id=' . $transactionId);
            exit();
        }

        $error_msg = 'Gagal memproses pembayaran: ' . mysqli_error($conn);
    }
    
    // Redirect with error msg if failing
    header('Location: dashboard.php?status=error&msg=' . urlencode($error_msg));
    exit();
}
header('Location: dashboard.php');
exit();
