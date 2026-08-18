<?php
session_start();
include "../config/database.php";

$submitted = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $recoveryId = trim($_POST["recovery_id"] ?? "");

    if ($recoveryId === "") {
        $error = "Masukkan username Anda terlebih dahulu.";
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id_user FROM users WHERE username = ? LIMIT 1",
        );
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $recoveryId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        $submitted = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barber.co | Atur Ulang Kata Sandi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&amp;family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20,300,0,0&amp;display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { jakarta: ['Plus Jakarta Sans', 'sans-serif'] }, colors: { slateink: '#0f172a', sand: '#f8fafc', amberaccent: '#fbbf24' } } } };</script>
    <style>
        .text-split-v { writing-mode: vertical-rl; text-orientation: mixed; }
        input:focus { outline: none !important; box-shadow: none !important; }
    </style>
</head>
<body class="flex min-h-screen flex-col bg-sand font-jakarta text-slateink antialiased">
    <header class="sticky top-0 z-20 flex h-20 items-center justify-between border-b border-slate-200 bg-white px-6 sm:px-12">
        <div class="flex items-center gap-4"><span class="text-2xl font-extrabold tracking-tighter text-slateink">BARBER.CO</span><span class="h-4 w-px bg-slate-200"></span><span class="text-[11px] font-bold tracking-[.2em] text-slate-500">RESET PROTOCOL</span></div>
        <a href="login.php" class="hidden text-[11px] font-bold tracking-[.2em] text-slate-500 transition-colors hover:text-slateink sm:block">KEMBALI MASUK</a>
    </header>
    <main class="relative flex flex-1 items-center justify-center overflow-hidden px-6 py-12 sm:px-12">
        <div class="pointer-events-none absolute left-12 top-12 hidden text-[10px] font-mono uppercase tracking-wider text-slate-400 lg:block">System Instance: GR-992<br>Security: Active TLS 1.3</div>
        <div class="pointer-events-none absolute bottom-12 right-12 hidden text-right text-[10px] font-mono uppercase tracking-wider text-slate-400 lg:block">Archival Registry v4.0<br>Ref: 0101-PRP-BCO</div>
        <section class="grid w-full max-w-6xl overflow-hidden border border-slate-200 bg-white shadow-sm lg:grid-cols-2">
            <div class="flex min-h-[520px] items-center p-8 sm:p-14 lg:p-20">
                <div class="mx-auto w-full max-w-sm">
                    <span class="mb-6 inline-block border-b border-slate-950 pb-1 text-[11px] font-bold tracking-[.2em]">SECURITY / RECOVERY</span>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slateink">Atur Ulang Kata Sandi</h1>
                    <p class="mt-4 text-sm leading-relaxed text-slate-500">Masukkan username Anda untuk meminta instruksi pemulihan akun.</p>

                    <?php if ($submitted): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Permintaan Diterima',
                                    text: 'Permintaan pemulihan telah diterima. Silakan hubungi admin barbershop.',
                                    background: '#f8fafc',
                                    color: '#0f172a',
                                    confirmButtonColor: '#0f172a',
                                    iconColor: '#059669'
                                });
                            });
                        </script>
                    <?php else: ?>
                        <?php if ($error): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Terjadi Kesalahan',
                                    text: '<?= htmlspecialchars(
                                        $error,
                                        ENT_QUOTES,
                                    ) ?>',
                                    background: '#f8fafc',
                                    color: '#0f172a',
                                    confirmButtonColor: '#0f172a',
                                    iconColor: '#dc2626'
                                });
                            });
                        </script>
                        <?php endif; ?>
                        <form action="forgot_password.php" method="POST" class="mt-10 space-y-8">
                            <div>
                                <label class="mb-2 block text-[11px] font-bold tracking-[.2em] text-slate-600" for="recovery_id">USERNAME</label>
                                <input class="w-full border border-slate-200 bg-white px-4 py-4 font-mono text-sm uppercase transition-colors placeholder:text-slate-300 focus:border-slate-950" id="recovery_id" name="recovery_id" autocomplete="username" placeholder="CONTOH: PELANGGAN" type="text" required>
                            </div>
                            <button class="flex w-full items-center justify-center gap-2 bg-slate-950 px-8 py-4 text-[11px] font-bold tracking-[.2em] text-white transition-all hover:bg-amberaccent hover:text-slateink active:scale-[.98]" type="submit"><span>KIRIM INSTRUKSI</span><span class="material-symbols-outlined text-sm">arrow_forward</span></button>
                        </form>
                    <?php endif; ?>
                    <div class="mt-12 border-t border-slate-200 pt-8"><a class="flex items-center gap-2 text-[11px] font-bold tracking-[.15em] text-slate-500 transition-colors hover:text-slateink" href="login.php"><span class="material-symbols-outlined text-sm">arrow_back</span>KEMBALI KE HALAMAN MASUK</a></div>
                </div>
            </div>
            <div class="relative hidden min-h-[600px] overflow-hidden border-l border-slate-200 bg-slate-100 lg:block">
                <img class="absolute inset-0 h-full w-full object-cover grayscale contrast-125 opacity-75" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDiJZl1DOa2y0swhvCOXNjZZNFBrx114FXV9CfWkOLFPUPfLqGx8EYpRTuiSRTmYHdg6i1qeUuBlT2D7dHXjMUci8wzlIg6ARAnCowYlxV-p-VXctcMZwZQaOzEduBL0RR9SEs9oCDBEPh6RE2IDObLZkB02jkbVvqTrJT6nekv0Y-s1H5D_KCjjVXHsyqZNZn44cTn2HzHjU_NeJRMy-h9KFyVUS2BGutCU6CvnMchvXbpMDn6TZ_UvTQC_qxkPiUR-bUwYjgZIhZf" alt="Interior barbershop modern">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/35 to-transparent"></div>
                <div class="absolute bottom-12 left-12 border border-white/30 bg-white/10 p-8 text-white backdrop-blur-md"><p class="text-2xl font-extrabold italic">Archival Grooming</p><p class="mt-2 text-[11px] font-bold tracking-[.2em] text-white/70">EST. 2024 / JAKARTA REGISTRY</p></div>
                <p class="text-split-v absolute right-12 top-12 border border-slate-200/20 bg-white/90 p-4 text-[10px] font-mono tracking-wider text-slate-950">BARBER.CO SYSTEM ACCESS</p>
            </div>
        </section>
    </main>
    <footer class="flex flex-col items-center justify-between gap-4 border-t border-slate-200 bg-white px-6 py-7 text-[10px] font-bold tracking-[.15em] text-slate-500 sm:flex-row sm:px-12"><span>BARBER.CO &mdash; HAK CIPTA &copy; <?= date(
        "Y",
    ) ?></span><span>PRIVASI &nbsp;&nbsp; BANTUAN</span></footer>
</body>
</html>
