<?php
include "_bootstrap.php";
include "_chrome.php";

// Fetch all transactions
$trxSql = "SELECT t.*, a.tanggal, a.waktu_dibuat, l.nama_layanan, 
            COALESCE(NULLIF(u.nama, ''), u.username, 'Pelanggan') AS nama_pelanggan,
            COALESCE(b.nama, '-') AS nama_barber
           FROM transaksi t
           JOIN antrian a ON a.id = t.antrian_id
           JOIN layanan l ON a.layanan_id = l.id
           JOIN users u ON a.pelanggan_id = u.id_user
           LEFT JOIN barber b ON a.barber_id = b.id
           ORDER BY t.waktu_bayar DESC";
$transaksi = mysqli_query($conn, $trxSql);

// Prepare monthly data for chart
$monthsLine = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "Mei",
    "Jun",
    "Jul",
    "Agu",
    "Sep",
    "Okt",
    "Nov",
    "Des",
];
$revenueLine = [];
for ($i = 1; $i <= 12; $i++) {
    $revenueLine[] = $adminMonthlyRevenue[$i] ?? 0;
}
?>
<?php admin_header("Transaksi", "transaksi"); ?>
    <div id="transaksiWrapper" class="p-md">
        <!-- Floating Action Bar for Preview Mode -->
        <div id="previewActionBar" class="fixed top-0 left-0 right-0 h-16 bg-surface border-b border-outline-variant z-[9999] flex items-center justify-between px-4 sm:px-6 shadow-2xl transition-transform -translate-y-full duration-300 no-print">
            <div>
                <h3 class="font-headline-md text-primary font-bold text-base sm:text-lg">Pratinjau Laporan Transaksi</h3>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <button onclick="window.print()" class="bg-primary text-on-primary font-bold px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg flex items-center gap-1 sm:gap-2 hover:opacity-90 transition-opacity text-xs sm:text-sm">
                    <span class="material-symbols-outlined text-[16px] sm:text-[18px]">print</span> Cetak
                </button>
                <button onclick="closePreview()" class="bg-surface-container-high border border-outline-variant text-on-surface hover:text-error hover:border-error px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg flex items-center gap-1 sm:gap-2 font-bold transition-colors text-xs sm:text-sm">
                    <span class="material-symbols-outlined text-[16px] sm:text-[18px]">close</span> Tutup
                </button>
            </div>
        </div>

        <div class="flex justify-between items-end mb-lg mt-4">
            <div>
                <h1 class="font-headline-lg text-headline-lg text-on-surface">Manajemen Transaksi</h1>
                <p class="text-on-surface-variant">Pantau riwayat pembayaran dan pendapatan Barbershop secara keseluruhan</p>
            </div>
        </div>
        
        <!-- Summary Dashboard -->
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-lg">
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Est. Pendapatan Hari Ini</p>
                <div class="flex justify-between items-center mt-2">
                    <h2 class="text-2xl font-bold font-headline-md text-on-surface">Rp <?= number_format(
                        $adminDashboardStats["revenueToday"],
                        0,
                        ",",
                        ".",
                    ) ?></h2>
                    <span class="material-symbols-outlined text-outline-variant">payments</span>
                </div>
            </article>
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Est. Pendapatan Bulan Ini</p>
                <div class="flex justify-between items-center mt-2">
                    <h2 class="text-2xl font-bold font-headline-md text-on-surface">Rp <?= number_format(
                        $adminDashboardStats["revenueMonth"],
                        0,
                        ",",
                        ".",
                    ) ?></h2>
                    <span class="material-symbols-outlined text-outline-variant">account_balance_wallet</span>
                </div>
            </article>
            <article class="bg-surface-container border border-outline-variant p-5 rounded-xl shadow-sm">
                <p class="text-[10px] font-bold uppercase tracking-widest text-on-surface-variant text-primary">Total Transaksi Selesai (Hari ini)</p>
                <div class="flex justify-between items-center mt-2">
                    <h2 class="text-2xl font-bold font-headline-md text-on-surface"><?= $adminDashboardStats[
                        "completedToday"
                    ] ?> Transaksi</h2>
                    <span class="material-symbols-outlined text-outline-variant">receipt_long</span>
                </div>
            </article>
        </section>

        <!-- Chart Section -->
        <section class="mb-lg">
            <article class="bg-surface-container border border-outline-variant p-6 rounded-xl shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-headline-md text-lg text-primary">Grafik Pendapatan</h3>
                        <p class="text-xs text-on-surface-variant font-bold uppercase tracking-widest">Tren Pendapatan Tahun <?= date(
                            "Y",
                        ) ?></p>
                    </div>
                </div>
                <div class="relative h-72 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </article>
        </section>

        <!-- Data Table Section -->
        <section id="laporanContainer" class="grid grid-cols-1 gap-6 lg:grid-cols-12 mb-lg">
            <article class="bg-surface-container border border-outline-variant overflow-hidden rounded-xl shadow-lg lg:col-span-12">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-6 border-b border-outline-variant bg-surface-container-low">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-primary/20 text-primary border border-primary/30 rounded-lg flex items-center justify-center">
                            <span class="material-symbols-outlined">history</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold font-headline-md text-primary">Riwayat Semua Transaksi</h2>
                            <p class="mt-1 text-xs font-bold uppercase tracking-widest text-on-surface-variant">Laporan Data Pembayaran Pelanggan</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3 sm:mt-0">
                        <button onclick="openPreview()" class="border border-outline-variant bg-surface rounded-lg px-4 py-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-on-surface hover:text-primary hover:border-primary transition-colors shadow-sm"><span class="material-symbols-outlined text-[18px]">visibility</span> Laporan</button>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="w-full overflow-x-auto">
                        <table id="transaksiTable" class="w-full whitespace-nowrap text-left" style="width: 100% !important;">
                            <thead>
                                <tr>
                                    <th class="px-4 py-4 border-b border-outline-variant">Faktur / Waktu</th>
                                    <th class="px-4 py-4 border-b border-outline-variant">Pelanggan & Kapster</th>
                                    <th class="px-4 py-4 border-b border-outline-variant">Layanan</th>
                                    <th class="px-4 py-4 border-b border-outline-variant">Subtotal</th>
                                    <th class="px-4 py-4 border-b border-outline-variant">Metode</th>
                                    <th class="px-4 py-4 border-b border-outline-variant">Status</th>
                                    <th class="px-4 py-4 border-b border-outline-variant text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (
                                    $transaksi &&
                                    mysqli_num_rows($transaksi) > 0
                                ): ?>
                                    <?php
                                    mysqli_data_seek($transaksi, 0);
                                    while (
                                        $trx = mysqli_fetch_assoc($transaksi)
                                    ): ?>
                                        <tr class="hover:bg-surface-container-high/50 transition-colors">
                                            <td class="px-4 py-4 align-middle">
                                                <p class="text-sm font-bold text-on-surface uppercase tracking-wider text-primary">#TRX-<?= str_pad(
                                                    (string) $trx["id"],
                                                    5,
                                                    "0",
                                                    STR_PAD_LEFT,
                                                ) ?></p>
                                                <p class="text-[10px] font-bold text-on-surface-variant mt-1"><?= date(
                                                    "d M Y - H:i",
                                                    strtotime(
                                                        $trx["waktu_bayar"],
                                                    ),
                                                ) ?></p>
                                            </td>
                                            <td class="px-4 py-4 align-middle">
                                                <p class="text-sm font-bold text-on-surface"><?= htmlspecialchars(
                                                    $trx["nama_pelanggan"],
                                                ) ?></p>
                                                <p class="text-[10px] uppercase tracking-widest text-on-surface-variant mt-1 font-bold">Kapster: <?= htmlspecialchars(
                                                    $trx["nama_barber"],
                                                ) ?></p>
                                            </td>
                                            <td class="px-4 py-4 text-sm font-medium text-on-surface align-middle">
                                                <?= htmlspecialchars(
                                                    $trx["nama_layanan"],
                                                ) ?>
                                            </td>
                                            <td class="px-4 py-4 text-sm font-bold text-on-surface align-middle">
                                                Rp <?= number_format(
                                                    (int) $trx["total_harga"],
                                                    0,
                                                    ",",
                                                    ".",
                                                ) ?>
                                            </td>
                                            <td class="px-4 py-4 text-sm font-bold text-on-surface align-middle uppercase">
                                                <?= htmlspecialchars(
                                                    $trx["metode_pembayaran"] ??
                                                        "CASH",
                                                ) ?>
                                            </td>
                                            <td class="px-4 py-4 align-middle">
                                                <?php if (
                                                    strtolower(
                                                        $trx[
                                                            "status_pembayaran"
                                                        ],
                                                    ) === "lunas"
                                                ): ?>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold bg-primary/10 text-primary border border-primary/30 uppercase tracking-widest">
                                                        <span class="material-symbols-outlined text-[14px]">check_circle</span> Lunas
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold bg-error-container/30 text-error border border-error border-opacity-50 uppercase tracking-widest">
                                                        <span class="material-symbols-outlined text-[14px]">pending</span> Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-4 text-right align-middle">
                                                <button onclick="showDetailTransaksi(<?= (int) $trx[
                                                    "id"
                                                ] ?>, <?= htmlspecialchars(
    json_encode($trx["nama_pelanggan"]),
    ENT_QUOTES,
) ?>, <?= htmlspecialchars(
    json_encode($trx["nama_barber"]),
    ENT_QUOTES,
) ?>, <?= htmlspecialchars(
    json_encode($trx["nama_layanan"]),
    ENT_QUOTES,
) ?>, <?= (int) $trx["total_harga"] ?>, <?= htmlspecialchars(
    json_encode($trx["status_pembayaran"]),
    ENT_QUOTES,
) ?>, <?= htmlspecialchars(
    json_encode(date("d M Y H:i", strtotime($trx["waktu_bayar"]))),
    ENT_QUOTES,
) ?>, <?= htmlspecialchars(
    json_encode($trx["metode_pembayaran"] ?? "CASH"),
    ENT_QUOTES,
) ?>)" class="inline-flex h-8 w-8 items-center justify-center border border-outline-variant text-on-surface-variant rounded-lg transition-colors hover:text-primary hover:border-primary hover:bg-surface-container-high" title="Lihat Detail">
                                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                    ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
        </section>
    </div>

    <style>
        /* CSS khusus memperbaiki isu lebar tabel mengecil (50%) karena DataTables 2 Flex Layout */
        .dt-container .dt-layout-row:has(table) {
            width: 100% !important;
            flex-basis: 100% !important;
        }
        .dt-container .dt-layout-cell {
            flex-grow: 1 !important;
        }
        .dt-layout-table, .dt-layout-table table, #transaksiTable {
            width: 100% !important;
            min-width: 100% !important;
        }
        table.dataTable thead th, table.dataTable tbody td {
            white-space: nowrap;
        }
    </style>
<script>
    new DataTable('#transaksiTable', {
        pageLength: 20,
        pagingType: 'simple_numbers',
        autoWidth: false,
        lengthMenu: [10, 20, 50, 100],
        order: [[0, 'desc']],
        language: {
            search: 'Cari Transaksi:',
            lengthMenu: 'Tampilkan _MENU_ baris',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ transaksi',
            infoEmpty: 'Tidak ada data',
            emptyTable: 'Belum ada data transaksi',
            paginate: { previous: '‹', next: '›' }
        }
    });

    function showDetailTransaksi(id, pelanggan, kapster, layanan, total, status, waktu, metode) {
        const totalFmt = 'Rp ' + total.toLocaleString('id-ID');
        const isLunas = status.toLowerCase() === 'lunas';
        const statusColor = isLunas ? '#f2ca50' : '#ffb4ab';
        const statusBg   = isLunas ? 'rgba(242,202,80,.12)' : 'rgba(147,0,10,.2)';
        const statusBorder = isLunas ? 'rgba(242,202,80,.3)' : 'rgba(255,180,171,.3)';
        const statusLabel  = isLunas ? 'LUNAS' : 'PENDING';
        const statusIcon   = isLunas ? 'check_circle' : 'pending';
        Swal.fire({
            background: '#1e2020',
            color: '#e2e2e2',
            confirmButtonColor: '#f2ca50',
            confirmButtonText: 'Tutup',
            width: '440px',
            html: `
                <div style="text-align:left;font-family:Inter,sans-serif;">
                    <div style="text-align:center;padding-bottom:16px;border-bottom:2px dashed #4d4635;margin-bottom:16px;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:.2em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">Barber.co — Kwitansi Transaksi</p>
                        <p style="font-size:22px;font-weight:800;color:#f2ca50;letter-spacing:.05em;margin:0;">#TRX-${String(id).padStart(5,'0')}</p>
                        <p style="font-size:11px;color:#99907c;margin:6px 0 0;">${waktu}</p>
                    </div>
                    <div style="display:grid;gap:10px;margin-bottom:16px;">
                        <div style="display:flex;justify-content:space-between;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#99907c;">Pelanggan</span>
                            <span style="font-size:13px;font-weight:600;color:#e2e2e2;">${pelanggan}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#99907c;">Kapster</span>
                            <span style="font-size:13px;font-weight:600;color:#e2e2e2;">${kapster}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#99907c;">Layanan</span>
                            <span style="font-size:13px;font-weight:600;color:#e2e2e2;">${layanan}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;">
                            <span style="font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#99907c;">Metode Bayar</span>
                            <span style="font-size:13px;font-weight:600;color:#e2e2e2;text-transform:uppercase;">${metode}</span>
                        </div>
                    </div>
                    <div style="border-top:2px dashed #4d4635;padding-top:14px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <p style="font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#99907c;margin:0 0 4px;">Total Pembayaran</p>
                            <p style="font-size:24px;font-weight:800;color:#f2ca50;margin:0;">${totalFmt}</p>
                        </div>
                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:${statusColor};background:${statusBg};padding:6px 12px;border-radius:6px;border:1px solid ${statusBorder};">
                            <span class="material-symbols-outlined" style="font-size:14px;">${statusIcon}</span>${statusLabel}
                        </span>
                    </div>
                </div>
            `
        });
    }

    // Chart.js - Revenue Line Chart
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    
    // Gradient fill for line chart
    const gradientFill = ctxRevenue.createLinearGradient(0, 0, 0, 300);
    gradientFill.addColorStop(0, 'rgba(242, 202, 80, 0.4)');
    gradientFill.addColorStop(1, 'rgba(242, 202, 80, 0.0)');

    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: <?= json_encode($monthsLine) ?>,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($revenueLine) ?>,
                backgroundColor: gradientFill,
                borderColor: '#f2ca50',
                borderWidth: 2,
                pointBackgroundColor: '#121414',
                pointBorderColor: '#f2ca50',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4 // Smooth curves
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { 
                    backgroundColor: '#1a1c1c', 
                    titleColor: '#f2ca50', 
                    bodyColor: '#e2e2e2', 
                    borderColor: '#4d4635', 
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed.y !== null) {
                                label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: { 
                    ticks: { color: '#d0c5af', font: { family: 'Inter', size: 12 } }, 
                    grid: { color: 'rgba(51, 53, 53, 0.5)', drawBorder: false } 
                },
                y: { 
                    ticks: { 
                        color: '#d0c5af',
                        font: { family: 'Inter', size: 12 },
                        callback: function(value, index, values) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: "compact", compactDisplay: "short" }).format(value);
                        }
                    }, 
                    grid: { color: 'rgba(51, 53, 53, 0.5)', drawBorder: false }, 
                    beginAtZero: true 
                }
            }
        }
    });

    // Mode Pratinjau CSS Handler
    function openPreview() {
        document.body.classList.add('preview-mode');
        document.getElementById('previewActionBar').classList.remove('-translate-y-full');
        // Tutup otomatis sidebar jika tidak tersembunyi
        if (!document.body.classList.contains('admin-sidebar-collapsed')) {
            document.body.classList.add('admin-sidebar-collapsed');
        }
    }

    function closePreview() {
        document.body.classList.remove('preview-mode');
        document.getElementById('previewActionBar').classList.add('-translate-y-full');
    }

    // Custom CSS for printing and preview
    const style = document.createElement('style');
    style.innerHTML = `
        /* CSS PRATINJAU */
        body.preview-mode { background: #d0c5af !important; animation: none !important; }
        body.preview-mode #sidebar, body.preview-mode header { display: none !important; }
        body.preview-mode #transaksiWrapper > *:not(#previewActionBar):not(#laporanContainer) { display: none !important; }
        body.preview-mode main { margin: 0 !important; margin-top: 64px !important; padding: 20px !important; width: 100% !important; background: #d0c5af !important; }
        body.preview-mode .bg-surface-container { background: white !important; border: none !important; box-shadow: 0 10px 40px rgba(0,0,0,0.15) !important; border-radius: 4px !important; max-width: 900px !important; margin: 0 auto !important; padding: 40px 30px !important; }
        
        /* Tambahkan KOP SURAT buatan lewat pseudo-element hanya di preview dan print */
        body.preview-mode .bg-surface-container::before {
            content: "BARBER.CO\\A Laporan Transaksi Barbershop\\A Dicetak: <?= date('d M Y') ?>";
            white-space: pre-wrap;
            display: block;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: black;
            border-bottom: 2px solid black;
            padding-bottom: 16px;
            margin-bottom: 24px;
            line-height: 1.5;
            font-family: serif;
        }

        body.preview-mode table { border-collapse: collapse !important; width: 100% !important; }
        body.preview-mode th, body.preview-mode td { border: 1px solid #999 !important; color: black !important; padding: 8px !important; font-size: 12px !important; }
        body.preview-mode th { background: #f0f0f0 !important; color: black !important; font-weight: bold !important; text-transform: uppercase; font-size: 10px !important; border-bottom: 2px solid #555 !important; }
        body.preview-mode h1, body.preview-mode h2, body.preview-mode h3, body.preview-mode p, body.preview-mode span { color: black !important; }
        body.preview-mode tr:hover td { background-color: transparent !important; }
        body.preview-mode .dataTables_wrapper .dataTables_length, body.preview-mode .dataTables_wrapper .dataTables_filter, body.preview-mode .dataTables_wrapper .dataTables_info, body.preview-mode .dataTables_wrapper .dataTables_paginate { display: none !important; }
        
        /* Hilangkan elemen hiasan tambahan saat preview */
        body.preview-mode .w-10.h-10 { display: none !important; }
        body.preview-mode .flex.items-center.gap-3 div:last-child h2, body.preview-mode .flex.items-center.gap-3 div:last-child p { display: none !important; }

        @media print {
            body { background: white !important; color: black !important; animation: none !important; }
            #sidebar, header, .no-print, section:not(:last-child) { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; width: 100% !important; background: white !important; }
            .bg-surface-container { background: transparent !important; border: none !important; box-shadow: none !important; max-width: 100% !important; padding: 0 !important; }
            
            .bg-surface-container::before {
                content: "BARBER.CO\\A Laporan Transaksi Barbershop\\A Dicetak: <?= date('d M Y') ?>";
                white-space: pre-wrap;
                display: block;
                text-align: center;
                font-size: 16px;
                font-weight: bold;
                color: black;
                border-bottom: 2px solid black;
                padding-bottom: 16px;
                margin-bottom: 24px;
                line-height: 1.5;
                font-family: serif;
            }

            table { border-collapse: collapse !important; width: 100% !important; margin-top: 0px;}
            th, td { border: 1px solid #ccc !important; color: black !important; padding: 6px 8px !important; font-size: 11px !important;}
            th { background: #f0f0f0 !important; color: black !important; font-weight: bold !important; font-size: 9px !important; border-bottom: 2px solid #555 !important; }
            h1, h2, h3, p, span { color: black !important; }
            .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate { display: none !important; }
        }
    `;
    document.head.appendChild(style);
</script>
<?php admin_footer("transaksi"); ?>
