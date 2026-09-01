<?php
include "_bootstrap.php";
include "_chrome.php";
?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <?php barber_head("Dashboard Barber"); ?>
</head>
<body class="bg-background text-on-background font-body-md selection:bg-primary selection:text-on-primary">
<div class="flex h-screen overflow-hidden">
    <!-- Sidebar Navigation -->
    <?php barber_sidebar("dashboard"); ?>

    <!-- Main Content Area -->
    <main class="flex-grow md:ml-[250px] flex flex-col h-screen overflow-hidden min-w-0">
        <!-- Top Bar -->
        <?php barber_header("Dashboard"); ?>

        <!-- Dashboard Canvas -->
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <div class="p-md pb-24 md:p-lg max-w-7xl w-full mx-auto">
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg flex items-center justify-between border <?= $messageType ===
                "error"
                    ? "bg-error-container/20 border-error text-error"
                    : "bg-primary/10 border-primary text-primary" ?>">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined"><?= $messageType ===
                        "error"
                            ? "error"
                            : "check_circle" ?></span>
                        <span class="font-bold text-sm"><?= htmlspecialchars(
                            $message,
                        ) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($barberId <= 0): ?>
                <div class="mb-6 p-4 rounded-lg bg-surface-container-high border border-outline-variant text-on-surface-variant flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary">warning</span>
                    <div>
                        <p class="font-bold text-sm text-on-surface">Profil Barber Belum Terhubung</p>
                        <p class="text-xs">Sistem telah menyiapkan ID otomatis. Hubungi admin jika butuh penyesuaian data barber.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Status Row -->
            <div class="mb-lg flex flex-col md:flex-row md:items-end justify-between gap-md">
                <div>
                    <h2 class="font-headline-lg text-headline-lg mb-2 text-on-surface">Selamat datang kembali, <?= htmlspecialchars(
                        $barberName,
                    ) ?>.</h2>
                    <p class="text-on-surface-variant max-w-xl">Stasiun Anda siap. Saat ini ada <span class="text-primary font-bold"><?= count(
                        $waitingQueues,
                    ) ?> pelanggan</span> menunggu di antrean Anda hari ini.</p>
                </div>
                <!-- Active Status Toggle -->
                <form method="POST" action="dashboard.php" class="bg-surface-container-high p-1 rounded-xl flex items-center border border-outline-variant">
                    <input type="hidden" name="action" value="toggle_status">
                    <button type="submit" name="status" value="active" class="px-6 py-2 rounded-lg font-label-caps text-label-caps flex items-center gap-2 <?= $barberStatus ===
                    "active"
                        ? "bg-primary text-on-primary active-glow"
                        : "text-on-surface-variant hover:text-on-surface" ?> transition-all duration-300">
                        <span class="w-2 h-2 rounded-full <?= $barberStatus ===
                        "active"
                            ? "bg-on-primary animate-pulse"
                            : "bg-outline" ?>"></span>
                        AKTIF
                    </button>
                    <button type="submit" name="status" value="off_duty" class="px-6 py-2 rounded-lg font-label-caps text-label-caps flex items-center gap-2 <?= $barberStatus ===
                    "off_duty"
                        ? "bg-error-container text-on-error-container"
                        : "text-on-surface-variant hover:text-on-surface" ?> transition-all duration-300">
                        <span class="w-2 h-2 rounded-full <?= $barberStatus ===
                        "off_duty"
                            ? "bg-on-error animate-pulse"
                            : "bg-outline" ?>"></span>
                        TIDAK BERTUGAS
                    </button>
                </form>
            </div>

            <!-- Bento Grid Layout -->
            <div class="bento-grid">
                <!-- Active Appointment (Featured Card) -->
                <div class="col-span-12 lg:col-span-8 bg-surface-container border border-outline-variant relative overflow-hidden group rounded-xl">
                    <div class="absolute top-0 left-0 w-full h-1 bg-primary"></div>
                    <div class="p-md md:p-lg flex flex-col md:flex-row gap-md items-center">
                        <?php if ($activeQueue): ?>
                            <div class="relative flex-shrink-0">
                                <div class="w-32 h-32 rounded-lg overflow-hidden border border-outline-variant bg-surface-container-highest flex items-center justify-center">
                                    <img class="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-500" alt="Client Avatar" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=300"/>
                                </div>
                                <div class="absolute -bottom-2 -right-2 bg-primary text-on-primary px-3 py-1 rounded text-xs font-bold shadow-lg">IN CHAIR</div>
                            </div>
                            <div class="flex-grow text-center md:text-left">
                                <p class="font-label-caps text-label-caps text-primary uppercase tracking-widest mb-1">Layanan Saat Ini &middot; Antrean #<?= str_pad(
                                    (string) $activeQueue["no_antrian"],
                                    3,
                                    "0",
                                    STR_PAD_LEFT,
                                ) ?></p>
                                <h3 class="font-headline-lg text-headline-lg mb-1 text-on-surface"><?= htmlspecialchars(
                                    $activeQueue["nama_pelanggan"],
                                ) ?></h3>
                                <p class="text-on-surface-variant mb-4 flex items-center justify-center md:justify-start gap-2">
                                    <span class="material-symbols-outlined text-sm text-primary" data-icon="content_cut">content_cut</span>
                                    <?= htmlspecialchars(
                                        $activeQueue["nama_layanan"],
                                    ) ?>
                                </p>
                                <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                                    <span class="bg-surface-container-highest px-3 py-1 text-xs border border-outline-variant rounded text-on-surface">Dimulai <?= date(
                                        "H:i",
                                        strtotime($activeQueue["waktu_dibuat"]),
                                    ) ?></span>
                                    <span class="bg-surface-container-highest px-3 py-1 text-xs border border-outline-variant rounded text-on-surface">Est. <?= (int) $activeQueue[
                                        "durasi"
                                    ] ?> mnt</span>
                                    <span class="bg-primary/10 text-primary border border-primary/30 px-3 py-1 text-xs rounded font-bold">Rp <?= number_format(
                                        (int) $activeQueue["harga"],
                                        0,
                                        ",",
                                        ".",
                                    ) ?></span>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2 w-full md:w-auto">
                                <form method="POST" action="dashboard.php" onsubmit="return confirm('Selesaikan antrean ini?');">
                                    <input type="hidden" name="queue_id" value="<?= (int) $activeQueue[
                                        "id"
                                    ] ?>">
                                    <button type="submit" name="action" value="finish" class="w-full bg-primary text-on-primary font-bold py-3 px-6 rounded hover:scale-105 active:scale-95 transition-all flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined" data-icon="task_alt">task_alt</span>
                                        SELESAI
                                    </button>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="w-full py-8 text-center flex flex-col items-center">
                                <div class="w-20 h-20 rounded-full bg-surface-container-high border border-outline-variant flex items-center justify-center text-primary mb-3">
                                    <span class="material-symbols-outlined text-4xl">chair</span>
                                </div>
                                <h3 class="font-headline-md text-headline-md text-on-surface mb-1">Kursi Barber Siap</h3>
                                <p class="text-on-surface-variant text-sm max-w-md mb-6">Belum ada pelanggan di kursi saat ini. Panggil antrean berikutnya untuk memulai pelayanan.</p>
                                <?php
                                $nextQueue = null;
                                foreach ($waitingQueues as $wq) {
                                    if (
                                        empty($wq["barber_id"]) ||
                                        (int) $wq["barber_id"] === $barberId
                                    ) {
                                        $nextQueue = $wq;
                                        break;
                                    }
                                }
                                ?>
                                <?php if ($nextQueue): ?>
                                    <form method="POST" action="dashboard.php">
                                        <input type="hidden" name="queue_id" value="<?= (int) $nextQueue[
                                            "id"
                                        ] ?>">
                                        <button type="submit" name="action" value="start" class="bg-primary text-on-primary font-bold py-3 px-8 rounded-lg hover:scale-105 active:scale-95 transition-all flex items-center gap-2 shadow-lg">
                                            <span class="material-symbols-outlined">play_arrow</span>
                                            MULAI BERIKUTNYA (#<?= str_pad(
                                                (string) $nextQueue[
                                                    "no_antrian"
                                                ],
                                                3,
                                                "0",
                                                STR_PAD_LEFT,
                                            ) ?> - <?= htmlspecialchars(
     $nextQueue["nama_pelanggan"],
 ) ?>)
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="px-4 py-2 bg-surface-container-highest border border-outline-variant rounded text-xs text-on-surface-variant uppercase font-bold">Semua Antrean Selesai / Kosong</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="col-span-12 lg:col-span-4 bg-surface-container-low border border-outline-variant p-md flex flex-col justify-between rounded-xl">
                    <div>
                        <h4 class="font-label-caps text-label-caps text-outline mb-md uppercase">Kinerja Hari Ini</h4>
                        <div class="space-y-6">
                            <div class="flex justify-between items-end border-b border-outline-variant pb-2">
                                <div>
                                    <p class="text-sm text-on-surface-variant">Layanan Selesai</p>
                                    <p class="text-2xl font-bold font-headline-md text-on-surface"><?= $stats[
                                        "completed"
                                    ] ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-primary font-bold">Hari ini</p>
                                </div>
                            </div>
                            <div class="flex justify-between items-end border-b border-outline-variant pb-2">
                                <div>
                                    <p class="text-sm text-on-surface-variant">Est. Pendapatan</p>
                                    <p class="text-2xl font-bold font-headline-md text-primary">Rp <?= number_format(
                                        $revenueToday,
                                        0,
                                        ",",
                                        ".",
                                    ) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-outline">Gold Status</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="riwayat.php" class="mt-md text-primary font-bold text-sm flex items-center gap-2 hover:underline">
                        View Full Report
                        <span class="material-symbols-outlined text-sm" data-icon="arrow_forward">arrow_forward</span>
                    </a>
                </div>

                <!-- Queue Management Section -->
                <div class="col-span-12 lg:col-span-7 bg-surface-container border border-outline-variant p-md rounded-xl">
                    <div class="flex justify-between items-center mb-md pb-4 border-b border-outline-variant">
                        <h3 class="font-headline-md text-headline-md text-on-surface">Antrean Berikutnya</h3>
                        <div class="flex items-center gap-2 text-on-surface-variant text-sm">
                            <span class="material-symbols-outlined text-base" data-icon="timer">timer</span>
                            <span>Est. Tunggu: ~<?= count($waitingQueues) *
                                30 ?> mnt</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <?php if ($waitingQueues): ?>
                            <?php foreach ($waitingQueues as $index => $q):

                                $isOwnOrOpen =
                                    empty($q["barber_id"]) ||
                                    (int) $q["barber_id"] === $barberId;
                                $initials = strtoupper(
                                    substr($q["nama_pelanggan"], 0, 2),
                                );
                                ?>
                                <div class="flex items-center gap-4 p-4 rounded-lg bg-surface border border-outline-variant hover:border-primary transition-colors group">
                                    <div class="w-12 h-12 bg-secondary-container rounded flex items-center justify-center text-on-secondary-container font-black text-xl flex-shrink-0">
                                        <?= $initials ?>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <p class="font-bold text-on-surface truncate"><?= htmlspecialchars(
                                            $q["nama_pelanggan"],
                                        ) ?></p>
                                        <p class="text-xs text-on-surface-variant uppercase truncate"><?= htmlspecialchars(
                                            $q["nama_layanan"],
                                        ) ?> &middot; <?= (int) $q[
     "durasi"
 ] ?> mnt</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-sm font-bold text-primary">#<?= str_pad(
                                            (string) $q["no_antrian"],
                                            3,
                                            "0",
                                            STR_PAD_LEFT,
                                        ) ?></p>
                                        <p class="text-[10px] text-outline uppercase"><?= $index ===
                                        0
                                            ? "Berikutnya"
                                            : "Antrean #" . ($index + 1) ?></p>
                                    </div>
                                    <?php if (!$activeQueue && $isOwnOrOpen): ?>
                                        <form method="POST" action="dashboard.php" class="flex-shrink-0">
                                            <input type="hidden" name="queue_id" value="<?= (int) $q[
                                                "id"
                                            ] ?>">
                                            <button type="submit" name="action" value="start" class="bg-primary text-on-primary px-3 py-2 rounded-lg font-bold text-xs hover:scale-105 active:scale-95 transition-all flex items-center gap-1">
                                                <span>Mulai</span>
                                                <span class="material-symbols-outlined text-sm" data-icon="chevron_right">chevron_right</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php
                            endforeach; ?>
                        <?php else: ?>
                            <div class="p-6 border border-dashed border-outline-variant rounded-lg text-center text-on-surface-variant text-sm">
                                Tidak ada antrean menunggu untuk hari ini.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Profile Management Section -->
                <div class="col-span-12 lg:col-span-5 bg-surface-container border border-outline-variant p-md rounded-xl">
                    <div class="flex justify-between items-center mb-md pb-4 border-b border-outline-variant">
                        <h3 class="font-headline-md text-headline-md text-on-surface">Profil Barber</h3>
                        <a href="profil.php" class="text-primary text-sm flex items-center gap-1 hover:underline">
                            <span class="material-symbols-outlined text-sm" data-icon="edit">edit</span>
                            Edit
                        </a>
                    </div>
                    <div class="space-y-6">
                        <div class="flex gap-md">
                            <div class="w-20 h-20 rounded border border-outline-variant overflow-hidden flex-shrink-0 bg-surface-container-high flex items-center justify-center text-primary">
                                <?php if (
                                    !empty($_SESSION["avatar"]) &&
                                    file_exists(
                                        __DIR__ .
                                            "/../uploads/avatars/" .
                                            $_SESSION["avatar"],
                                    )
                                ): ?>
                                    <img class="w-full h-full object-cover" alt="Master Barber Headshot" src="../uploads/avatars/<?= htmlspecialchars(
                                        $_SESSION["avatar"],
                                    ) ?>"/>
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-[40px]">person</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4 class="font-bold text-lg text-on-surface"><?= htmlspecialchars(
                                    $barberName,
                                ) ?></h4>
                                <p class="text-sm text-on-surface-variant mb-2">Master Barber</p>
                                <div class="flex gap-1 text-primary items-center">
                                    <span class="material-symbols-outlined text-sm" data-icon="star">star</span>
                                    <span class="material-symbols-outlined text-sm" data-icon="star">star</span>
                                    <span class="material-symbols-outlined text-sm" data-icon="star">star</span>
                                    <span class="material-symbols-outlined text-sm" data-icon="star">star</span>
                                    <span class="material-symbols-outlined text-sm" data-icon="star">star</span>
                                    <span class="text-xs text-on-surface-variant ml-1 font-bold">4.9 (240+ reviews)</span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-md pt-4 border-t border-outline-variant">
                            <div>
                                <p class="text-[10px] text-outline uppercase font-black mb-2">Spesialisasi</p>
                                <ul class="text-xs space-y-1 text-on-surface-variant">
                                    <?php
                                    $specs = array_map(
                                        "trim",
                                        explode(",", $barberSpecialties),
                                    );
                                    foreach ($specs as $spec):
                                        if ($spec): ?>
                                        <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-primary"></span> <?= htmlspecialchars(
                                            $spec,
                                        ) ?></li>
                                    <?php endif;
                                    endforeach;
                                    ?>
                                </ul>
                            </div>
                            <div>
                                <p class="text-[10px] text-outline uppercase font-black mb-2">Ketersediaan</p>
                                <p class="text-xs text-on-surface-variant font-bold">Selasa - Sabtu</p>
                                <p class="text-xs text-on-surface-variant">09:00 - 19:00</p>
                            </div>
                        </div>
                        <div class="pt-4 border-t border-outline-variant">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-on-surface">Station Auto-Lock</span>
                                <div class="w-10 h-5 bg-primary rounded-full relative cursor-pointer" onclick="this.classList.toggle('opacity-50')">
                                    <div class="w-4 h-4 bg-on-primary rounded-full absolute right-0.5 top-0.5 shadow-sm"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        </div>
    </main>
</div>

<?php barber_mobile_nav("dashboard"); ?>
<?php barber_scripts(); ?>
</body>
</html>
