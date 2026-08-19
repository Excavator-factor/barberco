<?php
session_start();
if (
    !isset($_SESSION["role"]) ||
    strtolower($_SESSION["role"]) !== "pelanggan"
) {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../config/database.php";
require_once "../config/helper.php";

$user_id =
    $_SESSION["id_user"] ?? ($_SESSION["user_id"] ?? ($_SESSION["id"] ?? null));
$username = $_SESSION["username"] ?? ($_SESSION["nama"] ?? "Pelanggan");


$col_l_harga = getExistingCol($conn, "layanan", ["harga", "price"]);
$col_l_durasi = getExistingCol($conn, "layanan", [
    "durasi",
    "duration",
    "estimasi_durasi",
]);

$pk_barber = getExistingCol($conn, "barber", ["id_barber", "barber_id", "id"]);
$col_b_nama = getExistingCol($conn, "barber", ["nama", "nama_barber", "name"]);

$active_queue = null;
if (!empty($col_a_user)) {
    $select_fields = ["a.*"];
    $joins = [];

    if (!empty($col_a_barber) && !empty($pk_barber) && !empty($col_b_nama)) {
        $select_fields[] = "b.`$col_b_nama` as nama_barber";
        $joins[] = "LEFT JOIN barber b ON a.`$col_a_barber` = b.`$pk_barber`";
    }
    if (!empty($col_a_layanan) && !empty($pk_layanan) && !empty($col_l_nama)) {
        $select_fields[] = "l.`$col_l_nama` as nama_layanan";
        if (!empty($col_l_harga)) {
            $select_fields[] = "l.`$col_l_harga` as harga_layanan";
        }
        $joins[] = "LEFT JOIN layanan l ON a.`$col_a_layanan` = l.`$pk_layanan`";
    }

    $sql_active =
        "SELECT " .
        implode(", ", $select_fields) .
        " FROM antrian a " .
        implode(" ", $joins) .
        " WHERE a.`$col_a_user` = '$user_id' " .
        (!empty($col_a_status)
            ? "AND LOWER(a.`$col_a_status`) IN ('menunggu', 'proses', 'diproses', 'ready')"
            : "") .
        " " .
        (!empty($pk_antrian) ? "ORDER BY a.`$pk_antrian` DESC" : "") .
        " LIMIT 1";
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

    $sql_history =
        "SELECT " .
        implode(", ", $select_fields) .
        " FROM antrian a " .
        implode(" ", $joins) .
        " WHERE a.`$col_a_user` = '$user_id' " .
        (!empty($pk_antrian) ? "ORDER BY a.`$pk_antrian` DESC" : "");
    $res_hist = @mysqli_query($conn, $sql_history);
    if ($res_hist) {
        while ($r = mysqli_fetch_assoc($res_hist)) {
            $history_queues[] = $r;
        }
    }
}

$status_msg = $_GET["status"] ?? "";
