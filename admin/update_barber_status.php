<?php
include '_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pengguna.php');
    exit;
}

$barberId = filter_input(INPUT_POST, 'barber_id', FILTER_VALIDATE_INT);
$status = $_POST['status'] ?? '';
$redirect = $_POST['redirect'] ?? 'pengguna.php';

if ($barberId && in_array($status, ['aktif', 'nonaktif'], true)) {
    $stmt = mysqli_prepare($conn, 'UPDATE barber SET status = ? WHERE id = ?');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'si', $status, $barberId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

header('Location: ' . $redirect);
exit;
