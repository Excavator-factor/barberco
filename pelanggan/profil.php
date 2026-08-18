<?php
include "_bootstrap.php";
include "_chrome.php";

$userId = (int) ($_SESSION["user_id"] ?? 0);
$profilNotice = "";
$profilError = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_profil"])) {
    $nama = trim((string) ($_POST["nama"] ?? ""));
    $username = trim((string) ($_POST["username"] ?? ""));
    $password = trim((string) ($_POST["password"] ?? ""));

    if ($nama === "" || $username === "") {
        $profilError = "Nama dan username tidak boleh kosong.";
    } else {
        $check = mysqli_prepare(
            $conn,
            "SELECT id_user FROM users WHERE username = ? AND id_user != ? LIMIT 1",
        );
        mysqli_stmt_bind_param($check, "si", $username, $userId);
        mysqli_stmt_execute($check);
        $exists = mysqli_stmt_get_result($check);
        if ($exists && mysqli_fetch_assoc($exists)) {
            $profilError = "Username sudah digunakan oleh akun lain.";
        }
        mysqli_stmt_close($check);

        if (!$profilError) {
            $avatarFileName = null;
            if (
                isset($_FILES["avatar"]) &&
                $_FILES["avatar"]["error"] === UPLOAD_ERR_OK
            ) {
                $uploadDir = __DIR__ . "/../uploads/avatars/";
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0777, true);
                }
                $tmpName = $_FILES["avatar"]["tmp_name"];
                $ext = strtolower(
                    pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION),
                );
                $allowed = ["jpg", "jpeg", "png", "webp"];
                if (in_array($ext, $allowed)) {
                    $avatarFileName =
                        "avatar_" . $userId . "_" . time() . "." . $ext;
                    move_uploaded_file($tmpName, $uploadDir . $avatarFileName);
                } else {
                    $profilError = "Format gambar tidak didukung.";
                }
            }

            if (!$profilError) {
                if ($password === "") {
                    if ($avatarFileName) {
                        $stmt = mysqli_prepare(
                            $conn,
                            "UPDATE users SET nama = ?, username = ?, avatar = ? WHERE id_user = ?",
                        );
                        mysqli_stmt_bind_param(
                            $stmt,
                            "sssi",
                            $nama,
                            $username,
                            $avatarFileName,
                            $userId,
                        );
                    } else {
                        $stmt = mysqli_prepare(
                            $conn,
                            "UPDATE users SET nama = ?, username = ? WHERE id_user = ?",
                        );
                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssi",
                            $nama,
                            $username,
                            $userId,
                        );
                    }
                } else {
                    if ($avatarFileName) {
                        $stmt = mysqli_prepare(
                            $conn,
                            "UPDATE users SET nama = ?, username = ?, password = ?, avatar = ? WHERE id_user = ?",
                        );
                        mysqli_stmt_bind_param(
                            $stmt,
                            "ssssi",
                            $nama,
                            $username,
                            $password,
                            $avatarFileName,
                            $userId,
                        );
                    } else {
                        $stmt = mysqli_prepare(
                            $conn,
                            "UPDATE users SET nama = ?, username = ?, password = ? WHERE id_user = ?",
                        );
                        mysqli_stmt_bind_param(
                            $stmt,
                            "sssi",
                            $nama,
                            $username,
                            $password,
                            $userId,
                        );
                    }
                }
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $_SESSION["nama"] = $nama;
                $_SESSION["username"] = $username;
                $GLOBALS["username"] = $username;
                if ($avatarFileName) {
                    $_SESSION["avatar"] = $avatarFileName;
                }

                $profilNotice = "Profil berhasil diperbarui.";
            }
        }
    }
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT nama, username, avatar FROM users WHERE id_user = ? LIMIT 1",
);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = $result ? mysqli_fetch_assoc($result) : null;
mysqli_stmt_close($stmt);

$currentNama = $user["nama"] ?? ($_SESSION["nama"] ?? "");
$currentUsername = $user["username"] ?? ($_SESSION["username"] ?? "");
$currentAvatar = $user["avatar"] ?? null;
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <?php pelanggan_theme_head("Profil Pelanggan"); ?>
</head>
<body class="min-h-screen">
    <?php pelanggan_sidebar("profil"); ?>
    <main data-pelanggan-main class="min-h-screen transition-[margin] duration-200 md:ml-64">
        <?php pelanggan_topbar("Profil & Pengaturan"); ?>

        <style>
            .glass-card {
                background: rgba(26, 26, 26, 0.6);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(45, 45, 45, 1);
                transition: all 0.3s ease;
            }
            .grain-overlay {
                background-image: url("https://www.transparenttextures.com/patterns/carbon-fibre.png");
                opacity: 0.03;
                pointer-events: none;
            }
            .form-input {
                background: rgba(45, 45, 45, 0.3);
                border: 1px solid rgba(156, 143, 120, 0.2);
                color: #e5e2e1;
                transition: all 0.2s ease;
            }
            .form-input:focus {
                border-color: #fbbc00;
                outline: none;
                box-shadow: 0 0 0 1px rgba(251, 188, 0, 0.2);
            }
        </style>

        <!-- Atmospheric Layer -->
        <div class="fixed inset-0 grain-overlay z-0"></div>
        <div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 py-8">
            <!-- Header Branding -->
            <header class="flex flex-col items-start mb-8 w-full">
                <h1 class="font-headline-md text-3xl font-bold text-white tracking-tighter mb-1">Profil Saya</h1>
                <p class="font-body-md text-sm text-on-surface-variant">Kelola informasi pribadi dan keamanan akun Anda</p>
            </header>

            <?php if ($profilNotice): ?>
                <div class="glass-card mb-6 p-4 rounded-lg flex items-center gap-3 border-green-500/30 text-green-400">
                    <span class="material-symbols-outlined">check_circle</span>
                    <span class="font-bold text-sm"><?= htmlspecialchars(
                        $profilNotice,
                    ) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($profilError): ?>
                <div class="glass-card mb-6 p-4 rounded-lg flex items-center gap-3 border-red-500/30 text-red-400">
                    <span class="material-symbols-outlined">error</span>
                    <span class="font-bold text-sm"><?= htmlspecialchars(
                        $profilError,
                    ) ?></span>
                </div>
            <?php endif; ?>

            <!-- Profile Edit Section -->
            <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-8">
                <input type="hidden" name="update_profil" value="1">
                
                <!-- Left Column: Profile Card -->
                <div class="glass-card rounded-xl p-6 flex flex-col items-center">
                    <!-- Avatar Display -->
                    <div class="flex flex-col items-center gap-4 mb-6">
                        <div class="relative group w-24 h-24 rounded-full border-2 border-primary/30 flex items-center justify-center overflow-hidden bg-surface-container-high shrink-0 transition-all hover:border-primary/60">
                            <?php if (
                                $currentAvatar &&
                                file_exists(
                                    __DIR__ .
                                        "/../uploads/avatars/" .
                                        $currentAvatar,
                                )
                            ): ?>
                                <img id="avatarPreview" class="w-full h-full object-cover" src="../uploads/avatars/<?= htmlspecialchars(
                                    $currentAvatar,
                                ) ?>" alt="Avatar">
                            <?php else: ?>
                                <img id="avatarPreview" class="w-full h-full object-cover hidden" src="" alt="Avatar">
                                <span id="avatarFallback" class="material-symbols-outlined text-5xl text-primary/70">person</span>
                            <?php endif; ?>
                            <label class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-full cursor-pointer z-10 w-full h-full m-0">
                                <input type="file" name="avatar" class="hidden" accept="image/png, image/jpeg, image/webp" onchange="previewAvatar(this)">
                                <span class="material-symbols-outlined text-white text-xl">photo_camera</span>
                            </label>
                        </div>
                        <div class="text-center">
                            <h3 class="font-bold text-white mb-2 text-lg"><?= htmlspecialchars(
                                $currentUsername,
                            ) ?></h3>
                            <span class="bg-primary/20 text-primary border border-primary/30 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">PELANGGAN</span>
                        </div>
                    </div>
                    <div class="w-full h-px bg-white/10 my-6"></div>
                    <!-- Contact Info -->
                    <div class="w-full space-y-4">
                        <div class="flex items-center gap-3 text-on-surface-variant">
                            <span class="material-symbols-outlined text-sm text-primary">badge</span>
                            <span class="text-sm"><?= htmlspecialchars(
                                $currentNama,
                            ) ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-on-surface-variant">
                            <span class="material-symbols-outlined text-sm text-primary">person</span>
                            <span class="text-sm">@<?= htmlspecialchars(
                                $currentUsername,
                            ) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Settings Card -->
                <div class="glass-card rounded-xl p-8">
                    <div class="flex items-center gap-2 mb-8 text-primary">
                        <span class="material-symbols-outlined">settings</span>
                        <h2 class="font-bold text-xl text-white">Pengaturan Akun</h2>
                    </div>
                    
                    <div class="space-y-8">
                        <!-- Form Fields Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="font-bold text-[11px] text-white tracking-widest ml-1 uppercase">Nama Lengkap</label>
                                <input name="nama" class="w-full h-12 px-4 rounded-lg form-input text-sm" placeholder="Nama Lengkap" type="text" value="<?= htmlspecialchars(
                                    $currentNama,
                                ) ?>" required>
                            </div>
                            <div class="space-y-2">
                                <label class="font-bold text-[11px] text-white tracking-widest ml-1 uppercase">Username</label>
                                <input name="username" class="w-full h-12 px-4 rounded-lg form-input text-sm" placeholder="@username" type="text" value="<?= htmlspecialchars(
                                    $currentUsername,
                                ) ?>" required>
                            </div>
                        </div>
                        <div class="w-full h-px bg-white/10 my-6"></div>
                        <!-- Security Section -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-2 text-primary">
                                <span class="material-symbols-outlined text-sm">lock</span>
                                <h3 class="font-bold tracking-widest text-sm uppercase text-white">Keamanan</h3>
                            </div>
                            <div class="space-y-2 w-full md:w-1/2">
                                <label class="text-xs text-on-surface-variant ml-1">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                                <input name="password" class="w-full h-12 px-4 rounded-lg form-input text-sm" placeholder="••••••••" type="password" value="">
                            </div>
                        </div>
                        <!-- Submit Action -->
                        <div class="pt-6 flex justify-end">
                            <button class="px-6 h-12 bg-primary text-on-primary font-bold rounded-lg hover:bg-primary-fixed-dim transition-all active:scale-[0.98] flex items-center justify-center gap-2" type="submit">
                                <span class="material-symbols-outlined text-lg">save</span>
                                <span class="text-sm">Simpan Perubahan</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
    <?php pelanggan_mobile_nav("profil"); ?>
    <script>
        // Simple parallax effect on main card
        document.addEventListener('mousemove', (e) => {
            const cards = document.querySelectorAll('.glass-card');
            cards.forEach(card => {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                const speed = 1.0;
                const xOffset = (x - 0.5) * speed;
                const yOffset = (y - 0.5) * speed;
                card.style.transform = `translate(${xOffset}px, ${yOffset}px)`;
            });
        });
    </script>
</body>
</html>
