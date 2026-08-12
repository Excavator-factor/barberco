<?php
include '../config/database.php';
include '../config/helper.php';

session_start();
check_login('admin');

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    // Check user_id for the barber
    $q = mysqli_query($conn, "SELECT user_id FROM barber WHERE id = $id LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);
        $user_id = (int)$row['user_id'];
        
        mysqli_begin_transaction($conn);
        try {
            mysqli_query($conn, "DELETE FROM barber WHERE id = $id");
            if ($user_id > 0) {
                mysqli_query($conn, "DELETE FROM users WHERE id_user = $user_id AND role = 'barber'");
            }
            mysqli_commit($conn);
            header("Location: pengguna.php?success=Kapster berhasil dihapus.");
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            header("Location: pengguna.php?error=Gagal menghapus kapster.");
            exit();
        }
    } else {
        header("Location: pengguna.php?error=Data kapster tidak ditemukan.");
        exit();
    }
}
header("Location: pengguna.php");
exit();
