<?php
include '_bootstrap.php';
include '_chrome.php';

$queueNoValue = $active_queue && $col_a_no && isset($active_queue[$col_a_no]) ? $active_queue[$col_a_no] : null;
$queueNo = $queueNoValue !== null ? str_pad((string) $queueNoValue, 3, '0', STR_PAD_LEFT) : '---';
$queueStatus = $active_queue && $col_a_status && isset($active_queue[$col_a_status]) ? strtoupper((string) $active_queue[$col_a_status]) : 'BELUM ADA';
$serviceName = $active_queue['nama_layanan'] ?? 'Belum memilih layanan';
$barberName = $active_queue['nama_barber'] ?? 'Barber tersedia';
$priceLabel = isset($active_queue['harga_layanan']) ? 'Rp ' . number_format((int) $active_queue['harga_layanan'], 0, ',', '.') : 'Menunggu detail';
$waitEstimate = '0 min';
$shopLoad = 1;

if ($active_queue) {
    // Estimate wait time based on queue order
    $qDate = $active_queue['tanggal'];
    $qNo = (int) $active_queue['no_antrian'];
    $cntQ = mysqli_query($conn, "SELECT COUNT(*) as wait_count FROM antrian WHERE tanggal = '$qDate' AND no_antrian < $qNo AND LOWER(status_antrian) NOT IN ('selesai', 'dibatalkan')");
    $waitRow = $cntQ ? mysqli_fetch_assoc($cntQ) : null;
    $peopleAhead = $waitRow ? (int) $waitRow['wait_count'] : 0;
    
    // Each person ahead might take around 25 mins
    $estMins = max(0, $peopleAhead * 25);
    if (strtolower((string)$active_queue['status_antrian']) === 'proses') {
        $waitEstimate = 'Sekarang';
    } else {
        $waitEstimate = $estMins > 0 ? $estMins . ' min' : '15 min'; // if they are next, give 15 min buffer or similar
    }

    // Shop Load calculation (1-5)
    $activeCountQ = mysqli_query($conn, "SELECT COUNT(*) as active_c FROM antrian WHERE tanggal = '$qDate' AND LOWER(status_antrian) NOT IN ('selesai', 'dibatalkan')");
    $loadRow = $activeCountQ ? mysqli_fetch_assoc($activeCountQ) : null;
    $totalActive = $loadRow ? (int)$loadRow['active_c'] : 0;
    
    if ($totalActive <= 2) $shopLoad = 1;
    elseif ($totalActive <= 4) $shopLoad = 2;
    elseif ($totalActive <= 6) $shopLoad = 3;
    elseif ($totalActive <= 10) $shopLoad = 4;
    else $shopLoad = 5;
}
$recentHistory = array_slice($history_queues, 0, 4);
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <?php pelanggan_theme_head('Dashboard Pelanggan'); ?>
</head>
<body class="min-h-screen">
    <?php pelanggan_sidebar('ringkasan'); ?>
    <main data-pelanggan-main class="min-h-screen transition-[margin] duration-200 md:ml-64">
        <?php pelanggan_topbar('Client Portal'); ?>

        <div class="mx-auto w-full max-w-[1440px] space-y-10 p-5 pb-28 md:p-8">
            <?php if ($status_msg === 'success'): ?>
                <div class="customer-card flex items-center justify-between gap-4 border-primary bg-primary/10 p-4 text-sm font-bold uppercase tracking-[0.14em] text-primary">
                    <span>Antrean Anda berhasil terdaftar ke dalam sistem.</span>
                    <button type="button" onclick="this.parentElement.remove()" class="material-symbols-outlined">close</button>
                </div>
            <?php endif; ?>

            <section class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                <div>
                    <p class="text-[12px] font-black uppercase tracking-[0.24em] text-primary">Dashboard Pelanggan</p>
                    <h1 class="mt-3 font-display text-4xl font-black text-on-surface md:text-5xl">Selamat datang kembali, <span class="text-primary"><?= htmlspecialchars($username); ?></span></h1>
                    <p class="mt-3 text-lg leading-8 text-on-muted">Pantau status antrean, lihat riwayat, dan pesan layanan berikutnya dari satu tempat.</p>
                </div>
                <a href="#" onclick="openBookingModal(); return false;" class="inline-flex items-center justify-center gap-2 border border-primary bg-primary px-6 py-4 text-[12px] font-black uppercase tracking-[0.16em] text-on-primary transition hover:bg-transparent hover:text-primary">
                    <span class="material-symbols-outlined">add_circle</span>
                    Pesan Layanan Baru
                </a>
            </section>

            <section class="grid grid-cols-1 gap-5 lg:grid-cols-12">
                <article class="customer-card relative overflow-hidden p-6 lg:col-span-8">
                    <div class="absolute left-0 top-0 h-full w-1 bg-primary"></div>
                    <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-start">
                        <div>
                            <span class="inline-flex bg-primary/10 px-3 py-2 text-[11px] font-black uppercase tracking-[0.18em] text-primary">Antrean Real-Time</span>
                            <h2 class="mt-5 font-display text-3xl font-black text-on-surface">Status Antrean Saat Ini</h2>
                        </div>
                        <div class="sm:text-right">
                            <p class="text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Beban Toko</p>
                            <div class="mt-2 flex gap-1 sm:justify-end">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <span class="h-7 w-2 <?= $i <= $shopLoad ? 'bg-primary' : 'bg-surface-high'; ?>"></span>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-5 md:grid-cols-3">
                        <div class="border border-outline bg-surface-panel p-5 text-center">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-on-muted">Estimasi Waktu Tunggu</p>
                            <div class="mt-3 font-display text-5xl font-black text-primary"><?= htmlspecialchars($waitEstimate); ?></div>
                            <p class="mt-2 text-sm text-on-muted"><?= $active_queue ? 'Antrean sedang berjalan' : 'Tidak ada antrean aktif'; ?></p>
                        </div>
                        <div class="border border-outline bg-surface-panel p-5 text-center">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-on-muted">Posisi Anda</p>
                            <div class="mt-3 font-display text-5xl font-black text-on-surface">#<?= htmlspecialchars($queueNo); ?></div>
                            <p class="mt-2 text-sm text-on-muted">Status: <?= htmlspecialchars($queueStatus); ?></p>
                        </div>
                        <div class="border border-outline bg-surface-panel p-5 text-center">
                            <p class="text-[11px] font-black uppercase tracking-[0.16em] text-on-muted">Barber yang Ditugaskan</p>
                            <div class="mx-auto mt-4 flex h-14 w-14 items-center justify-center border-2 border-primary bg-surface-high">
                                <span class="material-symbols-outlined text-3xl text-primary">person</span>
                            </div>
                            <p class="mt-3 font-bold text-on-surface"><?= htmlspecialchars($barberName); ?></p>
                        </div>
                    </div>
                </article>

                <article class="customer-card flex min-h-[300px] flex-col justify-between overflow-hidden bg-cover bg-center p-6 lg:col-span-4" style="background-image: linear-gradient(to bottom, rgba(18, 20, 20, 0.7), rgba(18, 20, 20, 0.96)), url('https://images.unsplash.com/photo-1605497788044-5a32c7078486?auto=format&fit=crop&w=900&q=80')">
                    <div>
                        <span class="border-b border-primary pb-1 text-[12px] font-black uppercase tracking-[0.18em] text-primary">Layanan Aktif</span>
                        <h2 class="mt-5 font-display text-2xl font-black text-on-surface"><?= htmlspecialchars($serviceName); ?></h2>
                        <p class="mt-3 text-sm leading-7 text-on-muted"><?= $active_queue ? 'Detail layanan aktif Anda siap dipantau dari dashboard ini.' : 'Belum ada antrean aktif. Pilih layanan untuk mulai booking.'; ?></p>
                    </div>
                    <div class="mt-8">
                        <div class="mb-5 flex items-end justify-between gap-4">
                            <span class="font-display text-2xl font-black text-on-surface"><?= htmlspecialchars($priceLabel); ?></span>
                            <span class="text-sm text-on-muted"><?= count($history_queues); ?> kunjungan</span>
                        </div>
                        <a href="#" onclick="openBookingModal(); return false;" class="block w-full border border-on-surface bg-on-surface py-3 text-center text-[12px] font-black uppercase tracking-[0.16em] text-background transition hover:border-primary hover:bg-primary hover:text-on-primary">Pilih Layanan</a>
                    </div>
                </article>

                <article class="customer-card overflow-hidden lg:col-span-8">
                    <div class="flex items-center justify-between border-b border-outline bg-surface-high p-5">
                        <h2 class="font-display text-2xl font-black text-on-surface">Riwayat Potong Rambut</h2>
                        <a href="riwayat.php" class="inline-flex items-center gap-1 text-[12px] font-black uppercase tracking-[0.16em] text-primary hover:underline">
                            Lihat Semua <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                        </a>
                    </div>
                    <div class="overflow-x-auto customer-scroll">
                        <table class="w-full min-w-[720px] text-left">
                            <thead class="border-b border-outline bg-surface-panel text-[11px] font-black uppercase tracking-[0.16em] text-on-muted">
                                <tr>
                                    <th class="px-5 py-4">Tanggal</th>
                                    <th class="px-5 py-4">Layanan</th>
                                    <th class="px-5 py-4">Barber</th>
                                    <th class="px-5 py-4 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline text-sm">
                                <?php if ($recentHistory): ?>
                                    <?php foreach ($recentHistory as $item): ?>
                                        <?php
                                            $hDate = ($col_a_tgl && isset($item[$col_a_tgl])) ? date('d M Y', strtotime($item[$col_a_tgl])) : date('d M Y');
                                            $hStatus = ($col_a_status && isset($item[$col_a_status])) ? strtoupper((string) $item[$col_a_status]) : 'SELESAI';
                                        ?>
                                        <tr class="transition hover:bg-surface-panel">
                                            <td class="px-5 py-5 font-bold text-on-surface"><?= htmlspecialchars($hDate); ?></td>
                                            <td class="px-5 py-5 text-on-muted"><?= htmlspecialchars($item['nama_layanan'] ?? 'Layanan'); ?></td>
                                            <td class="px-5 py-5 text-on-muted"><?= htmlspecialchars($item['nama_barber'] ?? 'Barber tersedia'); ?></td>
                                            <td class="px-5 py-5 text-right font-bold text-primary"><?= htmlspecialchars($hStatus); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-5 py-8 text-center text-on-muted">Belum ada riwayat kunjungan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="customer-card p-6 lg:col-span-4">
                    <div class="flex items-center gap-5">
                        <div class="flex h-20 w-20 items-center justify-center border-2 border-primary bg-surface-high overflow-hidden">
                            <?php if (!empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $_SESSION['avatar'])): ?>
                                <img src="../uploads/avatars/<?= htmlspecialchars($_SESSION['avatar']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <span class="material-symbols-outlined text-4xl text-primary">person</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h2 class="font-display text-2xl font-black text-on-surface"><?= htmlspecialchars($username); ?></h2>
                            <p class="mt-1 text-sm text-on-muted">Member Barber.co</p>
                            <span class="mt-3 inline-flex border border-primary px-2 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-primary">Gold Tier</span>
                        </div>
                    </div>
                    <dl class="mt-7 space-y-4 text-sm">
                        <div class="flex justify-between border-b border-outline pb-3">
                            <dt class="text-on-muted">Total kunjungan</dt>
                            <dd class="font-bold text-on-surface"><?= count($history_queues); ?></dd>
                        </div>
                        <div class="flex justify-between border-b border-outline pb-3">
                            <dt class="text-on-muted">Antrean aktif</dt>
                            <dd class="font-bold text-on-surface"><?= $active_queue ? 'Ada' : 'Tidak ada'; ?></dd>
                        </div>
                    </dl>
                </article>
            </section>
        </div>
    </main>
    <?php pelanggan_mobile_nav('ringkasan'); ?>
    <?php pelanggan_booking_modal(); ?>
    <?php if (isset($_GET['open_modal'])): ?><script>window.addEventListener('DOMContentLoaded', () => openBookingModal());</script><?php endif; ?>
</body>
</html>
