<?php
include '_bootstrap.php';
include '_chrome.php';

// Fetch all services from the layanan table
$services = [];
$sql_services = "SELECT * FROM layanan ORDER BY " . ($pk_layanan ?? '1') . " ASC";
$res_services = @mysqli_query($conn, $sql_services);
if ($res_services) {
    while ($row = mysqli_fetch_assoc($res_services)) {
        $services[] = $row;
    }
}

// Fetch description column if exists
$col_l_desc    = getExistingCol($conn, 'layanan', ['deskripsi', 'description', 'keterangan', 'desc']);
$col_l_gambar  = getExistingCol($conn, 'layanan', ['gambar', 'foto', 'image', 'foto_layanan']);

// Fallback Unsplash barbershop images (same as admin/layanan.php)
$fallback_images = [
    'https://images.unsplash.com/photo-1622287162716-f311baa1a2b8?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1502823403499-6ccfcf4fb453?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1605497788044-5a32c7078486?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1000&q=80',
    'https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=1000&q=80',
];

function katalog_service_image(array $svc, array $fallbacks, ?string $col_gambar): string {
    if ($col_gambar) {
        $stored = trim((string) ($svc[$col_gambar] ?? ''));
        if ($stored !== '') {
            $local = '../' . ltrim($stored, '/');
            if (is_file(__DIR__ . '/../' . ltrim($stored, '/'))) {
                return $local;
            }
        }
    }
    $id = (int) ($svc['id'] ?? $svc['id_layanan'] ?? 0);
    return $fallbacks[$id % count($fallbacks)];
}
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <?php pelanggan_theme_head('Katalog Layanan'); ?>
</head>
<body class="min-h-screen">
    <?php pelanggan_sidebar('katalog'); ?>
    <main data-pelanggan-main class="min-h-screen transition-[margin] duration-200 md:ml-64">
        <?php pelanggan_topbar('Katalog Layanan'); ?>

        <div class="mx-auto w-full max-w-[1440px] space-y-10 p-5 pb-28 md:p-8">

            <!-- Page Header -->
            <section>
                <div>
                    <p class="text-[12px] font-black uppercase tracking-[0.24em] text-primary">Barber.co</p>
                    <h1 class="mt-3 font-display text-4xl font-black text-on-surface md:text-5xl">Katalog <span class="text-primary">Layanan</span></h1>
                    <p class="mt-3 text-lg leading-8 text-on-muted">Temukan layanan terbaik untuk tampilan Anda. Pilih layanan dan pesan antrean sekarang.</p>
                </div>
            </section>

            <!-- Service Cards Grid -->
            <section>
                <?php if (empty($services)): ?>
                    <div class="customer-card flex flex-col items-center justify-center gap-4 py-16 text-center">
                        <span class="material-symbols-outlined text-5xl text-on-muted">content_cut</span>
                        <p class="text-lg font-bold text-on-muted">Belum ada layanan yang tersedia saat ini.</p>
                        <p class="text-sm text-on-muted">Silakan hubungi admin untuk informasi lebih lanjut.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        <?php foreach ($services as $svc): 
                            $nama    = htmlspecialchars($svc[$col_l_nama] ?? 'Layanan');
                            $harga   = isset($svc[$col_l_harga]) ? 'Rp ' . number_format((int)$svc[$col_l_harga], 0, ',', '.') : 'Hubungi Kami';
                            $durasi  = ($col_l_durasi && isset($svc[$col_l_durasi])) ? (int)$svc[$col_l_durasi] . ' menit' : null;
                            $desc    = ($col_l_desc && isset($svc[$col_l_desc])) ? htmlspecialchars($svc[$col_l_desc]) : null;
                            $img_url = katalog_service_image($svc, $fallback_images, $col_l_gambar);
                            $id_svc  = $svc[$pk_layanan] ?? null;
                        ?>
                            <article class="customer-card flex flex-col overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_8px_30px_rgba(242,202,80,0.12)]">
                                <!-- Card Image -->
                                <div class="relative h-44 overflow-hidden bg-surface-high">
                                    <img src="<?= htmlspecialchars($img_url) ?>" alt="<?= $nama ?>"
                                         class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-gradient-to-t from-background/80 via-transparent to-transparent"></div>
                                    <!-- Price Badge -->
                                    <div class="absolute right-3 top-3 border border-primary bg-background/90 px-3 py-1 text-[11px] font-black uppercase tracking-[0.1em] text-primary backdrop-blur-sm">
                                        <?= $harga ?>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="flex flex-1 flex-col gap-3 p-5">
                                    <div class="flex items-start justify-between gap-2">
                                        <h2 class="font-display text-lg font-black text-on-surface leading-tight"><?= $nama ?></h2>
                                    </div>

                                    <?php if ($durasi): ?>
                                        <div class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.12em] text-on-muted">
                                            <span class="material-symbols-outlined text-[16px] text-primary">schedule</span>
                                            <?= htmlspecialchars($durasi) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($desc): ?>
                                        <p class="text-sm leading-6 text-on-muted line-clamp-3"><?= $desc ?></p>
                                    <?php else: ?>
                                        <p class="text-sm leading-6 text-on-muted">Layanan premium dengan standar kualitas tertinggi oleh barber profesional Barber.co.</p>
                                    <?php endif; ?>

                                    <!-- Divider -->
                                    <div class="mt-auto pt-4 border-t border-outline">
                                        <div class="flex items-center justify-between gap-3">
                                            <span class="font-display text-xl font-black text-primary"><?= $harga ?></span>
                                            <a href="ambil_antrian.php<?= $id_svc ? '?layanan=' . urlencode($id_svc) : '' ?>"
                                               class="inline-flex items-center gap-1.5 border border-outline bg-surface-high px-4 py-2.5 text-[11px] font-black uppercase tracking-[0.12em] text-on-muted transition hover:border-primary hover:bg-primary hover:text-on-primary">
                                                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                                                Pilih
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Info Banner -->
            <section class="customer-card relative overflow-hidden p-6">
                <div class="absolute left-0 top-0 h-full w-1 bg-primary"></div>
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-primary">Info</p>
                        <h3 class="mt-2 font-display text-xl font-black text-on-surface">Butuh konsultasi?</h3>
                        <p class="mt-1 text-sm text-on-muted">Barber kami siap memberikan saran terbaik untuk gaya rambut yang sesuai dengan kebutuhan Anda.</p>
                    </div>
                    <a href="ambil_antrian.php" class="inline-flex shrink-0 items-center justify-center gap-2 border border-primary px-6 py-3 text-[12px] font-black uppercase tracking-[0.14em] text-primary transition hover:bg-primary hover:text-on-primary">
                        <span class="material-symbols-outlined">calendar_add_on</span>
                        Buat Reservasi
                    </a>
                </div>
            </section>

        </div>
    </main>
    <?php pelanggan_mobile_nav('katalog'); ?>

    <style>
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</body>
</html>
