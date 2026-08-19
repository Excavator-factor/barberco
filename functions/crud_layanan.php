<?php
session_start();
include "../config/database.php";
include "../config/helper.php";

// Cek autentikasi admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit();
}

$action = $_GET["action"] ?? ($_POST["action"] ?? "");

if ($action === "add_layanan") {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $nama = trim($_POST["nama_layanan"] ?? "");
        $deskripsi = trim($_POST["deskripsi"] ?? "");
        $harga = (int) ($_POST["harga"] ?? 0);
        $durasi = (int) ($_POST["durasi"] ?? 0);

        if ($nama === "" || $harga <= 0 || $durasi <= 0) {
            $_SESSION["modalError"] = "Data layanan tidak valid.";
        } else {
            $imagePath = "";
            if (
                isset($_FILES["gambar"]) &&
                $_FILES["gambar"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {
                if ($_FILES["gambar"]["error"] !== UPLOAD_ERR_OK) {
                    $_SESSION["modalError"] =
                        "Upload gagal. Kode: " . $_FILES["gambar"]["error"];
                } else {
                    $allowed = ["jpg", "jpeg", "png", "webp"];
                    $ext = strtolower(
                        pathinfo(
                            basename((string) $_FILES["gambar"]["name"]),
                            PATHINFO_EXTENSION,
                        ),
                    );
                    $uploadDir = __DIR__ . "/../uploads/layanan";
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0775, true);
                    }
                    if (!in_array($ext, $allowed, true)) {
                        $_SESSION["modalError"] =
                            "Format gambar harus JPG, PNG, atau WEBP.";
                    } else {
                        $safeName =
                            "layanan-" .
                            preg_replace(
                                "/[^a-z0-9]+/i",
                                "-",
                                strtolower($nama),
                            ) .
                            "-" .
                            time() .
                            "." .
                            $ext;
                        if (
                            move_uploaded_file(
                                $_FILES["gambar"]["tmp_name"],
                                $uploadDir . "/" . $safeName,
                            )
                        ) {
                            $imagePath = "uploads/layanan/" . $safeName;
                        } else {
                            $_SESSION["modalError"] = "Upload gambar gagal.";
                        }
                    }
                }
            }

            if (empty($_SESSION["modalError"])) {
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO layanan (nama_layanan, deskripsi, harga, durasi, gambar) VALUES (?, ?, ?, ?, ?)",
                );
                mysqli_stmt_bind_param(
                    $stmt,
                    "ssiis",
                    $nama,
                    $deskripsi,
                    $harga,
                    $durasi,
                    $imagePath,
                );
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION[
                        "modalSuccess"
                    ] = "Layanan \"$nama\" berhasil ditambahkan!";
                } else {
                    $_SESSION["modalError"] =
                        "Gagal menyimpan layanan: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
    header("Location: ../admin/layanan.php");
    exit();
}

if ($action === "edit_layanan") {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = (int) ($_POST["layanan_id"] ?? 0);
        $nama = trim($_POST["nama_layanan"] ?? "");
        $deskripsi = trim($_POST["deskripsi"] ?? "");
        $harga = (int) ($_POST["harga"] ?? 0);
        $durasi = (int) ($_POST["durasi"] ?? 0);
        $imagePath = "";

        if ($id <= 0 || $nama === "" || $harga <= 0 || $durasi <= 0) {
            $_SESSION["modalError"] = "Data layanan tidak valid.";
        } else {
            if (
                isset($_FILES["gambar"]) &&
                $_FILES["gambar"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {
                if ($_FILES["gambar"]["error"] !== UPLOAD_ERR_OK) {
                    $_SESSION["modalError"] =
                        "Upload gagal. Kode: " . $_FILES["gambar"]["error"];
                } else {
                    $allowed = ["jpg", "jpeg", "png", "webp"];
                    $ext = strtolower(
                        pathinfo(
                            basename((string) $_FILES["gambar"]["name"]),
                            PATHINFO_EXTENSION,
                        ),
                    );
                    $uploadDir = __DIR__ . "/../uploads/layanan";
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0775, true);
                    }
                    if (!in_array($ext, $allowed, true)) {
                        $_SESSION["modalError"] =
                            "Format gambar harus JPG, PNG, atau WEBP.";
                    } else {
                        $safeName =
                            "layanan-" .
                            preg_replace(
                                "/[^a-z0-9]+/i",
                                "-",
                                strtolower($nama),
                            ) .
                            "-" .
                            time() .
                            "." .
                            $ext;
                        if (
                            move_uploaded_file(
                                $_FILES["gambar"]["tmp_name"],
                                $uploadDir . "/" . $safeName,
                            )
                        ) {
                            $imagePath = "uploads/layanan/" . $safeName;
                        } else {
                            $_SESSION["modalError"] = "Upload gambar gagal.";
                        }
                    }
                }
            }

            if (empty($_SESSION["modalError"])) {
                if ($imagePath !== "") {
                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE layanan SET nama_layanan=?, deskripsi=?, harga=?, durasi=?, gambar=? WHERE id=?",
                    );
                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssiisi",
                        $nama,
                        $deskripsi,
                        $harga,
                        $durasi,
                        $imagePath,
                        $id,
                    );
                } else {
                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE layanan SET nama_layanan=?, deskripsi=?, harga=?, durasi=? WHERE id=?",
                    );
                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssiii",
                        $nama,
                        $deskripsi,
                        $harga,
                        $durasi,
                        $id,
                    );
                }
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION[
                        "modalSuccess"
                    ] = "Layanan \"$nama\" berhasil diperbarui!";
                } else {
                    $_SESSION["modalError"] =
                        "Gagal memperbarui layanan: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
    header("Location: ../admin/layanan.php");
    exit();
}

if ($action === "delete") {
    $serviceId =
        filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT) ?:
        filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT) ?:
        0;

    if ($serviceId > 0) {
        try {
            $stmt = mysqli_prepare($conn, "UPDATE layanan SET is_deleted = 1 WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $serviceId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            $_SESSION["modalSuccess"] = "Layanan berhasil dihapus.";
        } catch (mysqli_sql_exception $e) {
            $_SESSION["modalError"] =
                "Gagal menghapus layanan: " . $e->getMessage();
        }
    }
    header("Location: ../admin/layanan.php");
    exit();
}

header("Location: ../admin/dashboard.php");
exit();
