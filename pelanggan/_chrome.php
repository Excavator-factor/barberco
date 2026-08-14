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


function pelanggan_booking_modal() {
    global $conn;
    $q_layanan = mysqli_query($conn, "SELECT * FROM layanan ORDER BY id ASC");
    $q_barber =  mysqli_query($conn, "SELECT * FROM barber WHERE LOWER(status) = 'aktif' ORDER BY id ASC");

    // Fetch into arrays
    $layanan = [];
    $col_l_gambar = getExistingCol($conn, 'layanan', ['gambar', 'foto', 'image', 'foto_layanan']);
    if ($q_layanan) { while ($row = mysqli_fetch_assoc($q_layanan)) { $layanan[] = $row; } }
    $barbers = [];
    if ($q_barber) { while ($row = mysqli_fetch_assoc($q_barber)) { $barbers[] = $row; } }
    
    $fallback_images = [
        'https://images.unsplash.com/photo-1622287162716-f311baa1a2b8?auto=format&fit=crop&w=1000&q=80',
        'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?auto=format&fit=crop&w=1000&q=80',
        'https://images.unsplash.com/photo-1502823403499-6ccfcf4fb453?auto=format&fit=crop&w=1000&q=80',
        'https://images.unsplash.com/photo-1605497788044-5a32c7078486?auto=format&fit=crop&w=1000&q=80',
        'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1000&q=80',
        'https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=1000&q=80',
    ];
?>
    <div id="bookingModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeBookingModal()"></div>
        
        <!-- Modal Content -->
        <div class="relative w-full max-w-2xl max-h-[90vh] flex flex-col bg-surface border border-outline shadow-2xl scale-95 transition-transform duration-300" id="bookingModalBody">
            
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-outline p-5 bg-surface-high shrink-0">
                <div>
                    <h3 class="font-display text-xl font-black text-on-surface">Pesan Layanan</h3>
                    <p class="text-[11px] font-black uppercase text-primary tracking-widest mt-1">Multi-step Booking</p>
                </div>
                <button type="button" onclick="closeBookingModal()" class="text-on-muted hover:text-error transition"><span class="material-symbols-outlined">close</span></button>
            </div>
            
            <form action="proses_booking.php" method="POST" id="bookingForm" class="flex flex-col overflow-hidden min-h-0 flex-1">
                <input type="hidden" name="id_layanan" id="modal_id_layanan" value="">
                <input type="hidden" name="id_barber" id="modal_id_barber" value="">
                
                <div class="overflow-y-auto p-6 customer-scroll relative flex-1" id="modalScrollArea">
                    <!-- Step 1: Layanan -->
                    <div id="step1" class="step-container space-y-4">
                        <h4 class="font-bold text-on-surface mb-4">Langkah 1: Pilih Layanan</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($layanan as $l): 
                                $img_url = '';
                                if ($col_l_gambar && !empty($l[$col_l_gambar])) {
                                    $img_url = '../' . ltrim($l[$col_l_gambar], '/');
                                } else {
                                    $id = (int) ($l['id'] ?? 0);
                                    $img_url = $fallback_images[$id % count($fallback_images)];
                                }
                            ?>
                                <label class="block cursor-pointer">
                                    <input type="radio" name="layanan_sel" class="peer hidden" value="<?= $l['id'] ?>" data-name="<?= htmlspecialchars($l['nama_layanan']) ?>" data-price="<?= number_format($l['harga'],0,',','.') ?>">
                                    <div class="border border-outline bg-surface-panel p-4 hover:border-primary peer-checked:border-primary peer-checked:bg-primary/5 transition flex items-center gap-4">
                                        <div class="w-16 h-16 rounded overflow-hidden shrink-0 border border-outline">
                                            <img src="<?= htmlspecialchars($img_url) ?>" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <h5 class="font-bold text-on-surface"><?= htmlspecialchars($l['nama_layanan']) ?></h5>
                                            <p class="text-primary text-sm font-black mt-1">Rp <?= number_format($l['harga'],0,',','.') ?></p>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 2: Barber -->
                    <div id="step2" class="step-container space-y-4 hidden">
                        <h4 class="font-bold text-on-surface mb-2">Langkah 2: Pilih Barber</h4>
                        <p class="text-sm text-on-muted mb-4">Opsional. Biarkan kosong jika tidak ada preferensi.</p>
                        
                        <label class="block cursor-pointer mb-3">
                            <input type="radio" name="barber_sel" class="peer hidden" value="0" data-name="Barber Tersedia" checked>
                            <div class="border border-outline bg-surface-panel p-4 hover:border-primary peer-checked:border-primary peer-checked:bg-primary/5 transition flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary">group</span>
                                <span class="font-bold text-on-surface">Siapapun yang tersedia</span>
                            </div>
                        </label>
                            
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($barbers as $b): ?>
                                <label class="block cursor-pointer">
                                    <input type="radio" name="barber_sel" class="peer hidden" value="<?= $b['id'] ?>" data-name="<?= htmlspecialchars($b['nama']) ?>">
                                    <div class="border border-outline bg-surface-panel p-4 hover:border-primary peer-checked:border-primary peer-checked:bg-primary/5 transition flex items-center gap-3">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($b['nama']) ?>&background=random" class="w-10 h-10 rounded-full border border-outline">
                                        <div>
                                            <h5 class="font-bold text-on-surface"><?= htmlspecialchars($b['nama']) ?></h5>
                                            <p class="text-[10px] text-primary uppercase"><?= htmlspecialchars($b['spesialisasi'] ?: 'Master Barber') ?></p>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Step 3: Summary -->
                    <div id="step3" class="step-container space-y-4 hidden">
                        <h4 class="font-bold text-on-surface mb-4">Langkah 3: Konfirmasi Ringkasan</h4>
                        
                        <div class="bg-surface-panel p-5 border border-outline space-y-4">
                            <div class="flex justify-between border-b border-outline pb-3">
                                <span class="text-on-muted">Layanan</span>
                                <span class="font-bold text-on-surface" id="summary-layanan">-</span>
                            </div>
                            <div class="flex justify-between border-b border-outline pb-3">
                                <span class="text-on-muted">Barber</span>
                                <span class="font-bold text-on-surface" id="summary-barber">-</span>
                            </div>
                            <div class="flex justify-between pt-2">
                                <span class="text-[11px] font-black uppercase tracking-widest text-on-muted">Total Biaya</span>
                                <span class="font-display text-2xl font-black text-primary" id="summary-biaya">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer / Navigation -->
                <div class="border-t border-outline p-5 bg-surface-high flex justify-between items-center gap-4 shrink-0">
                    <button type="button" id="btnPrev" onclick="prevStep()" class="hidden px-5 py-2.5 border border-outline text-on-muted hover:text-on-surface font-bold text-xs uppercase tracking-widest transition">
                        Kembali
                    </button>
                    <div class="flex-1"></div>
                    <button type="button" id="btnNext" onclick="nextStep()" class="px-5 py-2.5 bg-primary text-on-primary font-bold text-xs uppercase tracking-widest hover:bg-white transition">
                        Selanjutnya
                    </button>
                    <button type="button" id="btnSubmit" onclick="submitBooking()" class="hidden px-5 py-2.5 bg-primary text-on-primary font-bold text-xs uppercase tracking-widest hover:bg-white transition flex items-center gap-2">
                        Konfirmasi <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        let currentStep = 1;

        function openBookingModal(preselectedLayananId = null) {
            document.getElementById("bookingModal").classList.remove("hidden");
            setTimeout(() => {
                document.getElementById("bookingModalBody").classList.remove("scale-95");
                document.getElementById("bookingModalBody").classList.add("scale-100");
            }, 10);
            
            if (preselectedLayananId) {
                const input = document.querySelector(`input[name="layanan_sel"][value="${preselectedLayananId}"]`);
                if (input) input.checked = true;
            }
            
            goToStep(1);
        }

        function closeBookingModal() {
            document.getElementById("bookingModalBody").classList.remove("scale-100");
            document.getElementById("bookingModalBody").classList.add("scale-95");
            setTimeout(() => {
                document.getElementById("bookingModal").classList.add("hidden");
            }, 300);
        }

        function goToStep(step) {
            if (step === 2) {
                const selectedLayanan = document.querySelector('input[name="layanan_sel"]:checked');
                if (!selectedLayanan) {
                    Swal.fire({icon: "error", title: "Oops!", text: "Harap Pilih Layanan Terlebih Dahulu.", background: "#1e2020", color: "#e2e2e2"});
                    return;
                }
            } else if (step === 3) {
                 const l = document.querySelector('input[name="layanan_sel"]:checked');
                 const b = document.querySelector('input[name="barber_sel"]:checked');
                 
                 document.getElementById("modal_id_layanan").value = l.value;
                 document.getElementById("modal_id_barber").value = b.value;
                 
                 document.getElementById("summary-layanan").innerText = l.getAttribute("data-name");
                 document.getElementById("summary-biaya").innerText = "Rp " + l.getAttribute("data-price");
                 document.getElementById("summary-barber").innerText = b.getAttribute("data-name");
            }

            document.getElementById("step1").classList.add("hidden");
            document.getElementById("step2").classList.add("hidden");
            document.getElementById("step3").classList.add("hidden");
            
            document.getElementById("btnPrev").classList.add("hidden");
            document.getElementById("btnNext").classList.add("hidden");
            document.getElementById("btnSubmit").classList.add("hidden");

            document.getElementById("step" + step).classList.remove("hidden");
            document.getElementById("modalScrollArea").scrollTop = 0;

            if (step === 1) {
                document.getElementById("btnNext").classList.remove("hidden");
            } else if (step === 2) {
                document.getElementById("btnPrev").classList.remove("hidden");
                document.getElementById("btnNext").classList.remove("hidden");
            } else if (step === 3) {
                document.getElementById("btnPrev").classList.remove("hidden");
                document.getElementById("btnSubmit").classList.remove("hidden");
            }
            
            currentStep = step;
        }

        function nextStep() { goToStep(currentStep + 1); }
        function prevStep() { goToStep(currentStep - 1); }
        function submitBooking() { document.getElementById("bookingForm").submit(); }
    </script>
<?php
}

function pelanggan_payment_modal($active_queue) {
    if (!$active_queue) return;
    $harga = (int) $active_queue['harga_layanan'];
?>
    <div id="paymentModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closePaymentModal()"></div>
        
        <!-- Modal Content -->
        <div class="relative w-full max-w-lg max-h-[90vh] flex flex-col bg-surface border border-outline shadow-2xl scale-95 transition-transform duration-300" id="paymentModalBody">
            
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-outline p-5 bg-surface-high shrink-0">
                <div>
                    <h3 class="font-display text-xl font-black text-on-surface">Pembayaran</h3>
                    <p class="text-[11px] font-black uppercase text-primary tracking-widest mt-1">Selesaikan Transaksi</p>
                </div>
                <button type="button" onclick="closePaymentModal()" class="text-on-muted hover:text-error transition"><span class="material-symbols-outlined">close</span></button>
            </div>
            
            <form action="proses_pembayaran.php" method="POST" enctype="multipart/form-data" class="flex flex-col overflow-hidden min-h-0 flex-1">
                <input type="hidden" name="antrian_id" value="<?= (int)$active_queue['id'] ?>">
                
                <div class="overflow-y-auto p-6 customer-scroll relative flex-1 space-y-6">
                    <div class="flex justify-between items-end pb-4 border-b-2 border-primary">
                        <span class="text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Total Tagihan</span>
                        <span class="font-display text-2xl font-black text-primary">Rp <?= number_format($harga, 0, ',', '.'); ?></span>
                    </div>

                    <div>
                        <label class="mb-3 block text-[12px] font-black uppercase tracking-[0.18em] text-primary">Metode Pembayaran</label>
                        <div class="grid gap-3">
                            <label class="flex cursor-pointer items-center justify-between gap-4 border border-outline bg-surface-panel p-4 transition hover:border-primary">
                                <span class="flex items-center gap-3 text-sm font-bold uppercase text-on-surface"><input type="radio" name="metode_pembayaran" value="QRIS" checked onclick="togglePaymentUpload(true, 'qris')" class="accent-[#f2ca50]">QRIS Instant</span>
                                <span class="material-symbols-outlined text-primary">qr_code_scanner</span>
                            </label>
                            <label class="flex cursor-pointer items-center justify-between gap-4 border border-outline bg-surface-panel p-4 transition hover:border-primary">
                                <span class="flex items-center gap-3 text-sm font-bold uppercase text-on-surface"><input type="radio" name="metode_pembayaran" value="Transfer Bank" onclick="togglePaymentUpload(true, 'bank')" class="accent-[#f2ca50]">Transfer Bank</span>
                                <span class="material-symbols-outlined text-primary">account_balance</span>
                            </label>
                            <label class="flex cursor-pointer items-center justify-between gap-4 border border-outline bg-surface-panel p-4 transition hover:border-primary">
                                <span class="flex items-center gap-3 text-sm font-bold uppercase text-on-surface"><input type="radio" name="metode_pembayaran" value="Tunai" onclick="togglePaymentUpload(false, 'cash')" class="accent-[#f2ca50]">Tunai di Kasir</span>
                                <span class="material-symbols-outlined text-primary">payments</span>
                            </label>
                        </div>
                    </div>

                    <div id="modal-info-qris" class="border border-dashed border-primary bg-primary/5 p-5 text-center">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Scan QRIS Barber.co</p>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=BARBERCO-IDR-<?= $harga ?>" alt="QRIS" class="mx-auto border border-outline bg-white p-2 w-32 h-32 object-contain">
                    </div>

                    <div id="modal-info-bank" class="hidden border border-dashed border-primary bg-primary/5 p-5">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Rekening Pembayaran</p>
                        <div class="space-y-1 text-sm text-on-surface">
                            <p><strong>BCA:</strong> 8830-1234-5678 a.n Barber.co</p>
                            <p><strong>Mandiri:</strong> 112-00-9876-5432 a.n Barber.co</p>
                        </div>
                    </div>

                    <div id="modal-wrapper-upload" class="space-y-2">
                        <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-on-muted">Unggah Bukti</label>
                        <input type="file" name="bukti_pembayaran" accept="image/*,.pdf" class="w-full border border-outline bg-surface-panel p-2 text-sm text-on-muted file:mr-4 file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-[10px] file:font-black file:uppercase file:text-on-primary">
                    </div>
                </div>

                <div class="border-t border-outline p-5 bg-surface-high flex shrink-0">
                    <button type="submit" class="flex w-full items-center justify-center gap-2 border border-primary bg-primary py-3 text-[12px] font-black uppercase tracking-[0.18em] text-on-primary transition hover:bg-transparent hover:text-primary">
                        <span class="material-symbols-outlined">verified</span>
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openPaymentModal() {
            const modal = document.getElementById("paymentModal");
            if(modal) {
                modal.classList.remove("hidden");
                setTimeout(() => {
                    document.getElementById("paymentModalBody").classList.remove("scale-95");
                    document.getElementById("paymentModalBody").classList.add("scale-100");
                }, 10);
            }
        }

        function closePaymentModal() {
            document.getElementById("paymentModalBody").classList.remove("scale-100");
            document.getElementById("paymentModalBody").classList.add("scale-95");
            setTimeout(() => {
                document.getElementById("paymentModal").classList.add("hidden");
            }, 300);
        }

        function togglePaymentUpload(showUpload, type) {
            document.getElementById('modal-wrapper-upload').classList.toggle('hidden', !showUpload);
            document.getElementById('modal-info-qris').classList.toggle('hidden', type !== 'qris');
            document.getElementById('modal-info-bank').classList.toggle('hidden', type !== 'bank');
        }
    </script>
<?php
}
?>
