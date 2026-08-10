<?php
session_start();
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'pelanggan') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$user_id  = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$username = $_SESSION['username'] ?? $_SESSION['nama'] ?? 'Pelanggan';

function getExistingCol($conn, $table, $candidates) {
    $res = @mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
    if (!$res) return null;
    $cols = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $cols[] = strtolower($r['Field']);
    }
    foreach ($candidates as $cand) {
        if (in_array(strtolower($cand), $cols)) return $cand;
    }
    return null;
}

$pk_antrian    = getExistingCol($conn, 'antrian', ['id_antrian', 'id', 'antrian_id']);
$col_a_user    = getExistingCol($conn, 'antrian', ['id_pelanggan', 'id_user', 'user_id', 'pelanggan_id', 'id_customer', 'id_pemesan', 'id_klien', 'id_member', 'user']);
$col_a_barber  = getExistingCol($conn, 'antrian', ['id_barber', 'barber_id', 'id_kapster', 'id_pegawai', 'id_karyawan', 'id_staff']);
$col_a_layanan = getExistingCol($conn, 'antrian', ['id_layanan', 'layanan_id', 'id_service', 'service_id']);
$col_a_no      = getExistingCol($conn, 'antrian', ['no_antrian', 'nomor_antrian', 'queue_no', 'no_antri', 'nomor']);
$col_a_tgl     = getExistingCol($conn, 'antrian', ['tanggal', 'tgl', 'created_at', 'date', 'tgl_antrian']);
$col_a_status  = getExistingCol($conn, 'antrian', ['status', 'status_antrian', 'stts']);

$pk_users       = getExistingCol($conn, 'users', ['id_user', 'user_id', 'id']);
$col_u_name     = getExistingCol($conn, 'users', ['nama', 'username', 'nama_lengkap', 'name']);

$pk_layanan     = getExistingCol($conn, 'layanan', ['id_layanan', 'layanan_id', 'id']);
$col_l_nama     = getExistingCol($conn, 'layanan', ['nama_layanan', 'nama', 'layanan']);
$col_l_harga    = getExistingCol($conn, 'layanan', ['harga', 'price']);
$col_l_durasi   = getExistingCol($conn, 'layanan', ['durasi', 'duration', 'estimasi_durasi']);

$pk_barber      = getExistingCol($conn, 'barber', ['id_barber', 'barber_id', 'id']);
$col_b_nama     = getExistingCol($conn, 'barber', ['nama', 'nama_barber', 'name']);

$active_queue = null;
if (!empty($col_a_user)) {
    $select_fields = ["a.*"];
    $joins = [];

    if (!empty($col_a_barber) && !empty($pk_users) && !empty($col_u_name)) {
        $select_fields[] = "u.`$col_u_name` as nama_barber";
        $joins[] = "LEFT JOIN users u ON a.`$col_a_barber` = u.`$pk_users`";
    }
    if (!empty($col_a_layanan) && !empty($pk_layanan) && !empty($col_l_nama)) {
        $select_fields[] = "l.`$col_l_nama` as nama_layanan";
        if (!empty($col_l_harga)) {
            $select_fields[] = "l.`$col_l_harga` as harga_layanan";
        }
        $joins[] = "LEFT JOIN layanan l ON a.`$col_a_layanan` = l.`$pk_layanan`";
    }

    $sql_active = "SELECT " . implode(", ", $select_fields) . " FROM antrian a " . implode(" ", $joins) . " WHERE a.`$col_a_user` = '$user_id' " . (!empty($col_a_status) ? "AND LOWER(a.`$col_a_status`) IN ('menunggu', 'proses', 'diproses', 'ready')" : "") . " " . (!empty($pk_antrian) ? "ORDER BY a.`$pk_antrian` DESC" : "") . " LIMIT 1";
    $res_active = @mysqli_query($conn, $sql_active);
    if ($res_active && mysqli_num_rows($res_active) > 0) {
        $active_queue = mysqli_fetch_assoc($res_active);
    }
}

$history_queues = [];
if (!empty($col_a_user)) {
    $select_fields = ["a.*"];
    $joins = [];

    if (!empty($col_a_barber) && !empty($pk_barber) && !empty($col_b_nama)) {
        $select_fields[] = "b.`$col_b_nama` as nama_barber";
        $joins[] = "LEFT JOIN barber b ON a.`$col_a_barber` = b.`$pk_barber`";
    }
    if (!empty($col_a_layanan) && !empty($pk_layanan) && !empty($col_l_nama)) {
        $select_fields[] = "l.`$col_l_nama` as nama_layanan";
        if (!empty($col_l_durasi)) {
            $select_fields[] = "l.`$col_l_durasi` as durasi_layanan";
        }
        $joins[] = "LEFT JOIN layanan l ON a.`$col_a_layanan` = l.`$pk_layanan`";
    }
    if (!empty($pk_antrian)) {
        $select_fields[] = "t.id AS transaksi_id, t.status_pembayaran";
        $joins[] = "LEFT JOIN transaksi t ON t.antrian_id = a.`$pk_antrian`";
    }

    $sql_history = "SELECT " . implode(", ", $select_fields) . " FROM antrian a " . implode(" ", $joins) . " WHERE a.`$col_a_user` = '$user_id' " . (!empty($pk_antrian) ? "ORDER BY a.`$pk_antrian` DESC" : "");
    $res_hist = @mysqli_query($conn, $sql_history);
    if ($res_hist) {
        while ($r = mysqli_fetch_assoc($res_hist)) {
            $history_queues[] = $r;
        }
    }
}

$status_msg = $_GET['status'] ?? '';
