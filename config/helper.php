<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_login($required_role = null)
{
    global $conn;

    // --- START AUTO LOGIN LOGIC ---
    if (
        (!isset($_SESSION["role"]) || empty($_SESSION["role"])) &&
        isset($_COOKIE["remember_me"])
    ) {
        if (isset($conn) && $conn) {
            $token = mysqli_real_escape_string($conn, $_COOKIE["remember_me"]);
            // Pastikan kolom token sudah pernah dibuat
            @mysqli_query(
                $conn,
                "SHOW COLUMNS FROM users LIKE 'remember_token'",
            ); // dummy trigger or error supression if not exists

            $res = @mysqli_query(
                $conn,
                "SELECT * FROM users WHERE remember_token = '$token' AND remember_expires > NOW() AND is_deleted = 0 LIMIT 1",
            );
            if ($res && mysqli_num_rows($res) > 0) {
                // Token valid, pulihkan sesi pengguna
                $user = mysqli_fetch_assoc($res);
                $uid =
                    $user["id"] ??
                    ($user["id_user"] ?? ($user["user_id"] ?? 1));
                $_SESSION["user_id"] = $uid;
                $_SESSION["username"] =
                    $user["username"] ?? ($user["nama"] ?? "User");
                $_SESSION["role"] = strtolower(
                    trim($user["role"] ?? "pelanggan"),
                );
                if (!empty($user["avatar"])) {
                    $_SESSION["avatar"] = $user["avatar"];
                }

                // Token Rotation: Ganti token lama dengan token baru untuk mencegah hijacking
                $newToken = bin2hex(random_bytes(32));
                $expiry = date("Y-m-d H:i:s", time() + 86400 * 30);

                @mysqli_query(
                    $conn,
                    "UPDATE users SET remember_token = '$newToken', remember_expires = '$expiry' WHERE id_user = $uid",
                );

                setcookie("remember_me", $newToken, [
                    "expires" => time() + 86400 * 30,
                    "path" => "/",
                    "secure" =>
                        isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on",
                    "httponly" => true,
                    "samesite" => "Lax",
                ]);
            }
        }
    }
    // --- END AUTO LOGIN LOGIC ---

    // Cek apakah user sudah login
    if (!isset($_SESSION["role"]) || empty($_SESSION["role"])) {
        header("Location: ../auth/login.php");
        exit();
    }

    // Jika halaman membutuhkan role spesifik
    if ($required_role !== null) {
        $user_role = strtolower(trim($_SESSION["role"]));
        $req_role = strtolower(trim($required_role));

        if ($user_role !== $req_role) {
            // Jika salah role, lempar ke dashboard yang sesuai dengan rolenya masing-masing
            if ($user_role === "admin") {
                header("Location: ../admin/dashboard.php");
            } elseif ($user_role === "barber") {
                header("Location: ../barber/dashboard.php");
            } else {
                header("Location: ../pelanggan/dashboard.php");
            }
            exit();
        }
    }
}

if (isset($_SESSION["user_id"]) && isset($GLOBALS["conn"])) {
    $uid = (int) $_SESSION["user_id"];
    @mysqli_query(
        $GLOBALS["conn"],
        "UPDATE users SET terakhir_aktivitas = NOW() WHERE id_user = $uid",
    );
}

function getExistingCol($conn, $table, $candidates)
{
    $res = @mysqli_query($conn, "SHOW COLUMNS FROM `$table`");
    if (!$res) {
        return null;
    }
    $cols = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $cols[] = strtolower($r["Field"]);
    }
    foreach ($candidates as $cand) {
        if (in_array(strtolower($cand), $cols)) {
            return $cand;
        }
    }
    return null;
}

$pk_antrian = getExistingCol($conn, "antrian", [
    "id_antrian",
    "id",
    "antrian_id",
]);
$col_a_user = getExistingCol($conn, "antrian", [
    "id_pelanggan",
    "id_user",
    "user_id",
    "pelanggan_id",
    "id_customer",
    "id_pemesan",
    "id_klien",
    "id_member",
    "user",
]);
$col_a_barber = getExistingCol($conn, "antrian", [
    "id_barber",
    "barber_id",
    "id_kapster",
    "id_pegawai",
    "id_karyawan",
    "id_staff",
]);
$col_a_layanan = getExistingCol($conn, "antrian", [
    "id_layanan",
    "layanan_id",
    "id_service",
    "service_id",
]);
$col_a_no = getExistingCol($conn, "antrian", [
    "no_antrian",
    "nomor_antrian",
    "queue_no",
    "no_antri",
    "nomor",
]);
$col_a_tgl = getExistingCol($conn, "antrian", [
    "tanggal",
    "tgl",
    "created_at",
    "date",
    "tgl_antrian",
]);
$col_a_status = getExistingCol($conn, "antrian", [
    "status",
    "status_antrian",
    "stts",
]);

$pk_users = getExistingCol($conn, "users", ["id_user", "user_id", "id"]);
$col_u_name = getExistingCol($conn, "users", [
    "nama",
    "username",
    "nama_lengkap",
    "name",
]);

$pk_layanan = getExistingCol($conn, "layanan", [
    "id_layanan",
    "layanan_id",
    "id",
]);
$col_l_nama = getExistingCol($conn, "layanan", [
    "nama_layanan",
    "nama",
    "layanan",
]);
?>
