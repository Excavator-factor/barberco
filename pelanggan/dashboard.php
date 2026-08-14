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
