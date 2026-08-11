<?php
include '_bootstrap.php';
include '_chrome.php';

function admin_service_card_image(array $service): string
{
    $stored = trim((string) ($service['gambar'] ?? ''));
    if ($stored !== '') {
        $local = '../' . ltrim($stored, '/');
        if (is_file(__DIR__ . '/../' . ltrim($stored, '/'))) {
            return $local;
        }
    }
    return '';
}

// ─────────────────────────────────────────────
// HANDLE POST: Tambah Layanan (inline modal)
// ─────────────────────────────────────────────
$modalError   = $_GET['error'] ?? '';
$modalSuccess = $_GET['success'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
    $modalError = "Gagal memproses form. Ukuran file yang Anda upload terlalu besar dan melampaui batas server PHP (post_max_size). Silakan kompresi gambar Anda di bawah " . ini_get('post_max_size') . ".";
}

admin_ensure_layanan_image_column($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_layanan') {
    $nama      = trim((string) ($_POST['nama_layanan'] ?? ''));
    $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
    $harga     = (int) ($_POST['harga'] ?? 0);
    $durasi    = (int) ($_POST['durasi'] ?? 0);
    $imagePath = '';

    if ($nama === '' || $harga <= 0 || $durasi <= 0) {
        $modalError = 'Nama layanan, harga, dan durasi wajib diisi dengan benar.';
    } else {
        // Handle optional image upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
                $modalError = 'Upload gagal (Ukuran terlalu besar atau file rusak). Kode: ' . $_FILES['gambar']['error'];
            } else {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo(basename((string)$_FILES['gambar']['name']), PATHINFO_EXTENSION));
                $uploadDir = __DIR__ . '/../uploads/layanan';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
                if (!in_array($ext, $allowed, true)) {
                    $modalError = 'Format gambar harus JPG, PNG, atau WEBP.';
                } else {
                    $safeName = 'layanan-' . preg_replace('/[^a-z0-9]+/i','-',strtolower($nama)) . '-' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir . '/' . $safeName)) {
                        $imagePath = 'uploads/layanan/' . $safeName;
                    } else {
                        $modalError = 'Upload gambar gagal.';
                    }
                }
            }
        }

        if ($modalError === '') {
            $stmt = mysqli_prepare($conn, 'INSERT INTO layanan (nama_layanan, deskripsi, harga, durasi, gambar) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssiis', $nama, $deskripsi, $harga, $durasi, $imagePath);
            if (mysqli_stmt_execute($stmt)) {
                $modalSuccess = "Layanan \"$nama\" berhasil ditambahkan!";
                // Refresh data
                $adminServices = mysqli_query($conn, 'SELECT * FROM layanan ORDER BY id DESC');
            } else {
                $modalError = 'Gagal menyimpan layanan: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_layanan') {
    $id = (int)($_POST['id_layanan'] ?? 0);
    $nama = trim((string) ($_POST['nama_layanan'] ?? ''));
    $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
    $harga = (int) ($_POST['harga'] ?? 0);
    $durasi = (int) ($_POST['durasi'] ?? 0);
    $imagePath = '';

    if ($id <= 0 || $nama === '' || $harga <= 0 || $durasi <= 0) {
        $modalError = 'Data layanan tidak valid.';
    } else {
        // Handle optional image upload
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {
                $modalError = 'Upload gagal (Ukuran terlalu besar atau file rusak). Kode: ' . $_FILES['gambar']['error'];
            } else {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo(basename((string)$_FILES['gambar']['name']), PATHINFO_EXTENSION));
                $uploadDir = __DIR__ . '/../uploads/layanan';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
                if (!in_array($ext, $allowed, true)) {
                    $modalError = 'Format gambar harus JPG, PNG, atau WEBP.';
                } else {
                    $safeName = 'layanan-' . preg_replace('/[^a-z0-9]+/i','-',strtolower($nama)) . '-' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadDir . '/' . $safeName)) {
                        $imagePath = 'uploads/layanan/' . $safeName;
                    } else {
                        $modalError = 'Upload gambar gagal.';
                    }
                }
            }
        }

        if ($modalError === '') {
            if ($imagePath !== '') {
                $stmt = mysqli_prepare($conn, 'UPDATE layanan SET nama_layanan=?, deskripsi=?, harga=?, durasi=?, gambar=? WHERE id=?');
                mysqli_stmt_bind_param($stmt, 'ssiisi', $nama, $deskripsi, $harga, $durasi, $imagePath, $id);
            } else {
                $stmt = mysqli_prepare($conn, 'UPDATE layanan SET nama_layanan=?, deskripsi=?, harga=?, durasi=? WHERE id=?');
                mysqli_stmt_bind_param($stmt, 'ssiii', $nama, $deskripsi, $harga, $durasi, $id);
            }
            if (mysqli_stmt_execute($stmt)) {
                $modalSuccess = "Layanan \"$nama\" berhasil diperbarui!";
                $adminServices = mysqli_query($conn, 'SELECT * FROM layanan ORDER BY id DESC');
            } else {
                $modalError = 'Gagal menyimpan layanan: ' . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>
<?php admin_header('Layanan & Harga', 'layanan'); ?>
    <div class="p-md space-y-8">
        <div class="flex flex-wrap justify-between items-start gap-3 mb-lg mt-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Layanan & Harga</h1>
                <p class="text-on-surface-variant">Manajemen menu layanan barbershop</p>
            </div>
            <button onclick="document.getElementById('modalTambahLayanan').classList.remove('hidden')"
                class="bg-primary text-on-primary font-bold px-4 py-2 rounded flex items-center gap-xs hover:opacity-90 transition-opacity">
                <span class="material-symbols-outlined text-sm">add</span>
                Tambah Layanan
            </button>
        </div>

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Jumlah Layanan</p>
                <h2 class="mt-2 text-2xl font-bold font-headline-md text-primary"><?= $adminDashboardStats['totalServices']; ?></h2>
            </article>
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Pendapatan Bulan Ini</p>
                <h2 class="mt-2 text-2xl font-bold font-headline-md text-primary">Rp <?= number_format($adminDashboardStats['revenueMonth'], 0, ',', '.'); ?></h2>
            </article>
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Pelanggan Aktif</p>
                <h2 class="mt-2 text-2xl font-bold font-headline-md text-primary"><?= $adminDashboardStats['totalUsers']; ?></h2>
            </article>
        </section>

        <section class="bg-surface-container border border-outline-variant rounded-xl shadow-lg mb-lg">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 border-b border-outline-variant bg-surface-container-low">
                <div>
                    <h2 class="text-xl font-bold font-headline-md text-primary">Daftar Layanan</h2>
                    <p class="mt-1 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Data lengkap layanan barbershop</p>
                </div>
                <button onclick="window.print()" class="mt-3 sm:mt-0 border border-outline-variant bg-surface rounded-lg px-4 py-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-on-surface hover:text-primary hover:border-primary transition-colors no-print shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">print</span> Cetak
                </button>
            </div>
            <div class="p-4 overflow-x-auto">
                <table id="servicesTable" class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="px-3 py-4">Foto</th>
                            <th class="px-3 py-4">Layanan</th>
                            <th class="px-3 py-4">Deskripsi</th>
                            <th class="px-3 py-4">Harga</th>
                            <th class="px-3 py-4">Durasi</th>
                            <th class="px-3 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($adminServices && mysqli_num_rows($adminServices) > 0): ?>
                            <?php mysqli_data_seek($adminServices, 0); while ($service = mysqli_fetch_assoc($adminServices)): ?>
                                <?php $serviceImage = admin_service_card_image($service); ?>
                                <tr class="hover:bg-secondary-container/30 transition-colors">
                                    <td class="px-3 py-3">
                                        <div class="h-14 w-20 overflow-hidden border border-outline-variant bg-surface rounded-lg flex items-center justify-center">
                                            <?php if ($serviceImage !== ''): ?>
                                                <img src="<?= htmlspecialchars($serviceImage); ?>" alt="<?= htmlspecialchars($service['nama_layanan']); ?>" class="h-full w-full object-cover">
                                            <?php else: ?>
                                                <span class="material-symbols-outlined text-outline-variant text-[24px]">inventory_2</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <p class="text-sm font-bold text-on-surface"><?= htmlspecialchars($service['nama_layanan']); ?></p>
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant mt-1">ID #<?= (int) $service['id']; ?></p>
                                    </td>
                                    <td class="px-3 py-3 text-sm leading-6 text-on-surface-variant" style="max-width:200px;">
                                        <span class="line-clamp-2"><?= htmlspecialchars($service['deskripsi'] ?: '—'); ?></span>
                                    </td>
                                    <td class="px-3 py-3 text-sm font-bold text-primary whitespace-nowrap">Rp <?= number_format((int) $service['harga'], 0, ',', '.'); ?></td>
                                    <td class="px-3 py-3 text-sm font-bold text-on-surface whitespace-nowrap"><?= (int) $service['durasi']; ?> <span class="font-normal text-on-surface-variant text-xs">menit</span></td>
                                    <td class="px-3 py-3 text-right">
                                        <div class="inline-flex items-center gap-2">
                                            <button onclick="showDetailLayanan(<?= (int)$service['id']; ?>, <?= htmlspecialchars(json_encode($service['nama_layanan']), ENT_QUOTES); ?>, <?= htmlspecialchars(json_encode($service['deskripsi'] ?: '-'), ENT_QUOTES); ?>, <?= (int)$service['harga']; ?>, <?= (int)$service['durasi']; ?>, <?= htmlspecialchars(json_encode($serviceImage), ENT_QUOTES); ?>)"
                                                class="inline-flex h-8 w-8 items-center justify-center border border-outline-variant text-on-surface-variant rounded-lg transition-colors hover:text-primary hover:border-primary hover:bg-surface-container-high" title="Lihat Detail">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            </button>
                                            <button onclick="openEditLayanan(<?= (int)$service['id']; ?>, <?= htmlspecialchars(json_encode($service['nama_layanan']), ENT_QUOTES); ?>, <?= htmlspecialchars(json_encode($service['deskripsi'] ?: ''), ENT_QUOTES); ?>, <?= (int)$service['harga']; ?>, <?= (int)$service['durasi']; ?>)"
                                                class="inline-flex h-8 w-8 items-center justify-center border border-outline-variant text-on-surface-variant rounded-lg transition-colors hover:text-primary hover:border-primary hover:bg-surface-container-high" title="Edit">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </button>
                                            <a href="hapus_layanan.php?id=<?= (int) $service['id']; ?>"
                                                class="inline-flex h-8 w-8 items-center justify-center border border-outline-variant text-error rounded-lg transition-colors hover:bg-error/10 hover:border-error" title="Hapus"
                                                onclick="return confirm('Hapus layanan ini?');">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

<?php admin_render_mobile_nav('layanan'); ?>

<!-- ══════════════════════════════════════════
     MODAL — TAMBAH LAYANAN
══════════════════════════════════════════ -->
<div id="modalTambahLayanan" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.75);backdrop-filter:blur(4px);">
    <div class="bg-surface-container border border-outline-variant rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between p-5 border-b border-outline-variant bg-surface-container-low sticky top-0 rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-primary/20 text-primary border border-primary/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface">Tambah Layanan Baru</h3>
                    <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold">Daftarkan layanan barbershop</p>
                </div>
            </div>
            <button onclick="document.getElementById('modalTambahLayanan').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
            <input type="hidden" name="action" value="add_layanan">
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Nama Layanan *</label>
                <input name="nama_layanan" type="text" required placeholder="Contoh: Signature Cut + Beard Trim"
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Deskripsi</label>
                <textarea name="deskripsi" rows="3" placeholder="Jelaskan layanan secara singkat..."
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all custom-scrollbar resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Harga (Rp) *</label>
                    <input name="harga" type="number" min="1000" required placeholder="75000"
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Durasi (menit) *</label>
                    <input name="durasi" type="number" min="5" required placeholder="30"
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Foto Layanan (opsional)</label>
                <input name="gambar" type="file" accept=".jpg,.jpeg,.png,.webp"
                    class="block w-full text-sm text-on-surface-variant file:mr-4 file:border-0 file:bg-primary file:px-4 file:py-2 file:text-[10px] file:font-bold file:uppercase file:tracking-widest file:text-on-primary hover:file:opacity-90 rounded-lg overflow-hidden border border-outline-variant">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 bg-primary text-on-primary font-bold py-2.5 rounded-lg flex items-center justify-center gap-2 text-sm hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined text-[18px]">save</span> Simpan Layanan
                </button>
                <button type="button" onclick="document.getElementById('modalTambahLayanan').classList.add('hidden')"
                    class="flex-1 border border-outline-variant text-on-surface-variant font-bold py-2.5 rounded-lg text-sm hover:border-primary hover:text-primary transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════
     MODAL — EDIT LAYANAN
══════════════════════════════════════════ -->
<div id="modalEditLayanan" class="hidden fixed inset-0 z-[60] flex items-center justify-center p-4" style="background:rgba(0,0,0,0.75);backdrop-filter:blur(4px);">
    <div class="bg-surface-container border border-outline-variant rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto custom-scrollbar">
        <div class="flex items-center justify-between p-5 border-b border-outline-variant bg-surface-container-low sticky top-0 rounded-t-xl z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-primary/20 text-primary border border-primary/30 rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">edit</span>
                </div>
                <div>
                    <h3 class="font-bold text-on-surface">Edit Layanan</h3>
                    <p class="text-[10px] uppercase tracking-widest text-on-surface-variant font-bold">Perbarui data layanan barbershop</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalEditLayanan').classList.add('hidden')" class="w-8 h-8 flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container-high rounded-lg transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" enctype="multipart/form-data" class="p-5 space-y-4">
            <input type="hidden" name="action" value="edit_layanan">
            <input type="hidden" name="id_layanan" id="editLayanan_id">
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Nama Layanan *</label>
                <input name="nama_layanan" id="editLayanan_nama" type="text" required placeholder="Contoh: Signature Cut + Beard Trim"
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Deskripsi</label>
                <textarea name="deskripsi" id="editLayanan_deskripsi" rows="3" placeholder="Jelaskan layanan secara singkat..."
                    class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all custom-scrollbar resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Harga (Rp) *</label>
                    <input name="harga" id="editLayanan_harga" type="number" min="1000" required placeholder="75000"
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
                <div>
                    <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Durasi (menit) *</label>
                    <input name="durasi" id="editLayanan_durasi" type="number" min="5" required placeholder="30"
                        class="w-full bg-surface border border-outline-variant py-2.5 px-3 rounded-lg text-on-surface text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Foto Layanan Baru (opsional)</label>
                <input name="gambar" type="file" accept=".jpg,.jpeg,.png,.webp"
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
    new DataTable('#servicesTable', {
        pageLength: 10,
        pagingType: 'simple_numbers',
        autoWidth: false,
        language: {
            search: 'Cari Layanan:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ layanan',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada layanan terdaftar',
            paginate: { previous: '‹', next: '›' }
        }
    });

    <?php if ($modalError): ?>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error', title: 'Gagal', text: <?= json_encode($modalError); ?>,
            background: '#1e2020', color: '#e2e2e2', confirmButtonColor: '#f2ca50', iconColor: '#ffb4ab'
        });
        
        <?php if (isset($_POST['action']) && $_POST['action'] === 'edit_layanan'): ?>
            document.getElementById('modalEditLayanan').classList.remove('hidden');
        <?php elseif (isset($_POST['action']) && $_POST['action'] === 'add_layanan'): ?>
            document.getElementById('modalTambahLayanan').classList.remove('hidden');
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0): ?>
            document.getElementById('modalTambahLayanan').classList.remove('hidden');
        <?php endif; ?>
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

    function showDetailLayanan(id, nama, deskripsi, harga, durasi, foto) {
        const hargaFormatted = 'Rp ' + harga.toLocaleString('id-ID');
        
        let imageHtml = '';
        if (foto && foto.trim() !== '') {
            imageHtml = `<img src="${foto}" alt="${nama}" style="width:100%;height:180px;object-fit:cover;border-radius:4px;margin-bottom:16px;border:1px solid #4d4635;">`;
        } else {
            imageHtml = `<div style="width:100%;height:180px;background:#282a2b;border-radius:4px;margin-bottom:16px;border:1px solid #4d4635;display:flex;align-items:center;justify-content:center;"><span class="material-symbols-outlined" style="color:#99907c;font-size:48px;">inventory_2</span></div>`;
        }
        
        Swal.fire({
            background: '#1e2020', color: '#e2e2e2', confirmButtonColor: '#f2ca50', confirmButtonText: 'Tutup', width: '480px',
            html: `<div style="text-align:left; font-family:Inter,sans-serif;">
                ${imageHtml}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#99907c;">ID #${id}</span>
                    <span style="font-size:12px;font-weight:700;letter-spacing:.1em;color:#f2ca50;background:rgba(242,202,80,.12);padding:2px 10px;border-radius:4px;border:1px solid rgba(242,202,80,.3);">${durasi} menit</span>
                </div>
                <h2 style="font-size:20px;font-weight:700;color:#e2e2e2;margin:0 0 8px;">${nama}</h2>
                <p style="font-size:14px;color:#d0c5af;line-height:1.6;margin:0 0 16px;">${deskripsi}</p>
                <div style="border-top:1px solid #4d4635;padding-top:12px;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#99907c;">Harga Layanan</span>
                    <span style="font-size:22px;font-weight:800;color:#f2ca50;">${hargaFormatted}</span>
                </div>
            </div>`
        });
    }

    // Close modal on backdrop click
    ['modalTambahLayanan', 'modalEditLayanan'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', function(e) { if (e.target === el) el.classList.add('hidden'); });
    });

    function openEditLayanan(id, nama, deskripsi, harga, durasi) {
        document.getElementById('editLayanan_id').value = id;
        document.getElementById('editLayanan_nama').value = nama;
        document.getElementById('editLayanan_deskripsi').value = deskripsi || '';
        document.getElementById('editLayanan_harga').value = harga;
        document.getElementById('editLayanan_durasi').value = durasi;
        document.getElementById('modalEditLayanan').classList.remove('hidden');
    }
</script>
<?php admin_footer('layanan'); ?>
