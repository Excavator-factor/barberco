<?php
include '_bootstrap.php';
include '_chrome.php';

// ─────────────────────────────────────────────
// HANDLE POST: Tambah Kapster (inline modal)
// ─────────────────────────────────────────────
$modalError   = '';
$modalSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'add_barber') {
        $nama         = trim(mysqli_real_escape_string($conn, $_POST['nama'] ?? ''));
        $username     = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ''));
        $password     = trim(mysqli_real_escape_string($conn, $_POST['password'] ?? ''));
        $spesialisasi = trim(mysqli_real_escape_string($conn, $_POST['spesialisasi'] ?? ''));
        $status       = in_array($_POST['status'] ?? '', ['aktif','nonaktif','cuti']) ? $_POST['status'] : 'aktif';

        if ($nama === '' || $username === '' || $password === '') {
            $modalError = 'Nama, username, dan password wajib diisi.';
        } else {
            $chk = mysqli_query($conn, "SELECT id_user FROM users WHERE username = '$username' LIMIT 1");
            if ($chk && mysqli_num_rows($chk) > 0) {
                $modalError = 'Username sudah digunakan. Pilih username lain.';
            } else {
                mysqli_begin_transaction($conn);
                try {
                    $insUser = mysqli_query($conn, "INSERT INTO users (username, password, role, nama) VALUES ('$username', '$password', 'barber', '$nama')");
                    if (!$insUser) throw new Exception(mysqli_error($conn));
                    $newUserId = mysqli_insert_id($conn);
                    $insBarber = mysqli_query($conn, "INSERT INTO barber (user_id, nama, spesialisasi, status) VALUES ('$newUserId', '$nama', '$spesialisasi', '$status')");
                    if (!$insBarber) throw new Exception(mysqli_error($conn));
                    mysqli_commit($conn);
                    $modalSuccess = "Kapster \"$nama\" berhasil didaftarkan!";
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $modalError = 'Gagal mendaftarkan kapster: ' . $e->getMessage();
                }
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        $nama     = trim(mysqli_real_escape_string($conn, $_POST['nama'] ?? ''));
        $username = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ''));
        $password = trim(mysqli_real_escape_string($conn, $_POST['password'] ?? ''));

        if ($username === '' || $password === '') {
            $modalError = 'Username dan password wajib diisi.';
        } else {
            $chk = mysqli_query($conn, "SELECT id_user FROM users WHERE username = '$username' LIMIT 1");
            if ($chk && mysqli_num_rows($chk) > 0) {
                $modalError = 'Username sudah digunakan. Pilih username lain.';
            } else {
                $ins = mysqli_query($conn, "INSERT INTO users (username, password, role, nama) VALUES ('$username', '$password', 'pelanggan', '$nama')");
                if ($ins) {
                    $modalSuccess = "Pelanggan \"$nama\" berhasil ditambahkan!";
                } else {
                    $modalError = 'Gagal menyimpan data pelanggan.';
                }
            }
        }
    }
    
    // EDIT KAPSTER
    if (isset($_POST['action']) && $_POST['action'] === 'edit_barber') {
        $id_barber = (int)($_POST['id_barber'] ?? 0);
        $nama = trim(mysqli_real_escape_string($conn, $_POST['nama'] ?? ''));
        $username = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';
        $spesialisasi = trim(mysqli_real_escape_string($conn, $_POST['spesialisasi'] ?? ''));
        $status = in_array($_POST['status'] ?? '', ['aktif','nonaktif','cuti']) ? $_POST['status'] : 'aktif';

        if (!$id_barber || $nama === '' || $username === '') {
            $modalError = 'Data kapster tidak valid.';
        } else {
            $owner = mysqli_query($conn, "SELECT user_id FROM barber WHERE id = $id_barber LIMIT 1");
            $barberRow = mysqli_fetch_assoc($owner);
            if (!$barberRow) {
                $modalError = 'Data barber tidak ditemukan.';
            } else {
                $userId = (int) $barberRow['user_id'];
                $chk = mysqli_query($conn, "SELECT id_user FROM users WHERE username = '$username' AND id_user <> $userId LIMIT 1");
                if (mysqli_num_rows($chk) > 0) {
                    $modalError = 'Username sudah digunakan.';
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
                            }
                        }

                        $passQuery = "";
                        if ($password !== '') { $passQuery = ", password = '" . mysqli_real_escape_string($conn, $password) . "'"; }
                        $avatarQuery = "";
                        if ($avatarFileName) { $avatarQuery = ", avatar = '" . mysqli_real_escape_string($conn, $avatarFileName) . "'"; }
                        
                        $updUser = mysqli_query($conn, "UPDATE users SET nama = '$nama', username = '$username' $passQuery $avatarQuery WHERE id_user = $userId AND role = 'barber'");
                        if (!$updUser) throw new Exception(mysqli_error($conn));
                        
                        $updBarber = mysqli_query($conn, "UPDATE barber SET nama = '$nama', spesialisasi = '$spesialisasi', status = '$status' WHERE id = $id_barber");
                        if (!$updBarber) throw new Exception(mysqli_error($conn));
                        
                        mysqli_commit($conn);
                        $modalSuccess = 'Data kapster berhasil diperbarui!';
                    } catch (Exception $e) {
                        mysqli_rollback($conn);
                        $modalError = 'Gagal menyimpan kapster: ' . $e->getMessage();
                    }
                }
            }
        }
    }

    // EDIT PELANGGAN
    if (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
        $id_user = (int)($_POST['id_user'] ?? 0);
        $nama = trim(mysqli_real_escape_string($conn, $_POST['nama'] ?? ''));
        $username = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!$id_user || $nama === '' || $username === '') {
            $modalError = 'Data pelanggan tidak valid.';
        } else {
            $chk = mysqli_query($conn, "SELECT id_user FROM users WHERE username = '$username' AND id_user <> $id_user LIMIT 1");
            if (mysqli_num_rows($chk) > 0) {
                $modalError = 'Username sudah digunakan orang lain.';
            } else {
                $avatarFileName = null;
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/avatars/';
                    if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
                    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                        $avatarFileName = 'avatar_' . $id_user . '_' . time() . '.' . $ext;
                        move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatarFileName);
                    }
                }
                
                $passQuery = "";
                if ($password !== '') { $passQuery = ", password = '" . mysqli_real_escape_string($conn, $password) . "'"; }
                $avatarQuery = "";
                if ($avatarFileName) { $avatarQuery = ", avatar = '" . mysqli_real_escape_string($conn, $avatarFileName) . "'"; }
                
                $updUser = mysqli_query($conn, "UPDATE users SET nama = '$nama', username = '$username' $passQuery $avatarQuery WHERE id_user = $id_user AND role = 'pelanggan'");
                if ($updUser) {
                    $modalSuccess = 'Data pelanggan berhasil diperbarui!';
                } else {
                    $modalError = 'Gagal menyimpan perubahan pelanggan.';
                }
            }
        }
    }
}
?>
<?php admin_header('Pengguna (Users)', 'pengguna'); ?>
    <div class="p-md">
        <!-- Page Header -->
        <div class="flex flex-wrap justify-between items-start gap-3 mb-lg mt-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Manajemen Pengguna</h1>
                <p class="text-on-surface-variant">Kelola data seluruh akun kapster, pelanggan, dan status operasional sistem</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button onclick="document.getElementById('modalTambahPelanggan').classList.remove('hidden')"
                    class="bg-surface text-primary border border-primary font-bold px-4 py-2 rounded flex items-center gap-xs hover:bg-primary/10 transition-colors">
                    <span class="material-symbols-outlined text-sm">person_add</span>
                    Tambah Pelanggan
                </button>
                <button onclick="document.getElementById('modalTambahKapster').classList.remove('hidden')"
                    class="bg-primary text-on-primary font-bold px-4 py-2 rounded flex items-center gap-xs hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-sm">content_cut</span>
                    Tambah Kapster
                </button>
            </div>
        </div>

        <?php if (!empty($customerNotice)): ?>
            <div class="mb-6 p-4 rounded-lg bg-surface-container-high border border-outline-variant text-on-surface-variant flex items-center gap-3">
                <span class="material-symbols-outlined text-primary">info</span>
                <span class="font-bold text-sm text-on-surface"><?= htmlspecialchars($customerNotice); ?></span>
            </div>
        <?php endif; ?>

        <!-- Statistics Overview -->
        <section class="grid grid-cols-2 gap-4 md:grid-cols-4 mb-lg">
            <article class="bg-surface-container border border-outline-variant p-4 rounded-xl shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Total Pelanggan</p>
                <div class="flex justify-between items-center mt-2">
                    <h2 class="text-2xl font-bold font-headline-md text-on-surface"><?= $adminDashboardStats['totalUsers']; ?></h2>
                    <span class="material-symbols-outlined text-outline-variant">person</span>
                </div>
            </article>
            <article class="bg-surface-container border border-outline-variant p-4 rounded-xl shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Status Kapster</p>
                <div class="flex justify-between items-center mt-2">
                    <h2 class="text-2xl font-bold font-headline-md text-on-surface"><span class="text-primary"><?= $adminDashboardStats['activeBarbers']; ?></span> / <?= mysqli_num_rows($adminBarbers); ?></h2>
                    <span class="material-symbols-outlined text-outline-variant">content_cut</span>
                </div>
            </article>
            <article class="bg-surface-container border border-outline-variant p-4 rounded-xl shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Antrean Menunggu</p>
                <div class="flex justify-between items-center mt-2">
                    <h2 class="text-2xl font-bold font-headline-md text-on-surface"><?= $adminDashboardStats['liveQueue']; ?></h2>
                    <span class="material-symbols-outlined text-outline-variant">group</span>
                </div>
            </article>
            <article class="bg-surface-container border border-outline-variant p-4 flex flex-col items-start justify-center rounded-xl shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Akses Cepat</p>
                <a href="backup_database.php" class="mt-3 text-sm font-semibold text-on-surface-variant hover:text-primary underline flex items-center gap-1 transition-colors">
                    <span class="material-symbols-outlined text-xs">download</span> Backup Database
                </a>
            </article>
        </section>

        <?php
        // Prepare chart data
        $barberChartNames = [];
        $barberChartSessions = [];
        if ($adminBarbers && mysqli_num_rows($adminBarbers) > 0) {
            mysqli_data_seek($adminBarbers, 0);
            while ($b = mysqli_fetch_assoc($adminBarbers)) {
                $barberChartNames[] = $b['nama'] ?: 'Kapster';
                $barberChartSessions[] = (int) $b['sesi_selesai'];
            }
        }
        $totalPelanggan = (int)$adminDashboardStats['totalUsers'];
        $totalBarber = mysqli_num_rows($adminBarbers);
        ?>

        <!-- Charts Section -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-lg">
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm">
                <div class="mb-4">
                    <h3 class="font-headline-md text-lg text-primary">Kinerja Kapster</h3>
                    <p class="text-xs text-on-surface-variant font-bold uppercase tracking-widest">Sesi Selesai Hari Ini</p>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="barberPerformanceChart"></canvas>
                </div>
            </article>
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm">
                <div class="mb-4">
                    <h3 class="font-headline-md text-lg text-primary">Komposisi Pengguna</h3>
                    <p class="text-xs text-on-surface-variant font-bold uppercase tracking-widest">Pelanggan vs Kapster</p>
                </div>
                <div class="relative h-64 w-full flex justify-center">
                    <canvas id="userCompositionChart"></canvas>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-6 lg:grid-cols-12 mb-lg">
            <!-- Kapster Table -->
            <article class="bg-surface-container border border-outline-variant rounded-xl shadow-lg lg:col-span-12">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 border-b border-outline-variant bg-surface-container-low">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary/20 text-primary border border-primary/30 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined">content_cut</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold font-headline-md text-primary">Tim Kapster / Barber</h2>
                            <p class="mt-1 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Manajemen status operasional</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 overflow-x-auto">
                    <table id="kapsterTable" class="w-full text-left">
                        <thead>
                            <tr>
                                <th class="px-3 py-4">ID Stasiun</th>
                                <th class="px-3 py-4">Nama Kapster</th>
                                <th class="px-3 py-4">Sesi Selesai</th>
                                <th class="px-3 py-4">Pengaturan Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($adminBarbers && mysqli_num_rows($adminBarbers) > 0): ?>
                                <?php mysqli_data_seek($adminBarbers, 0); $station = 1; while ($barber = mysqli_fetch_assoc($adminBarbers)): $isActive = (strtolower($barber['status']) === 'aktif'); ?>
                                    <tr>
                                        <td class="px-3 py-3 text-primary font-bold">Stasiun <?= str_pad((string)$station++, 2, '0', STR_PAD_LEFT); ?></td>
                                        <td class="px-3 py-3">
                                            <p class="text-sm font-bold text-on-surface"><?= htmlspecialchars($barber['nama'] ?: 'Kapster'); ?></p>
                                            <p class="text-[10px] font-bold text-on-surface-variant mt-1 uppercase tracking-widest"><?= htmlspecialchars($barber['spesialisasi'] ?: '-'); ?></p>
                                        </td>
                                        <td class="px-3 py-3 text-xl font-bold text-primary"><?= (int) $barber['sesi_selesai']; ?></td>
                                        <td class="px-3 py-3">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <form method="post" class="flex items-center gap-2 m-0 p-1 rounded inline-flex border border-outline-variant bg-surface-container-low" action="update_barber_status.php">
                                                    <input type="hidden" name="barber_id" value="<?= (int) $barber['id']; ?>">
                                                    <input type="hidden" name="redirect" value="pengguna.php">
                                                    <select name="status" class="bg-transparent border-none text-xs font-semibold focus:ring-0 text-primary py-1 pr-6 uppercase tracking-wider">
                                                        <option value="aktif" <?= $isActive ? 'selected' : ''; ?> class="bg-surface text-on-surface">AKTIF</option>
                                                        <option value="nonaktif" <?= !$isActive ? 'selected' : ''; ?> class="bg-surface text-error">TIDAK AKTIF</option>
                                                    </select>
                                                    <button type="submit" class="bg-primary/20 text-primary px-2 py-1 rounded text-[10px] font-bold hover:bg-primary/30 transition shadow-sm border border-primary/20 uppercase tracking-widest">Update</button>
                                                </form>
                                                <button onclick="showDetailKapster(<?= (int)$barber['id']; ?>, <?= htmlspecialchars(json_encode($barber['nama'] ?: 'Kapster'), ENT_QUOTES); ?>, <?= htmlspecialchars(json_encode($barber['spesialisasi'] ?: '-'), ENT_QUOTES); ?>, '<?= $isActive ? 'Aktif' : 'Tidak Aktif'; ?>', <?= (int)$barber['sesi_selesai']; ?>)"
                                                    class="inline-flex h-8 w-8 items-center justify-center border border-outline-variant text-on-surface-variant rounded-lg transition-colors hover:text-primary hover:border-primary hover:bg-surface-container-high" title="Lihat Detail">
                                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                                </button>
                                                <button onclick="openEditKapster(<?= (int)$barber['id']; ?>, <?= htmlspecialchars(json_encode($barber['nama'] ?: ''), ENT_QUOTES); ?>, <?= htmlspecialchars(json_encode($barber['username']), ENT_QUOTES); ?>, <?= htmlspecialchars(json_encode($barber['spesialisasi'] ?: ''), ENT_QUOTES); ?>, '<?= strtolower($barber['status']); ?>')"
                                                    class="inline-flex h-8 w-8 items-center justify-center border border-outline-variant text-on-surface-variant rounded-lg transition-colors hover:text-primary hover:border-primary hover:bg-surface-container-high" title="Edit">
                                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <!-- Pelanggan Table -->
            <article class="bg-surface-container border border-outline-variant rounded-xl shadow-lg lg:col-span-12">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 border-b border-outline-variant bg-surface-container-low">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary/20 text-primary border border-primary/30 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined">group</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold font-headline-md text-primary">Klien & Pelanggan</h2>
                            <p class="mt-1 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Daftar pengguna barbershop</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 overflow-x-auto">
                    <table id="customersTable" class="w-full text-left">
                        <thead>
                            <tr>
                                <th class="px-3 py-4">Informasi Akun</th>
                                <th class="px-3 py-4">Username</th>
                                <th class="px-3 py-4">Role Akses</th>
                                <th class="px-3 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($adminCustomers && mysqli_num_rows($adminCustomers) > 0): ?>
                                <?php mysqli_data_seek($adminCustomers, 0); while ($customer = mysqli_fetch_assoc($adminCustomers)): ?>
                                    <tr>
                                        <td class="px-3 py-3">
                                            <p class="text-sm font-bold text-on-surface"><?= htmlspecialchars($customer['nama'] ?: $customer['username']); ?></p>
                                            <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-bold">ID #<?= str_pad((string) $customer['id_user'], 4, '0', STR_PAD_LEFT); ?></p>
                                        </td>
                                        <td class="px-3 py-3 text-sm font-medium text-primary">@<?= htmlspecialchars($customer['username']); ?></td>
                                        <td class="px-3 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/30 uppercase tracking-wider">Pelanggan</span></td>
                                        <td class="px-3 py-3 text-right">
                                            <div class="inline-flex items-center gap-2">
                                                <button onclick="showDetailPelanggan(<?= (int)$customer['id_user']; ?>, <?= htmlspecialchars(json_encode($customer['nama'] ?: $customer['username']), ENT_QUOTES); ?>, <?= htmlspecialchars(json_encode($customer['username']), ENT_QUOTES); ?>)"
                                                    class="inline-flex h-8 w-8 items-center justify-center border border-outline-variant text-on-surface-variant rounded-lg transition-colors hover:text-primary hover:border-primary hover:bg-surface-container-high" title="Lihat Detail">
                                                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                                                </button>
                                                <button onclick="openEditPelanggan(<?= (int)$customer['id_user']; ?>, <?= htmlspecialchars(json_encode($customer['nama'] ?: ''), ENT_QUOTES); ?>, <?= htmlspecialchars(json_encode($customer['username']), ENT_QUOTES); ?>)"
                                                    class="inline-flex h-8 w-8 items-center justify-center border border-outline-variant text-on-surface-variant rounded-lg transition-colors hover:text-primary hover:border-primary hover:bg-surface-container-high" title="Edit">
                                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    </div>

<!-- ══════════════════════════════════════════
     MODAL — TAMBAH KAPSTER
══════════════════════════════════════════ -->
<div id="modalTambahKapster" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);">
    <div class="bg-surface-container border border-outline-variant rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between p-5 border-b border-outline-variant bg-surface-container-low sticky top-0 rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-primary/20 text-primary border border-primary/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">content_cut</span>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface">Tambah Kapster Baru</h3>
                    <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold">Daftarkan akun & profil kapster</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalTambahKapster').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" class="p-5 space-y-4">
            <input type="hidden" name="action" value="add_barber">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Nama Lengkap *</label>
                    <input name="nama" type="text" required placeholder="Contoh: Andi Saputra"
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Spesialisasi</label>
                    <select name="spesialisasi" class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none">
                        <option value="">— Pilih spesialisasi —</option>
                        <option value="Haircut">Haircut</option>
                        <option value="Shaving">Shaving</option>
                        <option value="Coloring">Coloring</option>
                        <option value="All-Round">All-Round</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Username *</label>
                    <input name="username" type="text" required placeholder="Contoh: andi_cuts"
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Password *</label>
                    <input name="password" type="password" required placeholder="Min. 6 karakter"
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Status Awal</label>
                    <select name="status" class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none">
                        <option value="aktif" selected>Aktif</option>
                        <option value="nonaktif">Tidak Aktif</option>
                        <option value="cuti">Cuti / Off</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-primary text-on-primary font-bold py-2.5 rounded-lg flex items-center justify-center gap-2 text-sm hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined text-[18px]">save</span> Simpan Kapster
                </button>
                <button type="button" onclick="document.getElementById('modalTambahKapster').classList.add('hidden')"
                    class="flex-1 border border-outline-variant text-on-surface-variant font-bold py-2.5 rounded-lg text-sm hover:border-primary hover:text-primary transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — TAMBAH PELANGGAN
══════════════════════════════════════════ -->
<div id="modalTambahPelanggan" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);">
    <div class="bg-surface-container border border-outline-variant rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-outline-variant bg-surface-container-low rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-primary/20 text-primary border border-primary/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">person_add</span>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface">Tambah Pelanggan Baru</h3>
                    <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold">Buat akun pelanggan</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalTambahPelanggan').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" class="p-5 space-y-4">
            <input type="hidden" name="action" value="add_user">
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Nama Lengkap</label>
                <input name="nama" type="text" placeholder="Contoh: Budi Santoso"
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Username *</label>
                <input name="username" type="text" required placeholder="Contoh: budi123"
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Password *</label>
                <input name="password" type="password" required placeholder="Min. 6 karakter"
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-primary text-on-primary font-bold py-2.5 rounded-lg flex items-center justify-center gap-2 text-sm hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined text-[18px]">save</span> Simpan Pelanggan
                </button>
                <button type="button" onclick="document.getElementById('modalTambahPelanggan').classList.add('hidden')"
                    class="flex-1 border border-outline-variant text-on-surface-variant font-bold py-2.5 rounded-lg text-sm hover:border-primary hover:text-primary transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — EDIT KAPSTER
══════════════════════════════════════════ -->
<div id="modalEditKapster" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);">
    <div class="bg-surface-container border border-outline-variant rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between p-5 border-b border-outline-variant bg-surface-container-low sticky top-0 rounded-t-xl z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-primary/20 text-primary border border-primary/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">edit</span>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface">Edit Kapster</h3>
                    <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold">Perbarui Profil Kapster</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalEditKapster').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
            <input type="hidden" name="action" value="edit_barber">
            <input type="hidden" name="id_barber" id="editKapster_id">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Nama Lengkap *</label>
                    <input name="nama" id="editKapster_nama" type="text" required
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Spesialisasi</label>
                    <input name="spesialisasi" id="editKapster_spesialisasi" type="text"
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Username *</label>
                    <input name="username" id="editKapster_username" type="text" required
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Password Baru</label>
                    <input name="password" type="password" placeholder="(Kosongkan jika tidak diubah)"
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Status</label>
                    <select name="status" id="editKapster_status" class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none appearance-none">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Tidak Aktif</option>
                        <option value="cuti">Cuti / Off</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Foto Profil Baru</label>
                    <input name="avatar" type="file" accept="image/png, image/jpeg, image/webp"
                        class="w-full bg-surface border border-outline-variant py-2 px-3 rounded-lg text-on-surface text-xs focus:border-primary outline-none transition-all cursor-pointer">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-primary text-on-primary font-bold py-2.5 rounded-lg flex items-center justify-center gap-2 text-sm hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined text-[18px]">save</span> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — EDIT PELANGGAN
══════════════════════════════════════════ -->
<div id="modalEditPelanggan" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);">
    <div class="bg-surface-container border border-outline-variant rounded-xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-5 border-b border-outline-variant bg-surface-container-low rounded-t-xl z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-primary/20 text-primary border border-primary/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">edit</span>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface">Edit Pelanggan</h3>
                    <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold">Perbarui Profil Pelanggan</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalEditPelanggan').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="id_user" id="editPelanggan_id">
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Nama Lengkap</label>
                <input name="nama" id="editPelanggan_nama" type="text" required
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Username *</label>
                <input name="username" id="editPelanggan_username" type="text" required
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Password Baru</label>
                <input name="password" type="password" placeholder="(Kosongkan jika tidak diubah)"
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Foto Profil Baru</label>
                <input name="avatar" type="file" accept="image/png, image/jpeg, image/webp"
                    class="w-full bg-surface border border-outline-variant py-2 px-3 rounded-lg text-on-surface text-xs focus:border-primary outline-none transition-all cursor-pointer">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-primary text-on-primary font-bold py-2.5 rounded-lg flex items-center justify-center gap-2 text-sm hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined text-[18px]">save</span> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* CSS khusus memperbaiki isu lebar tabel mengecil karena DataTables 2 Flex Layout */
    .dt-container .dt-layout-row:has(table) {
        width: 100% !important;
        flex-basis: 100% !important;
    }
    .dt-container .dt-layout-cell {
        flex-grow: 1 !important;
    }
    .dt-layout-table, table.dataTable {
        width: 100% !important;
        min-width: 100% !important;
    }
    table.dataTable thead th, table.dataTable tbody td {
        white-space: nowrap;
    }
</style>
<script>
    // ── Inisialisasi DataTables ──
    new DataTable('#kapsterTable', {
        pageLength: 10,
        pagingType: 'simple_numbers',
        autoWidth: false,
        lengthMenu: [5, 10, 25],
        language: {
            search: 'Cari Kapster:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada kapster terdaftar',
            paginate: { previous: '‹', next: '›' }
        }
    });

    new DataTable('#customersTable', {
        pageLength: 10,
        pagingType: 'simple_numbers',
        autoWidth: false,
        language: {
            search: 'Cari Pelanggan:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ pelanggan',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada pelanggan terdaftar',
            paginate: { previous: '‹', next: '›' }
        }
    });

    // ── SweetAlert setelah simpan modal ──
    <?php if ($modalError): ?>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error', title: 'Gagal', text: <?= json_encode($modalError); ?>,
            background: '#1e2020', color: '#e2e2e2', confirmButtonColor: '#f2ca50', iconColor: '#ffb4ab'
        });
    });
    <?php endif; ?>
    <?php if ($modalSuccess): ?>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'success', title: 'Berhasil!', text: <?= json_encode($modalSuccess); ?>,
            background: '#1e2020', color: '#e2e2e2', confirmButtonColor: '#f2ca50', iconColor: '#f2ca50'
        });
    });
    <?php endif; ?>

    // ── Detail Kapster ──
    function showDetailKapster(id, nama, spesialisasi, status, sesi) {
        const statusColor = status === 'Aktif' ? '#f2ca50' : '#ffb4ab';
        const statusBg = status === 'Aktif' ? 'rgba(242,202,80,.12)' : 'rgba(147,0,10,.2)';
        const statusBorder = status === 'Aktif' ? 'rgba(242,202,80,.3)' : 'rgba(255,180,171,.3)';
        Swal.fire({
            background: '#1e2020', color: '#e2e2e2', confirmButtonColor: '#f2ca50', confirmButtonText: 'Tutup', width: '420px',
            html: `<div style="text-align:left;font-family:Inter,sans-serif;">
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #4d4635;">
                    <div style="width:52px;height:52px;background:rgba(242,202,80,.15);border:1px solid rgba(242,202,80,.3);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span class="material-symbols-outlined" style="color:#f2ca50;font-size:28px;">content_cut</span>
                    </div>
                    <div>
                        <p style="font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">Barber / Kapster</p>
                        <h2 style="font-size:18px;font-weight:700;color:#e2e2e2;margin:0;">${nama}</h2>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div style="background:#282a2b;border:1px solid #4d4635;border-radius:8px;padding:12px;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">ID Barber</p>
                        <p style="font-size:16px;font-weight:700;color:#f2ca50;margin:0;">#${String(id).padStart(4,'0')}</p>
                    </div>
                    <div style="background:#282a2b;border:1px solid #4d4635;border-radius:8px;padding:12px;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">Sesi Selesai</p>
                        <p style="font-size:16px;font-weight:700;color:#e2e2e2;margin:0;">${sesi} sesi</p>
                    </div>
                    <div style="background:#282a2b;border:1px solid #4d4635;border-radius:8px;padding:12px;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">Spesialisasi</p>
                        <p style="font-size:14px;font-weight:600;color:#e2e2e2;margin:0;">${spesialisasi}</p>
                    </div>
                    <div style="background:#282a2b;border:1px solid #4d4635;border-radius:8px;padding:12px;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">Status</p>
                        <span style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:${statusColor};background:${statusBg};padding:3px 10px;border-radius:4px;border:1px solid ${statusBorder};">${status}</span>
                    </div>
                </div>
            </div>`
        });
    }

    // ── Detail Pelanggan ──
    function showDetailPelanggan(id, nama, username) {
        Swal.fire({
            background: '#1e2020', color: '#e2e2e2', confirmButtonColor: '#f2ca50', confirmButtonText: 'Tutup', width: '380px',
            html: `<div style="text-align:left;font-family:Inter,sans-serif;">
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid #4d4635;">
                    <div style="width:52px;height:52px;background:rgba(242,202,80,.15);border:1px solid rgba(242,202,80,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span class="material-symbols-outlined" style="color:#f2ca50;font-size:28px;">person</span>
                    </div>
                    <div>
                        <p style="font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">Pelanggan</p>
                        <h2 style="font-size:18px;font-weight:700;color:#e2e2e2;margin:0;">${nama}</h2>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div style="background:#282a2b;border:1px solid #4d4635;border-radius:8px;padding:12px;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">ID Pengguna</p>
                        <p style="font-size:16px;font-weight:700;color:#f2ca50;margin:0;">#${String(id).padStart(4,'0')}</p>
                    </div>
                    <div style="background:#282a2b;border:1px solid #4d4635;border-radius:8px;padding:12px;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">Role</p>
                        <span style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#f2ca50;background:rgba(242,202,80,.12);padding:3px 10px;border-radius:4px;border:1px solid rgba(242,202,80,.3);">Pelanggan</span>
                    </div>
                    <div style="background:#282a2b;border:1px solid #4d4635;border-radius:8px;padding:12px;grid-column:1/-1;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">Username</p>
                        <p style="font-size:15px;font-weight:600;color:#e2e2e2;margin:0;">@${username}</p>
                    </div>
                </div>
            </div>`
        });
    }

    // Charts
    const chartOptions = {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#e2e2e2', font: { family: 'Inter', size: 12 } } },
            tooltip: { backgroundColor: '#1a1c1c', titleColor: '#f2ca50', bodyColor: '#e2e2e2', borderColor: '#4d4635', borderWidth: 1 }
        },
        scales: {
            x: { ticks: { color: '#d0c5af' }, grid: { color: '#333535' } },
            y: { ticks: { color: '#d0c5af', stepSize: 1 }, grid: { color: '#333535' }, beginAtZero: true }
        }
    };
    new Chart(document.getElementById('barberPerformanceChart').getContext('2d'), {
        type: 'bar',
        data: { labels: <?= json_encode($barberChartNames); ?>, datasets: [{ label: 'Sesi Selesai', data: <?= json_encode($barberChartSessions); ?>, backgroundColor: 'rgba(242, 202, 80, 0.8)', borderColor: '#f2ca50', borderWidth: 1, borderRadius: 4 }] },
        options: chartOptions
    });
    new Chart(document.getElementById('userCompositionChart').getContext('2d'), {
        type: 'doughnut',
        data: { labels: ['Pelanggan', 'Kapster'], datasets: [{ data: [<?= $totalPelanggan; ?>, <?= $totalBarber; ?>], backgroundColor: ['#f2ca50', '#474746'], borderColor: ['#121414', '#121414'], borderWidth: 2, hoverOffset: 4 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { position: 'right', labels: { color: '#e2e2e2', font: { family: 'Inter', size: 12 } } } } }
    });

    // ── Open Edit Kapster ──
    function openEditKapster(id, nama, username, spesialisasi, status) {
        document.getElementById('editKapster_id').value = id;
        document.getElementById('editKapster_nama').value = nama;
        document.getElementById('editKapster_username').value = username;
        document.getElementById('editKapster_spesialisasi').value = spesialisasi;
        document.getElementById('editKapster_status').value = status.toLowerCase();
        document.getElementById('modalEditKapster').classList.remove('hidden');
    }

    // ── Open Edit Pelanggan ──
    function openEditPelanggan(id, nama, username) {
        document.getElementById('editPelanggan_id').value = id;
        document.getElementById('editPelanggan_nama').value = nama;
        document.getElementById('editPelanggan_username').value = username;
        document.getElementById('modalEditPelanggan').classList.remove('hidden');
    }

    // Close modal on backdrop click
    ['modalTambahKapster','modalTambahPelanggan','modalEditKapster','modalEditPelanggan'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', function(e) { if (e.target === el) el.classList.add('hidden'); });
    });
</script>
<?php admin_footer('pengguna'); ?>
