<?php
include '_bootstrap.php';
include '_chrome.php';
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <?php pelanggan_theme_head('Riwayat Pelanggan'); ?>
</head>
<body class="min-h-screen">
    <?php pelanggan_sidebar('riwayat'); ?>
    <main data-pelanggan-main class="min-h-screen transition-[margin] duration-200 md:ml-64">
        <?php pelanggan_topbar('Haircut History'); ?>

        <div class="mx-auto w-full max-w-[1440px] space-y-8 p-5 pb-28 md:p-8">
            <section class="flex flex-col justify-between gap-5 border-b border-outline pb-6 md:flex-row md:items-end">
                <div>
                    <p class="text-[12px] font-black uppercase tracking-[0.24em] text-primary">Visit Archive</p>
                    <h1 class="mt-3 font-display text-4xl font-black text-on-surface md:text-5xl">Riwayat Kunjungan</h1>
                </div>
                <span class="border border-outline bg-surface-panel px-4 py-2 text-sm font-bold text-on-muted"><?= count($history_queues); ?> data</span>
            </section>

            <section class="customer-card overflow-hidden">
                <div class="overflow-x-auto customer-scroll">
                    <table class="w-full min-w-[920px] text-left">
                        <thead class="border-b border-outline bg-surface-high text-[11px] font-black uppercase tracking-[0.16em] text-on-muted">
                            <tr>
                                <th class="px-5 py-4">No.</th>
                                <th class="px-5 py-4">Tanggal</th>
                                <th class="px-5 py-4">Layanan</th>
                                <th class="px-5 py-4">Barber</th>
                                <th class="px-5 py-4">Durasi</th>
                                <th class="px-5 py-4 text-right">Status</th>
                                <th class="px-5 py-4 text-center">Struk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline text-sm">
                            <?php if (!empty($history_queues)): ?>
                                <?php foreach ($history_queues as $item): ?>
                                    <?php
                                        $hNoValue = $col_a_no && isset($item[$col_a_no]) ? $item[$col_a_no] : '0';
                                        $hNo = str_pad((string) $hNoValue, 3, '0', STR_PAD_LEFT);
                                        $hDate = ($col_a_tgl && isset($item[$col_a_tgl])) ? date('d M Y', strtotime($item[$col_a_tgl])) : date('d M Y');
                                        $hStatus = ($col_a_status && isset($item[$col_a_status])) ? strtoupper((string) $item[$col_a_status]) : 'SELESAI';
                                        $hBarber = $item['nama_barber'] ?? 'Belum ditentukan';
                                        $hService = $item['nama_layanan'] ?? 'Layanan';
                                        $hDuration = isset($item['durasi_layanan']) ? (int) $item['durasi_layanan'] . ' menit' : 'Belum tersedia';
                                        $trxId = (int) ($item['transaksi_id'] ?? 0);
                                    ?>
                                    <tr class="transition hover:bg-surface-panel">
                                        <td class="px-5 py-5 font-display text-lg font-black text-primary">#<?= htmlspecialchars($hNo); ?></td>
                                        <td class="px-5 py-5 font-bold text-on-surface"><?= htmlspecialchars($hDate); ?></td>
                                        <td class="px-5 py-5 text-on-muted"><?= htmlspecialchars($hService); ?></td>
                                        <td class="px-5 py-5 text-on-muted"><?= htmlspecialchars($hBarber); ?></td>
                                        <td class="px-5 py-5 text-on-muted"><?= htmlspecialchars($hDuration); ?></td>
                                        <td class="px-5 py-5 text-right">
                                            <span class="<?= strtolower($hStatus) === 'selesai' ? 'border-primary text-primary' : 'border-outline text-on-muted'; ?> inline-flex border px-3 py-1 text-[11px] font-black uppercase tracking-[0.16em]"><?= htmlspecialchars($hStatus); ?></span>
                                        </td>
                                        <td class="px-5 py-5 text-center">
                                            <?php if ($trxId > 0): ?>
                                                <a href="struk.php?id=<?= $trxId; ?>" class="material-symbols-outlined text-on-muted transition hover:text-primary" title="Lihat struk">receipt_long</a>
                                            <?php else: ?>
                                                <span class="text-on-muted/40">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-5 py-12 text-center text-on-muted">Belum ada riwayat antrean terdaftar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    <?php pelanggan_mobile_nav('riwayat'); ?>
</body>
</html>
