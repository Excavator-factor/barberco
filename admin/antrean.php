<?php
include '_bootstrap.php';
include '_chrome.php';
?>
<?php admin_header('Antrean', 'antrean'); ?>
    <div class="p-md">
        <div class="flex justify-between items-end mb-lg mt-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Antrean</h1>
                <p class="text-on-surface-variant">Status pelayanan harian</p>
            </div>
            <button type="button" onclick="window.print()" class="no-print bg-surface-container border border-outline-variant px-4 py-2 rounded flex items-center gap-xs text-on-surface-variant font-label-caps hover:bg-primary hover:text-on-primary transition-colors">
                <span class="material-symbols-outlined text-sm">print</span>
                Cetak
            </button>
        </div>
        
        <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm"><p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Hari Ini</p><div class="flex justify-between mt-2"><h2 class="text-2xl font-bold font-headline-md text-on-surface"><?= $adminDashboardStats['bookingsToday']; ?></h2><span class="material-symbols-outlined text-outline-variant">today</span></div></article>
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm"><p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Sedang/Belum Dilayani</p><div class="flex justify-between mt-2"><h2 class="text-2xl font-bold font-headline-md text-on-surface"><?= $adminDashboardStats['liveQueue']; ?></h2><span class="material-symbols-outlined text-outline-variant">hourglass_empty</span></div></article>
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm"><p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Selesai Hari Ini</p><div class="flex justify-between mt-2"><h2 class="text-2xl font-bold font-headline-md text-on-surface"><?= $adminDashboardStats['completedToday']; ?></h2><span class="material-symbols-outlined text-outline-variant">check_circle</span></div></article>
        </section>

        <?php
        // Prepare chart data
        $statusCounts = ['menunggu' => 0, 'proses' => 0, 'selesai' => 0];
        $barberLoads = [];

        if ($adminQueues && mysqli_num_rows($adminQueues) > 0) {
            mysqli_data_seek($adminQueues, 0);
            while ($q = mysqli_fetch_assoc($adminQueues)) {
                $st = strtolower($q['status_antrian']);
                if (isset($statusCounts[$st])) {
                    $statusCounts[$st]++;
                }
                
                $bName = $q['nama_barber'] ?: 'Belum dipilih';
                if (!isset($barberLoads[$bName])) {
                    $barberLoads[$bName] = 0;
                }
                $barberLoads[$bName]++;
            }
        }
        ?>

        <!-- Charts Section -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-lg">
            <!-- Queue Status Doughnut -->
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm">
                <div class="mb-4">
                    <h3 class="font-headline-md text-lg text-primary">Status Antrean</h3>
                    <p class="text-xs text-on-surface-variant font-bold uppercase tracking-widest">Proporsi Sesi Hari Ini</p>
                </div>
                <div class="relative h-64 w-full flex justify-center">
                    <canvas id="queueStatusChart"></canvas>
                </div>
            </article>

            <!-- Barber Workload Bar -->
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm">
                <div class="mb-4">
                    <h3 class="font-headline-md text-lg text-primary">Beban Kerja Kapster</h3>
                    <p class="text-xs text-on-surface-variant font-bold uppercase tracking-widest">Total Pelanggan Hari Ini</p>
                </div>
                <div class="relative h-64 w-full">
                    <canvas id="barberWorkloadChart"></canvas>
                </div>
            </article>
        </section>
        <section class="bg-surface-container border border-outline-variant overflow-hidden rounded-xl shadow-lg mb-lg">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-6 border-b border-outline-variant bg-surface-container-low">
                <div>
                    <h2 class="text-xl font-bold font-headline-md text-primary">Daftar Antrean</h2>
                    <p class="mt-1 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Status pelayanan harian barbershop</p>
                </div>
                <div class="flex flex-wrap gap-2 mt-3 sm:mt-0">
                    <button onclick="window.print()" class="border border-outline-variant bg-surface rounded-lg px-4 py-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-on-surface hover:text-primary hover:border-primary transition-colors no-print shadow-sm"><span class="material-symbols-outlined text-[18px]">print</span> Cetak</button>
                </div>
            </div>
            
            <div class="p-6 overflow-x-auto w-full">
                <table id="antreanTable" class="w-full min-w-full mx-auto text-center" style="width: 100% !important;">
                    <thead>
                        <tr>
                            <th class="px-4 py-4 text-center">No.</th>
                            <th class="px-4 py-4 text-center">Pelanggan</th>
                            <th class="px-4 py-4 text-center">Layanan</th>
                            <th class="px-4 py-4 text-center">Kapster</th>
                            <th class="px-4 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($adminQueues && mysqli_num_rows($adminQueues) > 0): ?>
                            <?php mysqli_data_seek($adminQueues, 0); while ($queue = mysqli_fetch_assoc($adminQueues)): $st = strtolower($queue['status_antrian']); ?>
                                <tr>
                                    <td class="px-4 py-4 text-lg font-bold text-primary align-middle"><?= str_pad((string) $queue['no_antrian'], 2, '0', STR_PAD_LEFT); ?></td>
                                    <td class="px-4 py-4 align-middle">
                                        <div class="font-bold text-on-surface"><?= htmlspecialchars($queue['nama_pelanggan']); ?></div>
                                        <div class="text-[10px] uppercase font-medium tracking-widest text-on-surface-variant mt-1"><?= date('H:i', strtotime($queue['waktu_dibuat'])); ?></div>
                                    </td>
                                    <td class="px-4 py-4 text-sm font-medium text-on-surface-variant align-middle"><?= htmlspecialchars($queue['nama_layanan']); ?></td>
                                    <td class="px-4 py-4 text-sm font-medium text-on-surface-variant align-middle"><?= htmlspecialchars($queue['nama_barber']); ?></td>
                                    <td class="px-4 py-4 align-middle text-center">
                                        <?php if ($st === 'proses'): ?>
                                            <span class="inline-flex items-center px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest rounded-full bg-primary/20 text-primary border border-primary/50"><?= htmlspecialchars($queue['status_antrian']); ?></span>
                                        <?php elseif ($st === 'selesai'): ?>
                                            <span class="inline-flex items-center px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest rounded-full bg-surface-container-high text-primary border border-outline-variant"><?= htmlspecialchars($queue['status_antrian']); ?></span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest rounded-full border border-outline-variant text-on-surface-variant"><?= htmlspecialchars($queue['status_antrian']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
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
    new DataTable('#antreanTable', {
        pageLength: 10,
        language: {
            search: 'Cari Antrean:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ antrean hari ini',
            emptyTable: 'Belum ada antrean'
        }
    });
</script>
<script>
    // Chart.js Configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#e2e2e2', font: { family: 'Inter', size: 12 } } },
            tooltip: { backgroundColor: '#1a1c1c', titleColor: '#f2ca50', bodyColor: '#e2e2e2', borderColor: '#4d4635', borderWidth: 1 }
        }
    };

    // Render Doughnut Chart for Queue Status
    const ctxStatus = document.getElementById('queueStatusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: ['Selesai', 'Di Kursi (Proses)', 'Menunggu'],
            datasets: [{
                data: [<?= $statusCounts['selesai']; ?>, <?= $statusCounts['proses']; ?>, <?= $statusCounts['menunggu']; ?>],
                backgroundColor: ['#1e2020', '#f2ca50', '#a3a3a3'],
                borderColor: ['#333535', '#121414', '#121414'],
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: { ...chartOptions, cutout: '70%', plugins: { ...chartOptions.plugins, legend: { position: 'right', labels: { color: '#e2e2e2', font: { family: 'Inter', size: 12 } } } } }
    });

    // Render Bar Chart for Barber Workload
    const ctxLoad = document.getElementById('barberWorkloadChart').getContext('2d');
    new Chart(ctxLoad, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_keys($barberLoads)); ?>,
            datasets: [{
                label: 'Total Antrean Diterima',
                data: <?= json_encode(array_values($barberLoads)); ?>,
                backgroundColor: 'rgba(242, 202, 80, 0.2)',
                borderColor: '#f2ca50',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            ...chartOptions,
            scales: {
                x: { ticks: { color: '#d0c5af' }, grid: { color: '#333535', drawBorder: false } },
                y: { ticks: { color: '#d0c5af', stepSize: 1 }, grid: { color: '#333535', drawBorder: false }, beginAtZero: true }
            }
        }
    });

    // Custom CSS for printing
    const style = document.createElement('style');
    style.innerHTML = `
        @media print {
            body { background: white !important; color: black !important; animation: none !important; }
            #sidebar, header, .no-print, section:not(:last-child) { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
            .bg-surface-container { background: transparent !important; border: none !important; box-shadow: none !important;}
            table { border-collapse: collapse !important; width: 100% !important; margin-top: 20px;}
            th, td { border: 1px solid #ddd !important; color: black !important; padding: 8px !important; }
            h1, h2, h3, p, span, div { color: black !important; }
            .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate { display: none !important; }
        }
    `;
    document.head.appendChild(style);
</script>
    </div>
<?php admin_footer('antrean'); ?>
