<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_login($required_role = null) {
    global $conn;
    
    // --- START AUTO LOGIN LOGIC ---
    if ((!isset($_SESSION['role']) || empty($_SESSION['role'])) && isset($_COOKIE['remember_me'])) {
        if (isset($conn) && $conn) {
            $token = mysqli_real_escape_string($conn, $_COOKIE['remember_me']);
            // Pastikan kolom token sudah pernah dibuat
            @mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'remember_token'"); // dummy trigger or error supression if not exists
            
            $res = @mysqli_query($conn, "SELECT * FROM users WHERE remember_token = '$token' AND remember_expires > NOW() LIMIT 1");
            if ($res && mysqli_num_rows($res) > 0) {
                // Token valid, pulihkan sesi pengguna
                $user = mysqli_fetch_assoc($res);
                $uid = $user['id'] ?? $user['id_user'] ?? $user['user_id'] ?? 1;
                $_SESSION['user_id'] = $uid;
                $_SESSION['username'] = $user['username'] ?? $user['nama'] ?? 'User';
                $_SESSION['role'] = strtolower(trim($user['role'] ?? 'pelanggan'));
                if (!empty($user['avatar'])) {
                    $_SESSION['avatar'] = $user['avatar'];
                }

                // Token Rotation: Ganti token lama dengan token baru untuk mencegah hijacking
                $newToken = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', time() + (86400 * 30));
                
                @mysqli_query($conn, "UPDATE users SET remember_token = '$newToken', remember_expires = '$expiry' WHERE id_user = $uid");

                setcookie('remember_me', $newToken, [
                    'expires' => time() + (86400 * 30),
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
            }
        }
    }
    // --- END AUTO LOGIN LOGIC ---

    // Cek apakah user sudah login
    if (!isset($_SESSION['role']) || empty($_SESSION['role'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    // Jika halaman membutuhkan role spesifik
    if ($required_role !== null) {
        $user_role = strtolower(trim($_SESSION['role']));
        $req_role = strtolower(trim($required_role));

        if ($user_role !== $req_role) {
            // Jika salah role, lempar ke dashboard yang sesuai dengan rolenya masing-masing
            if ($user_role === 'admin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($user_role === 'barber') {
                header("Location: ../barber/dashboard.php");
            } else {
                header("Location: ../pelanggan/dashboard.php");
            }
            exit;
        }
    }
}

if (isset($_SESSION['user_id']) && isset($GLOBALS['conn'])) {
    $uid = (int) $_SESSION['user_id'];
    @mysqli_query($GLOBALS['conn'], "UPDATE users SET terakhir_aktivitas = NOW() WHERE id_user = $uid");
}
?>