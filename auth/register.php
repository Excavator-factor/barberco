<?php
include '../config/database.php';

$error = '';

if (isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'pelanggan';

    $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $password, $role);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: login.php");
        exit;
    }

    $error = mysqli_errno($conn) === 1062
        ? 'Username sudah digunakan.'
        : 'Pendaftaran gagal. Silakan coba lagi.';
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Register | Barber.co</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Montserrat:wght@600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Montserrat:wght@100..900&display=swap" rel="stylesheet"/>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container": "#1e2020",
                        "on-primary-fixed": "#241a00",
                        "inverse-on-surface": "#2f3131",
                        "error": "#ffb4ab",
                        "surface-container-lowest": "#0c0f0f",
                        "surface-variant": "#333535",
                        "on-error-container": "#ffdad6",
                        "secondary": "#c8c6c5",
                        "error-container": "#93000a",
                        "surface-bright": "#37393a",
                        "secondary-fixed-dim": "#c8c6c5",
                        "outline": "#99907c",
                        "on-primary-fixed-variant": "#574500",
                        "background": "#121414",
                        "tertiary-fixed-dim": "#c8c6c5",
                        "tertiary-fixed": "#e5e2e1",
                        "on-secondary-fixed": "#1c1b1b",
                        "outline-variant": "#4d4635",
                        "surface-dim": "#121414",
                        "on-error": "#690005",
                        "on-background": "#e2e2e2",
                        "on-secondary-fixed-variant": "#474746",
                        "surface-container-high": "#282a2b",
                        "on-secondary-container": "#b7b5b4",
                        "on-tertiary-container": "#454545",
                        "secondary-fixed": "#e5e2e1",
                        "inverse-primary": "#735c00",
                        "primary-fixed-dim": "#e9c349",
                        "on-tertiary": "#313030",
                        "on-primary-container": "#554300",
                        "primary-container": "#d4af37",
                        "secondary-container": "#474746",
                        "on-surface": "#e2e2e2",
                        "on-secondary": "#313030",
                        "surface-tint": "#e9c349",
                        "on-primary": "#3c2f00",
                        "inverse-surface": "#e2e2e2",
                        "surface-container-highest": "#333535",
                        "primary-fixed": "#ffe088",
                        "tertiary": "#d0cecd",
                        "on-surface-variant": "#d0c5af",
                        "surface": "#121414",
                        "on-tertiary-fixed": "#1c1b1b",
                        "tertiary-container": "#b5b2b2",
                        "primary": "#f2ca50",
                        "on-tertiary-fixed-variant": "#474646",
                        "surface-container-low": "#1a1c1c"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "md": "24px",
                        "xs": "4px",
                        "lg": "48px",
                        "xl": "80px",
                        "gutter": "20px",
                        "container-max": "1440px",
                        "sm": "12px",
                        "base": "8px"
                    },
                    "fontFamily": {
                        "body-md": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-xl": ["Montserrat"],
                        "body-sm": ["Inter"],
                        "label-caps": ["Inter"],
                        "headline-md": ["Montserrat"],
                        "headline-lg-mobile": ["Montserrat"],
                        "headline-lg": ["Montserrat"]
                    },
                    "fontSize": {
                        "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "headline-xl": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-sm": ["14px", {"lineHeight": "1.5", "fontWeight": "400"}],
                        "label-caps": ["12px", {"lineHeight": "1", "letterSpacing": "0.1em", "fontWeight": "700"}],
                        "headline-md": ["20px", {"lineHeight": "1.4", "fontWeight": "600"}],
                        "headline-lg-mobile": ["24px", {"lineHeight": "1.3", "fontWeight": "600"}],
                        "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .form-input-focus:focus {
            border-color: #f2ca50 !important;
            outline: none;
            box-shadow: 0 0 0 1px #f2ca50;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #121414;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4d4635;
        }
    </style>
</head>
<body class="bg-background text-on-surface font-body-md min-h-screen flex flex-col items-center justify-center selection:bg-primary selection:text-on-primary">
    <!-- Top Navigation -->
    <nav class="fixed top-0 w-full flex justify-between items-center px-md py-base bg-surface z-50 border-b border-outline-variant">
        <div class="font-headline-md text-headline-md font-extrabold text-primary tracking-tighter">
            Barber.co
        </div>
        <a class="font-label-caps text-label-caps text-on-surface-variant hover:text-primary transition-colors flex items-center gap-2" href="login.php">
            Sudah jadi anggota? <span class="text-primary">MASUK</span>
        </a>
    </nav>
    
    <!-- Main Registration Container -->
    <main class="w-full max-w-[1200px] grid grid-cols-1 lg:grid-cols-2 min-h-[85vh] mt-xl mb-lg mx-md bg-surface-container-low border border-outline-variant overflow-hidden">
        
        <!-- Left Side: Aesthetic/Brand Panel -->
        <section class="hidden lg:flex flex-col justify-end p-xl relative overflow-hidden group">
            <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent z-10 opacity-90"></div>
                <img class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" data-alt="A moody, high-contrast interior shot of a luxury barbershop at night. Gleaming chrome barber chairs, dark wood cabinetry, and a warm gold glow from overhead vintage pendant lights. The atmosphere is sophisticated and masculine, with deep shadows and sharp, precise light reflections on leather surfaces, mirroring the Barber.co premium aesthetic." src="https://lh3.googleusercontent.com/aida-public/AB6AXuAVrXiscqUQ5qMad1xr3R8yXDmuS8lPPmm9__V7FT2R40eAAv1AJan7yudhg9h1Oiv5gMaoZ9b4vsneFzjJIZ2wF_hAmaHv3lgnIqek8PR5Zy9DNuVamzqtse4COWyELZZQ-Aqnq97AqZBBLPC5sUOZi4A-5X75ekDYMFPWALVIpyjyAwTIf1zgEeoKjdlJG1LjGV3_NuaQJzVwjkc5At2tTztDbzyoDBi3YVqEnt-49vFblX3AJ2nc" />
            </div>
            <div class="relative z-20 space-y-md">
                <div class="w-12 h-1 bg-primary"></div>
                <h1 class="font-headline-xl text-headline-xl text-on-surface max-w-sm">
                    Kuasai Seni <span class="text-primary">Grooming.</span>
                </h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-md">
                    Bergabunglah dengan kolektif eksklusif Barber.co. Dapatkan akses pemesanan prioritas, profil gaya personal, dan Sistem Grooming Aura premium kami.
                </p>
            </div>
        </section>
        
        <!-- Right Side: Form Panel -->
        <section class="flex flex-col justify-center p-md lg:p-xl bg-surface-container overflow-y-auto custom-scrollbar">
            <div class="max-w-md mx-auto w-full py-md">
                <header class="mb-lg">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Buat Akun</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant">Tingkatkan pengalaman grooming Anda hari ini.</p>
                </header>
                
                <?php if (!empty($error)): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Pendaftaran Gagal',
                                text: '<?= htmlspecialchars($error, ENT_QUOTES); ?>',
                                background: '#1e2020',
                                color: '#e2e2e2',
                                confirmButtonColor: '#f2ca50',
                                iconColor: '#ffb4ab'
                            });
                        });
                    </script>
                <?php endif; ?>

                <form class="space-y-base" id="registerForm" method="POST" action="">
                    <!-- Full Name -->
                    <div class="space-y-xs">
                        <label class="font-label-caps text-label-caps text-on-surface-variant">Nama Lengkap</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">person</span>
                            <input class="w-full bg-background border border-outline-variant rounded-none py-3 pl-12 pr-4 text-on-surface font-body-md form-input-focus placeholder:text-on-secondary-fixed-variant" name="nama" placeholder="Johnathan Doe" type="text" required />
                        </div>
                    </div>
                    
                    <!-- Username -->
                    <div class="space-y-xs">
                        <label class="font-label-caps text-label-caps text-on-surface-variant">Username</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">badge</span>
                            <input class="w-full bg-background border border-outline-variant rounded-none py-3 pl-12 pr-4 text-on-surface font-body-md form-input-focus placeholder:text-on-secondary-fixed-variant" name="username" placeholder="johndoe" type="text" required />
                        </div>
                    </div>
                    
                    <!-- Password -->
                    <div class="space-y-xs">
                        <label class="font-label-caps text-label-caps text-on-surface-variant">Password</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline text-sm">lock</span>
                            <input class="w-full bg-background border border-outline-variant rounded-none py-3 pl-12 pr-4 text-on-surface font-body-md form-input-focus placeholder:text-on-secondary-fixed-variant" name="password" placeholder="••••••••" type="password" required />
                            <button class="absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors focus:outline-none" type="button" id="togglePasswordBtn">
                                <span class="material-symbols-outlined text-sm">visibility</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Terms -->
                    <div class="py-base flex items-start gap-3">
                        <input class="mt-1 w-5 h-5 bg-background border border-outline-variant text-primary focus:ring-1 focus:ring-primary focus:ring-offset-0 rounded-none cursor-pointer" id="terms" type="checkbox" required />
                        <label class="font-body-sm text-body-sm text-on-surface-variant select-none" for="terms">
                            Saya setuju dengan <a class="text-primary hover:underline decoration-1 underline-offset-4" href="#">Syarat Layanan</a> dan <a class="text-primary hover:underline decoration-1 underline-offset-4" href="#">Kebijakan Privasi</a>.
                        </label>
                    </div>
                    
                    <!-- Submit -->
                    <button name="register" class="w-full bg-primary-container hover:bg-primary text-on-primary-fixed font-bold py-4 uppercase tracking-widest transition-all duration-300 transform active:scale-[0.98] mt-md" type="submit">
                        Mulai Perjalanan
                    </button>
                </form>

            </div>
        </section>
    </main>
    
    <!-- Footer -->
    <footer class="w-full py-md bg-surface-dim border-t border-outline-variant">
        <div class="max-w-7xl mx-auto px-md flex flex-col md:flex-row justify-between items-center gap-md">
            <p class="font-label-caps text-label-caps text-on-surface-variant">© <?php echo date('Y'); ?> Barber.co GROOMING CO.</p>
            <div class="flex gap-lg">
                <a class="font-label-caps text-label-caps text-on-surface-variant hover:text-primary transition-colors" href="#">KEBIJAKAN PRIVASI</a>
                <a class="font-label-caps text-label-caps text-on-surface-variant hover:text-primary transition-colors" href="#">SYARAT LAYANAN</a>
                <a class="font-label-caps text-label-caps text-on-surface-variant hover:text-primary transition-colors" href="#">BANTUAN</a>
            </div>
        </div>
    </footer>
    
    <script>
        // Password Visibility Toggle
        const passToggle = document.getElementById('togglePasswordBtn');
        const passInput = document.querySelector('input[name="password"]');
        
        passToggle.addEventListener('click', () => {
            if (passInput.type === 'password') {
                passInput.type = 'text';
                passToggle.querySelector('span').textContent = 'visibility_off';
            } else {
                passInput.type = 'password';
                passToggle.querySelector('span').textContent = 'visibility';
            }
        });

        // Floating label simulation (simple focus effects)
        const inputs = document.querySelectorAll('input:not([type="checkbox"])');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.classList.add('ring-1', 'ring-primary', 'rounded');
            });
            input.addEventListener('blur', () => {
                input.parentElement.classList.remove('ring-1', 'ring-primary', 'rounded');
            });
        });
    </script>
</body>
</html>
