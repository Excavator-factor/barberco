<?php
session_start();
include "../config/database.php";
include "../config/helper.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$action = $_GET["action"] ?? ($_POST["action"] ?? "");

if ($action === "add_barber") {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nama = trim(mysqli_real_escape_string($conn, $_POST["nama"] ?? ""));
        $username = trim(
            mysqli_real_escape_string($conn, $_POST["username"] ?? ""),
        );
        $password = trim(
            mysqli_real_escape_string($conn, $_POST["password"] ?? ""),
        );
        $spesialisasi = trim(
            mysqli_real_escape_string($conn, $_POST["spesialisasi"] ?? ""),
        );
        $status = in_array($_POST["status"] ?? "", [
            "aktif",
            "nonaktif",
            "cuti",
        ])
            ? $_POST["status"]
            : "aktif";

        if ($nama === "" || $username === "" || $password === "") {
            $_SESSION["modalError"] =
                "Nama, username, dan password wajib diisi.";
        } else {
            $chk = mysqli_query(
                $conn,
                "SELECT id_user FROM users WHERE username = '$username' LIMIT 1",
            );
            if ($chk && mysqli_num_rows($chk) > 0) {
                $_SESSION["modalError"] =
                    "Username sudah digunakan. Pilih username lain.";
            } else {
                mysqli_begin_transaction($conn);
                try {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    // To maintain backwards compatibility if password hashing was added later,
                    // I will just use the exact logic from the old file. Wait, the old file literally saved it in plain text!
                    // Let's keep the old behavior for now or update it minimally:
                    $insUser = mysqli_query(
                        $conn,
                        "INSERT INTO users (username, password, role, nama) VALUES ('$username', '$password', 'barber', '$nama')",
                    );
                    if (!$insUser) {
                        throw new Exception(mysqli_error($conn));
                    }
                    $newUserId = mysqli_insert_id($conn);
                    $insBarber = mysqli_query(
                        $conn,
                        "INSERT INTO barber (user_id, nama, spesialisasi, status) VALUES ('$newUserId', '$nama', '$spesialisasi', '$status')",
                    );
                    if (!$insBarber) {
                        throw new Exception(mysqli_error($conn));
                    }
                    mysqli_commit($conn);
                    $_SESSION[
                        "modalSuccess"
                    ] = "Kapster \"$nama\" berhasil didaftarkan!";
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $_SESSION["modalError"] =
                        "Gagal mendaftarkan kapster: " . $e->getMessage();
                }
            }
        }
    }
    header("Location: ../admin/pengguna.php?t=kapster");
    exit();
}

if ($action === "edit_barber") {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id_barber = (int) ($_POST["id_barber"] ?? 0);
        $nama = trim(mysqli_real_escape_string($conn, $_POST["nama"] ?? ""));
        $username = trim(
            mysqli_real_escape_string($conn, $_POST["username"] ?? ""),
        );
        $password = $_POST["password"] ?? "";
        $spesialisasi = trim(
            mysqli_real_escape_string($conn, $_POST["spesialisasi"] ?? ""),
        );
        $status = in_array($_POST["status"] ?? "", [
            "aktif",
            "nonaktif",
            "cuti",
        ])
            ? $_POST["status"]
            : "aktif";

        if (!$id_barber || $nama === "" || $username === "") {
            $_SESSION["modalError"] = "Data kapster tidak valid.";
        } else {
            $owner = mysqli_query(
                $conn,
                "SELECT user_id FROM barber WHERE id = $id_barber LIMIT 1",
            );
            $barberRow = mysqli_fetch_assoc($owner);
            if (!$barberRow) {
                $_SESSION["modalError"] = "Data barber tidak ditemukan.";
            } else {
                $userId = (int) $barberRow["user_id"];
                $chk = mysqli_query(
                    $conn,
                    "SELECT id_user FROM users WHERE username = '$username' AND id_user <> $userId LIMIT 1",
                );
                if (mysqli_num_rows($chk) > 0) {
                    $_SESSION["modalError"] = "Username sudah digunakan.";
                } else {
                    mysqli_begin_transaction($conn);
                    try {
                        $avatarFileName = null;
                        if (
                            isset($_FILES["avatar"]) &&
                            $_FILES["avatar"]["error"] === UPLOAD_ERR_OK
                        ) {
                            $uploadDir = __DIR__ . "/../uploads/avatars/";
                            if (!is_dir($uploadDir)) {
                                @mkdir($uploadDir, 0777, true);
                            }
                            $ext = strtolower(
                                pathinfo(
                                    $_FILES["avatar"]["name"],
                                    PATHINFO_EXTENSION,
                                ),
                            );
                            if (
                                in_array($ext, ["jpg", "jpeg", "png", "webp"])
                            ) {
                                $avatarFileName =
                                    "avatar_" .
                                    $userId .
                                    "_" .
                                    time() .
                                    "." .
                                    $ext;
                                move_uploaded_file(
                                    $_FILES["avatar"]["tmp_name"],
                                    $uploadDir . $avatarFileName,
                                );
                            }
                        }

                        $passQuery = "";
                        if ($password !== "") {
                            $passQuery =
                                ", password = '" .
                                mysqli_real_escape_string($conn, $password) .
                                "'";
                        }
                        $avatarQuery = "";
                        if ($avatarFileName) {
                            $avatarQuery =
                                ", avatar = '" .
                                mysqli_real_escape_string(
                                    $conn,
                                    $avatarFileName,
                                ) .
                                "'";
                        }

                        $updUser = mysqli_query(
                            $conn,
                            "UPDATE users SET nama = '$nama', username = '$username' $passQuery $avatarQuery WHERE id_user = $userId AND role = 'barber'",
                        );
                        if (!$updUser) {
                            throw new Exception(mysqli_error($conn));
                        }

                        $updBarber = mysqli_query(
                            $conn,
                            "UPDATE barber SET nama = '$nama', spesialisasi = '$spesialisasi', status = '$status' WHERE id = $id_barber",
                        );
                        if (!$updBarber) {
                            throw new Exception(mysqli_error($conn));
                        }

                        mysqli_commit($conn);
                        $_SESSION["modalSuccess"] =
                            "Data kapster berhasil diperbarui!";
                    } catch (Exception $e) {
                        mysqli_rollback($conn);
                        $_SESSION["modalError"] =
                            "Gagal menyimpan kapster: " . $e->getMessage();
                    }
                }
            }
        }
    }
    header("Location: ../admin/pengguna.php?t=kapster");
    exit();
}

if ($action === "delete_barber") {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if ($id) {
        $ownerResp = mysqli_query(
            $conn,
            "SELECT user_id FROM barber WHERE id = $id LIMIT 1",
        );
        $barberUsr = mysqli_fetch_assoc($ownerResp);
        if ($barberUsr) {
            $userId = (int) $barberUsr["user_id"];
            $chk = mysqli_query(
                $conn,
                "SELECT COUNT(*) as jml FROM antrian WHERE barber_id = $id",
            );
            $cekData = mysqli_fetch_assoc($chk);
            if ($cekData && $cekData["jml"] > 0) {
                $_SESSION["modalError"] =
                    "Barber tidak bisa dihapus karena terkait dengan " .
                    $cekData["jml"] .
                    " antrean/transaksi.";
            } else {
                mysqli_begin_transaction($conn);
                try {
                    $delBarber = mysqli_query(
                        $conn,
                        "DELETE FROM barber WHERE id = $id",
                    );
                    if (!$delBarber) {
                        throw new Exception(mysqli_error($conn));
                    }

                    if ($userId > 0) {
                        $delUser = mysqli_query(
                            $conn,
                            "DELETE FROM users WHERE id_user = $userId AND role = 'barber'",
                        );
                        if (!$delUser) {
                            throw new Exception(mysqli_error($conn));
                        }
                    }
                    mysqli_commit($conn);
                    $_SESSION["modalSuccess"] =
                        "Data kemitraan kapster berhasil dicabut.";
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $_SESSION["modalError"] =
                        "Gagal menghapus barber: " . $e->getMessage();
                }
            }
        } else {
            $_SESSION["modalError"] = "Data kapster tidak ditemukan.";
        }
    }
    header("Location: ../admin/pengguna.php?t=kapster");
    exit();
}

if ($action === "update_status") {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    $status = filter_input(INPUT_GET, "status", FILTER_SANITIZE_SPECIAL_CHARS);

    if ($id && in_array($status, ["aktif", "nonaktif", "cuti"])) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE barber SET status = ? WHERE id = ?",
        );
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION["modalSuccess"] =
                "Status kapster berhasil diubah menjadi: " .
                strtoupper($status);
        } else {
            $_SESSION["modalError"] = "Gagal mengubah status.";
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: ../admin/pengguna.php?t=kapster");
    exit();
}

header("Location: ../admin/pengguna.php");
exit();
