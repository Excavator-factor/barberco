<?php
include "_bootstrap.php";
include "_chrome.php";
require_once __DIR__ . "/../functions/whatsapp.php";

$testResult = null;

// Handle Form Update Pengaturan
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_pengaturan"])) {
    $token_fonnte = trim($_POST["token_fonnte"] ?? "");
    $nomor_admin = preg_replace('/[^0-9]/', '', trim($_POST["nomor_admin"] ?? ""));
    $status_wa = isset($_POST["status_wa"]) ? 1 : 0;
    $notif_booking = isset($_POST["notif_booking"]) ? 1 : 0;
    $notif_panggilan = isset($_POST["notif_panggilan"]) ? 1 : 0;
    $notif_selesai = isset($_POST["notif_selesai"]) ? 1 : 0;

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `pengaturan_wa` SET `token_fonnte` = ?, `nomor_admin` = ?, `status_wa` = ?, `notif_booking` = ?, `notif_panggilan` = ?, `notif_selesai` = ? WHERE `id` = 1"
    );

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "ssiiii",
            $token_fonnte,
            $nomor_admin,
            $status_wa,
            $notif_booking,
            $notif_panggilan,
            $notif_selesai
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION["modalSuccess"] = "Pengaturan WhatsApp berhasil diperbarui!";
    } else {
        $_SESSION["modalError"] = "Gagal memperbarui pengaturan: " . mysqli_error($conn);
    }

    header("Location: pengaturan_wa.php");
    exit();
}

// Handle Form Uji Coba Pengiriman
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["kirim_tes"])) {
    $testTarget = trim($_POST["test_target"] ?? "");
    $testPesan = trim($_POST["test_pesan"] ?? "");

    if (empty($testTarget)) {
        $testResult = [
            'success' => false,
            'message' => 'Harap masukkan nomor WhatsApp tujuan.'
        ];
    } elseif (empty($testPesan)) {
        $testResult = [
            'success' => false,
            'message' => 'Harap masukkan teks pesan uji coba.'
        ];
    } else {
        $testResult = kirim_wa_fonnte($testTarget, $testPesan);
    }
}

$waConfig = get_pengaturan_wa();
$admin_header_title = "Pengaturan WhatsApp";
?>

<?php admin_header("Pengaturan WhatsApp", "pengaturan_wa"); ?>
<div class="p-md md:p-lg max-w-container-max mx-auto w-full">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-lg mt-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
                    <span class="material-symbols-outlined text-[16px]">chat</span>
                    WhatsApp Gateway
                </span>
                <?php if ((int)($waConfig['status_wa'] ?? 1) === 1): ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-500/10 text-green-400 border border-green-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        Aktif
                    </span>
                <?php else: ?>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/20">
                        Nonaktif
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-surface">Notifikasi WhatsApp</h1>
            <p class="text-sm md:text-base text-on-surface-variant mt-1">Konfigurasi token Fonnte API, nomor admin, dan pengiriman otomatis antrean.</p>
        </div>
    </div>

    <!-- Alert Test Result if any -->
    <?php if ($testResult !== null): ?>
        <div class="mb-6 p-4 rounded-xl border flex items-start gap-3 <?= $testResult['success'] ? 'bg-green-500/10 border-green-500/30 text-green-300' : 'bg-red-500/10 border-red-500/30 text-red-300' ?>">
            <span class="material-symbols-outlined text-2xl <?= $testResult['success'] ? 'text-green-400' : 'text-red-400' ?>">
                <?= $testResult['success'] ? 'check_circle' : 'error' ?>
            </span>
            <div class="flex-1">
                <p class="font-bold text-base"><?= $testResult['success'] ? 'Pengiriman Berhasil!' : 'Pengiriman Gagal' ?></p>
                <p class="text-sm mt-0.5 opacity-90"><?= htmlspecialchars($testResult['message']) ?></p>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Kolom Kiri: Form Konfigurasi (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-surface-container rounded-xl border border-outline-variant p-6 shadow-sm">
                <div class="flex items-center gap-3 pb-4 border-b border-outline-variant mb-6">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">tune</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-lg text-on-surface">Konfigurasi Gateway Fonnte</h2>
                        <p class="text-xs text-on-surface-variant">Hubungkan website dengan perangkat WhatsApp melalui API Fonnte</p>
                    </div>
                </div>

                <form method="POST" action="pengaturan_wa.php" class="space-y-5">
                    <input type="hidden" name="update_pengaturan" value="1">

                    <!-- Toggle Status Utama -->
                    <div class="flex items-center justify-between p-4 rounded-lg bg-background border border-outline-variant">
                        <div>
                            <p class="font-bold text-sm text-on-surface">Aktifkan Layanan WhatsApp</p>
                            <p class="text-xs text-on-surface-variant mt-0.5">Matikan jika ingin menonaktifkan seluruh notifikasi WA sementara waktu.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="status_wa" value="1" class="sr-only peer" <?= (int)($waConfig['status_wa'] ?? 1) === 1 ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:width-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <!-- Token API Fonnte -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            Token API Fonnte (Device Token)
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[20px]">key</span>
                            <input type="text" name="token_fonnte" id="tokenFonnteInput"
                                value="<?= htmlspecialchars($waConfig['token_fonnte'] ?? '') ?>"
                                placeholder="Masukkan token device dari akun Fonnte Anda"
                                class="w-full bg-background border border-outline-variant rounded-lg py-3 pl-11 pr-12 text-sm text-on-surface focus:border-primary focus:outline-none">
                            <button type="button" onclick="toggleTokenVisibility()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-outline-variant hover:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]" id="tokenVisibilityIcon">visibility</span>
                            </button>
                        </div>
                        <p class="text-[11px] text-on-surface-variant opacity-70">Didapatkan dari dashboard Fonnte setelah menghubungkan/scan QR WhatsApp Anda.</p>
                    </div>

                    <!-- Nomor WhatsApp Admin -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            Nomor WhatsApp Admin Toko
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[20px]">call</span>
                            <input type="tel" name="nomor_admin"
                                value="<?= htmlspecialchars($waConfig['nomor_admin'] ?? '') ?>"
                                placeholder="Contoh: 081234567890"
                                class="w-full bg-background border border-outline-variant rounded-lg py-3 pl-11 pr-4 text-sm text-on-surface focus:border-primary focus:outline-none">
                        </div>
                        <p class="text-[11px] text-on-surface-variant opacity-70">Menerima pesan notifikasi otomatis setiap kali ada pelanggan yang mengambil antrean baru.</p>
                    </div>

                    <!-- Pilihan Pemicu Notifikasi -->
                    <div class="pt-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant mb-3">
                            Pemicu Pengiriman Pesan Otomatis
                        </label>
                        <div class="space-y-2.5">
                            <!-- Booking Notif -->
                            <label class="flex items-start gap-3 p-3 rounded-lg bg-background border border-outline-variant cursor-pointer hover:border-primary/50 transition-colors">
                                <input type="checkbox" name="notif_booking" value="1" class="mt-0.5 rounded text-primary focus:ring-primary border-outline-variant bg-surface" <?= (int)($waConfig['notif_booking'] ?? 1) === 1 ? 'checked' : '' ?>>
                                <div>
                                    <p class="font-bold text-xs text-on-surface">Saat Booking / Ambil Antrean</p>
                                    <p class="text-[11px] text-on-surface-variant">Kirim tiket nomor antrean ke pelanggan & kirim alert pesanan baru ke nomor admin.</p>
                                </div>
                            </label>

                            <!-- Panggilan Notif -->
                            <label class="flex items-start gap-3 p-3 rounded-lg bg-background border border-outline-variant cursor-pointer hover:border-primary/50 transition-colors">
                                <input type="checkbox" name="notif_panggilan" value="1" class="mt-0.5 rounded text-primary focus:ring-primary border-outline-variant bg-surface" <?= (int)($waConfig['notif_panggilan'] ?? 1) === 1 ? 'checked' : '' ?>>
                                <div>
                                    <p class="font-bold text-xs text-on-surface">Saat Giliran Tiba (Panggilan Barber)</p>
                                    <p class="text-[11px] text-on-surface-variant">Kirim pesan pemberitahuan ke pelanggan saat barber mengklik tombol "Mulai Antrean".</p>
                                </div>
                            </label>

                            <!-- Selesai Notif -->
                            <label class="flex items-start gap-3 p-3 rounded-lg bg-background border border-outline-variant cursor-pointer hover:border-primary/50 transition-colors">
                                <input type="checkbox" name="notif_selesai" value="1" class="mt-0.5 rounded text-primary focus:ring-primary border-outline-variant bg-surface" <?= (int)($waConfig['notif_selesai'] ?? 1) === 1 ? 'checked' : '' ?>>
                                <div>
                                    <p class="font-bold text-xs text-on-surface">Saat Selesai Pangkas / Transaksi Lunas</p>
                                    <p class="text-[11px] text-on-surface-variant">Kirim rincian struk digital dan ucapan terima kasih ke pelanggan saat sesi selesai.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-outline-variant flex justify-end">
                        <button type="submit" class="bg-primary text-on-primary font-bold px-6 py-3 rounded-lg hover:bg-primary-container transition-all flex items-center gap-2 text-sm shadow-md active:scale-95">
                            <span class="material-symbols-outlined text-[20px]">save</span>
                            <span>Simpan Pengaturan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kolom Kanan: Uji Coba Pengiriman & Panduan (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Box Uji Coba Pengiriman -->
            <div class="bg-surface-container rounded-xl border border-outline-variant p-6 shadow-sm">
                <div class="flex items-center gap-3 pb-4 border-b border-outline-variant mb-5">
                    <div class="w-10 h-10 rounded-lg bg-green-500/10 border border-green-500/20 flex items-center justify-center text-green-400">
                        <span class="material-symbols-outlined">send</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-lg text-on-surface">Uji Coba Kirim Pesan</h2>
                        <p class="text-xs text-on-surface-variant">Tes apakah token Fonnte berfungsi dengan nomor tujuan Anda</p>
                    </div>
                </div>

                <form method="POST" action="pengaturan_wa.php" class="space-y-4">
                    <input type="hidden" name="kirim_tes" value="1">

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            Nomor WhatsApp Tujuan
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-outline text-[20px]">smartphone</span>
                            <input type="tel" name="test_target"
                                value="<?= htmlspecialchars($_POST['test_target'] ?? ($waConfig['nomor_admin'] ?? '')) ?>"
                                placeholder="Contoh: 081234567890" required
                                class="w-full bg-background border border-outline-variant rounded-lg py-2.5 pl-11 pr-4 text-sm text-on-surface focus:border-primary focus:outline-none">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            Pesan Uji Coba
                        </label>
                        <textarea name="test_pesan" rows="3" required
                            class="w-full bg-background border border-outline-variant rounded-lg p-3 text-sm text-on-surface focus:border-primary focus:outline-none"><?= htmlspecialchars($_POST['test_pesan'] ?? "Halo! Ini adalah pesan uji coba integrasi WhatsApp dari Barber.co. Sistem notifikasi WhatsApp Anda telah berhasil aktif! 💈✨") ?></textarea>
                    </div>

                    <button type="submit" class="w-full bg-surface-variant hover:bg-primary hover:text-on-primary text-on-surface font-bold py-3 rounded-lg transition-all flex items-center justify-center gap-2 text-sm border border-outline-variant">
                        <span class="material-symbols-outlined text-[18px]">outgoing_mail</span>
                        <span>Kirim Pesan Uji Coba Sekarang</span>
                    </button>
                </form>
            </div>

            <!-- Box Panduan Akun Gratis Fonnte -->
            <div class="bg-surface-container rounded-xl border border-outline-variant p-6 shadow-sm">
                <div class="flex items-center gap-2 pb-3 border-b border-outline-variant mb-4 text-primary">
                    <span class="material-symbols-outlined">help</span>
                    <h3 class="font-bold text-sm uppercase tracking-wider">Panduan Akun Gratis Fonnte</h3>
                </div>
                
                <ol class="space-y-3 text-xs text-on-surface-variant list-decimal list-inside">
                    <li class="leading-relaxed">
                        <span class="font-semibold text-on-surface">Daftar Akun Gratis:</span> Buka website resmi <a href="https://fonnte.com" target="_blank" class="text-primary hover:underline font-bold inline-flex items-center gap-0.5">fonnte.com <span class="material-symbols-outlined text-[12px]">open_in_new</span></a> dan daftarkan nomor Anda.
                    </li>
                    <li class="leading-relaxed">
                        <span class="font-semibold text-on-surface">Hubungkan WhatsApp:</span> Di dashboard Fonnte, buka menu <b>Device</b>, klik <b>Tambah Device</b>, lalu scan kode QR menggunakan WhatsApp di smartphone Anda.
                    </li>
                    <li class="leading-relaxed">
                        <span class="font-semibold text-on-surface">Salin Token:</span> Setelah status terhubung (*connected*), salin <b>API Token</b> yang tertera.
                    </li>
                    <li class="leading-relaxed">
                        <span class="font-semibold text-on-surface">Tempel & Simpan:</span> Tempelkan Token tersebut di form sebelah kiri, lalu klik <b>Simpan Pengaturan</b>.
                    </li>
                </ol>

                <div class="mt-4 p-3 rounded-lg bg-primary/5 border border-primary/20 text-xs text-primary">
                    <div class="flex items-center gap-1.5 font-bold mb-1">
                        <span class="material-symbols-outlined text-sm">shield</span>
                        <span>Cadangan Otomatis</span>
                    </div>
                    <p class="text-[11px] opacity-90">Jika token belum diisi atau kuota pesan habis, website tetap menyediakan tombol <b>Direct WhatsApp (wa.me)</b> sehingga Anda tetap bisa mengirim pesan secara gratis!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleTokenVisibility() {
        const input = document.getElementById('tokenFonnteInput');
        const icon = document.getElementById('tokenVisibilityIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility_off';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility';
        }
    }
</script>

<?php if (isset($_SESSION['modalSuccess'])): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '<?= htmlspecialchars($_SESSION['modalSuccess']) ?>',
        background: '#1e2020',
        color: '#e2e2e2',
        confirmButtonColor: '#f2ca50',
    });
</script>
<?php unset($_SESSION['modalSuccess']); endif; ?>

<?php if (isset($_SESSION['modalError'])): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: '<?= htmlspecialchars($_SESSION['modalError']) ?>',
        background: '#1e2020',
        color: '#e2e2e2',
        confirmButtonColor: '#f2ca50',
    });
</script>
<?php unset($_SESSION['modalError']); endif; ?>

<?php admin_footer("pengaturan_wa"); ?>
