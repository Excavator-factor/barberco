<?php
function barber_head(string $title): void
{
    ?>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($title) ?> | Barber.co Barber</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;900&family=Inter:wght@400;500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <style>
        /* Smooth Page Transitions */
        body { background-color: #121414; color: #e2e2e2; overflow-x: hidden; animation: pageFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; opacity: 0; }
        @keyframes pageFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        body.page-fade-out { animation: pageFadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes pageFadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-8px); } }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .active-glow {
            box-shadow: 0 0 15px rgba(242, 202, 80, 0.3);
        }
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #121414;
        }
        ::-webkit-scrollbar-thumb {
            background: #333535;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #f2ca50;
        }
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 24px;
        }
        @media (max-width: 1023px) {
            .bento-grid {
                display: flex;
                flex-direction: column;
            }
        }
    </style>
    <script id="tailwind-config">
        tailwind.config = {
          darkMode: "class",
          theme: {
            extend: {
              "colors": {
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
              "borderRadius": {
                      "DEFAULT": "0.125rem",
                      "lg": "0.25rem",
                      "xl": "0.5rem",
                      "full": "0.75rem"
              },
              "spacing": {
                      "container-max": "1440px",
                      "md": "24px",
                      "base": "8px",
                      "xs": "4px",
                      "lg": "48px",
                      "gutter": "20px",
                      "sm": "12px",
                      "xl": "80px"
              },
              "fontFamily": {
                      "body-md": ["Inter", "sans-serif"],
                      "headline-lg-mobile": ["Montserrat", "sans-serif"],
                      "headline-md": ["Montserrat", "sans-serif"],
                      "headline-lg": ["Montserrat", "sans-serif"],
                      "body-sm": ["Inter", "sans-serif"],
                      "label-caps": ["Inter", "sans-serif"],
                      "body-lg": ["Inter", "sans-serif"],
                      "headline-xl": ["Montserrat", "sans-serif"]
              },
              "fontSize": {
                      "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                      "headline-lg-mobile": ["24px", {"lineHeight": "1.3", "fontWeight": "600"}],
                      "headline-md": ["20px", {"lineHeight": "1.4", "fontWeight": "600"}],
                      "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                      "body-sm": ["14px", {"lineHeight": "1.5", "fontWeight": "400"}],
                      "label-caps": ["12px", {"lineHeight": "1", "letterSpacing": "0.1em", "fontWeight": "700"}],
                      "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                      "headline-xl": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}]
              }
            }
          }
        };
    </script>
    <style>
        body.barber-sidebar-collapsed aside { width: 5.25rem !important; }
        body.barber-sidebar-collapsed aside h1, 
        body.barber-sidebar-collapsed aside p,
        body.barber-sidebar-collapsed aside .font-label-caps,
        body.barber-sidebar-collapsed aside .uppercase { display: none !important; }
        body.barber-sidebar-collapsed aside nav a,
        body.barber-sidebar-collapsed aside form button,
        body.barber-sidebar-collapsed aside > button { justify-content: center; padding-left:0; padding-right:0; width: 100%; margin:0 0 0.5rem 0;}
        body.barber-sidebar-collapsed aside .border-t a { justify-content: center; width: 100%; padding-left:0; padding-right:0; margin:0; }
        body.barber-sidebar-collapsed aside .p-md { justify-content: center; padding-left: 0; padding-right: 0;}
        body.barber-sidebar-collapsed main { margin-left: 5.25rem !important; }
    </style>
    <?php
}

function barber_header(string $title): void
{
    $barberName = $GLOBALS['barberName'] ?? 'Marcus Thorne';
    ?>
    <header class="h-16 flex-shrink-0 w-full bg-surface border-b border-outline-variant flex justify-between items-center px-md sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <button type="button" class="text-primary hover:opacity-80 transition" id="barberSidebarToggle">
                <span class="material-symbols-outlined" data-icon="menu">menu</span>
            </button>
            <div class="hidden sm:block">
                <span class="font-headline-md text-headline-md font-bold text-primary"><?= htmlspecialchars($title) ?></span>
            </div>
        </div>
        <div class="flex items-center gap-6">
            <!-- Real Time Clock -->
            <div class="font-label-caps text-label-caps text-primary border-r border-outline-variant pr-6 py-1" id="real-time-clock">
                <?= date('h:i:s A'); ?>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="text-right hidden lg:block">
                        <p class="font-bold text-sm leading-none text-on-surface"><?= htmlspecialchars($barberName) ?></p>
                        <p class="text-xs text-on-surface-variant">Master Barber</p>
                    </div>
                    <div class="w-10 h-10 rounded-full border-2 border-primary overflow-hidden flex items-center justify-center bg-surface-container-high text-primary font-bold">
                        <?php if (!empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $_SESSION['avatar'])): ?>
                            <img class="w-full h-full object-cover" alt="Barber Profile" src="../uploads/avatars/<?= htmlspecialchars($_SESSION['avatar']) ?>"/>
                        <?php else: ?>
                            <span class="material-symbols-outlined">person</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <?php
}

function barber_sidebar(string $active): void
{
    $activeQueue = $GLOBALS['activeQueue'] ?? null;
    $waitingQueues = $GLOBALS['waitingQueues'] ?? [];
    $barberId = $GLOBALS['barberId'] ?? 0;

    $firstStartable = null;
    if (!$activeQueue && $waitingQueues) {
        foreach ($waitingQueues as $q) {
            if (empty($q['barber_id']) || (int)$q['barber_id'] === $barberId) {
                $firstStartable = $q;
                break;
            }
        }
    }
    ?>
    <script>if(localStorage.getItem('barberSidebarCollapsed') === '1') document.body.classList.add('barber-sidebar-collapsed');</script>
    <aside class="hidden md:flex flex-col h-screen w-[250px] bg-surface-container border-r border-outline-variant fixed left-0 top-0 z-50">
        <div class="p-md">
            <a href="dashboard.php" class="block">
                <h1 class="font-headline-md text-headline-md font-black text-primary uppercase tracking-tighter">Barber.co</h1>
                <p class="font-label-caps text-label-caps text-on-surface-variant mt-1">Premium Grooming Admin</p>
            </a>
        </div>
        <nav class="flex-grow py-md overflow-y-auto space-y-1">
            <a class="<?= $active === 'dashboard' ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant hover:bg-secondary-container hover:text-on-secondary-container' ?> rounded-lg px-4 py-3 mx-2 my-1 flex items-center gap-3 transition-all duration-200" href="dashboard.php">
                <span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
                <span class="font-label-caps text-label-caps">Dashboard</span>
            </a>
            <a class="<?= $active === 'riwayat' ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant hover:bg-secondary-container hover:text-on-secondary-container' ?> rounded-lg px-4 py-3 mx-2 my-1 flex items-center gap-3 transition-all duration-200" href="riwayat.php">
                <span class="material-symbols-outlined" data-icon="analytics">analytics</span>
                <span class="font-label-caps text-label-caps">Riwayat Sesi</span>
            </a>



            <div class="px-md pt-lg pb-base">
                <p class="font-label-caps text-label-caps text-outline uppercase">Preferences</p>
            </div>
            <a class="<?= $active === 'profil' ? 'bg-primary text-on-primary font-bold' : 'text-on-surface-variant hover:bg-secondary-container hover:text-on-secondary-container' ?> rounded-lg px-4 py-3 mx-2 my-1 flex items-center gap-3 transition-all duration-200" href="profil.php">
                <span class="material-symbols-outlined" data-icon="person">person</span>
                <span class="font-label-caps text-label-caps">Profil & Pengaturan</span>
            </a>

        </nav>
        <div class="p-md border-t border-outline-variant space-y-2">
            <a class="text-on-surface-variant hover:text-primary flex items-center gap-3 text-sm" href="#">
                <span class="material-symbols-outlined" data-icon="help">help</span>
                <span>Support</span>
            </a>
            <a class="text-on-surface-variant hover:text-error flex items-center gap-3 text-sm" href="../auth/logout.php">
                <span class="material-symbols-outlined" data-icon="logout">logout</span>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    <?php
}

function barber_mobile_nav(string $active): void
{
    ?>
    <nav class="md:hidden fixed bottom-0 left-0 right-0 h-16 bg-surface-container border-t border-outline-variant flex justify-around items-center z-50 px-2">
        <a href="dashboard.php" class="flex flex-col items-center gap-1 <?= $active === 'dashboard' ? 'text-primary' : 'text-on-surface-variant' ?>">
            <span class="material-symbols-outlined text-xl" data-icon="dashboard">dashboard</span>
            <span class="text-[10px] font-bold uppercase">Dashboard</span>
        </a>
        <a href="riwayat.php" class="flex flex-col items-center gap-1 <?= $active === 'riwayat' ? 'text-primary' : 'text-on-surface-variant' ?>">
            <span class="material-symbols-outlined text-xl" data-icon="analytics">analytics</span>
            <span class="text-[10px] font-bold uppercase">Riwayat</span>
        </a>
        <a href="../auth/logout.php" class="flex flex-col items-center gap-1 text-error">
            <span class="material-symbols-outlined text-xl" data-icon="logout">logout</span>
            <span class="text-[10px] font-bold uppercase">Keluar</span>
        </a>
    </nav>
    <?php
}

function barber_scripts(): void
{
    ?>
    <script>
        // Real-Time Clock
        function updateClock() {
            const clockEl = document.getElementById('real-time-clock');
            if (clockEl) {
                const now = new Date();
                clockEl.textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        const barberToggle = document.getElementById('barberSidebarToggle');
        if (barberToggle) {
            const bStorage = 'barberSidebarCollapsed';
            if (localStorage.getItem(bStorage) === '1') {
                document.body.classList.add('barber-sidebar-collapsed');
            }
            barberToggle.addEventListener('click', () => {
                document.body.classList.toggle('barber-sidebar-collapsed');
                localStorage.setItem(bStorage, document.body.classList.contains('barber-sidebar-collapsed') ? '1' : '0');
                
                // For mobile view, just use the old 'hidden' toggle for the aside itself natively if strictly required
                if (window.innerWidth < 768) { document.querySelector('aside').classList.toggle('hidden'); }
            });
        }

        // Add subtle hover effects for bento cards
        document.querySelectorAll('.bento-grid > div').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-2px)';
                card.style.transition = 'transform 0.3s ease';
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
            });
        });
    </script>
    <?php
}
