<?php
include '../config/database.php';
include '../config/helper.php';
require_once __DIR__ . '/_chrome.php';

check_login('pelanggan');

$pelanggan_id = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$antrian_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error_msg = '';

$query = "SELECT a.*, l.nama_layanan, l.harga, b.nama AS nama_barber
          FROM antrian a
          JOIN layanan l ON a.layanan_id = l.id
          LEFT JOIN barber b ON a.barber_id = b.id
          WHERE a.id = '$antrian_id' AND a.pelanggan_id = '$pelanggan_id'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode = isset($_POST['metode_pembayaran']) ? mysqli_real_escape_string($conn, $_POST['metode_pembayaran']) : 'Tunai';
    $nama_file_bukti = null;

    if (!in_array($metode, ['QRIS', 'Transfer Bank', 'Tunai'], true)) {
        $error_msg = 'Metode pembayaran tidak valid.';
    }

    if ($error_msg === '' && $metode !== 'Tunai' && (!isset($_FILES['bukti_pembayaran']) || $_FILES['bukti_pembayaran']['error'] !== UPLOAD_ERR_OK)) {
        $error_msg = 'Unggah bukti pembayaran untuk QRIS atau transfer bank.';
    }

    if ($error_msg === '' && $metode !== 'Tunai' && isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['bukti_pembayaran']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];

        if ($_FILES['bukti_pembayaran']['size'] > 2 * 1024 * 1024) {
            $error_msg = 'Ukuran bukti pembayaran maksimal 2MB.';
        } elseif (!in_array($ext, $allowed, true)) {
            $error_msg = 'Format bukti pembayaran harus JPG, PNG, atau PDF.';
        } elseif (is_uploaded_file($tmp_name)) {
            $folder_upload = '../uploads/bukti/';
            if (!file_exists($folder_upload)) {
                mkdir($folder_upload, 0755, true);
            }

            $nama_file_bukti = 'BUKTI_' . $antrian_id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (!move_uploaded_file($tmp_name, $folder_upload . $nama_file_bukti)) {
                $error_msg = 'Bukti pembayaran gagal disimpan. Silakan coba lagi.';
            }
        } else {
            $error_msg = 'Berkas bukti pembayaran tidak valid.';
        }
    }

    if ($error_msg === '') {
        $status_pembayaran = 'lunas';
        $harga = (int) $data['harga'];
        $existing = mysqli_query($conn, "SELECT id FROM transaksi WHERE antrian_id = '$antrian_id' LIMIT 1");
        $existingRow = $existing ? mysqli_fetch_assoc($existing) : null;

        if ($existingRow) {
            $transactionId = (int) $existingRow['id'];
            $buktiSql = $nama_file_bukti ? ", bukti_pembayaran = '" . mysqli_real_escape_string($conn, $nama_file_bukti) . "'" : '';
            $paymentQuery = "UPDATE transaksi SET total_harga = '$harga', metode_pembayaran = '$metode', status_pembayaran = '$status_pembayaran', waktu_bayar = NOW(6)$buktiSql WHERE id = '$transactionId'";
        } else {
            $buktiSql = $nama_file_bukti ? "'" . mysqli_real_escape_string($conn, $nama_file_bukti) . "'" : 'NULL';
            $paymentQuery = "INSERT INTO transaksi (antrian_id, total_harga, metode_pembayaran, bukti_pembayaran, status_pembayaran, waktu_bayar) VALUES ('$antrian_id', '$harga', '$metode', $buktiSql, '$status_pembayaran', NOW(6))";
        }

        if (mysqli_query($conn, $paymentQuery)) {
            $transactionId = $existingRow ? (int) $existingRow['id'] : (int) mysqli_insert_id($conn);
            header('Location: struk.php?id=' . $transactionId);
            exit();
        }

        $error_msg = 'Gagal memproses pembayaran: ' . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <?php pelanggan_theme_head('Selesaikan Pembayaran'); ?>
</head>
<body class="min-h-screen">
    <main class="mx-auto min-h-screen w-full max-w-[1100px] px-5 py-8">
        <header class="mb-8 flex items-center justify-between border-b border-outline pb-5">
            <div>
                <a href="dashboard.php" class="font-display text-2xl font-black text-primary">Barber.co</a>
                <p class="mt-1 text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Payment Gateway</p>
            </div>
            <a href="dashboard.php" class="text-[12px] font-black uppercase tracking-[0.16em] text-on-muted underline hover:text-primary">Batal / Dashboard</a>
        </header>

        <section class="mb-8">
            <p class="text-[12px] font-black uppercase tracking-[0.24em] text-primary">Secure Checkout</p>
            <h1 class="mt-3 font-display text-4xl font-black text-on-surface md:text-5xl">Selesaikan Pembayaran</h1>
            <p class="mt-2 text-on-muted">Nomor Tiket Antrean: <span class="font-bold text-primary">#<?= str_pad((string) $data['no_antrian'], 3, '0', STR_PAD_LEFT); ?></span></p>
        </section>

        <?php if ($error_msg): ?>
            <div class="customer-card mb-6 border-error bg-error/10 p-4 text-sm font-bold uppercase tracking-[0.12em] text-error">
                <?= htmlspecialchars($error_msg); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-12">
            <aside class="customer-card h-fit p-6 md:col-span-5">
                <h2 class="border-b border-outline pb-4 text-[12px] font-black uppercase tracking-[0.18em] text-primary">Rincian Transaksi</h2>
                <dl class="mt-5 space-y-4 text-sm">
                    <div class="flex justify-between gap-4 border-b border-outline pb-3">
                        <dt class="text-on-muted">Layanan</dt>
                        <dd class="text-right font-bold text-on-surface"><?= htmlspecialchars($data['nama_layanan']); ?></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-outline pb-3">
                        <dt class="text-on-muted">Barber</dt>
                        <dd class="text-right font-bold text-on-surface"><?= htmlspecialchars($data['nama_barber'] ?? 'Bebas / Pilih Nanti'); ?></dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-outline pb-3">
                        <dt class="text-on-muted">Tanggal Sesi</dt>
                        <dd class="font-bold text-on-surface"><?= date('d M Y', strtotime($data['tanggal'])); ?></dd>
                    </div>
                </dl>
                <div class="mt-6 flex items-end justify-between gap-4 border-t-2 border-primary pt-5">
                    <span class="text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Total Tagihan</span>
                    <span class="font-display text-2xl font-black text-primary">Rp <?= number_format((int) $data['harga'], 0, ',', '.'); ?></span>
                </div>
            </aside>

            <section class="customer-card p-6 md:col-span-7 md:p-8">
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="mb-3 block text-[12px] font-black uppercase tracking-[0.18em] text-primary">Pilih Metode Pembayaran</label>
                        <div class="grid gap-3">
                            <label class="flex cursor-pointer items-center justify-between gap-4 border border-outline bg-surface-panel p-4 transition hover:border-primary">
                                <span class="flex items-center gap-3 text-sm font-bold uppercase text-on-surface"><input type="radio" name="metode_pembayaran" value="QRIS" checked onclick="toggleUpload(true, 'qris')" class="accent-[#f2ca50]">QRIS Instant</span>
                                <span class="material-symbols-outlined text-primary">qr_code_scanner</span>
                            </label>
                            <label class="flex cursor-pointer items-center justify-between gap-4 border border-outline bg-surface-panel p-4 transition hover:border-primary">
                                <span class="flex items-center gap-3 text-sm font-bold uppercase text-on-surface"><input type="radio" name="metode_pembayaran" value="Transfer Bank" onclick="toggleUpload(true, 'bank')" class="accent-[#f2ca50]">Transfer Bank</span>
                                <span class="material-symbols-outlined text-primary">account_balance</span>
                            </label>
                            <label class="flex cursor-pointer items-center justify-between gap-4 border border-outline bg-surface-panel p-4 transition hover:border-primary">
                                <span class="flex items-center gap-3 text-sm font-bold uppercase text-on-surface"><input type="radio" name="metode_pembayaran" value="Tunai" onclick="toggleUpload(false, 'cash')" class="accent-[#f2ca50]">Bayar Tunai di Kasir</span>
                                <span class="material-symbols-outlined text-primary">payments</span>
                            </label>
                        </div>
                    </div>

                    <div id="info-qris" class="border border-dashed border-primary bg-primary/5 p-5 text-center">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Scan QRIS Barber.co</p>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=BARBERCO-IDR-<?= (int) $data['harga']; ?>" alt="QRIS" class="mx-auto border border-outline bg-white p-2">
                        <p class="mt-3 text-sm text-on-muted">Nominal Pas: <strong class="text-primary">Rp <?= number_format((int) $data['harga'], 0, ',', '.'); ?></strong></p>
                    </div>

                    <div id="info-bank" class="hidden border border-dashed border-primary bg-primary/5 p-5">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Rekening Pembayaran</p>
                        <div class="space-y-1 text-sm text-on-surface">
                            <p><strong>BCA:</strong> 8830-1234-5678 a.n Barber.co</p>
                            <p><strong>Mandiri:</strong> 112-00-9876-5432 a.n Barber.co</p>
                        </div>
                    </div>

                    <div id="wrapper-upload" class="space-y-2">
                        <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Unggah Bukti Pembayaran</label>
                        <input type="file" name="bukti_pembayaran" accept="image/*,.pdf" class="w-full border border-outline bg-surface-panel p-3 text-sm text-on-muted file:mr-4 file:border-0 file:bg-primary file:px-4 file:py-2 file:text-[11px] file:font-black file:uppercase file:tracking-[0.14em] file:text-on-primary">
                        <p class="text-xs text-on-muted">Format: JPG, PNG, atau PDF. Maksimal 2MB.</p>
                    </div>

                    <button type="submit" class="flex w-full items-center justify-center gap-2 border border-primary bg-primary py-4 text-[12px] font-black uppercase tracking-[0.18em] text-on-primary transition hover:bg-transparent hover:text-primary">
                        <span class="material-symbols-outlined">verified</span>
                        Konfirmasi Pembayaran
                    </button>
                </form>
            </section>
        </div>
    </main>

    <script>
        function toggleUpload(showUpload, type) {
            const wrapper = document.getElementById('wrapper-upload');
            const infoQris = document.getElementById('info-qris');
            const infoBank = document.getElementById('info-bank');

            wrapper.classList.toggle('hidden', !showUpload);
            infoQris.classList.toggle('hidden', type !== 'qris');
            infoBank.classList.toggle('hidden', type !== 'bank');
        }
    </script>
</body>
</html>
