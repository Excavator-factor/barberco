<?php
session_start();
include "../config/database.php";
include "../config/helper.php";

$action = $_GET["action"] ?? ($_POST["action"] ?? "");

if ($action === "book_queue") {
    $user_id = $_SESSION["user_id"] ?? ($_SESSION["id_user"] ?? 0);
    if ($user_id <= 0) {
        header("Location: ../auth/login.php");
        exit();
    }

    // Support dynamic database columns based on original logic
    require_once "../pelanggan/_bootstrap.php";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $layanan_id = isset($_POST["id_layanan"])
            ? (int) $_POST["id_layanan"]
            : 0;
        $barber_input = isset($_POST["id_barber"])
            ? trim((string) $_POST["id_barber"])
            : "";
        $barber_id_sql = "NULL";
        $error_msg = "";

        if ($layanan_id <= 0) {
            $error_msg = "Harap pilih layanan terlebih dahulu.";
        }

        if (
            $error_msg === "" &&
            $barber_input !== "" &&
            $barber_input !== "0"
        ) {
            $barber_id = (int) $barber_input;
            $barberCheck = mysqli_prepare(
                $conn,
                "SELECT id FROM barber WHERE id = ? AND LOWER(status) = 'aktif' LIMIT 1",
            );
            mysqli_stmt_bind_param($barberCheck, "i", $barber_id);
            mysqli_stmt_execute($barberCheck);
            $selectedBarber = mysqli_fetch_assoc(
                mysqli_stmt_get_result($barberCheck),
            );
            mysqli_stmt_close($barberCheck);

            if ($selectedBarber) {
                $barber_id_sql = (string) $barber_id;
            } else {
                $error_msg = "Barber yang dipilih sedang tidak aktif.";
            }
        }

        if ($error_msg === "") {
            $today = date("Y-m-d");
            $q_no = mysqli_query(
                $conn,
                "SELECT MAX(`$col_a_no`) AS max_no FROM antrian WHERE `$col_a_tgl` = '$today'",
            );
            $data_no = mysqli_fetch_assoc($q_no);
            $next_no = ($data_no["max_no"] ?? 0) + 1;

            $query = "INSERT INTO antrian (`$col_a_user`, `$col_a_layanan`, `$col_a_barber`, `$col_a_no`, `$col_a_status`, `$col_a_tgl`)
                      VALUES ('$user_id', '$layanan_id', $barber_id_sql, '$next_no', 'menunggu', '$today')";

            if (mysqli_query($conn, $query)) {
                $newAntrianId = mysqli_insert_id($conn);

                // Kirim Notifikasi WhatsApp Otomatis
                require_once __DIR__ . "/whatsapp.php";
                $custRes = mysqli_query($conn, "SELECT nama, username, no_hp FROM users WHERE id_user = '$user_id' LIMIT 1");
                $custRow = $custRes ? mysqli_fetch_assoc($custRes) : null;
                $custName = $custRow['nama'] ?? ($custRow['username'] ?? 'Pelanggan');
                $custPhone = $custRow['no_hp'] ?? '';

                $layRes = mysqli_query($conn, "SELECT nama_layanan, harga FROM layanan WHERE id = '$layanan_id' LIMIT 1");
                $layRow = $layRes ? mysqli_fetch_assoc($layRes) : null;
                $serviceName = $layRow['nama_layanan'] ?? 'Layanan Grooming';
                $servicePrice = $layRow['harga'] ?? 0;

                $barberName = 'Kapster Bertugas';
                if ($barber_id_sql !== "NULL") {
                    $barberRes = mysqli_query($conn, "SELECT nama FROM barber WHERE id = '$barber_input' LIMIT 1");
                    if ($barberRes && ($bRow = mysqli_fetch_assoc($barberRes))) {
                        $barberName = $bRow['nama'];
                    }
                }

                $waData = [
                    'nama_pelanggan' => $custName,
                    'no_hp_pelanggan' => $custPhone,
                    'no_antrian' => $next_no,
                    'nama_layanan' => $serviceName,
                    'harga' => $servicePrice,
                    'nama_barber' => $barberName,
                    'tanggal' => date('d-m-Y')
                ];
                @kirim_notifikasi_wa('booking_pelanggan', $waData);
                @kirim_notifikasi_wa('booking_admin', $waData);

                header(
                    "Location: ../pelanggan/dashboard.php?open_payment_modal=1",
                );
                exit();
            }

            $error_msg = "Gagal mengambil antrean: " . mysqli_error($conn);
        }

        header(
            "Location: ../pelanggan/dashboard.php?status=error&msg=" .
                urlencode($error_msg),
        );
        exit();
    }
    header("Location: ../pelanggan/dashboard.php");
    exit();
}

if ($action === "start" || $action === "finish") {
    $userId = (int) ($_SESSION["user_id"] ?? ($_SESSION["id_user"] ?? 0));
    $barberId = 0;

    if ($userId > 0) {
        $profile = mysqli_prepare(
            $conn,
            "SELECT id FROM barber WHERE user_id = ? LIMIT 1",
        );
        mysqli_stmt_bind_param($profile, "i", $userId);
        mysqli_stmt_execute($profile);
        $res = mysqli_stmt_get_result($profile);
        if ($res && ($row = mysqli_fetch_assoc($res))) {
            $barberId = (int) $row["id"];
        }
        mysqli_stmt_close($profile);
    }

    $queueId = filter_input(INPUT_POST, "queue_id", FILTER_VALIDATE_INT);

    if ($action === "start" && $queueId) {
        if ($barberId <= 0) {
            $_SESSION["barber_flash"] = [
                "text" => "Profil barber belum tersedia.",
                "type" => "error",
            ];
        } else {
            $activeCheck = mysqli_prepare(
                $conn,
                "SELECT id FROM antrian WHERE barber_id = ? AND status_antrian = 'proses' LIMIT 1",
            );
            mysqli_stmt_bind_param($activeCheck, "i", $barberId);
            mysqli_stmt_execute($activeCheck);
            $hasActiveQueue =
                mysqli_num_rows(mysqli_stmt_get_result($activeCheck)) > 0;
            mysqli_stmt_close($activeCheck);

            if ($hasActiveQueue) {
                $_SESSION["barber_flash"] = [
                    "text" => "Selesaikan antrean aktif terlebih dahulu.",
                    "type" => "error",
                ];
            } else {
                $stmt = mysqli_prepare(
                    $conn,
                    "UPDATE antrian SET status_antrian = 'proses', barber_id = ? WHERE id = ? AND status_antrian = 'menunggu' AND (barber_id IS NULL OR barber_id = ?)",
                );
                mysqli_stmt_bind_param(
                    $stmt,
                    "iii",
                    $barberId,
                    $queueId,
                    $barberId,
                );
                mysqli_stmt_execute($stmt);
                $changed = mysqli_stmt_affected_rows($stmt);
                mysqli_stmt_close($stmt);
                if ($changed) {
                    $_SESSION["barber_flash"] = [
                        "text" => "Antrean berhasil dimulai!",
                        "type" => "success",
                    ];

                    // Kirim Notifikasi WhatsApp Panggilan ke Pelanggan
                    require_once __DIR__ . "/whatsapp.php";
                    $detailQ = mysqli_query($conn, "SELECT a.no_antrian, u.nama, u.username, u.no_hp, l.nama_layanan, l.harga, b.nama as barber_name 
                        FROM antrian a 
                        JOIN users u ON a.pelanggan_id = u.id_user 
                        JOIN layanan l ON a.layanan_id = l.id 
                        LEFT JOIN barber b ON a.barber_id = b.id 
                        WHERE a.id = $queueId LIMIT 1");
                    if ($detailQ && ($qRow = mysqli_fetch_assoc($detailQ))) {
                        $waData = [
                            'nama_pelanggan' => $qRow['nama'] ?: $qRow['username'],
                            'no_hp_pelanggan' => $qRow['no_hp'],
                            'no_antrian' => $qRow['no_antrian'],
                            'nama_layanan' => $qRow['nama_layanan'],
                            'harga' => $qRow['harga'],
                            'nama_barber' => $qRow['barber_name'] ?: 'Kapster Bertugas',
                        ];
                        @kirim_notifikasi_wa('panggilan_giliran', $waData);
                    }
                } else {
                    $_SESSION["barber_flash"] = [
                        "text" =>
                            "Antrean tidak dapat dimulai. Mungkin sudah diambil barber lain.",
                        "type" => "error",
                    ];
                }
            }
        }
    } elseif ($action === "finish" && $queueId) {
        if ($barberId <= 0) {
            $_SESSION["barber_flash"] = [
                "text" => "Profil barber belum tersedia.",
                "type" => "error",
            ];
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE antrian SET status_antrian = 'selesai' WHERE id = ? AND status_antrian = 'proses' AND barber_id = ?",
            );
            mysqli_stmt_bind_param($stmt, "ii", $queueId, $barberId);
            mysqli_stmt_execute($stmt);
            $changed = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($changed) {
                $chkTrx = mysqli_prepare(
                    $conn,
                    "SELECT id FROM transaksi WHERE antrian_id = ? LIMIT 1",
                );
                mysqli_stmt_bind_param($chkTrx, "i", $queueId);
                mysqli_stmt_execute($chkTrx);
                $resTrx = mysqli_stmt_get_result($chkTrx);
                $trxRow = mysqli_fetch_assoc($resTrx);
                $transaksi_id = $trxRow ? (int) $trxRow["id"] : 0;
                mysqli_stmt_close($chkTrx);

                if (!$transaksi_id) {
                    $layananRes = mysqli_query(
                        $conn,
                        "SELECT l.harga FROM antrian a JOIN layanan l ON l.id = a.layanan_id WHERE a.id = $queueId LIMIT 1",
                    );
                    if (
                        $layananRes &&
                        ($lRow = mysqli_fetch_assoc($layananRes))
                    ) {
                        $price = (int) $lRow["harga"];
                        $insTrx = mysqli_prepare(
                            $conn,
                            "INSERT INTO transaksi (antrian_id, total_harga, metode_pembayaran, status_pembayaran, waktu_bayar) VALUES (?, ?, 'cash', 'lunas', NOW(6))",
                        );
                        mysqli_stmt_bind_param($insTrx, "ii", $queueId, $price);
                        mysqli_stmt_execute($insTrx);
                        $transaksi_id = mysqli_insert_id($conn);
                        mysqli_stmt_close($insTrx);
                    }
                }
                $_SESSION["barber_flash"] = [
                    "text" =>
                        "Antrean telah diselesaikan. Sesi berhasil ditutup!",
                    "type" => "success",
                ];

                // Kirim Notifikasi WhatsApp Selesai & Struk ke Pelanggan
                require_once __DIR__ . "/whatsapp.php";
                $detailQ = mysqli_query($conn, "SELECT a.no_antrian, u.nama, u.username, u.no_hp, l.nama_layanan, l.harga, b.nama as barber_name, t.total_harga, t.metode_pembayaran 
                    FROM antrian a 
                    JOIN users u ON a.pelanggan_id = u.id_user 
                    JOIN layanan l ON a.layanan_id = l.id 
                    LEFT JOIN barber b ON a.barber_id = b.id 
                    LEFT JOIN transaksi t ON t.antrian_id = a.id 
                    WHERE a.id = $queueId LIMIT 1");
                if ($detailQ && ($qRow = mysqli_fetch_assoc($detailQ))) {
                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $urlStruk = $transaksi_id > 0 ? "{$protocol}://{$host}/pelanggan/struk.php?id={$transaksi_id}" : "";

                    $waData = [
                        'nama_pelanggan' => $qRow['nama'] ?: $qRow['username'],
                        'no_hp_pelanggan' => $qRow['no_hp'],
                        'no_antrian' => $qRow['no_antrian'],
                        'nama_layanan' => $qRow['nama_layanan'],
                        'harga' => $qRow['total_harga'] ?: $qRow['harga'],
                        'nama_barber' => $qRow['barber_name'] ?: 'Kapster Bertugas',
                        'metode_pembayaran' => $qRow['metode_pembayaran'] ?: 'Tunai',
                        'url_struk' => $urlStruk
                    ];
                    @kirim_notifikasi_wa('selesai_antrean', $waData);
                }

                if ($transaksi_id > 0) {
                    header(
                        "Location: ../barber/struk.php?id=" .
                            $transaksi_id .
                            "&auto_print=1",
                    );
                    exit();
                }
            } else {
                $_SESSION["barber_flash"] = [
                    "text" => "Antrean tidak dapat diselesaikan.",
                    "type" => "error",
                ];
            }
        }
    }

    $redirect = $_SERVER["HTTP_REFERER"] ?? "../barber/dashboard.php";
    header("Location: $redirect");
    exit();
}

if ($action === "cancel") {
    // Handling delete / cancel by admin
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if ($id && $_SESSION["role"] === "admin") {
        $del = mysqli_prepare($conn, "DELETE FROM antrian WHERE id = ?");
        mysqli_stmt_bind_param($del, "i", $id);
        if (mysqli_stmt_execute($del)) {
            $_SESSION["modalSuccess"] = "Antrean berhasil dibatalkan/dihapus.";
        } else {
            $_SESSION["modalError"] = "Gagal membatalkan antrean.";
        }
        mysqli_stmt_close($del);
    }
    header("Location: ../admin/antrean.php");
    exit();
}

header("Location: ../index.php");
exit();
