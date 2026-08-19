<?php
session_start();
include "../config/database.php";
include "../config/helper.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$action = $_GET["action"] ?? ($_POST["action"] ?? "");

if ($action === "add_user") {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nama = trim(mysqli_real_escape_string($conn, $_POST["nama"] ?? ""));
        $username = trim(
            mysqli_real_escape_string($conn, $_POST["username"] ?? ""),
        );
        $password = trim(
            mysqli_real_escape_string($conn, $_POST["password"] ?? ""),
        );

        if ($username === "" || $password === "") {
            $_SESSION["modalError"] = "Username dan password wajib diisi.";
        } else {
            $chk = mysqli_query(
                $conn,
                "SELECT id_user FROM users WHERE username = '$username' LIMIT 1",
            );
            if ($chk && mysqli_num_rows($chk) > 0) {
                $_SESSION["modalError"] =
                    "Username sudah digunakan. Pilih username lain.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $ins = mysqli_query(
                    $conn,
                    "INSERT INTO users (username, password, role, nama) VALUES ('$username', '$password', 'pelanggan', '$nama')",
                );
                if ($ins) {
                    $_SESSION[
                        "modalSuccess"
                    ] = "Pelanggan \"$nama\" berhasil ditambahkan!";
                } else {
                    $_SESSION["modalError"] = "Gagal menyimpan data pelanggan.";
                }
            }
        }
    }
    header("Location: ../admin/pengguna.php?t=pelanggan");
    exit();
}

if ($action === "edit_user") {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id_user = (int) ($_POST["id_user"] ?? 0);
        $nama = trim(mysqli_real_escape_string($conn, $_POST["nama"] ?? ""));
        $username = trim(
            mysqli_real_escape_string($conn, $_POST["username"] ?? ""),
        );
        $password = $_POST["password"] ?? "";

        if (!$id_user || $nama === "" || $username === "") {
            $_SESSION["modalError"] = "Data pelanggan tidak valid.";
        } else {
            $chk = mysqli_query(
                $conn,
                "SELECT id_user FROM users WHERE username = '$username' AND id_user <> $id_user LIMIT 1",
            );
            if ($chk && mysqli_num_rows($chk) > 0) {
                $_SESSION["modalError"] = "Username sudah digunakan.";
            } else {
                $passQuery = "";
                if ($password !== "") {
                    $passQuery =
                        ", password = '" .
                        mysqli_real_escape_string($conn, $password) .
                        "'";
                }

                $updUser = mysqli_query(
                    $conn,
                    "UPDATE users SET nama = '$nama', username = '$username' $passQuery WHERE id_user = $id_user AND role = 'pelanggan'",
                );
                if ($updUser) {
                    $_SESSION["modalSuccess"] =
                        "Data pelanggan berhasil diperbarui!";
                } else {
                    $_SESSION["modalError"] =
                        "Gagal menyimpan pelanggan: " . mysqli_error($conn);
                }
            }
        }
    }
    header("Location: ../admin/pengguna.php?t=pelanggan");
    exit();
}

if ($action === "delete_user") {
    $id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
    if ($id) {
        $delUser = mysqli_query(
            $conn,
            "UPDATE users SET is_deleted = 1 WHERE id_user = $id AND role = 'pelanggan'",
        );
        if ($delUser) {
            $_SESSION["modalSuccess"] =
                "Akun pelanggan berhasil dihapus.";
            } else {
                $_SESSION["modalError"] =
                    "Gagal menghapus pengguna: " . mysqli_error($conn);
            }
        }
    }
    header("Location: ../admin/pengguna.php?t=pelanggan");
    exit();
}

header("Location: ../admin/pengguna.php");
exit();
