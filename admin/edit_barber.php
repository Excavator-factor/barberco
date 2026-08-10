<?php
include '_bootstrap.php';
include '_chrome.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: kapster.php'); exit; }
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? ''); 
    $username = trim($_POST['username'] ?? ''); 
    $password = $_POST['password'] ?? '';
    $spesialisasi = trim($_POST['spesialisasi'] ?? ''); 
    $status = $_POST['status'] ?? 'aktif';
    
    if ($nama === '' || $username === '' || !in_array($status, ['aktif', 'nonaktif'], true)) {
        $error = 'Data yang diisi tidak valid.';
    } else {
        $owner = mysqli_prepare($conn, 'SELECT user_id FROM barber WHERE id = ? LIMIT 1'); 
        mysqli_stmt_bind_param($owner, 'i', $id); 
        mysqli_stmt_execute($owner); 
        $barberRow = mysqli_fetch_assoc(mysqli_stmt_get_result($owner)); 
        mysqli_stmt_close($owner);
        
        if (!$barberRow) {
            $error = 'Data barber tidak ditemukan.';
        } else {
            $userId = (int) $barberRow['user_id'];
            $check = mysqli_prepare($conn, 'SELECT id_user FROM users WHERE username = ? AND id_user <> ? LIMIT 1'); 
            mysqli_stmt_bind_param($check, 'si', $username, $userId); 
            mysqli_stmt_execute($check); 
            $duplicate = mysqli_num_rows(mysqli_stmt_get_result($check)) > 0; 
            mysqli_stmt_close($check);
            
            if ($duplicate) {
                $error = 'Username sudah digunakan.';
            } else {
                mysqli_begin_transaction($conn);
                try {
                    $avatarFileName = null;
                    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/../uploads/avatars/';
                        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $avatarFileName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                            move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatarFileName);
                        } else {
                            throw new Exception('Format gambar tidak didukung.');
                        }
                    }

                    if ($password !== '') {
                        if ($avatarFileName) {
                            $u = mysqli_prepare($conn, "UPDATE users SET nama = ?, username = ?, password = ?, avatar = ? WHERE id_user = ? AND role = 'barber'"); 
                            mysqli_stmt_bind_param($u, 'ssssi', $nama, $username, $password, $avatarFileName, $userId);
                        } else {
                            $u = mysqli_prepare($conn, "UPDATE users SET nama = ?, username = ?, password = ? WHERE id_user = ? AND role = 'barber'"); 
                            mysqli_stmt_bind_param($u, 'sssi', $nama, $username, $password, $userId); 
                        }
                    } else {
                        if ($avatarFileName) {
                            $u = mysqli_prepare($conn, "UPDATE users SET nama = ?, username = ?, avatar = ? WHERE id_user = ? AND role = 'barber'"); 
                            mysqli_stmt_bind_param($u, 'sssi', $nama, $username, $avatarFileName, $userId);
                        } else {
                            $u = mysqli_prepare($conn, "UPDATE users SET nama = ?, username = ? WHERE id_user = ? AND role = 'barber'"); 
                            mysqli_stmt_bind_param($u, 'ssi', $nama, $username, $userId); 
                        }
                    }
                    mysqli_stmt_execute($u); mysqli_stmt_close($u);
                    
                    $b = mysqli_prepare($conn, 'UPDATE barber SET nama = ?, spesialisasi = ?, status = ? WHERE id = ?'); 
                    mysqli_stmt_bind_param($b, 'sssi', $nama, $spesialisasi, $status, $id); 
                    mysqli_stmt_execute($b); 
                    mysqli_stmt_close($b);
                    
                    mysqli_commit($conn); 
                    $success = 'Data barber berhasil diperbarui.';
                } catch (Throwable $e) { 
                    mysqli_rollback($conn); 
                    $error = 'Gagal: ' . $e->getMessage(); 
                }
            }
        }
    }
}

$stmt = mysqli_prepare($conn, "SELECT b.id, b.nama, b.spesialisasi, b.status, u.username, u.avatar FROM barber b JOIN users u ON u.id_user = b.user_id WHERE b.id = ? LIMIT 1"); 
mysqli_stmt_bind_param($stmt, 'i', $id); 
mysqli_stmt_execute($stmt); 
$barber = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)); 
mysqli_stmt_close($stmt);

if (!$barber) { header('Location: kapster.php'); exit; }
?>
<?php admin_header('Edit Barber', 'kapster'); ?>

<style>
    .glass-card {
        background: rgba(26, 26, 26, 0.6) !important;
        backdrop-filter: blur(12px) !important;
        border: 1px solid rgba(45, 45, 45, 1) !important;
        transition: all 0.3s ease;
    }
    .grain-overlay {
        background-image: url("https://www.transparenttextures.com/patterns/carbon-fibre.png");
        opacity: 0.03;
        pointer-events: none;
    }
    .form-input {
        background: rgba(45, 45, 45, 0.3) !important;
        border: 1px solid rgba(156, 143, 120, 0.2) !important;
        color: #e5e2e1 !important;
        border-radius: 0.5rem !important;
        transition: all 0.2s ease;
    }
    .form-input:focus {
        border-color: #fbbc00 !important;
        outline: none !important;
        box-shadow: 0 0 0 1px rgba(251, 188, 0, 0.2) !important;
    }
</style>

<!-- Atmospheric Layer -->
<div class="fixed inset-0 grain-overlay z-0"></div>
<div class="fixed top-[-10%] left-[-10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>
<div class="fixed bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-primary/5 rounded-full blur-[120px] pointer-events-none"></div>

<div class="relative z-10 max-w-5xl mx-auto px-4 py-8">
    <!-- Header Branding -->
    <div class="flex items-center justify-between mb-8 w-full">
        <header class="flex flex-col items-start">
            <h1 class="font-headline-md text-3xl font-bold text-white tracking-tighter mb-1">Edit Kapster</h1>
            <p class="font-body-md text-sm text-on-surface-variant">Perbarui informasi dan preferensi akun kapster</p>
        </header>
        <a href="kapster.php" class="px-4 py-2 bg-surface-container border border-outline-variant rounded-lg text-sm font-bold text-white hover:bg-surface-high transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Kembali
        </a>
    </div>

    <?php if ($success): ?>
        <div class="glass-card mb-6 p-4 rounded-lg flex items-center gap-3 border-green-500/30 text-green-400">
            <span class="material-symbols-outlined">check_circle</span>
            <span class="font-bold text-sm"><?= htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="glass-card mb-6 p-4 rounded-lg flex items-center gap-3 border-red-500/30 text-red-400">
            <span class="material-symbols-outlined">error</span>
            <span class="font-bold text-sm"><?= htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-8">
        <input type="hidden" name="id" value="<?= (int) $barber['id'] ?>">
        
        <!-- Left Column: Profile Card -->
        <div class="glass-card rounded-xl p-6 flex flex-col items-center justify-center">
            <!-- Avatar Display -->
            <div class="flex flex-col items-center gap-4 mb-6">
                <div class="relative group w-24 h-24 rounded-full border-2 border-primary/30 flex items-center justify-center overflow-hidden bg-surface-container-high shrink-0 transition-all hover:border-primary/60">
                    <?php if (!empty($barber['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $barber['avatar'])): ?>
                        <img id="avatarPreview" class="w-full h-full object-cover" src="../uploads/avatars/<?= htmlspecialchars($barber['avatar']) ?>" alt="Avatar">
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
                    <h3 class="font-bold text-white mb-2 text-lg"><?= htmlspecialchars($barber['username']); ?></h3>
                    <span class="bg-primary/20 text-primary border border-primary/30 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">BARBER</span>
                </div>
            </div>
            
            <div class="w-full h-px bg-white/10 my-6"></div>
            
            <!-- Contact Info -->
            <div class="w-full space-y-4">
                <div class="flex items-center gap-3 text-on-surface-variant flex-wrap">
                    <span class="material-symbols-outlined text-sm text-primary">badge</span>
                    <span class="text-sm"><?= htmlspecialchars($barber['nama']); ?></span>
                </div>
                <?php if (!empty($barber['spesialisasi'])): ?>
                <div class="flex items-center gap-3 text-on-surface-variant flex-wrap">
                    <span class="material-symbols-outlined text-sm text-primary">content_cut</span>
                    <span class="text-sm"><?= htmlspecialchars($barber['spesialisasi']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Settings Card -->
        <div class="glass-card rounded-xl p-8">
            <div class="flex items-center gap-2 mb-8 text-primary">
                <span class="material-symbols-outlined">edit_square</span>
                <h2 class="font-bold text-xl text-white">Data Barber</h2>
            </div>
            
            <div class="space-y-8">
                <!-- Form Fields Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="font-bold text-[11px] text-white tracking-widest ml-1 uppercase">Nama Lengkap</label>
                        <input name="nama" class="w-full h-12 px-4 rounded-lg form-input text-sm" type="text" value="<?= htmlspecialchars($barber['nama']) ?>" required>
                    </div>
                    <div class="space-y-2">
                        <label class="font-bold text-[11px] text-white tracking-widest ml-1 uppercase">Username</label>
                        <input name="username" class="w-full h-12 px-4 rounded-lg form-input text-sm" type="text" value="<?= htmlspecialchars($barber['username']) ?>" required>
                    </div>
                    <div class="space-y-2 md:col-span-2">
                        <label class="font-bold text-[11px] text-white tracking-widest ml-1 uppercase">Spesialisasi Artistik</label>
                        <input name="spesialisasi" class="w-full h-12 px-4 rounded-lg form-input text-sm" placeholder="Cth: Classic Fade, Hair Tattoo, dll" type="text" value="<?= htmlspecialchars($barber['spesialisasi'] ?? '') ?>">
                    </div>
                    <div class="space-y-2">
                        <label class="font-bold text-[11px] text-white tracking-widest ml-1 uppercase">Status Aktif</label>
                        <select name="status" class="w-full h-12 px-4 rounded-lg form-input text-sm focus:outline-none focus:border-primary">
                            <option value="aktif" class="bg-[#1a1a1a] text-white" <?= $barber['status'] === 'aktif' ? 'selected' : '' ?>>Aktif Melayani</option>
                            <option value="nonaktif" class="bg-[#1a1a1a] text-white" <?= $barber['status'] === 'nonaktif' ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="w-full h-px bg-white/10 my-6"></div>
                <!-- Security Section -->
                <div class="space-y-6">
                    <div class="flex items-center gap-2 text-primary">
                        <span class="material-symbols-outlined text-sm">lock</span>
                        <h3 class="font-bold tracking-widest text-sm uppercase text-white">Ubah Password</h3>
                    </div>
                    <div class="space-y-2 w-full md:w-1/2">
                        <label class="text-xs text-on-surface-variant ml-1">Ketik password baru (Opsional)</label>
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

<script>
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                const fallback = document.getElementById('avatarFallback');
                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                if (fallback) {
                    fallback.classList.add('hidden');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

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

<?php admin_footer('kapster'); ?>
