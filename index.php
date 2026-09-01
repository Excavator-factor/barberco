<?php
session_start();
include "config/database.php";

function landing_scalar_query($conn, string $query): int
{
    $result = mysqli_query($conn, $query);
    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);
    return (int) ($row["total"] ?? 0);
}

$hasServiceImage = false;
$imageColumnCheck = @mysqli_query(
    $conn,
    "SHOW COLUMNS FROM layanan LIKE 'gambar'",
);
if ($imageColumnCheck && mysqli_fetch_assoc($imageColumnCheck)) {
    $hasServiceImage = true;
}

$serviceColumns = $hasServiceImage
    ? "id, nama_layanan, deskripsi, harga, durasi, gambar"
    : "id, nama_layanan, deskripsi, harga, durasi";

$servicesResult = mysqli_query(
    $conn,
    "SELECT {$serviceColumns} FROM layanan WHERE is_deleted = 0 ORDER BY id ASC",
);
$services = $servicesResult
    ? mysqli_fetch_all($servicesResult, MYSQLI_ASSOC)
    : [];

$queueResult = mysqli_query(
    $conn,
    "SELECT a.no_antrian, a.status_antrian, COALESCE(NULLIF(b.nama, ''), 'Barber tersedia') AS nama_barber, l.nama_layanan, COALESCE(l.durasi, 30) AS durasi
     FROM antrian a
     LEFT JOIN barber b ON b.id = a.barber_id
     LEFT JOIN layanan l ON l.id = a.layanan_id
     WHERE a.tanggal = CURDATE() AND a.status_antrian IN ('menunggu', 'proses')
     ORDER BY FIELD(a.status_antrian, 'proses', 'menunggu'), a.no_antrian ASC
     LIMIT 6",
);
$currentQueues = $queueResult
    ? mysqli_fetch_all($queueResult, MYSQLI_ASSOC)
    : [];
$nowServing = $currentQueues[0] ?? null;

$waitingCount = landing_scalar_query(
    $conn,
    "SELECT COUNT(*) AS total FROM antrian WHERE tanggal = CURDATE() AND status_antrian = 'menunggu'",
);
$processCount = landing_scalar_query(
    $conn,
    "SELECT COUNT(*) AS total FROM antrian WHERE tanggal = CURDATE() AND status_antrian = 'proses'",
);
$servedCount = landing_scalar_query(
    $conn,
    "SELECT COUNT(*) AS total FROM antrian WHERE tanggal = CURDATE() AND status_antrian = 'selesai'",
);
$activeBarbers = landing_scalar_query(
    $conn,
    "SELECT COUNT(*) AS total FROM barber WHERE LOWER(COALESCE(status, 'aktif')) = 'aktif'",
);
$queueTotal = $waitingCount + $processCount + $servedCount;
$estimatedWait = max(5, $waitingCount * 12);
$capacity = min(100, max(12, ($waitingCount + $processCount) * 14));

function landing_service_visual(array $service): string
{
    $stored = trim((string) ($service["gambar"] ?? ""));
    if ($stored !== "") {
        $localPath = __DIR__ . "/" . ltrim($stored, "/");
        if (is_file($localPath)) {
            return ltrim($stored, "/");
        }
    }

    $id = (int) ($service["id"] ?? 0);
    $images = [
        "https://images.unsplash.com/photo-1622287162716-f311baa1a2b8?auto=format&fit=crop&w=1000&q=80",
        "https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1000&q=80",
        "https://images.unsplash.com/photo-1502823403499-6ccfcf4fb453?auto=format&fit=crop&w=1000&q=80",
        "https://images.unsplash.com/photo-1605497788044-5a32c7078486?auto=format&fit=crop&w=1000&q=80",
        "https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1000&q=80",
        "https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=1000&q=80",
    ];
    return $images[$id % count($images)];
}

$is_logged_in = false;
$user_role = "";
$book_now_url = "#";

if (isset($_SESSION["role"])) {
    $is_logged_in = true;
    $user_role = $_SESSION["role"];

    if ($user_role === "pelanggan") {
        $book_now_url = "pelanggan/dashboard.php?open_modal=1";
    } elseif ($user_role === "admin") {
        $book_now_url = "admin/dashboard.php";
    } elseif ($user_role === "barber") {
        $book_now_url = "barber/dashboard.php";
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Barber.co | Premium Queue & Grooming</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Montserrat:wght@500;600;700;800;900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        background: '#101313',
                        surface: '#151818',
                        'surface-low': '#1b1e1e',
                        'surface-high': '#252828',
                        primary: '#f2ca50',
                        'primary-soft': '#ffe088',
                        'on-primary': '#211800',
                        'on-surface': '#f1f1f1',
                        'on-muted': '#d0c5af',
                        outline: '#4d4635',
                        'outline-strong': '#99907c'
                    },
                    fontFamily: {
                        display: ['Montserrat', 'sans-serif'],
                        body: ['Inter', 'sans-serif']
                    },
                    spacing: {
                        page: '24px',
                        section: '80px'
                    },
                    borderRadius: {
                        DEFAULT: '2px',
                        lg: '4px'
                    }
                }
            }
        };

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
            menu.classList.toggle('flex');
            document.body.style.overflow = menu.classList.contains('hidden') ? '' : 'hidden';
        }
    </script>
    <style>
        .material-symbols-outlined {
            display: inline-block;
            vertical-align: middle;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }

        .hero-photo {
            background-image:
                linear-gradient(90deg, rgba(16, 19, 19, .96) 0%, rgba(16, 19, 19, .78) 47%, rgba(16, 19, 19, .38) 100%),
                url('https://images.unsplash.com/photo-1622287162716-f311baa1a2b8?auto=format&fit=crop&w=1800&q=80');
            background-position: center;
            background-size: cover;
        }

        @media (max-width: 767px) {
            .hero-photo {
                background-image:
                    linear-gradient(to bottom, rgba(16, 19, 19, .95) 0%, rgba(16, 19, 19, .65) 45%, rgba(16, 19, 19, .95) 100%),
                    url('https://images.unsplash.com/photo-1622287162716-f311baa1a2b8?auto=format&fit=crop&w=1800&q=80');
                background-position: 65% center;
            }
        }

        .gold-line {
            background: linear-gradient(90deg, transparent, #f2ca50, transparent);
            height: 1px;
        }

        /* Smooth Page Transitions */
        body { overflow-x: hidden; background: #101313; }
        .page-wrapper { animation: pageFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; opacity: 0; }
        @keyframes pageFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .page-fade-out .page-wrapper { animation: pageFadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes pageFadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-8px); } }
    </style>
</head>
<body class="bg-background font-body text-on-surface selection:bg-primary selection:text-on-primary">
    <div class="page-wrapper">
    <header class="sticky top-0 z-50 border-b border-outline bg-surface/95 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-[1440px] items-center justify-between px-5 md:px-8">
            <a href="index.php" class="font-display text-lg font-black uppercase text-primary">Barber.co</a>
            <nav class="hidden items-center gap-8 text-[12px] font-bold uppercase tracking-[0.18em] text-on-muted md:flex">
                <a class="transition hover:text-primary" href="#status">Status</a>
                <a class="transition hover:text-primary" href="#about">About</a>
                <a class="transition hover:text-primary" href="#layanan">Layanan</a>
            </nav>
            <div class="flex items-center gap-2 sm:gap-3">
                <button onclick="handleBookNow()" class="border border-primary bg-primary px-3 sm:px-4 py-2 text-[10px] sm:text-[12px] font-black uppercase tracking-[0.16em] text-on-primary transition hover:bg-transparent hover:text-primary">
                    Ambil Antrean
                </button>
                <button onclick="toggleMobileMenu()" class="md:hidden flex h-[34px] w-[34px] sm:h-[38px] sm:w-[38px] items-center justify-center border border-outline bg-surface text-primary transition hover:border-primary hover:bg-primary/10">
                    <span class="material-symbols-outlined text-[20px]">menu</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Overlay -->
    <div id="mobileMenu" class="fixed inset-0 z-[60] hidden bg-background/95 backdrop-blur-md flex-col items-center justify-center space-y-8">
        <button onclick="toggleMobileMenu()" class="absolute top-6 right-6 text-on-surface hover:text-primary">
            <span class="material-symbols-outlined text-4xl">close</span>
        </button>
        <a class="font-display text-2xl font-black uppercase tracking-widest text-on-muted hover:text-primary transition" href="#status" onclick="toggleMobileMenu()">Status</a>
        <a class="font-display text-2xl font-black uppercase tracking-widest text-on-muted hover:text-primary transition" href="#about" onclick="toggleMobileMenu()">About</a>
        <a class="font-display text-2xl font-black uppercase tracking-widest text-on-muted hover:text-primary transition" href="#layanan" onclick="toggleMobileMenu()">Layanan</a>
        <?php if ($is_logged_in): ?>
             <a class="font-display text-2xl font-black uppercase tracking-widest text-primary hover:opacity-80 transition" href="<?= htmlspecialchars(
                 $book_now_url,
             ) ?>">Dashboard</a>
        <?php endif; ?>
    </div>

    <main>
        <section class="hero-photo relative overflow-hidden border-b border-outline">
            <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-[1440px] content-center md:content-end md:items-end gap-8 px-5 py-10 md:gap-10 md:py-16 md:px-8 lg:grid-cols-[1.1fr_0.9fr] lg:py-20">
                <div class="max-w-4xl pb-4 mt-8 md:mt-0">
                    <p class="mb-5 text-[12px] font-black uppercase tracking-[0.24em] text-primary">Premium grooming queue system</p>
                    <h1 class="font-display text-4xl min-[400px]:text-[44px] sm:text-5xl font-black uppercase leading-[1.03] text-on-surface md:text-[72px] lg:text-[84px]">
                        Cukur rapi, antrean jelas, waktu lebih terukur.
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-on-muted">
                        Barber.co menggabungkan suasana barbershop premium dengan sistem antrean real-time untuk pelanggan, barber, dan admin.
                    </p>
                    <div class="mt-9 flex flex-col sm:flex-row gap-4">
                        <button onclick="handleBookNow()" class="w-full sm:w-auto border border-primary bg-primary px-7 py-4 text-[12px] font-black uppercase tracking-[0.18em] text-on-primary transition hover:bg-transparent hover:text-primary">
                            Book Service
                        </button>
                        <a href="#status" class="w-full sm:w-auto border border-outline-strong bg-background/60 px-7 py-4 text-[12px] font-black uppercase tracking-[0.18em] text-on-surface transition hover:border-primary hover:text-primary text-center">
                            Lihat Status
                        </a>
                    </div>
                </div>

                <aside id="status" class="border border-outline bg-surface/90 p-5 shadow-[0_28px_80px_rgba(0,0,0,0.35)] backdrop-blur">
                    <div class="flex items-start justify-between gap-4 border-b border-outline pb-5">
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.22em] text-primary">Now Serving</p>
                            <div class="mt-3 flex items-end gap-3">
                                <span class="font-display text-6xl font-black leading-none text-on-surface">
                                    #<?= $nowServing
                                        ? str_pad(
                                            (string) $nowServing["no_antrian"],
                                            3,
                                            "0",
                                            STR_PAD_LEFT,
                                        )
                                        : "---" ?>
                                </span>
                                <span class="pb-2 text-sm font-bold uppercase tracking-[0.16em] text-on-muted">
                                    <?= $queueTotal ?> total
                                </span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 border border-primary px-3 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-primary">
                            <span class="h-2 w-2 animate-pulse bg-primary"></span>
                            Live
                        </span>
                    </div>

                    <div class="grid gap-4 py-5 sm:grid-cols-2">
                        <div class="border border-outline bg-background p-4">
                            <span class="material-symbols-outlined text-primary">schedule</span>
                            <p class="mt-3 text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Estimasi Tunggu</p>
                            <h2 class="mt-1 font-display text-3xl font-black text-on-surface"><?= $estimatedWait ?> Min</h2>
                        </div>
                        <div class="border border-outline bg-background p-4">
                            <span class="material-symbols-outlined text-primary">groups</span>
                            <p class="mt-3 text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Kapasitas Toko</p>
                            <h2 class="mt-1 font-display text-3xl font-black text-on-surface"><?= $capacity ?>%</h2>
                            <div class="mt-3 h-1 bg-surface-high">
                                <div class="h-full bg-primary" style="width: <?= $capacity ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <?php if ($currentQueues): ?>
                            <?php foreach ($currentQueues as $queue): ?>
                                <article class="flex items-center justify-between gap-4 border border-outline bg-surface-low p-4">
                                    <div>
                                        <p class="text-[11px] font-black uppercase tracking-[0.18em] text-primary">#<?= str_pad(
                                            (string) $queue["no_antrian"],
                                            3,
                                            "0",
                                            STR_PAD_LEFT,
                                        ) ?></p>
                                        <h3 class="mt-1 font-display text-base font-bold text-on-surface"><?= htmlspecialchars(
                                            $queue["nama_layanan"] ?? "Layanan",
                                        ) ?></h3>
                                        <p class="mt-1 text-sm text-on-muted"><?= htmlspecialchars(
                                            $queue["nama_barber"] ??
                                                "Barber tersedia",
                                        ) ?></p>
                                    </div>
                                    <span class="text-[11px] font-black uppercase tracking-[0.18em] text-on-muted"><?= htmlspecialchars(
                                        $queue["status_antrian"],
                                    ) ?></span>
                                </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="border border-dashed border-outline p-6 text-sm leading-7 text-on-muted">
                                Belum ada antrean aktif hari ini. Jadilah pelanggan pertama yang masuk ke kursi.
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>
            </div>
        </section>

        <section id="about" class="border-b border-outline bg-surface-low px-5 py-section md:px-8">
            <div class="mx-auto grid max-w-[1440px] gap-12 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                <div class="relative">
                    <div class="absolute -left-4 -top-4 h-24 w-24 border-l-4 border-t-4 border-primary"></div>
                    <div class="relative aspect-[4/5] overflow-hidden border border-outline bg-background">
                        <img src="https://images.unsplash.com/photo-1599351431202-1e0f0137899a?auto=format&fit=crop&w=1000&q=80" alt="Interior barbershop premium" class="h-full w-full object-cover grayscale transition duration-500 hover:grayscale-0">
                    </div>
                    <div class="absolute -bottom-5 right-5 border border-primary bg-primary px-5 py-4 text-on-primary">
                        <p class="font-display text-xl font-black uppercase">Est. <?= date(
                            "Y",
                        ) ?></p>
                    </div>
                </div>

                <div>
                    <p class="text-[12px] font-black uppercase tracking-[0.24em] text-primary">The Barber.co Standard</p>
                    <h2 class="mt-4 font-display text-4xl font-black uppercase leading-tight text-on-surface md:text-5xl">
                        Presisi layanan, alur antrean yang tidak bikin menebak.
                    </h2>
                    <p class="mt-6 max-w-3xl text-lg leading-8 text-on-muted">
                        Landing page ini dibuat dengan tema dark premium, aksen emas, dan layout operasional agar status toko, layanan, serta aksi booking terasa lebih tegas.
                    </p>

                    <div class="mt-9 grid gap-5 md:grid-cols-3">
                        <article class="border border-outline bg-background p-5">
                            <span class="material-symbols-outlined text-primary">verified</span>
                            <h3 class="mt-4 font-display text-lg font-bold text-on-surface">Master Barber</h3>
                            <p class="mt-2 text-sm leading-6 text-on-muted">Tampilkan barber aktif dan status pelayanan secara lebih meyakinkan.</p>
                        </article>
                        <article class="border border-outline bg-background p-5">
                            <span class="material-symbols-outlined text-primary">confirmation_number</span>
                            <h3 class="mt-4 font-display text-lg font-bold text-on-surface">Live Queue</h3>
                            <p class="mt-2 text-sm leading-6 text-on-muted">Antrean hari ini langsung tampil di halaman depan.</p>
                        </article>
                        <article class="border border-outline bg-background p-5">
                            <span class="material-symbols-outlined text-primary">content_cut</span>
                            <h3 class="mt-4 font-display text-lg font-bold text-on-surface">Service Menu</h3>
                            <p class="mt-2 text-sm leading-6 text-on-muted">Katalog layanan memakai data asli dari database.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section id="layanan" class="overflow-hidden border-b border-outline bg-background px-5 py-section md:px-8">
            <div class="mx-auto max-w-[1440px]">
                <div class="mb-9 flex flex-col justify-between gap-6 md:flex-row md:items-end">
                    <div>
                        <p class="text-[12px] font-black uppercase tracking-[0.24em] text-primary">The Menu</p>
                        <h2 class="mt-3 font-display text-4xl font-black uppercase text-on-surface md:text-5xl">Signature Services</h2>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="servicePrev" class="flex h-12 w-12 items-center justify-center border border-outline text-on-muted transition hover:border-primary hover:text-primary" title="Geser layanan ke kiri">
                            <span class="material-symbols-outlined">arrow_back</span>
                        </button>
                        <button type="button" id="serviceNext" class="flex h-12 w-12 items-center justify-center border border-outline text-on-muted transition hover:border-primary hover:text-primary" title="Geser layanan ke kanan">
                            <span class="material-symbols-outlined">arrow_forward</span>
                        </button>
                    </div>
                </div>

                <?php if ($services): ?>
                    <div id="serviceCarousel" class="hide-scrollbar flex gap-5 overflow-x-auto pb-4 snap-x snap-mandatory scroll-smooth">
                        <?php foreach ($services as $service): ?>
                            <?php $serviceImage = landing_service_visual(
                                $service,
                            ); ?>
                            <article class="group relative w-[85vw] min-[400px]:w-auto min-[400px]:min-w-[280px] sm:min-w-[320px] border border-outline bg-surface md:min-w-[400px] shrink-0 snap-center">
                                <div class="aspect-[16/10] overflow-hidden bg-surface-high">
                                    <img src="<?= htmlspecialchars(
                                        $serviceImage,
                                    ) ?>" alt="Foto layanan <?= htmlspecialchars(
    $service["nama_layanan"],
) ?>" class="h-full w-full object-cover grayscale transition duration-500 group-hover:scale-105 group-hover:grayscale-0">
                                </div>
                                <div class="p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <h3 class="font-display text-xl font-bold text-on-surface"><?= htmlspecialchars(
                                            $service["nama_layanan"],
                                        ) ?></h3>
                                        <span class="whitespace-nowrap text-lg font-black text-primary">Rp <?= number_format(
                                            (int) $service["harga"],
                                            0,
                                            ",",
                                            ".",
                                        ) ?></span>
                                    </div>
                                    <p class="mt-4 min-h-20 text-sm leading-7 text-on-muted"><?= htmlspecialchars(
                                        $service["deskripsi"] ?:
                                        "Deskripsi layanan tersedia saat pemesanan.",
                                    ) ?></p>
                                    <div class="mt-5 flex items-center gap-2 border-t border-outline pt-4 text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">
                                        <span class="material-symbols-outlined text-sm text-primary">schedule</span>
                                        <span><?= (int) $service[
                                            "durasi"
                                        ] ?> Menit</span>
                                    </div>
                                </div>
                                <div class="absolute inset-0 flex items-center justify-center bg-primary/90 opacity-0 transition duration-300 group-hover:opacity-100">
                                    <button type="button" onclick="handleBookNow()" class="border border-on-primary bg-on-primary px-7 py-3 text-[12px] font-black uppercase tracking-[0.18em] text-primary">
                                        Pilih Layanan
                                    </button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="border border-dashed border-outline p-10 text-center text-on-muted">
                        Katalog layanan sedang diperbarui.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="border-t border-outline bg-surface pt-16 pb-8 px-5 md:px-8 mt-20">
        <div class="mx-auto max-w-[1440px]">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                <!-- Brand & About -->
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center border border-primary bg-primary/10">
                            <span class="material-symbols-outlined text-primary text-xl" style="font-variation-settings: 'FILL' 1;">content_cut</span>
                        </div>
                        <span class="font-display text-xl font-black uppercase text-primary tracking-widest">Barber.co</span>
                    </div>
                    <p class="text-sm leading-7 text-on-muted">
                        Pengalaman grooming premium dengan sistem antrean pintar. Tampil maksimal tanpa batas waktu tunggu yang tidak pasti.
                    </p>
                    <div class="flex gap-4 pt-2">
                        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="h-10 w-10 border border-outline flex items-center justify-center text-on-muted hover:text-primary hover:border-primary transition-all rounded-full group">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="group-hover:scale-110 transition-transform">
                                <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                            </svg>
                        </a>
                        <a href="https://instagram.com/barber.co" target="_blank" rel="noopener noreferrer" class="h-10 w-10 border border-outline flex items-center justify-center text-on-muted hover:text-primary hover:border-primary transition-all rounded-full group">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" class="group-hover:scale-110 transition-transform">
                                <path d="M8 0C5.829 0 5.556.01 4.703.048 3.85.088 3.269.222 2.76.42a3.917 3.917 0 0 0-1.417.923A3.927 3.927 0 0 0 .42 2.76C.222 3.268.087 3.85.048 4.7.01 5.555 0 5.827 0 8.001c0 2.172.01 2.444.048 3.297.04.852.174 1.433.372 1.942.205.526.478.972.923 1.417.444.445.89.719 1.416.923.51.198 1.09.333 1.942.372C5.555 15.99 5.827 16 8 16s2.444-.01 3.298-.048c.851-.04 1.434-.174 1.943-.372a3.916 3.916 0 0 0 1.416-.923c.445-.445.718-.891.923-1.417.197-.509.332-1.09.372-1.942C15.99 10.445 16 10.173 16 8s-.01-2.445-.048-3.299c-.04-.851-.175-1.433-.372-1.941a3.926 3.926 0 0 0-.923-1.417A3.911 3.911 0 0 0 13.24.42c-.51-.198-1.092-.333-1.943-.372C10.443.01 10.172 0 7.998 0h.003zm-.717 1.442h.718c2.136 0 2.389.007 3.232.046.78.036 1.204.166 1.486.275.373.145.64.319.92.599.28.28.453.546.598.92.11.281.24.705.275 1.485.039.843.047 1.096.047 3.231s-.008 2.389-.047 3.232c-.035.78-.166 1.203-.275 1.485a2.47 2.47 0 0 1-.599.919c-.28.28-.546.453-.92.598-.28.11-.704.24-1.487.276-.843.038-1.096.047-3.232.047s-2.39-.009-3.233-.047c-.78-.036-1.203-.166-1.485-.276a2.478 2.478 0 0 1-.92-.598 2.48 2.48 0 0 1-.6-.92c-.109-.281-.24-.705-.275-1.485-.038-.843-.046-1.096-.046-3.233 0-2.136.008-2.388.046-3.231.036-.78.166-1.204.276-1.486.145-.373.319-.64.599-.92.28-.28.546-.453.92-.598.282-.11.705-.24 1.485-.276.738-.034 1.024-.044 2.515-.045v.002zm4.988 1.328a.96.96 0 1 0 0 1.92.96.96 0 0 0 0-1.92zm-4.27 1.122a4.109 4.109 0 1 0 0 8.217 4.109 4.109 0 0 0 0-8.217zm0 1.441a2.667 2.667 0 1 1 0 5.334 2.667 2.667 0 0 1 0-5.334z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="space-y-6">
                    <h4 class="font-bold text-sm tracking-[0.2em] uppercase text-on-surface">Eksplorasi</h4>
                    <ul class="space-y-4 text-sm text-on-muted">
                        <li><a href="#about" class="hover:text-primary transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[14px]">arrow_right</span> Tentang Kami</a></li>
                        <li><a href="#layanan" class="hover:text-primary transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[14px]">arrow_right</span> Layanan & Harga</a></li>
                        <li><a href="#status" class="hover:text-primary transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[14px]">arrow_right</span> Status Antrean</a></li>
                        <li><a href="auth/login.php" class="hover:text-primary transition-colors flex items-center gap-2"><span class="material-symbols-outlined text-[14px]">arrow_right</span> Member Login</a></li>
                    </ul>
                </div>

                <!-- Opening Hours -->
                <div class="space-y-6">
                    <h4 class="font-bold text-sm tracking-[0.2em] uppercase text-on-surface">Jam Operasional</h4>
                    <ul class="space-y-3 text-sm text-on-muted">
                        <li class="flex justify-between border-b border-outline/50 pb-2">
                            <span>Senin - Jumat</span>
                            <span class="text-primary font-bold">09:00 - 21:00</span>
                        </li>
                        <li class="flex justify-between border-b border-outline/50 pb-2">
                            <span>Sabtu</span>
                            <span class="text-primary font-bold">10:00 - 22:00</span>
                        </li>
                        <li class="flex justify-between border-b border-outline/50 pb-2">
                            <span>Minggu</span>
                            <span class="text-on-muted font-bold">Tutup</span>
                        </li>
                    </ul>
                </div>

                <!-- Contact/Location -->
                <div class="space-y-6">
                    <h4 class="font-bold text-sm tracking-[0.2em] uppercase text-on-surface">Hubungi Kami</h4>
                    <ul class="space-y-4 text-sm text-on-muted">
                        <li class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">location_on</span>
                            <span class="leading-relaxed">Jl. Gaya Premium No. 123,<br>Distrik Elit, Jakarta 12345</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">call</span>
                            <span>0812-3456-7890</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-xl">mail</span>
                            <span>hello@barber.co</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-outline pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-[11px] text-on-muted uppercase tracking-widest font-black">&copy; <?= date(
                    "Y",
                ) ?> Barber.co Premium Grooming.</p>
                <div class="flex gap-6 text-[10px] text-on-muted font-bold uppercase tracking-widest">
                    <a href="#" class="hover:text-primary transition-colors">Privasi</a>
                    <a href="#" class="hover:text-primary transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>
    </div> <!-- Close page-wrapper -->

    <div id="authChoiceModal" style="display: none; position: fixed; inset: 0; z-index: 99999;">
        <!-- Backdrop -->
        <div onclick="closeAuthModal()" class="backdrop-blur-sm" style="position: absolute; inset: 0; background-color: rgba(0,0,0,0.8);"></div>
        
        <!-- Modal Content (Absolute Centered) -->
        <div class="bg-surface shadow-[0_28px_80px_rgba(0,0,0,0.5)] border border-outline" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: calc(100% - 2rem); max-width: 28rem; padding: 1.75rem;">
            <div class="flex items-start justify-between gap-4 border-b border-outline pb-5">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-primary">Akses Layanan</p>
                    <h3 class="mt-2 font-display text-2xl font-black uppercase text-on-surface">Masuk dulu untuk booking</h3>
                </div>
                <button type="button" onclick="closeAuthModal()" class="text-2xl font-bold leading-none text-on-muted hover:text-primary">&times;</button>
            </div>
            <p class="mt-5 text-sm leading-7 text-on-muted">
                Silakan login atau daftar akun pelanggan agar antrean dapat dicatat ke sistem.
            </p>
            <div class="mt-7 grid gap-3">
                <a href="auth/login.php" class="border border-primary bg-primary py-4 text-center text-[12px] font-black uppercase tracking-[0.18em] text-on-primary transition hover:bg-transparent hover:text-primary">
                    Login
                </a>
                <a href="auth/register.php" class="border border-outline py-4 text-center text-[12px] font-black uppercase tracking-[0.18em] text-on-surface transition hover:border-primary hover:text-primary">
                    Register
                </a>
            </div>
        </div>
    </div>

    <script>
        const isLoggedIn = <?= json_encode($is_logged_in) ?>;
        const bookNowUrl = <?= json_encode($book_now_url) ?>;

        function handleBookNow() {
            if (isLoggedIn) {
                window.location.href = bookNowUrl;
                return;
            }

            openAuthModal();
        }

        function openAuthModal() {
            const modal = document.getElementById('authChoiceModal');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeAuthModal() {
            const modal = document.getElementById('authChoiceModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        const carousel = document.getElementById('serviceCarousel');
        const prevButton = document.getElementById('servicePrev');
        const nextButton = document.getElementById('serviceNext');

        if (carousel && prevButton && nextButton) {
            prevButton.addEventListener('click', () => carousel.scrollBy({ left: -420, behavior: 'smooth' }));
            nextButton.addEventListener('click', () => carousel.scrollBy({ left: 420, behavior: 'smooth' }));
        }
    </script>
</body>
</html>
