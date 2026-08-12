<?php
include '../config/database.php';
include '../config/helper.php';

session_start();
check_login('admin');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    if (mysqli_query($conn, "DELETE FROM users WHERE id_user = $id AND role = 'pelanggan'")) {
        header("Location: pengguna.php?success=Pelanggan berhasil dihapus.");
        exit();
    } else {
        header("Location: pengguna.php?error=Gagal menghapus pelanggan.");
        exit();
    }
}
header("Location: pengguna.php");
exit();
