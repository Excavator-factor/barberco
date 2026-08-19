<?php
function admin_render_sidebar(string $active): void
{
    global $conn;
    $unreadCount = 0;
    if ($conn) {
        $qNotif = @mysqli_query($conn, "SELECT COUNT(*) as unread FROM admin_notifications WHERE is_read = 0");
        if ($qNotif && $row = mysqli_fetch_assoc($qNotif)) {
            $unreadCount = (int)$row['unread'];
        }
    }

    $links = [
        ["dashboard.php", "dashboard", "dashboard", "Dashboard"],
        ["antrean.php", "event_seat", "antrean", "Manajemen Antrean"],
        ["transaksi.php", "receipt_long", "transaksi", "Transaksi"],
        ["pengguna.php", "group", "pengguna", "Pengguna"],
        ["layanan.php", "inventory_2", "layanan", "Layanan"],
        ["notifikasi.php", "notifications", "notifikasi", "Notifikasi"],
    ];
    $adminName = htmlspecialchars(
        $_SESSION["username"] ?? ($_SESSION["nama"] ?? "Admin"),
    );
    ?>
    <aside class="sidebar-transition flex-shrink-0 w-[250px] bg-surface-container border-r border-outline-variant hidden md:flex flex-col h-full overflow-hidden z-30" id="sidebar">
        <div class="p-md flex items-center gap-base border-b border-outline-variant h-16">
            <div class="w-8 h-8 bg-primary rounded flex items-center justify-center">
                <span class="material-symbols-outlined text-on-primary" style="font-variation-settings: 'FILL' 1;">content_cut</span>
            </div>
            <div class="flex flex-col">
                <span class="font-headline-md text-headline-md font-black text-primary leading-none">Barber.co</span>
                <span class="font-label-caps text-[10px] text-on-surface-variant opacity-70">Premium Grooming</span>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto custom-scrollbar py-md">
            <nav class="space-y-1">
                <?php foreach ($links as [$href, $icon, $key, $label]): ?>
                    <a class="flex items-center gap-base <?= $active === $key
                        ? "bg-primary text-on-primary font-bold"
                        : "text-on-surface-variant hover:bg-secondary-container hover:text-on-secondary-container" ?> rounded-lg px-4 py-3 mx-2 my-1 transition-all group" href="<?= htmlspecialchars(
     $href,
 ) ?>">
                        <span class="material-symbols-outlined <?= $active ===
                        $key
                            ? ""
                            : "group-hover:scale-110 transition-transform" ?>" <?= $active ===
$key
    ? 'style="font-variation-settings: \'FILL\' 1;"'
    : "" ?>><?= htmlspecialchars($icon) ?></span>
                        <span class="font-label-caps"><?= htmlspecialchars(
                            $label,
                        ) ?></span>
                        <?php if ($key === 'notifikasi' && $unreadCount > 0): ?>
                            <span class="ml-auto bg-error text-on-error text-[10px] font-bold px-2 py-0.5 rounded-full"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>

            </nav>
            
            <div class="mt-lg px-md">
                <p class="font-label-caps text-on-surface-variant opacity-50 mb-base">Aksi Cepat</p>
                <a href="../index.php" target="_blank" class="w-full bg-primary-container text-on-primary-container font-bold py-3 rounded-lg flex items-center justify-center gap-base active:scale-95 transition-transform no-underline">
                    <span class="material-symbols-outlined">public</span>
                    <span>Halaman Utama</span>
                </a>
            </div>
        </div>
        
        <div class="border-t border-outline-variant p-sm">
            <div class="flex items-center gap-base px-4 py-2 mb-2">
                <div class="flex flex-col">
                    <span class="font-label-caps text-on-surface-variant text-[10px]">Masuk sebagai</span>
                    <span class="font-label-caps text-primary"><?= $adminName ?></span>
                </div>
            </div>
            <a class="flex items-center gap-base <?= $active === "profil"
                ? "text-primary"
                : "text-on-surface-variant hover:text-primary" ?> px-4 py-2 transition-all" href="profil.php">
                <span class="material-symbols-outlined text-base" <?= $active ===
                "profil"
                    ? 'style="font-variation-settings: \'FILL\' 1;"'
                    : "" ?>>person</span>
                <span class="font-label-caps">Profil Anda</span>
            </a>
            <a class="flex items-center gap-base text-on-surface-variant hover:text-primary px-4 py-2 transition-all" href="backup_database.php">
                <span class="material-symbols-outlined text-base">download</span>
                <span class="font-label-caps">Backup Database</span>
            </a>
            <a class="flex items-center gap-base text-error hover:opacity-80 px-4 py-2 transition-all" href="../auth/logout.php">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-label-caps">Keluar</span>
            </a>
        </div>
    </aside>
    <?php
}

function admin_render_mobile_nav(string $active): void
{
    $links = [
        ["dashboard.php", "dashboard", "dashboard"],
        ["antrean.php", "event_seat", "antrean"],
        ["transaksi.php", "receipt_long", "transaksi"],
        ["pengguna.php", "group", "pengguna"],
        ["layanan.php", "inventory_2", "layanan"],
        ["notifikasi.php", "notifications", "notifikasi"],
    ]; ?>
    <nav class="fixed bottom-0 left-0 right-0 z-50 flex w-full justify-around border-t border-outline-variant bg-surface-container py-3 md:hidden">
        <?php foreach ($links as [$href, $icon, $key]): ?>
            <a href="<?= htmlspecialchars(
                $href,
            ) ?>" class="flex flex-col items-center gap-1 <?= $active === $key
    ? "text-primary"
    : "text-on-surface-variant hover:text-primary" ?> no-underline">
                <span class="material-symbols-outlined" <?= $active === $key
                    ? 'style="font-variation-settings: \'FILL\' 1;"'
                    : "" ?>><?= htmlspecialchars($icon) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="h-16 md:hidden"></div>
    <?php
}

function admin_header(string $title, string $active): void
{
    global $dateLabel;
    $bulan_array = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
    ];
    $hari_array = [
        "Minggu",
        "Senin",
        "Selasa",
        "Rabu",
        "Kamis",
        "Jumat",
        "Sabtu",
    ];
    if (!isset($dateLabel)) {
        $dateLabel =
            $hari_array[date("w")] .
            ", " .
            date("d") .
            " " .
            $bulan_array[date("n") - 1] .
            " " .
            date("Y");
    }
    ?>
<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($title) ?> | Barber.co Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&family=Inter:wght@400;500;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "secondary-fixed-dim": "#c8c6c5",
                        "inverse-primary": "#735c00",
                        "on-primary-fixed": "#241a00",
                        "secondary-fixed": "#e5e2e1",
                        "primary-fixed-dim": "#e9c349",
                        "secondary": "#c8c6c5",
                        "error-container": "#93000a",
                        "primary-fixed": "#ffe088",
                        "on-primary-container": "#554300",
                        "surface-container-lowest": "#0c0f0f",
                        "tertiary-fixed": "#e5e2e1",
                        "surface-dim": "#121414",
                        "outline-variant": "#4d4635",
                        "on-error": "#690005",
                        "on-secondary-container": "#b7b5b4",
                        "on-secondary-fixed-variant": "#474746",
                        "tertiary-container": "#b5b2b2",
                        "outline": "#99907c",
                        "tertiary": "#d0cecd",
                        "on-tertiary-fixed": "#1c1b1b",
                        "surface": "#121414",
                        "on-surface-variant": "#d0c5af",
                        "surface-tint": "#e9c349",
                        "on-secondary": "#313030",
                        "on-tertiary": "#313030",
                        "on-primary-fixed-variant": "#574500",
                        "primary": "#f2ca50",
                        "surface-variant": "#333535",
                        "background": "#121414",
                        "secondary-container": "#474746",
                        "on-tertiary-container": "#454545",
                        "on-primary": "#3c2f00",
                        "inverse-on-surface": "#2f3131",
                        "surface-container": "#1e2020",
                        "primary-container": "#d4af37",
                        "error": "#ffb4ab",
                        "tertiary-fixed-dim": "#c8c6c5",
                        "surface-bright": "#37393a",
                        "on-tertiary-fixed-variant": "#474646",
                        "on-secondary-fixed": "#1c1b1b",
                        "surface-container-high": "#282a2b",
                        "surface-container-highest": "#333535",
                        "on-error-container": "#ffdad6",
                        "on-surface": "#e2e2e2",
                        "on-background": "#e2e2e2",
                        "surface-container-low": "#1a1c1c",
                        "inverse-surface": "#e2e2e2"
                    },
                    borderRadius: { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                    spacing: { "container-max": "1440px", "md": "24px", "base": "8px", "xs": "4px", "lg": "48px", "gutter": "20px", "sm": "12px", "xl": "80px" },
                    fontFamily: {
                        "body-md": ["Inter"],
                        "headline-lg-mobile": ["Montserrat"],
                        "headline-md": ["Montserrat"],
                        "headline-lg": ["Montserrat"],
                        "body-sm": ["Inter"],
                        "label-caps": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-xl": ["Montserrat"]
                    },
                    fontSize: {
                        "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "headline-lg-mobile": ["24px", {"lineHeight": "1.3", "fontWeight": "600"}],
                        "headline-md": ["20px", {"lineHeight": "1.4", "fontWeight": "600"}],
                        "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                        "body-sm": ["14px", {"lineHeight": "1.5", "fontWeight": "400"}],
                        "label-caps": ["12px", {"lineHeight": "1", "letterSpacing": "0.1em", "fontWeight": "700"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "headline-xl": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}]
                    }
                },
            },
        }
    </script>
    <style>
        /* Smooth Page Transitions */
        body { background-color: #121414; color: #e2e2e2; overflow-x: hidden; animation: pageFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; opacity: 0; }
        @keyframes pageFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        body.page-fade-out { animation: pageFadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes pageFadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-8px); } }

        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .sidebar-transition { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #1e2020; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #f2ca50; }
        .form-input, .form-select, input[type="text"], input[type="email"], input[type="password"], input[type="number"], select, textarea {
            background-color: #121414;
            border: 1px solid #4d4635;
            color: #e2e2e2;
            border-radius: 0.125rem;
        }
        .form-input:focus, .form-select:focus, input:focus, select:focus, textarea:focus {
            border-color: #f2ca50;
            box-shadow: none;
            outline: none;
        }

        /* ═══ Premium DataTables Override ═══ */
        .dt-container { font-family: 'Inter', sans-serif; font-size: 14px; color: #e2e2e2; width: 100%; }
        .dt-container .dt-layout-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; margin-bottom: 1rem; width: 100%; }
        .dt-container .dt-layout-table { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .dt-container .dt-layout-table table { width: 100% !important; }
        .dt-container .dt-search, .dt-container .dt-length { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .dt-search label, .dt-length label { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.1em; color: #d0c5af; white-space: nowrap; }
        .dt-search input, .dt-length select {
            background-color: #1a1c1c;
            border: 1px solid #4d4635;
            color: #f2ca50;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 12px;
            transition: all 0.2s ease;
            max-width: 180px;
        }
        .dt-search input:focus, .dt-length select:focus {
            border-color: #f2ca50;
            background-color: #121414;
            outline: none;
            box-shadow: 0 0 0 2px rgba(242, 202, 80, 0.2);
        }
        
        table.dataTable { width: 100% !important; border-collapse: separate; border-spacing: 0; }
        table.dataTable thead th {
            background-color: #1a1c1c;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #d0c5af;
            padding: 0.875rem 0.75rem;
            border-bottom: 2px solid #4d4635;
            cursor: pointer;
            white-space: nowrap;
        }
        table.dataTable thead th:hover { color: #f2ca50; }
        table.dataTable tbody td {
            padding: 0.875rem 0.75rem;
            border-bottom: 1px solid #333535;
            background-color: transparent;
            transition: background-color 0.2s ease;
        }
        table.dataTable tbody tr:hover td { background-color: rgba(51, 53, 53, 0.5); }
        table.dataTable.no-footer { border-bottom: none; }
        
        /* Pagination — mobile friendly */
        div.dt-container .dt-info { color: #d0c5af !important; font-size: 11px; margin-top: 1rem; }
        div.dt-container .dt-paging { margin-top: 1rem; display: flex; flex-wrap: wrap; gap: 3px; justify-content: flex-end; }
        div.dt-container .dt-paging .dt-paging-button {
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid #4d4635;
            background: #1a1c1c;
            color: #d0c5af !important;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 32px;
            text-align: center;
        }
        div.dt-container .dt-paging .dt-paging-button:hover:not(.disabled) {
            background: #333535;
            border-color: #f2ca50;
            color: #f2ca50 !important;
        }
        div.dt-container .dt-paging .dt-paging-button.current, 
        div.dt-container .dt-paging .dt-paging-button.current:hover {
            background: #f2ca50;
            border-color: #f2ca50;
            color: #121414 !important;
            font-weight: 700;
        }
        div.dt-container .dt-paging .dt-paging-button.disabled { opacity: 0.4; cursor: not-allowed; }
        
        /* Mobile: table container scrollable */
        .overflow-x-auto { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* Mobile: add bottom padding for mobile nav */
        @media (max-width: 767px) {
            div.flex-1.overflow-y-auto { padding-bottom: 5rem !important; }
        }

        /* Sidebar collapse */
        body.admin-sidebar-collapsed #sidebar { width: 5.25rem !important; }
        body.admin-sidebar-collapsed .font-headline-md,
        body.admin-sidebar-collapsed .font-label-caps,
        body.admin-sidebar-collapsed .border-t .flex-col { display: none !important; }
        body.admin-sidebar-collapsed #sidebar nav a { justify-content: center; padding-left: 0; padding-right: 0; margin-left: 0.5rem; margin-right: 0.5rem; }
        body.admin-sidebar-collapsed .mt-lg { display: none !important; }
        body.admin-sidebar-collapsed .w-8.h-8 { margin: 0 auto; }
        body.admin-sidebar-collapsed .p-md.flex.items-center { justify-content: center; padding-left: 0; padding-right: 0; }
        body.admin-sidebar-collapsed #sidebar .border-t { padding: 0.5rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; border-top: none; }
        body.admin-sidebar-collapsed #sidebar .border-t a { justify-content: center; width: 100%; padding-left: 0; padding-right: 0; margin: 0; }
        @media (max-width: 767px) { body.admin-sidebar-collapsed #sidebar { width: 0 !important; } }
    </style>
    </script>
</head>
<body class="font-body-md text-body-md selection:bg-primary selection:text-on-primary">
<script>if(localStorage.getItem('adminSidebarCollapsed') === '1') document.body.classList.add('admin-sidebar-collapsed');</script>
<div class="flex h-screen overflow-hidden">
    <!-- SideNavBar Component -->
    <?php admin_render_sidebar($active); ?>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-hidden w-full">
        <!-- TopNavBar Component -->
        <header class="bg-surface border-b border-outline-variant h-16 flex items-center justify-between px-md flex-shrink-0 z-20">
            <div class="flex items-center gap-md">
                <button class="text-on-surface-variant hover:text-primary transition-colors hidden md:block" id="sidebarToggle">
                    <span class="material-symbols-outlined text-[28px]">menu</span>
                </button>
                <nav class="hidden md:flex gap-md">
                    <a class="text-on-surface-variant hover:text-primary transition-colors font-body-md <?= $active ===
                    "dashboard"
                        ? "text-primary font-bold border-b-2 border-primary pb-1"
                        : "" ?>" href="dashboard.php">Dashboard</a>
                    <a class="text-on-surface-variant hover:text-primary transition-colors font-body-md <?= $active ===
                    "antrean"
                        ? "text-primary font-bold border-b-2 border-primary pb-1"
                        : "" ?>" href="antrean.php">Status Antrean</a>
                    <a class="text-on-surface-variant hover:text-primary transition-colors font-body-md <?= $active ===
                    "layanan"
                        ? "text-primary font-bold border-b-2 border-primary pb-1"
                        : "" ?>" href="layanan.php">Layanan</a>
                </nav>
            </div>
            <div class="flex items-center gap-md">
                <!-- Real-Time Clock -->
                <div class="hidden lg:flex flex-col items-end">
                    <span class="font-bold text-primary font-label-caps" id="clock-time"><?= date(
                        "H:i:s",
                    ) ?></span>
                    <span class="text-[10px] text-on-surface-variant font-label-caps" id="clock-date"><?= strtoupper(
                        (string) $dateLabel,
                    ) ?></span>
                </div>
                <div class="flex items-center gap-base">
                    <!-- User Account representation -->
                    <a href="profil.php" class="flex items-center gap-base ml-2 cursor-pointer group hover:opacity-80 transition-opacity no-underline">
                        <?php if (
                            !empty($_SESSION["avatar"]) &&
                            file_exists(
                                __DIR__ .
                                    "/../uploads/avatars/" .
                                    $_SESSION["avatar"],
                            )
                        ): ?>
                            <div class="w-8 h-8 rounded border border-primary flex items-center justify-center bg-surface-container overflow-hidden">
                                <img src="../uploads/avatars/<?= htmlspecialchars(
                                    $_SESSION["avatar"],
                                ) ?>" class="w-full h-full object-cover">
                            </div>
                        <?php else: ?>
                            <div class="w-8 h-8 rounded border border-primary flex items-center justify-center bg-surface-container text-primary">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                        <?php endif; ?>
                        <span class="font-label-caps hidden sm:block text-on-surface"><?= htmlspecialchars(
                            $_SESSION["username"] ??
                                ($_SESSION["nama"] ?? "Admin"),
                        ) ?></span>
                    </a>
                    
                    <div class="w-px h-6 bg-outline-variant mx-1"></div>
                    
                    <a href="../auth/logout.php" class="w-8 h-8 rounded border border-error/50 bg-error/10 text-error flex items-center justify-center hover:bg-error hover:text-on-error transition-colors" title="Keluar">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <div class="flex-1 overflow-y-auto p-md custom-scrollbar bg-background">
<?php
}

function admin_footer(string $active): void
{
    ?>
        </div>
        <!-- Footer Component -->
        <footer class="bg-surface-container-lowest border-t border-outline-variant w-full py-4 px-md flex flex-col md:flex-row justify-between items-center gap-base z-10 mt-auto">
            <div class="flex items-center gap-base">
                <span class="font-headline-sm text-headline-sm text-primary font-bold">Barber.co</span>
                <span class="text-on-surface-variant font-body-sm">© <?= date(
                    "Y",
                ) ?>. Hak Cipta Dilindungi.</span>
            </div>
            <div class="flex gap-md">
                <span class="font-body-sm text-on-surface-variant">Versi 2.0</span>
            </div>
        </footer>
    </main>
</div>

<?php admin_render_mobile_nav($active); ?>

<script>
    // Real-Time Clock Logic
    function updateClock() {
        const timeEl = document.getElementById('clock-time');
        if (timeEl) {
            const now = new Date();
            timeEl.textContent = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }
    setInterval(updateClock, 1000);

    // Sidebar Toggle Logic
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    if (sidebar && toggleBtn) {
        const storageKey = 'adminSidebarCollapsed';
        if (localStorage.getItem(storageKey) === '1') {
            document.body.classList.add('admin-sidebar-collapsed');
        }
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('admin-sidebar-collapsed');
            localStorage.setItem(storageKey, document.body.classList.contains('admin-sidebar-collapsed') ? '1' : '0');
        });
    }
    
    // Chart.js Default Config if loaded
    if (typeof Chart !== 'undefined') {
        Chart.defaults.color = '#d0c5af';
        Chart.defaults.font.family = 'Inter';
    }
</script>
</body>
</html>
<?php
}
