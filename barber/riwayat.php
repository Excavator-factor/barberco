<?php
include '_bootstrap.php';
include '_chrome.php';
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <?php barber_head('Riwayat Sesi'); ?>
    <style>
        @media print {
            #sidebar, header, nav, .no-print, .barber-mobile-nav { display: none !important; }
            body { background: #fff !important; color: #111 !important; animation: none !important; }
            .print-header { display: block !important; }
            main { margin: 0 !important; padding: 0 !important; }
            .print-table { border-collapse: collapse; width: 100%; }
            .print-table th, .print-table td { border: 1px solid #ccc; padding: 8px 10px; font-size: 12px; }
            .print-table thead { background: #f5f5f5; }
            .print-summary { display: flex !important; gap: 24px; margin-bottom: 16px; }
            .bg-surface-container, article { background: transparent !important; border: none !important; box-shadow: none !important; }
            .rounded-xl { border-radius: 0 !important; }
        }
        .print-header { display: none; }
        @media screen {
            .filter-tab { transition: all 0.2s ease; }
            .filter-tab.active { background: #f2ca50; color: #3c2f00; font-weight: 700; }
            .filter-tab:not(.active) { color: #d0c5af; }
            .filter-tab:not(.active):hover { color: #f2ca50; border-color: #f2ca50; }
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md selection:bg-primary selection:text-on-primary">
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar Navigation -->
    <?php barber_sidebar('riwayat'); ?>

    <!-- Main Content Area -->
    <main class="flex-grow md:ml-[250px] flex flex-col h-screen overflow-hidden min-w-0">
        <!-- Top Bar -->
        <?php barber_header('Riwayat Sesi'); ?>
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <div class="p-md md:p-lg max-w-7xl w-full mx-auto pb-24 md:pb-8">

                <!-- Print-only header -->
                <div class="print-header mb-4">
                    <h1 style="font-size:20px;font-weight:700;margin:0 0 4px;">Barber.co — Laporan Riwayat Sesi</h1>
                    <p style="font-size:12px;color:#555;margin:0;">Kapster: <?= htmlspecialchars($barberName) ?> &nbsp;|&nbsp; Periode: <?= htmlspecialchars($filterLabel) ?> &nbsp;|&nbsp; Dicetak: <?= date('d M Y H:i') ?></p>
                    <hr style="margin:12px 0;border-color:#ccc;">
                </div>

                <!-- Page Title & Stats -->
                <div class="mb-lg flex flex-col md:flex-row md:items-end justify-between gap-md border-b border-outline-variant pb-6 no-print">
                    <div>
                        <h2 class="font-headline-lg text-headline-lg mb-1 text-on-surface">Riwayat Sesi Selesai</h2>
                        <p class="text-on-surface-variant text-sm">Daftar pelayanan yang telah Anda selesaikan — <span class="text-primary font-bold"><?= htmlspecialchars($filterLabel) ?></span></p>
                    </div>
                    <div class="flex gap-4 flex-wrap">
                        <div class="bg-surface-container border border-outline-variant px-4 py-2 rounded-lg text-right">
                            <p class="text-xs text-on-surface-variant uppercase font-bold">Total Sesi</p>
                            <p class="text-xl font-bold text-primary font-headline-md"><?= count($completedQueues) ?></p>
                        </div>
                        <div class="bg-surface-container border border-outline-variant px-4 py-2 rounded-lg text-right">
                            <p class="text-xs text-on-surface-variant uppercase font-bold">Total Revenue</p>
                            <p class="text-xl font-bold text-primary font-headline-md">Rp <?= number_format($revenueToday, 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Print Summary (only visible when printing) -->
                <div class="print-summary" style="display:none;">
                    <div><strong>Total Sesi:</strong> <?= count($completedQueues) ?></div>
                    <div><strong>Total Revenue:</strong> Rp <?= number_format($revenueToday, 0, ',', '.') ?></div>
                </div>

                <!-- Filter Tabs + Print Button -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4 no-print">
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $filters = [
                            'today' => 'Hari Ini',
                            'week'  => 'Minggu Ini',
                            'month' => 'Bulan Ini',
                            'year'  => 'Tahun Ini',
                        ];
                        foreach ($filters as $fKey => $fLabel):
                            $isActive = $riwayatFilter === $fKey;
                        ?>
                        <a href="?filter=<?= $fKey ?>"
                           class="filter-tab px-4 py-2 rounded-full border border-outline-variant text-xs font-bold uppercase tracking-widest <?= $isActive ? 'active' : '' ?>">
                            <?= $fLabel ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <button onclick="window.print()"
                        class="no-print flex items-center gap-2 bg-surface-container border border-outline-variant text-on-surface-variant hover:text-primary hover:border-primary rounded-lg px-4 py-2 text-xs font-bold uppercase tracking-widest transition-colors">
                        <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                        Cetak / PDF
                    </button>
                </div>

                <!-- Table -->
                <div class="bg-surface-container border border-outline-variant rounded-xl overflow-hidden shadow-lg">
                    <div class="overflow-x-auto">
                        <table id="riwayatTable" class="w-full text-left print-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant bg-surface-container-low border-b border-outline-variant">#</th>
                                    <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant bg-surface-container-low border-b border-outline-variant">Pelanggan</th>
                                    <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant bg-surface-container-low border-b border-outline-variant">Layanan</th>
                                    <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant bg-surface-container-low border-b border-outline-variant">Harga</th>
                                    <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant bg-surface-container-low border-b border-outline-variant hidden sm:table-cell">Tanggal</th>
                                    <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant bg-surface-container-low border-b border-outline-variant">Waktu</th>
                                    <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-on-surface-variant bg-surface-container-low border-b border-outline-variant text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($completedQueues): ?>
                                    <?php $rowNo = 1; foreach ($completedQueues as $queue): ?>
                                    <tr class="border-b border-outline-variant hover:bg-surface-container-high/40 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="w-9 h-9 rounded-lg bg-primary/10 border border-primary/30 flex items-center justify-center text-primary font-black text-xs">
                                                <?= str_pad((string)$queue['no_antrian'], 3, '0', STR_PAD_LEFT) ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-bold text-on-surface text-sm"><?= htmlspecialchars($queue['nama_pelanggan']) ?></p>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-on-surface-variant"><?= htmlspecialchars($queue['nama_layanan']) ?></td>
                                        <td class="px-4 py-3 text-sm font-bold text-primary whitespace-nowrap">Rp <?= number_format((int)$queue['harga'], 0, ',', '.') ?></td>
                                        <td class="px-4 py-3 text-xs text-on-surface-variant whitespace-nowrap hidden sm:table-cell"><?= date('d M Y', strtotime($queue['tanggal'])) ?></td>
                                        <td class="px-4 py-3 text-xs text-on-surface-variant whitespace-nowrap"><?= date('H:i', strtotime($queue['waktu_dibuat'])) ?> WIB</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold bg-primary/10 text-primary border border-primary/30 uppercase tracking-widest">
                                                <span class="material-symbols-outlined text-[12px]">check_circle</span> Selesai
                                            </span>
                                        </td>
                                    </tr>
                                    <?php $rowNo++; endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-4 py-12 text-center">
                                            <span class="material-symbols-outlined text-4xl text-outline mb-2 block">history</span>
                                            <p class="text-base font-bold text-on-surface mb-1">Belum Ada Sesi Selesai</p>
                                            <p class="text-sm text-on-surface-variant">Belum ada data untuk periode <strong><?= htmlspecialchars($filterLabel) ?></strong>.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if ($completedQueues): ?>
                            <tfoot>
                                <tr class="bg-surface-container-low">
                                    <td colspan="3" class="px-4 py-3 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Total (<?= count($completedQueues) ?> sesi)</td>
                                    <td class="px-4 py-3 text-sm font-bold text-primary">Rp <?= number_format($revenueToday, 0, ',', '.') ?></td>
                                    <td colspan="3" class="hidden sm:table-cell"></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>

<?php barber_mobile_nav('riwayat'); ?>
<?php barber_scripts(); ?>
</body>
</html>
