<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
include "../config/database.php";

$error = $_SESSION["error"] ?? "";
unset($_SESSION["error"]);
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Barber.co | Login Aman</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&amp;family=Inter:wght@400;700&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
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
        body {
            background-color: #121414;
            background-image: 
                radial-gradient(circle at 2px 2px, #1e2020 1px, transparent 0);
            background-size: 40px 40px;
        }
        .luxury-gradient {
            background: linear-gradient(135deg, #1e2020 0%, #121414 100%);
        }
        .input-focus-gold:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 1px #d4af37;
            outline: none;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        /* Smooth Page Transitions */
        body { overflow-x: hidden; animation: pageFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; opacity: 0; }
        @keyframes pageFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        body.page-fade-out { animation: pageFadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes pageFadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-8px); } }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center items-center text-on-surface p-md">
    <!-- Login Container -->
    <main class="w-full max-w-[440px] flex flex-col gap-lg animate-in fade-in duration-700 z-10">
        <!-- Brand Identity -->
        <header class="flex flex-col items-center text-center gap-base">
            <div class="w-16 h-16 bg-primary-container flex items-center justify-center rounded-lg mb-base shadow-xl">
                <span class="material-symbols-outlined text-on-primary-container !text-4xl" style="font-variation-settings: 'FILL' 1;">content_cut</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-primary uppercase tracking-widest">Barber.co</h1>
            <p class="font-body-md text-body-md text-on-surface-variant max-w-[320px]">Lounge Perawatan Premium</p>
        </header>
        
        <!-- Auth Card -->
        <section class="luxury-gradient p-xl border border-outline-variant rounded-lg shadow-2xl relative overflow-hidden">
            <!-- Subtle Gold Accent Line -->
            <div class="absolute top-0 left-0 w-full h-[2px] bg-primary"></div>
            
            <?php if (!empty($error)): ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Gagal',
                            text: '<?= htmlspecialchars($error, ENT_QUOTES) ?>',
                            background: '#1e2020',
                            color: '#e2e2e2',
                            confirmButtonColor: '#f2ca50',
                            iconColor: '#ffb4ab'
                        });
                    });
                </script>
            <?php endif; ?>

            <form class="flex flex-col gap-md" method="POST" action="../functions/auth.php?action=login">
                <!-- Username Field -->
                <div class="flex flex-col gap-xs">
                    <label class="font-label-caps text-label-caps text-on-surface-variant uppercase" for="username">Username</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-on-surface-variant text-body-md">person</span>
                        <input class="w-full bg-surface-container-lowest border border-outline-variant py-md pl-[52px] pr-md rounded-lg font-body-md text-body-md text-on-surface input-focus-gold transition-all duration-200" id="username" name="username" placeholder="Masukkan username" type="text" value="<?= htmlspecialchars(
                            $_POST["username"] ?? "",
                        ) ?>" required>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="flex flex-col gap-xs">
                    <div class="flex justify-between items-end">
                        <label class="font-label-caps text-label-caps text-on-surface-variant uppercase" for="password">Password</label>
                        <a class="font-label-caps text-label-caps text-primary hover:text-primary-fixed-dim transition-colors" href="forgot_password.php">Lupa Kata Sandi?</a>
                    </div>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-md top-1/2 -translate-y-1/2 text-on-surface-variant text-body-md">lock</span>
                        <input class="w-full bg-surface-container-lowest border border-outline-variant py-md pl-[52px] pr-[52px] rounded-lg font-body-md text-body-md text-on-surface input-focus-gold transition-all duration-200" id="password" name="password" placeholder="••••••••" type="password" required>
                        <button class="absolute right-md top-1/2 -translate-y-1/2 text-on-surface-variant hover:text-primary transition-colors focus:outline-none" type="button" id="togglePasswordBtn">
                            <span class="material-symbols-outlined text-body-md">visibility</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-xs">
                    <input class="w-4 h-4 rounded-xs border-outline-variant bg-surface-container-lowest text-primary focus:ring-primary focus:ring-offset-surface" id="remember" name="remember" type="checkbox"/>
                    <label class="font-body-sm text-body-sm text-on-surface-variant cursor-pointer" for="remember">Biarkan saya tetap masuk</label>
                </div>

                <!-- Sign In Button -->
                <button class="mt-base bg-primary-container text-on-primary-fixed font-headline-md text-headline-md py-md rounded-lg hover:brightness-110 active:scale-[0.98] transition-all duration-200 shadow-lg flex items-center justify-center gap-base" type="submit">
                    Masuk
                    <span class="material-symbols-outlined">arrow_forward</span>
                </button>
            </form>
        </section>

        <!-- Footer Links -->
        <footer class="flex flex-col items-center gap-md">
            <p class="font-body-sm text-body-sm text-on-surface-variant">
                Belum punya akun? 
                <a class="text-primary font-bold hover:underline ml-1" href="register.php">Daftar Sekarang</a>
            </p>
            <nav class="flex gap-md">
                <a class="font-label-caps text-label-caps text-on-surface-variant hover:text-on-surface transition-colors" href="#">Kebijakan Privasi</a>
                <span class="w-[1px] h-3 bg-primary"></span>
                <a class="font-label-caps text-label-caps text-on-surface-variant hover:text-on-surface transition-colors" href="#">Ketentuan</a>
                <span class="w-[1px] h-3 bg-primary"></span>
                <a class="font-label-caps text-label-caps text-on-surface-variant hover:text-on-surface transition-colors" href="#">Bantuan</a>
            </nav>
            <p class="font-label-caps text-label-caps text-on-surface-variant mt-sm">© <?php echo date(
                "Y",
            ); ?> Barber.co Grooming Co.</p>
        </footer>
    </main>
    
    <!-- Background Decorative Elements -->
    <div class="fixed top-12 left-12 opacity-10 pointer-events-none hidden lg:block">
        <span class="material-symbols-outlined !text-[120px] text-primary">content_cut</span>
    </div>
    <div class="fixed bottom-12 right-12 opacity-10 pointer-events-none hidden lg:block rotate-12">
        <span class="material-symbols-outlined !text-[120px] text-primary">calendar_today</span>
    </div>

    <script>
        // Password toggle logic
        const togglePass = document.getElementById('togglePasswordBtn');
        const passInput = document.getElementById('password');
        
        if (togglePass && passInput) {
            togglePass.addEventListener('click', () => {
                const isPass = passInput.getAttribute('type') === 'password';
                passInput.setAttribute('type', isPass ? 'text' : 'password');
                togglePass.querySelector('span').innerText = isPass ? 'visibility_off' : 'visibility';
            });
        }
        
        // Button loading state
        document.querySelector('form').addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = `<span class="material-symbols-outlined animate-spin">progress_activity</span> <span class="tracking-widest">MENGHUBUNGKAN...</span>`;
            btn.style.pointerEvents = 'none';
        });
    </script>
</body>
</html>
