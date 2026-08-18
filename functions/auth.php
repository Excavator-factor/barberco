<?php
session_start();
include "../config/database.php";

$action = $_GET["action"] ?? "";

if ($action === "login") {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = mysqli_real_escape_string(
            $conn,
            trim($_POST["username"] ?? ""),
        );
        $password = $_POST["password"] ?? "";

        if (!empty($username) && !empty($password)) {
            $query = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                $dbPassword = $user["password"] ?? "";

                if (
                    $password === $dbPassword ||
                    password_verify($password, $dbPassword)
                ) {
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

                    if (isset($_POST["remember"])) {
                        $token = bin2hex(random_bytes(32));
                        $expiry = date("Y-m-d H:i:s", time() + 86400 * 30);

                        $checkCol = mysqli_query(
                            $conn,
                            "SHOW COLUMNS FROM users LIKE 'remember_token'",
                        );
                        if ($checkCol && mysqli_num_rows($checkCol) === 0) {
                            mysqli_query(
                                $conn,
                                "ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) NULL, ADD COLUMN remember_expires DATETIME NULL",
                            );
                        }

                        mysqli_query(
                            $conn,
                            "UPDATE users SET remember_token = '$token', remember_expires = '$expiry' WHERE id_user = $uid",
                        );

                        setcookie("remember_me", $token, [
                            "expires" => time() + 86400 * 30,
                            "path" => "/",
                            "secure" =>
                                isset($_SERVER["HTTPS"]) &&
                                $_SERVER["HTTPS"] === "on",
                            "httponly" => true,
                            "samesite" => "Lax",
                        ]);
                    }

                    if ($_SESSION["role"] === "admin") {
                        header("Location: ../admin/dashboard.php");
                    } elseif ($_SESSION["role"] === "barber") {
                        header("Location: ../barber/dashboard.php");
                    } else {
                        header("Location: ../pelanggan/dashboard.php");
                    }
                    exit();
                }
                $_SESSION["error"] = "Password yang Anda masukkan salah!";
            } elseif ($result) {
                $_SESSION["error"] = "Username tidak ditemukan!";
            } else {
                $_SESSION["error"] =
                    "Kesalahan Query SQL: " . mysqli_error($conn);
            }
        } else {
            $_SESSION["error"] =
                "Mohon isi username dan password terlebih dahulu!";
        }
    }
    header("Location: ../auth/login.php");
    exit();
}

if ($action === "logout") {
    session_destroy();
    if (isset($_COOKIE["remember_me"])) {
        setcookie("remember_me", "", time() - 3600, "/");
    }
    header("Location: ../index.php");
    exit();
}

if ($action === "register") {
    if (isset($_POST["register"])) {
        $nama = trim($_POST["nama"]);
        $username = trim($_POST["username"]);
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $role = "pelanggan";

        $check_stmt = mysqli_prepare(
            $conn,
            "SELECT id_user FROM users WHERE username = ?",
        );
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $_SESSION["error"] =
                "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)",
            );
            mysqli_stmt_bind_param(
                $stmt,
                "ssss",
                $nama,
                $username,
                $password,
                $role,
            );

            if (mysqli_stmt_execute($stmt)) {
                header("Location: ../auth/login.php?registered=1");
                exit();
            }

            $_SESSION["error"] = "Pendaftaran gagal. Silakan coba lagi.";
        }

        if (isset($check_stmt)) {
            mysqli_stmt_close($check_stmt);
        }
    }
    header("Location: ../auth/register.php");
    exit();
}

if ($action === "forgot_password") {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $recoveryId = trim($_POST["recovery_id"] ?? "");

        if ($recoveryId === "") {
            $_SESSION["error"] = "Masukkan username Anda terlebih dahulu.";
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "SELECT id_user FROM users WHERE username = ? LIMIT 1",
            );
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $recoveryId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            $_SESSION["submitted"] = true;
        }
    }
    header("Location: ../auth/forgot_password.php");
    exit();
}

header("Location: ../index.php");
exit();
