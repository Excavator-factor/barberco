<?php
include '../config/database.php';
include '../config/helper.php';
check_login('admin');

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_chrome.php';

admin_ensure_layanan_image_column($conn);

$serviceId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$isEdit = $serviceId > 0;
$error = '';
$success = '';
$service = [
    'nama_layanan' => '',
    'deskripsi' => '',
    'harga' => '',
    'durasi' => '',
    'gambar' => '',
];

if ($isEdit) {
    $stmt = mysqli_prepare($conn, 'SELECT id, nama_layanan, deskripsi, harga, durasi, gambar FROM layanan WHERE id = ? LIMIT 1');
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $serviceId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && ($row = mysqli_fetch_assoc($result))) {
            $service = array_merge($service, $row);
        }
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serviceId = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT) ?: 0;
    $isEdit = $serviceId > 0;

    $nama = trim((string) ($_POST['nama_layanan'] ?? ''));
    $deskripsi = trim((string) ($_POST['deskripsi'] ?? ''));
    $harga = (int) ($_POST['harga'] ?? 0);
    $durasi = (int) ($_POST['durasi'] ?? 0);
    $existingImage = trim((string) ($_POST['existing_gambar'] ?? ''));
    $imagePath = $existingImage;

    if ($nama === '' || $harga <= 0 || $durasi <= 0) {
        $error = 'Nama layanan, harga, dan durasi wajib diisi dengan benar.';
    } else {
        $uploadDir = __DIR__ . '/../uploads/layanan';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        if (!empty($_FILES['gambar']['name']) && is_uploaded_file($_FILES['gambar']['tmp_name'])) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $original = basename((string) $_FILES['gambar']['name']);
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) {
                $error = 'Format gambar harus JPG, PNG, atau WEBP.';
            } else {
                $safeName = 'layanan-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($nama)) . '-' . time() . '.' . $ext;
                $destination = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $destination)) {
                    $imagePath = 'uploads/layanan/' . $safeName;
                    if ($existingImage !== '' && $existingImage !== $imagePath) {
                        $oldPath = __DIR__ . '/../' . ltrim($existingImage, '/');
                        if (is_file($oldPath)) {
                            @unlink($oldPath);
                        }
                    }
                } else {
                    $error = 'Upload gambar gagal. Coba file lain.';
                }
            }
        }

        if ($error === '') {
            if ($isEdit) {
                $stmt = mysqli_prepare($conn, 'UPDATE layanan SET nama_layanan = ?, deskripsi = ?, harga = ?, durasi = ?, gambar = ? WHERE id = ?');
                mysqli_stmt_bind_param($stmt, 'ssiisi', $nama, $deskripsi, $harga, $durasi, $imagePath, $serviceId);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                header('Location: layanan.php');
                exit;
            }

            $stmt = mysqli_prepare($conn, 'INSERT INTO layanan (nama_layanan, deskripsi, harga, durasi, gambar) VALUES (?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'ssiis', $nama, $deskripsi, $harga, $durasi, $imagePath);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            header('Location: layanan.php');
            exit;
        }
    }

    $service = [
        'nama_layanan' => $nama,
        'deskripsi' => $deskripsi,
        'harga' => $harga,
        'durasi' => $durasi,
        'gambar' => $imagePath,
    ];
}

$pageTitle = $isEdit ? 'Edit Layanan' : 'Tambah Layanan';
?>
<?php admin_header($pageTitle, 'layanan'); ?>

<div class="p-md space-y-8">
    <div class="flex justify-between items-end mb-lg mt-4">
        <div>
            <h1 class="font-headline-lg text-headline-lg text-on-surface"><?= htmlspecialchars($pageTitle); ?></h1>
            <p class="text-on-surface-variant">Tambahkan data layanan, deskripsi, harga, durasi, dan foto.</p>
        </div>
        <a href="layanan.php" class="bg-surface-container-high border border-outline-variant text-on-surface font-bold px-4 py-2 rounded flex items-center gap-xs hover:border-primary transition-colors no-underline">
            Batal & Kembali
        </a>
    </div>

    <?php if ($error): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: '<?= htmlspecialchars($error, ENT_QUOTES); ?>',
                    background: '#1e2020',
                    color: '#e2e2e2',
                    confirmButtonColor: '#f2ca50',
                    iconColor: '#ffb4ab'
                });
            });
        </script>
    <?php endif; ?>

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <section class="bg-surface-container border border-outline-variant overflow-hidden rounded-xl shadow-lg">
            <div class="border-b border-outline-variant p-6 bg-surface-container-low">
                <h2 class="font-headline-md text-xl font-bold text-primary">Informasi Layanan</h2>
            </div>
            <form method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
                <input type="hidden" name="service_id" value="<?= (int) $serviceId; ?>">
                <input type="hidden" name="existing_gambar" value="<?= htmlspecialchars((string) $service['gambar']); ?>">
                
                <div>
                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant" for="nama_layanan">Nama Layanan</label>
                    <input id="nama_layanan" name="nama_layanan" type="text" required value="<?= htmlspecialchars((string) $service['nama_layanan']); ?>" class="w-full bg-surface-container-lowest border border-outline-variant py-3 px-4 rounded-lg font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all" placeholder="Contoh: Signature Cut + Beard Trim">
                </div>
                
                <div>
                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant" for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" class="w-full bg-surface-container-lowest border border-outline-variant py-3 px-4 rounded-lg font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all custom-scrollbar" placeholder="Jelaskan layanan..."><?= htmlspecialchars((string) $service['deskripsi']); ?></textarea>
                </div>
                
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant" for="harga">Harga</label>
                        <input id="harga" name="harga" type="number" min="0" required value="<?= htmlspecialchars((string) $service['harga']); ?>" class="w-full bg-surface-container-lowest border border-outline-variant py-3 px-4 rounded-lg font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all" placeholder="150000">
                    </div>
                    <div>
                        <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant" for="durasi">Durasi (menit)</label>
                        <input id="durasi" name="durasi" type="number" min="0" required value="<?= htmlspecialchars((string) $service['durasi']); ?>" class="w-full bg-surface-container-lowest border border-outline-variant py-3 px-4 rounded-lg font-body-md text-on-surface focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all" placeholder="45">
                    </div>
                </div>
                
                <div>
                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-widest text-on-surface-variant" for="gambar">Foto Layanan</label>
                    <input id="gambar" name="gambar" type="file" accept=".jpg,.jpeg,.png,.webp" class="block w-full text-sm text-on-surface-variant file:mr-4 file:border-0 file:bg-primary file:px-4 file:py-3 file:text-[10px] file:font-bold file:uppercase file:tracking-widest file:text-on-primary hover:file:opacity-90 rounded overflow-hidden">
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-4">
                    <button type="submit" class="bg-primary px-6 py-3 rounded-lg text-[12px] font-bold uppercase tracking-widest text-on-primary shadow-lg hover:brightness-110 transition-all flex hidden-items-center gap-2">
                        <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Layanan'; ?>
                        <span class="material-symbols-outlined text-sm">save</span>
                    </button>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <article class="bg-surface-container border border-outline-variant overflow-hidden rounded-xl shadow-lg">
                <div class="border-b border-outline-variant p-5 bg-surface-container-low">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant">Preview Foto</p>
                </div>
                <div class="p-5">
                    <div class="relative aspect-[4/3] overflow-hidden border border-outline-variant bg-surface-container-lowest rounded">
                        <?php if (!empty($service['gambar']) && is_file(__DIR__ . '/../' . ltrim((string) $service['gambar'], '/'))): ?>
                            <img src="../<?= htmlspecialchars(ltrim((string) $service['gambar'], '/')); ?>" alt="Preview layanan" class="h-full w-full object-cover">
                        <?php else: ?>
                            <div class="flex flex-col h-full items-center justify-center text-sm text-outline gap-2">
                                <span class="material-symbols-outlined text-4xl opacity-50">image_not_supported</span>
                                <span>Belum ada foto</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            
            <article class="bg-surface-container border border-outline-variant overflow-hidden rounded-xl p-5 border-l-4 border-l-primary">
                <p class="text-[10px] font-bold uppercase tracking-widest text-primary">Catatan</p>
                <p class="mt-3 text-sm leading-6 text-on-surface-variant">
                    Foto yang diunggah otomatis dioptimisasi dan dipakai di daftar layanan serta menjadi visual utama pada menu pengunjung (landing page).
                </p>
            </article>
        </aside>
    </div>
</div>

<?php admin_footer('layanan'); ?>
