<?php
include '../config/database.php';
include '../config/helper.php';
check_login('admin');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: pelanggan.php'); exit; }

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($nama === '' || $username === '') {
        $error = 'Nama dan username wajib diisi.';
    } else {
        $check = mysqli_prepare($conn, 'SELECT id_user FROM users WHERE username = ? AND id_user <> ? LIMIT 1');
        mysqli_stmt_bind_param($check, 'si', $username, $id); mysqli_stmt_execute($check);
        $duplicate = mysqli_num_rows(mysqli_stmt_get_result($check)) > 0; mysqli_stmt_close($check);
        if ($duplicate) {
            $error = 'Username sudah digunakan.';
        } else {
            $avatarFileName = null;
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $avatarFileName = 'avatar_' . $id . '_' . time() . '.' . $ext;
                    move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatarFileName);
                } else {
                    $error = 'Format gambar tidak didukung.';
                }
            }

            if (!$error) {
                if ($password !== '') {
                    if ($avatarFileName) {
                        $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, username = ?, password = ?, avatar = ? WHERE id_user = ? AND role = 'pelanggan'");
                        mysqli_stmt_bind_param($stmt, 'ssssi', $nama, $username, $password, $avatarFileName, $id);
                    } else {
                        $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, username = ?, password = ? WHERE id_user = ? AND role = 'pelanggan'");
                        mysqli_stmt_bind_param($stmt, 'sssi', $nama, $username, $password, $id);
                    }
                } else {
                    if ($avatarFileName) {
                        $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, username = ?, avatar = ? WHERE id_user = ? AND role = 'pelanggan'");
                        mysqli_stmt_bind_param($stmt, 'sssi', $nama, $username, $avatarFileName, $id);
                    } else {
                        $stmt = mysqli_prepare($conn, "UPDATE users SET nama = ?, username = ? WHERE id_user = ? AND role = 'pelanggan'");
                        mysqli_stmt_bind_param($stmt, 'ssi', $nama, $username, $id);
                    }
                }
                mysqli_stmt_execute($stmt); $updated = mysqli_stmt_affected_rows($stmt) >= 0; mysqli_stmt_close($stmt);
                if ($updated) $success = 'Data pelanggan berhasil diperbarui.';
                else $error = 'Data pelanggan tidak dapat diperbarui.';
            }
        }
    }
}
$stmt = mysqli_prepare($conn, "SELECT id_user, nama, username, avatar FROM users WHERE id_user = ? AND role = 'pelanggan' LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id); mysqli_stmt_execute($stmt); $pelanggan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); mysqli_stmt_close($stmt);
if (!$pelanggan) { header('Location: pelanggan.php'); exit; }
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Edit Pelanggan | Barber.co</title><script src="https://cdn.tailwindcss.com"></script><script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script></head>
<body class="min-h-screen bg-[#fbf9f4] p-5 font-sans text-[#1b1c19] md:p-12"><main class="mx-auto max-w-xl border border-black bg-white p-6 md:p-8"><div class="mb-8 flex items-center justify-between"><div><p class="text-xs font-bold uppercase tracking-widest text-neutral-500">Administrasi</p><h1 class="mt-1 text-2xl font-bold">Edit Pelanggan</h1></div><a href="pelanggan.php" class="text-sm underline">Kembali</a></div><?php if ($error): ?><script>document.addEventListener('DOMContentLoaded', () => Swal.fire({icon: 'error', title: 'Kesalahan', text: '<?= htmlspecialchars($error, ENT_QUOTES) ?>'}));</script><?php endif; ?><?php if ($success): ?><script>document.addEventListener('DOMContentLoaded', () => Swal.fire({icon: 'success', title: 'Berhasil', text: '<?= htmlspecialchars($success, ENT_QUOTES) ?>'}));</script><?php endif; ?><form method="post" enctype="multipart/form-data" class="space-y-5"><input type="hidden" name="id" value="<?= (int) $pelanggan['id_user'] ?>"><label class="block text-sm font-bold">Nama<input required name="nama" value="<?= htmlspecialchars($pelanggan['nama']) ?>" class="mt-2 w-full border border-black p-3 font-normal"></label><label class="block text-sm font-bold">Username<input required name="username" value="<?= htmlspecialchars($pelanggan['username']) ?>" class="mt-2 w-full border border-black p-3 font-normal"></label><label class="block text-sm font-bold">Foto Profil<input type="file" accept="image/png, image/jpeg, image/webp" name="avatar" class="mt-2 w-full border border-black p-2 font-normal"></label><label class="block text-sm font-bold">Password baru <span class="font-normal text-neutral-500">(kosongkan bila tidak diubah)</span><input type="password" name="password" class="mt-2 w-full border border-black p-3 font-normal"></label><button class="w-full bg-black px-5 py-3 text-sm font-bold uppercase tracking-widest text-white">Simpan perubahan</button></form></main></body></html>
