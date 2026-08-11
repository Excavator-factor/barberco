<?php
include '../config/database.php';
include '../config/helper.php';
require_once __DIR__ . '/_chrome.php';

check_login('pelanggan');

$pelanggan_id = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$username = $_SESSION['username'] ?? $_SESSION['nama'] ?? 'Pelanggan';
$error_msg = '';

$pk_layanan = 'id';
$col_layanan_nama = 'nama_layanan';
$col_layanan_harga = 'harga';
$col_layanan_desk = 'deskripsi';
$pk_users = 'id';
$col_users_nama = 'nama';

$query_layanan = mysqli_query($conn, 'SELECT * FROM layanan ORDER BY id ASC');
$query_barber = mysqli_query($conn, 'SELECT * FROM barber ORDER BY id ASC');

function service_photo_meta(array $service): array
{
    $id = (int) ($service['id'] ?? 0);
    $name = trim((string) ($service['nama_layanan'] ?? 'Layanan'));
    $stored = trim((string) ($service['gambar'] ?? ''));

    if ($stored !== '' && is_file(__DIR__ . '/../' . ltrim($stored, '/'))) {
        $image = '../' . ltrim($stored, '/');
    } else {
        $images = [
            'https://images.unsplash.com/photo-1622287162716-f311baa1a2b8?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1502823403499-6ccfcf4fb453?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1605497788044-5a32c7078486?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1000&q=80',
            'https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=1000&q=80'
        ];
        $image = $images[$id % count($images)];
    }

    $badge = 'Premium Grooming';
    if (stripos($name, 'cut') !== false) {
        $badge = 'Precision Cut';
    } elseif (stripos($name, 'shave') !== false) {
        $badge = 'Royal Shave';
    } elseif (stripos($name, 'beard') !== false) {
        $badge = 'Beard Detail';
    } elseif (stripos($name, 'treatment') !== false || stripos($name, 'wash') !== false) {
        $badge = 'Refresh Ritual';
    }

    return ['image' => $image, 'badge' => $badge];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $layanan_id = isset($_POST['id_layanan']) ? (int) $_POST['id_layanan'] : 0;
    $barber_input = isset($_POST['id_barber']) ? trim((string) $_POST['id_barber']) : '';
    $barber_id_sql = 'NULL';

    if ($layanan_id <= 0) {
        $error_msg = 'Harap pilih layanan terlebih dahulu.';
    }

    if ($error_msg === '' && $barber_input !== '' && $barber_input !== '0') {
        $barber_id = (int) $barber_input;
        $barberCheck = mysqli_prepare($conn, "SELECT id FROM barber WHERE id = ? AND LOWER(status) = 'aktif' LIMIT 1");
        mysqli_stmt_bind_param($barberCheck, 'i', $barber_id);
        mysqli_stmt_execute($barberCheck);
        $selectedBarber = mysqli_fetch_assoc(mysqli_stmt_get_result($barberCheck));
        mysqli_stmt_close($barberCheck);

        if ($selectedBarber) {
            $barber_id_sql = (string) $barber_id;
        } else {
            $error_msg = 'Barber yang dipilih sedang tidak aktif. Silakan pilih barber lain atau lanjutkan tanpa preferensi barber.';
        }
    }

    if ($error_msg === '') {
        $today = date('Y-m-d');
        $q_no = mysqli_query($conn, "SELECT MAX(no_antrian) AS max_no FROM antrian WHERE tanggal = '$today'");
        $data_no = mysqli_fetch_assoc($q_no);
        $next_no = ($data_no['max_no'] ?? 0) + 1;

        $query = "INSERT INTO antrian (pelanggan_id, layanan_id, barber_id, no_antrian, status_antrian, tanggal)
                  VALUES ('$pelanggan_id', '$layanan_id', $barber_id_sql, '$next_no', 'menunggu', '$today')";

        if (mysqli_query($conn, $query)) {
            header('Location: dashboard.php?status=success');
            exit();
        }

        $error_msg = 'Gagal mengambil antrean: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <?php pelanggan_theme_head('Ambil Antrean'); ?>
    <style>
        .active-selection {
            background: #f2ca50 !important;
            border-color: #f2ca50 !important;
            color: #241a00 !important;
            box-shadow: 0 20px 50px rgba(242, 202, 80, .16);
        }
        .active-selection .muted-copy,
        .active-selection .service-name,
        .active-selection .barber-name,
        .active-selection .price-text,
        .active-selection .material-symbols-outlined { color: #241a00 !important; }
    </style>
</head>
<body class="min-h-screen">
    <?php pelanggan_sidebar('antrean'); ?>
    <main data-pelanggan-main class="min-h-screen transition-[margin] duration-200 md:ml-64">
        <?php pelanggan_topbar('Book New Service'); ?>

        <div class="mx-auto w-full max-w-[1440px] space-y-8 p-5 pb-28 md:p-8">
            <section class="flex flex-col justify-between gap-5 border-b border-outline pb-6 md:flex-row md:items-end">
                <div>
                    <p class="text-[12px] font-black uppercase tracking-[0.24em] text-primary">Booking Registry</p>
                    <h1 class="mt-3 font-display text-4xl font-black text-on-surface md:text-5xl">Ambil Antrean</h1>
                    <p class="mt-3 max-w-2xl text-lg leading-8 text-on-muted">Pilih layanan, tentukan barber jika punya preferensi, lalu lanjutkan pembayaran.</p>
                </div>
                <div class="border border-outline bg-surface-panel px-4 py-3 text-right">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Status Sistem</p>
                    <p class="mt-1 font-display text-2xl font-black text-primary">Ready</p>
                </div>
            </section>

            <?php if ($error_msg): ?>
                <div class="customer-card border-error bg-error/10 p-4 text-sm font-bold uppercase tracking-[0.12em] text-error">
                    <?= htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <form id="antrianForm" method="POST" action="">
                <input type="hidden" name="id_layanan" id="input_id_layanan" value="">
                <input type="hidden" name="id_barber" id="input_id_barber" value="">

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <div class="space-y-6 lg:col-span-8">
                        <section class="customer-card p-5 md:p-6">
                            <div class="mb-6 flex items-center justify-between gap-4">
                                <h2 class="font-display text-2xl font-black text-on-surface">01. Pilih Layanan</h2>
                                <span class="text-[11px] font-black uppercase tracking-[0.18em] text-primary">Signature Menu</span>
                            </div>

                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <?php if ($query_layanan && mysqli_num_rows($query_layanan) > 0): ?>
                                    <?php while ($lay = mysqli_fetch_assoc($query_layanan)): ?>
                                        <?php
                                            $id_lay = (int) $lay[$pk_layanan];
                                            $nama_lay = $lay[$col_layanan_nama] ?? 'Layanan';
                                            $harga_lay = (int) ($lay[$col_layanan_harga] ?? 0);
                                            $harga_fmt = 'Rp ' . number_format($harga_lay, 0, ',', '.');
                                            $desk_lay = $lay[$col_layanan_desk] ?? '';
                                            $serviceVisual = service_photo_meta($lay);
                                        ?>
                                        <article class="service-card group cursor-pointer overflow-hidden border border-outline bg-surface-panel transition hover:border-primary" onclick="selectService(this, '<?= $id_lay; ?>', '<?= htmlspecialchars($harga_fmt, ENT_QUOTES); ?>', '<?= htmlspecialchars($nama_lay, ENT_QUOTES); ?>')">
                                            <div class="relative aspect-[16/10] overflow-hidden bg-surface-high">
                                                <img src="<?= htmlspecialchars($serviceVisual['image']); ?>" alt="Foto layanan <?= htmlspecialchars($nama_lay); ?>" class="h-full w-full object-cover grayscale transition duration-500 group-hover:scale-105 group-hover:grayscale-0">
                                                <span class="absolute left-4 top-4 bg-primary px-3 py-2 text-[11px] font-black uppercase tracking-[0.16em] text-on-primary"><?= htmlspecialchars($serviceVisual['badge']); ?></span>
                                            </div>
                                            <div class="p-5">
                                                <div class="flex items-start justify-between gap-3">
                                                    <h3 class="service-name font-display text-xl font-black text-on-surface"><?= htmlspecialchars($nama_lay); ?></h3>
                                                    <span class="price-text whitespace-nowrap text-sm font-black text-primary"><?= htmlspecialchars($harga_fmt); ?></span>
                                                </div>
                                                <p class="muted-copy mt-4 min-h-20 text-sm leading-7 text-on-muted"><?= htmlspecialchars($desk_lay ?: 'Deskripsi layanan tersedia saat pemesanan.'); ?></p>
                                            </div>
                                        </article>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="col-span-2 text-on-muted">Belum ada layanan yang terdaftar di database.</p>
                                <?php endif; ?>
                            </div>
                        </section>

                        <section class="customer-card p-5 md:p-6">
                            <div class="mb-6">
                                <h2 class="font-display text-2xl font-black text-on-surface">02. Pilih Barber</h2>
                                <p class="mt-2 text-sm leading-7 text-on-muted">Opsional. Kosongkan jika ingin dilayani barber yang tersedia.</p>
                            </div>

                            <div class="space-y-4">
                                <?php if ($query_barber && mysqli_num_rows($query_barber) > 0): ?>
                                    <?php while ($barb = mysqli_fetch_assoc($query_barber)): ?>
                                        <?php
                                            $id_barb = (int) $barb[$pk_users];
                                            $nama_barb = $barb[$col_users_nama] ?? 'Barber';
                                            $is_barber_active = strtolower(trim($barb['status'] ?? 'aktif')) === 'aktif';
                                        ?>
                                        <article class="barber-card flex items-center gap-5 border border-outline bg-surface-panel p-4 transition <?= $is_barber_active ? 'cursor-pointer hover:border-primary' : 'cursor-not-allowed opacity-50'; ?>" <?= $is_barber_active ? "onclick=\"selectBarber(this, '{$id_barb}', '" . htmlspecialchars($nama_barb, ENT_QUOTES) . "')\"" : 'aria-disabled="true"'; ?>>
                                            <div class="flex h-16 w-16 shrink-0 items-center justify-center border border-outline bg-surface-high">
                                                <span class="material-symbols-outlined text-3xl text-primary">person</span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
                                                    <h3 class="barber-name font-display text-xl font-black text-on-surface"><?= htmlspecialchars($nama_barb); ?></h3>
                                                    <span class="<?= $is_barber_active ? 'bg-primary text-on-primary' : 'bg-surface-high text-on-muted'; ?> w-fit px-3 py-1 text-[11px] font-black uppercase tracking-[0.16em]"><?= $is_barber_active ? 'Aktif' : 'Tidak Aktif'; ?></span>
                                                </div>
                                                <p class="muted-copy mt-2 text-sm text-on-muted"><?= $is_barber_active ? htmlspecialchars($barb['spesialisasi'] ?: 'Spesialisasi belum diisi') : 'Barber ini sementara tidak menerima antrean.'; ?></p>
                                            </div>
                                        </article>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-on-muted">Belum ada data barber terdaftar.</p>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>

                    <aside class="lg:col-span-4">
                        <div class="customer-card sticky top-24 overflow-hidden">
                            <div class="border-b border-outline bg-surface-high p-5">
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-primary">Final Ledger Entry</p>
                                <h2 class="mt-2 font-display text-2xl font-black text-on-surface">Ringkasan Booking</h2>
                            </div>
                            <div class="space-y-5 p-5">
                                <div class="space-y-4 text-sm">
                                    <div class="flex justify-between gap-4 border-b border-outline pb-3">
                                        <span class="text-on-muted">Layanan</span>
                                        <span class="text-right font-bold text-on-surface" id="summary-service">-</span>
                                    </div>
                                    <div class="flex justify-between gap-4 border-b border-outline pb-3">
                                        <span class="text-on-muted">Barber</span>
                                        <span class="text-right font-bold text-on-surface" id="summary-barber">Barber tersedia</span>
                                    </div>
                                    <div class="flex justify-between gap-4 border-b border-outline pb-3">
                                        <span class="text-on-muted">Estimasi</span>
                                        <span class="font-bold text-on-surface">30 - 45 min</span>
                                    </div>
                                </div>

                                <div class="border-b-2 border-primary py-4">
                                    <div class="flex items-end justify-between gap-4">
                                        <span class="text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Total Biaya</span>
                                        <span class="font-display text-2xl font-black text-primary" id="summary-total">Rp 0</span>
                                    </div>
                                </div>

                                <button type="button" onclick="submitForm()" class="flex w-full items-center justify-center gap-3 border border-primary bg-primary px-6 py-4 text-[12px] font-black uppercase tracking-[0.16em] text-on-primary transition hover:bg-transparent hover:text-primary">
                                    <span class="material-symbols-outlined">confirmation_number</span>
                                    Ambil Antrean Sekarang
                                </button>
                                <p class="text-center text-sm leading-6 text-on-muted">Setelah antrean dibuat, Anda dapat melihat status antrean di dashboard.</p>
                            </div>
                        </div>
                    </aside>
                </div>
            </form>
        </div>
    </main>
    <?php pelanggan_mobile_nav('antrean'); ?>

    <script>
        function selectService(element, id, price, title) {
            document.querySelectorAll('.service-card').forEach((card) => card.classList.remove('active-selection'));
            element.classList.add('active-selection');
            document.getElementById('input_id_layanan').value = id;
            document.getElementById('summary-service').innerText = title;
            document.getElementById('summary-total').innerText = price;
        }

        function selectBarber(element, id, barberName) {
            document.querySelectorAll('.barber-card').forEach((card) => card.classList.remove('active-selection'));
            element.classList.add('active-selection');
            document.getElementById('input_id_barber').value = id;
            document.getElementById('summary-barber').innerText = barberName;
        }

        function submitForm() {
            if (!document.getElementById('input_id_layanan').value) {
                alert('Harap pilih layanan terlebih dahulu.');
                return;
            }
            document.getElementById('antrianForm').submit();
        }
    </script>
</body>
</html>
