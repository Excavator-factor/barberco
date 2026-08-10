<?php
include '_bootstrap.php';
include '_chrome.php';

$waConfigPath = __DIR__ . '/../config/wa_gateway.json';
$waGateway = [
    'enabled' => false,
    'base_url' => '',
    'token' => '',
    'sender' => '',
    'template' => 'Halo {nama}, antrean Anda di Barber.co sudah diperbarui.',
];

if (is_file($waConfigPath)) {
    $decoded = json_decode((string) file_get_contents($waConfigPath), true);
    if (is_array($decoded)) {
        $waGateway = array_merge($waGateway, $decoded);
    }
}

$waNotice = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_wa_gateway'])) {
    $waGateway = [
        'enabled' => isset($_POST['wa_enabled']) ? true : false,
        'base_url' => trim((string) ($_POST['wa_base_url'] ?? '')),
        'token' => trim((string) ($_POST['wa_token'] ?? '')),
        'sender' => trim((string) ($_POST['wa_sender'] ?? '')),
        'template' => trim((string) ($_POST['wa_template'] ?? '')),
    ];

    if ($waGateway['template'] === '') {
        $waGateway['template'] = 'Halo {nama}, antrean Anda di Barber.co sudah diperbarui.';
    }

    @file_put_contents($waConfigPath, json_encode($waGateway, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $waNotice = 'Pengaturan WA Gateway tersimpan.';
}

$monthLabels = array_values($months);
$monthlyRevenueValues = array_values($adminMonthlyRevenue);
$queueByStatus = [
    'menunggu' => admin_scalar_query($conn, "SELECT COUNT(*) AS total FROM antrian WHERE tanggal = '$today' AND status_antrian = 'menunggu'"),
    'proses' => admin_scalar_query($conn, "SELECT COUNT(*) AS total FROM antrian WHERE tanggal = '$today' AND status_antrian = 'proses'"),
    'selesai' => $adminDashboardStats['completedToday'],
];

// Popular Services (Layanan Terpopuler)
$popularServicesQuery = mysqli_query($conn, "SELECT l.nama_layanan, COUNT(a.id) as total FROM layanan l JOIN antrian a ON a.layanan_id = l.id GROUP BY l.id ORDER BY total DESC LIMIT 5");
$popLabels = [];
$popData = [];
if ($popularServicesQuery) {
    while ($row = mysqli_fetch_assoc($popularServicesQuery)) {
        $popLabels[] = $row['nama_layanan'];
        $popData[] = (int) $row['total'];
    }
}
$popLabelsJson = json_encode($popLabels);
$popDataJson = json_encode($popData);
?>
<?php admin_header('Ringkasan', 'dashboard'); ?>
            <!-- Page Header -->
            <div class="flex justify-between items-end mb-lg mt-4">
                <div>
                    <h1 class="font-headline-lg text-headline-lg text-on-surface">Ringkasan Dashboard</h1>
                    <p class="text-on-surface-variant">Metrik real-time untuk Barber.co Premium Grooming.</p>
                </div>
            </div>

            <?php if ($waNotice): ?>
                <div class="mb-lg border border-primary bg-primary/10 px-4 py-3 text-sm font-semibold text-primary rounded"><?= htmlspecialchars($waNotice); ?></div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-md mb-lg">
                <div class="bg-surface-container p-md border border-outline-variant border-t-2 border-t-primary flex justify-between">
                    <div>
                        <p class="font-label-caps text-on-surface-variant mb-1">Pendapatan Hari Ini</p>
                        <h2 class="text-headline-lg font-bold text-white">Rp <?= number_format($adminDashboardStats['revenueToday'], 0, ',', '.'); ?></h2>
                        <p class="text-primary text-xs flex items-center gap-1 font-bold mt-1">
                            <span class="material-symbols-outlined text-sm">trending_up</span> Penjualan Hari Ini
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-primary opacity-20 text-4xl">payments</span>
                </div>
                <div class="bg-surface-container p-md border border-outline-variant border-t-2 border-t-primary flex justify-between">
                    <div>
                        <p class="font-label-caps text-on-surface-variant mb-1">Antrean Aktif</p>
                        <h2 class="text-headline-lg font-bold text-white"><?= $adminDashboardStats['liveQueue']; ?></h2>
                        <p class="text-on-surface-variant text-xs flex items-center gap-1 mt-1">
                            <span class="material-symbols-outlined text-sm">groups</span> Menunggu & Proses
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-primary opacity-20 text-4xl">person</span>
                </div>
                <div class="bg-surface-container p-md border border-outline-variant border-t-2 border-t-primary flex justify-between">
                    <div>
                        <p class="font-label-caps text-on-surface-variant mb-1">Layanan Aktif</p>
                        <h2 class="text-headline-lg font-bold text-white"><?= $adminDashboardStats['totalServices']; ?></h2>
                        <p class="text-primary text-xs flex items-center gap-1 font-bold mt-1">
                            <span class="material-symbols-outlined text-sm">verified</span> Ditawarkan
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-primary opacity-20 text-4xl">inventory_2</span>
                </div>
                <div class="bg-surface-container p-md border border-outline-variant border-t-2 border-t-primary flex justify-between">
                    <div>
                        <p class="font-label-caps text-on-surface-variant mb-1">Produktivitas</p>
                        <h2 class="text-headline-lg font-bold text-white"><?= $productivity; ?>%</h2>
                        <p class="text-primary text-xs flex items-center gap-1 font-bold mt-1">
                            <span class="material-symbols-outlined text-sm">star</span> Target Tercapai
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-primary opacity-20 text-4xl">monitoring</span>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-md mb-lg">
                <div class="bg-surface-container border border-outline-variant p-md">
                    <div class="flex justify-between items-center mb-md border-b border-outline-variant pb-2">
                        <h3 class="font-headline-md text-white">Pendapatan Bulanan (<?= date('Y'); ?>)</h3>
                        <a href="layanan.php" class="text-xs text-primary underline">Detail Layanan</a>
                    </div>
                    <div class="h-64 mt-4 relative">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                <div class="bg-surface-container border border-outline-variant p-md flex flex-col rounded-xl shadow-lg">
                    <div class="flex justify-between items-center mb-md border-b border-outline-variant pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">emoji_events</span>
                            <h3 class="font-headline-md text-white tracking-wide">Kinerja Kapster</h3>
                        </div>
                        <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-widest bg-surface-container-high px-2 py-1 rounded border border-outline-variant">Bulan Ini</span>
                    </div>
                    
                    <div class="h-64 overflow-y-auto relative pr-2 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:bg-outline-variant [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:rounded-full">
                        <div class="flex flex-col gap-3">
                        <?php 
                        $leaderboardQuery = mysqli_query($conn, "
                            SELECT b.id, b.nama AS nama_barber, u.username, u.avatar, 
                                   COUNT(a.id) AS total_layanan, 
                                   SUM(t.total_harga) AS total_pendapatan
                            FROM barber b
                            JOIN antrian a ON a.barber_id = b.id
                            JOIN transaksi t ON t.antrian_id = a.id
                            JOIN users u ON b.user_id = u.id_user
                            WHERE a.status_antrian = 'selesai' AND t.status_pembayaran = 'lunas' 
                                  AND MONTH(a.tanggal) = MONTH(CURRENT_DATE()) AND YEAR(a.tanggal) = YEAR(CURRENT_DATE())
                            GROUP BY b.id
                            ORDER BY total_pendapatan DESC, total_layanan DESC
                            LIMIT 5
                        ");
                        if ($leaderboardQuery && mysqli_num_rows($leaderboardQuery) > 0) {
                            $rank = 1;
                            while ($lbRow = mysqli_fetch_assoc($leaderboardQuery)) {
                                $rankColor = $rank === 1 ? 'text-yellow-400 border-yellow-400' : ($rank === 2 ? 'text-gray-300 border-gray-300' : ($rank === 3 ? 'text-amber-600 border-amber-600' : 'text-on-surface-variant border-outline-variant'));
                                ?>
                                <div class="flex items-center justify-between p-3 rounded-lg border border-outline-variant hover:bg-surface-container-high transition-all duration-300 group">
                                    <div class="flex items-center gap-4">
                                        <div class="relative w-11 h-11 flex-shrink-0">
                                            <?php if (!empty($lbRow['avatar']) && file_exists(__DIR__ . "/../uploads/avatars/" . $lbRow['avatar'])): ?>
                                                <img src="../uploads/avatars/<?= htmlspecialchars($lbRow['avatar']) ?>" alt="Avatar" class="w-full h-full rounded-full object-cover border border-outline-variant shadow-sm">
                                            <?php else: ?>
                                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($lbRow['username']) ?>&background=282a2b&color=f2ca50&rounded=true&bold=true" alt="Avatar" class="w-full h-full rounded-full object-cover border border-outline-variant shadow-sm">
                                            <?php endif; ?>
                                            <div class="absolute -top-1.5 -left-1.5 w-5 h-5 flex items-center justify-center bg-surface border rounded-full text-[10px] font-bold <?= $rankColor; ?>">
                                                <?= $rank; ?>
                                            </div>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-[13px] text-on-surface mb-0.5 truncate max-w-[120px] group-hover:text-primary transition-colors"><?= htmlspecialchars($lbRow['nama_barber']); ?></h4>
                                            <div class="text-[11px] text-on-surface-variant flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[14px]">content_cut</span>
                                                <span><?= (int)$lbRow['total_layanan']; ?> Layanan Selesai</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[14px] font-bold text-primary block mb-0.5">Rp <?= number_format($lbRow['total_pendapatan'], 0, ',', '.'); ?></span>
                                    </div>
                                </div>
                                <?php
                                $rank++;
                            }
                        } else {
                            echo '<div class="absolute inset-0 flex items-center justify-center text-on-surface-variant text-sm italic text-center px-4">Belum ada data kinerja kapster bulan ini.</div>';
                        }
                        ?>
                        </div>
                    </div>
                </div>
            </div>

            <section class="grid grid-cols-1 md:grid-cols-2 gap-md mb-lg">
                <!-- Popular Services Chart -->
                <div class="bg-surface-container border border-outline-variant p-md">
                    <div class="flex items-center gap-base mb-md border-b border-outline-variant pb-2">
                        <span class="material-symbols-outlined text-primary">content_cut</span>
                        <h3 class="font-headline-md text-white">Potongan Populer</h3>
                    </div>
                    <div class="h-64 mt-4 relative">
                        <?php if (empty($popData)): ?>
                            <div class="absolute inset-0 flex items-center justify-center text-on-surface-variant italic">Belum ada data pesanan.</div>
                        <?php else: ?>
                            <canvas id="popularServiceChart"></canvas>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- WhatsApp Gateway Settings -->
                <div class="bg-surface-container border border-outline-variant p-md" id="whatsapp">
                    <div class="flex items-center gap-base mb-md justify-between border-b border-outline-variant pb-2">
                        <div class="flex items-center gap-base">
                            <span class="material-symbols-outlined text-primary">settings_input_component</span>
                            <h3 class="font-headline-md text-white">WhatsApp Gateway</h3>
                        </div>
                        <span class="px-2 py-1 rounded bg-surface-container-high border border-outline-variant text-[10px] font-bold text-on-surface-variant">
                            <?= !empty($waGateway['enabled']) ? 'ACTIVE' : 'DISABLED'; ?>
                        </span>
                    </div>
                    
                    <form method="POST" class="space-y-4">
                        <label class="flex items-center gap-2 text-sm text-on-surface">
                            <input type="checkbox" name="wa_enabled" class="bg-background border-outline-variant text-primary rounded ring-0 focus:ring-primary focus:ring-offset-background" <?= !empty($waGateway['enabled']) ? 'checked' : ''; ?>>
                            Aktifkan Notifikasi WhatsApp
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-label-caps text-on-surface-variant mb-xs">URL Dasar</label>
                                <input name="wa_base_url" class="w-full bg-background border border-outline-variant p-2 text-sm focus:border-primary focus:ring-0 rounded text-on-surface" type="text" value="<?= htmlspecialchars($waGateway['base_url']); ?>" placeholder="https://gateway.example.com/api/send"/>
                            </div>
                            <div>
                                <label class="block font-label-caps text-on-surface-variant mb-xs">Token</label>
                                <input name="wa_token" class="w-full bg-background border border-outline-variant p-2 text-sm focus:border-primary focus:ring-0 rounded text-on-surface" type="text" value="<?= htmlspecialchars($waGateway['token']); ?>"/>
                            </div>
                            <div>
                                <label class="block font-label-caps text-on-surface-variant mb-xs">Pengirim / ID Perangkat</label>
                                <input name="wa_sender" class="w-full bg-background border border-outline-variant p-2 text-sm focus:border-primary focus:ring-0 rounded text-on-surface" type="text" value="<?= htmlspecialchars($waGateway['sender']); ?>"/>
                            </div>
                            <div>
                                <label class="block font-label-caps text-on-surface-variant mb-xs">Templat Pesan</label>
                                <input name="wa_template" class="w-full bg-background border border-outline-variant p-2 text-sm focus:border-primary focus:ring-0 rounded text-on-surface" type="text" value="<?= htmlspecialchars($waGateway['template']); ?>" placeholder="Halo {nama}..."/>
                            </div>
                        </div>
                        <button type="submit" name="save_wa_gateway" value="1" class="bg-primary text-on-primary font-bold w-full py-3 rounded active:scale-95 transition-transform mt-4 text-sm mt-2">SIMPAN KONFIGURASI GATEWAY</button>
                    </form>
                </div>
            </section>
<script>
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($monthLabels); ?>,
                datasets: [{
                    label: 'Pendapatan',
                    data: <?= json_encode($monthlyRevenueValues); ?>,
                    borderColor: '#f2ca50',
                    backgroundColor: 'rgba(242, 202, 80, 0.1)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2,
                    pointBackgroundColor: '#f2ca50'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#b7b5b4' }
                    },
                    y: { 
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { 
                            callback: (value) => 'Rp ' + value.toLocaleString('id-ID'),
                            color: '#b7b5b4'
                        } 
                    }
                }
            }
        });
    }


    const popularCtx = document.getElementById('popularServiceChart');
    if (popularCtx) {
        new Chart(popularCtx, {
            type: 'bar',
            data: {
                labels: <?= $popLabelsJson; ?>,
                datasets: [{
                    label: 'Total Dipesan',
                    data: <?= $popDataJson; ?>,
                    backgroundColor: '#f2ca50',
                    borderRadius: 4,
                    hoverBackgroundColor: '#e9c349'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#b7b5b4', font: { family: 'Inter', size: 10 } } },
                    y: { 
                        grid: { color: 'rgba(255, 255, 255, 0.05)' }, 
                        ticks: { color: '#b7b5b4', font: { family: 'Inter', size: 10 }, min: 0, precision: 0 } 
                    }
                }
            }
        });
    }
</script>

<?php admin_footer('dashboard'); ?>
