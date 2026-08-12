<?php
include '_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $layanan_id = isset($_POST['id_layanan']) ? (int) $_POST['id_layanan'] : 0;
    $barber_input = isset($_POST['id_barber']) ? trim((string) $_POST['id_barber']) : '';
    $barber_id_sql = 'NULL';
    $error_msg = '';

    if ($layanan_id <= 0) {
        $error_msg = 'Harap pilih layanan terlebih dahulu.';
    }

    if ($error_msg === '' && $barber_input !== '' && $barber_input !== '0') {
        $barber_id = (int) $barber_input;
        $barberCheck = mysqli_prepare($conn, "SELECT `$pk_barber` FROM barber WHERE `$pk_barber` = ? AND LOWER(status) = 'aktif' LIMIT 1");
        mysqli_stmt_bind_param($barberCheck, 'i', $barber_id);
        mysqli_stmt_execute($barberCheck);
        $selectedBarber = mysqli_fetch_assoc(mysqli_stmt_get_result($barberCheck));
        mysqli_stmt_close($barberCheck);

        if ($selectedBarber) {
            $barber_id_sql = (string) $barber_id;
        } else {
            $error_msg = 'Barber yang dipilih sedang tidak aktif.';
        }
    }

    if ($error_msg === '') {
        $today = date('Y-m-d');
        $q_no = mysqli_query($conn, "SELECT MAX(`$col_a_no`) AS max_no FROM antrian WHERE `$col_a_tgl` = '$today'");
        $data_no = mysqli_fetch_assoc($q_no);
        $next_no = ($data_no['max_no'] ?? 0) + 1;

        // Use the dynamically detected column names from _bootstrap.php
        $query = "INSERT INTO antrian (`$col_a_user`, `$col_a_layanan`, `$col_a_barber`, `$col_a_no`, `$col_a_status`, `$col_a_tgl`)
                  VALUES ('$user_id', '$layanan_id', $barber_id_sql, '$next_no', 'menunggu', '$today')";

        if (mysqli_query($conn, $query)) {
            header('Location: dashboard.php?status=success');
            exit();
        }

        $error_msg = 'Gagal mengambil antrean: ' . mysqli_error($conn);
    }
    
    // Redirect with error
    header('Location: dashboard.php?status=error&msg=' . urlencode($error_msg));
    exit();
}
header('Location: dashboard.php');
exit;
