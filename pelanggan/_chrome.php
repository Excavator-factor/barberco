<?php
function pelanggan_theme_head(string $title): void
{
    ?>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?= htmlspecialchars($title); ?> | Barber.co</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Montserrat:wght@500;600;700;800;900&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        background: '#121414',
                        surface: '#151818',
                        'surface-low': '#1a1c1c',
                        'surface-panel': '#1e2020',
                        'surface-high': '#282a2b',
                        primary: '#f2ca50',
                        'primary-soft': '#ffe088',
                        'on-primary': '#241a00',
                        'on-surface': '#e2e2e2',
                        'on-muted': '#d0c5af',
                        outline: '#4d4635',
                        'outline-soft': '#333535',
                        error: '#ffb4ab'
                    },
                    fontFamily: {
                        body: ['Inter', 'sans-serif'],
                        display: ['Montserrat', 'sans-serif']
                    },
                    borderRadius: {
                        DEFAULT: '2px',
                        lg: '4px',
                        xl: '8px'
                    }
                }
            }
        };
    </script>
    <style>
        /* Smooth Page Transitions */
        body { background: #121414; color: #e2e2e2; font-family: 'Inter', sans-serif; overflow-x: hidden; }
        [data-pelanggan-main] { animation: pageFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; opacity: 0; }
        @keyframes pageFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        body.page-fade-out [data-pelanggan-main] { animation: pageFadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes pageFadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-8px); } }
        .material-symbols-outlined {
            display: inline-block;
            vertical-align: middle;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .customer-card {
            background: #1a1c1c;
            border: 1px solid #333535;
            transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
        }
        .customer-card:hover { border-color: #f2ca50; }
        .customer-scroll::-webkit-scrollbar { width: 4px; height: 4px; }
        .customer-scroll::-webkit-scrollbar-track { background: #1e2020; }
        .customer-scroll::-webkit-scrollbar-thumb { background: #f2ca50; }
        body.pelanggan-sidebar-collapsed [data-pelanggan-sidebar] { width: 5.25rem; }
        body.pelanggan-sidebar-collapsed [data-pelanggan-main] { margin-left: 5.25rem; }
        body.pelanggan-sidebar-collapsed [data-pelanggan-sidebar] > .mb-10 { padding: 0 !important; }
        body.pelanggan-sidebar-collapsed [data-pelanggan-sidebar] .mb-10 > .flex { justify-content: center; gap: 0; }
        body.pelanggan-sidebar-collapsed .pelanggan-sidebar-brand { display: none !important; }
        body.pelanggan-sidebar-collapsed .pelanggan-sidebar-label,
        body.pelanggan-sidebar-collapsed [data-pelanggan-sidebar] .pelanggan-sidebar-footer-profile { display: none; }
        body.pelanggan-sidebar-collapsed [data-pelanggan-sidebar] nav a,
        body.pelanggan-sidebar-collapsed [data-pelanggan-sidebar] .pelanggan-sidebar-footer a { justify-content: center; gap: 0; }
        body.pelanggan-sidebar-collapsed [data-pelanggan-sidebar-toggle] { flex: 0 0 auto; }
        @media (max-width: 767px) {
            body.pelanggan-sidebar-collapsed [data-pelanggan-main] { margin-left: 0; }
        }
    </style>
    <?php
}

function pelanggan_sidebar(string $active): void
{
    $links = [
        ['dashboard.php', 'dashboard', 'ringkasan', 'Dashboard'],
        ['ambil_antrian.php', 'add_circle', 'antrean', 'Pesan Layanan'],
        ['katalog.php', 'content_cut', 'katalog', 'Katalog Layanan'],
        ['riwayat.php', 'history', 'riwayat', 'Riwayat'],
        ['profil.php', 'person', 'profil', 'Profil'],
    ];
    ?>
    <script>if(localStorage.getItem('pelangganSidebarCollapsed') === '1') document.body.classList.add('pelanggan-sidebar-collapsed');</script>
    <aside data-pelanggan-sidebar class="fixed left-0 top-0 z-50 hidden h-screen w-64 flex-col overflow-y-auto border-r border-outline bg-surface-panel py-6 md:flex">
        <div class="mb-10 px-5">
            <div class="flex items-start justify-between gap-3">
                <a href="dashboard.php" data-pelanggan-sidebar-brand class="pelanggan-sidebar-brand flex items-center gap-3 text-inherit no-underline">
                    <span class="flex h-11 w-11 items-center justify-center border border-primary bg-primary text-on-primary">
                        <span class="material-symbols-outlined">content_cut</span>
                    </span>
                    <span class="pelanggan-sidebar-brand-text">
                        <span class="font-display block text-xl font-black text-primary">Barber.co</span>
                        <span class="mt-1 block text-[10px] font-black uppercase tracking-[.18em] text-on-muted">Portal Pelanggan</span>
                    </span>
                </a>
                <button type="button" data-pelanggan-sidebar-toggle class="flex h-10 w-10 items-center justify-center border border-outline bg-surface text-on-muted transition hover:border-primary hover:text-primary" title="Toggle sidebar">
                    <span class="material-symbols-outlined text-[20px]">menu_open</span>
                </button>
            </div>
        </div>

        <nav class="flex-1 space-y-2 px-2">
            <?php foreach ($links as [$href, $icon, $key, $label]): ?>
                <a class="<?= $active === $key ? 'flex items-center gap-3 bg-primary px-4 py-3 text-[12px] font-black uppercase tracking-[0.12em] text-on-primary shadow-[0_0_18px_rgba(242,202,80,.18)]' : 'flex items-center gap-3 px-4 py-3 text-[12px] font-black uppercase tracking-[0.12em] text-on-muted transition hover:bg-surface-high hover:text-primary'; ?>" href="<?= htmlspecialchars($href); ?>">
                    <span class="material-symbols-outlined"><?= htmlspecialchars($icon); ?></span>
                    <span class="pelanggan-sidebar-label"><?= htmlspecialchars($label); ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="pelanggan-sidebar-footer mx-2 mt-8 border-t border-outline pt-5">
            <div class="pelanggan-sidebar-footer-profile mx-3 mb-3 border border-outline bg-surface-low p-4">
                <p class="text-[10px] font-black uppercase tracking-[.18em] text-on-muted mb-2">Profil Pengguna</p>
                <div class="flex items-center gap-3">
                    <?php if (!empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $_SESSION['avatar'])): ?>
                        <div class="w-8 h-8 rounded border border-primary overflow-hidden flex-shrink-0">
                            <img src="../uploads/avatars/<?= htmlspecialchars($_SESSION['avatar']); ?>" class="w-full h-full object-cover">
                        </div>
                    <?php endif; ?>
                    <p class="text-sm font-bold text-on-surface"><?= htmlspecialchars($GLOBALS['username'] ?? ($_SESSION['username'] ?? 'Pelanggan')); ?></p>
                </div>
            </div>
            <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 text-[12px] font-black uppercase tracking-[0.12em] text-on-muted transition hover:text-error">
                <span class="material-symbols-outlined">logout</span>
                <span class="pelanggan-sidebar-label">Keluar</span>
            </a>
        </div>
    </aside>
    <script>
        (function () {
            const body = document.body;
            const storageKey = 'pelangganSidebarCollapsed';
            const applyState = () => body.classList.toggle('pelanggan-sidebar-collapsed', localStorage.getItem(storageKey) === '1');
            applyState();

            document.querySelectorAll('[data-pelanggan-sidebar-brand]').forEach((brand) => {
                brand.addEventListener('click', (event) => {
                    if (!body.classList.contains('pelanggan-sidebar-collapsed')) {
                        return;
                    }
                    event.preventDefault();
                    body.classList.remove('pelanggan-sidebar-collapsed');
                    localStorage.setItem(storageKey, '0');
                });
            });

            document.querySelectorAll('[data-pelanggan-sidebar-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    body.classList.toggle('pelanggan-sidebar-collapsed');
                    localStorage.setItem(storageKey, body.classList.contains('pelanggan-sidebar-collapsed') ? '1' : '0');
                });
            });

            const clock = document.querySelector('[data-realtime-clock]');
            if (clock) {
                const tick = () => {
                    const now = new Date();
                    clock.textContent = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
                };
                tick();
                setInterval(tick, 1000);
            }
        })();
    </script>
    <?php
}

function pelanggan_topbar(string $subtitle = 'Portal Pelanggan'): void
{
    ?>
    <header class="sticky top-0 z-40 flex h-16 items-center justify-between border-b border-outline bg-surface px-5 md:px-8">
        <div class="flex items-center gap-4">
            <div class="border border-outline bg-surface-high px-3 py-1 text-[12px] font-black uppercase tracking-[.18em] text-primary" data-realtime-clock><?= date('H:i:s'); ?></div>
            <span class="hidden text-on-muted/50 md:block">|</span>
            <span class="hidden text-sm text-on-muted md:block"><?= htmlspecialchars($subtitle); ?></span>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <?php if (!empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $_SESSION['avatar'])): ?>
                    <div class="w-8 h-8 rounded border border-primary overflow-hidden hidden sm:block">
                        <img src="../uploads/avatars/<?= htmlspecialchars($_SESSION['avatar']); ?>" class="w-full h-full object-cover">
                    </div>
                <?php endif; ?>
                <span class="hidden text-sm text-on-muted sm:inline">Halo, <strong class="uppercase text-on-surface"><?= htmlspecialchars($GLOBALS['username'] ?? ($_SESSION['username'] ?? 'Pelanggan')); ?></strong></span>
            </div>
            <a href="../auth/logout.php" class="flex h-10 w-10 items-center justify-center border border-outline text-on-muted transition hover:border-primary hover:text-primary" title="Keluar">
                <span class="material-symbols-outlined">logout</span>
            </a>
        </div>
    </header>
    <?php
}

function pelanggan_mobile_nav(string $active): void
{
    $links = [
        ['dashboard.php', 'dashboard', 'ringkasan', 'Dashboard'],
        ['ambil_antrian.php', 'add_circle', 'antrean', 'Pesan'],
        ['katalog.php', 'content_cut', 'katalog', 'Katalog'],
        ['riwayat.php', 'history', 'riwayat', 'Riwayat'],
        ['profil.php', 'person', 'profil', 'Profil'],
    ];
    ?>
    <nav class="fixed bottom-0 left-0 z-50 flex w-full justify-around border-t border-outline bg-surface-panel py-3 md:hidden">
        <?php foreach ($links as [$href, $icon, $key, $label]): ?>
            <a href="<?= htmlspecialchars($href); ?>" class="<?= $active === $key ? 'text-primary' : 'text-on-muted'; ?> flex flex-col items-center justify-center gap-1">
                <span class="material-symbols-outlined"><?= htmlspecialchars($icon); ?></span>
                <span class="text-[10px] font-black uppercase tracking-[0.12em]"><?= htmlspecialchars($label); ?></span>
            </a>
        <?php endforeach; ?>
        <a href="../auth/logout.php" class="flex flex-col items-center justify-center gap-1 text-on-muted">
            <span class="material-symbols-outlined">logout</span>
            <span class="text-[10px] font-black uppercase tracking-[0.12em]">Keluar</span>
        </a>
    </nav>
    <?php
}
