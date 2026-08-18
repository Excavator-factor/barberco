<?php
include "../config/database.php";
include "../config/helper.php";
check_login("admin");

$error = "";
$success = "";

if (isset($_POST["simpan"])) {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    $nama = mysqli_real_escape_string($conn, $_POST["nama"]);
    $spesialisasi = mysqli_real_escape_string($conn, $_POST["spesialisasi"]);
    $status = mysqli_real_escape_string($conn, $_POST["status"]);

    // Check if username already exists
    $check = mysqli_query(
        $conn,
        "SELECT id_user FROM users WHERE username = '$username'",
    );
    if ($check && mysqli_num_rows($check) > 0) {
        $error = "Username sudah digunakan. Silakan pilih username lain.";
    } else {
        mysqli_begin_transaction($conn);
        try {
            $insert_user = mysqli_query(
                $conn,
                "INSERT INTO users (username, password, role, nama) VALUES ('$username', '$password', 'barber', '$nama')",
            );
            if (!$insert_user) {
                throw new Exception(mysqli_error($conn));
            }

            $user_id = mysqli_insert_id($conn);

            $insert_barber = mysqli_query(
                $conn,
                "INSERT INTO barber (user_id, nama, spesialisasi, status) VALUES ('$user_id', '$nama', '$spesialisasi', '$status')",
            );
            if (!$insert_barber) {
                throw new Exception(mysqli_error($conn));
            }

            mysqli_commit($conn);
            $success = "Arstisan Barber berhasil didaftarkan!";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Gagal mendaftarkan barber sistem: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Barber.co | Add New Barber</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&amp;family=Inter:wght@400;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Shared Tailwind Config -->
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "on-primary-fixed-variant": "#574500",
                    "on-surface": "#e2e2e2",
                    "on-tertiary-fixed": "#1c1b1b",
                    "surface-dim": "#121414",
                    "on-primary": "#3c2f00",
                    "primary-container": "#d4af37",
                    "surface-container-high": "#282a2b",
                    "on-secondary-fixed-variant": "#474746",
                    "tertiary": "#d0cecd",
                    "surface-bright": "#37393a",
                    "on-tertiary-container": "#454545",
                    "outline-variant": "#4d4635",
                    "on-background": "#e2e2e2",
                    "inverse-primary": "#735c00",
                    "surface-container-low": "#1a1c1c",
                    "on-secondary-fixed": "#1c1b1b",
                    "on-error-container": "#ffdad6",
                    "on-surface-variant": "#d0c5af",
                    "secondary-fixed": "#e5e2e1",
                    "on-tertiary": "#313030",
                    "tertiary-fixed-dim": "#c8c6c5",
                    "error-container": "#93000a",
                    "surface-variant": "#333535",
                    "secondary": "#c8c6c5",
                    "primary": "#f2ca50",
                    "on-tertiary-fixed-variant": "#474646",
                    "secondary-fixed-dim": "#c8c6c5",
                    "on-error": "#690005",
                    "inverse-surface": "#e2e2e2",
                    "error": "#ffb4ab",
                    "surface": "#121414",
                    "outline": "#99907c",
                    "primary-fixed": "#ffe088",
                    "primary-fixed-dim": "#e9c349",
                    "inverse-on-surface": "#2f3131",
                    "surface-container-highest": "#333535",
                    "tertiary-fixed": "#e5e2e1",
                    "surface-container": "#1e2020",
                    "surface-tint": "#e9c349",
                    "on-secondary": "#313030",
                    "on-secondary-container": "#b7b5b4",
                    "on-primary-fixed": "#241a00",
                    "tertiary-container": "#b5b2b2",
                    "on-primary-container": "#554300",
                    "surface-container-lowest": "#0c0f0f",
                    "background": "#121414",
                    "secondary-container": "#474746"
            },
            "borderRadius": {
                    "DEFAULT": "0.125rem",
                    "lg": "0.25rem",
                    "xl": "0.5rem",
                    "full": "0.75rem"
            },
            "spacing": {
                    "xl": "80px",
                    "md": "24px",
                    "container-max": "1440px",
                    "gutter": "20px",
                    "lg": "48px",
                    "xs": "4px",
                    "sm": "12px",
                    "base": "8px"
            },
            "fontFamily": {
                    "label-caps": ["Inter"],
                    "body-sm": ["Inter"],
                    "headline-xl": ["Montserrat"],
                    "headline-lg": ["Montserrat"],
                    "body-md": ["Inter"],
                    "headline-lg-mobile": ["Montserrat"],
                    "headline-md": ["Montserrat"],
                    "body-lg": ["Inter"]
            },
            "fontSize": {
                    "label-caps": ["12px", {"lineHeight": "1", "letterSpacing": "0.1em", "fontWeight": "700"}],
                    "body-sm": ["14px", {"lineHeight": "1.5", "fontWeight": "400"}],
                    "headline-xl": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                    "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                    "headline-lg-mobile": ["24px", {"lineHeight": "1.3", "fontWeight": "600"}],
                    "headline-md": ["20px", {"lineHeight": "1.4", "fontWeight": "600"}],
                    "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}]
            }
          },
        },
      }
    </script>
<style>
        body { background-color: #121414; color: #e2e2e2; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #4d4635; border-radius: 10px; }
        .input-gold-focus:focus { border-color: #f2ca50; ring-color: #f2ca50; }
        .border-gold-accent { border-top: 2px solid #f2ca50; }

        /* Smooth Page Transitions */
        body { overflow-x: hidden; animation: pageFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; opacity: 0; }
        @keyframes pageFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        body.page-fade-out { animation: pageFadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes pageFadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-8px); } }
    </style>
</head>
<body class="font-body-md text-body-md overflow-hidden bg-background">
<!-- Main Content Wrapper -->
<main class="w-full flex-1 flex flex-col h-screen overflow-hidden">
<!-- TopNavBar Shell -->
<header class="flex justify-between items-center px-md w-full sticky top-0 z-40 bg-surface border-b border-outline-variant h-16">
<div class="flex items-center space-x-md">
<div class="relative group">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant group-focus-within:text-primary transition-colors">search</span>
<input class="bg-surface-container-low border border-outline-variant rounded-full pl-10 pr-4 py-1.5 text-body-sm focus:outline-none focus:border-primary transition-all w-64" placeholder="Search team members..." type="text"/>
</div>
</div>
<div class="flex items-center space-x-md">
<div class="flex items-center space-x-3 cursor-pointer group">
<div class="text-right">
<p class="font-label-caps text-label-caps text-on-surface uppercase"><?= htmlspecialchars(
    $_SESSION["username"] ?? "Admin",
) ?></p>
<p class="text-[10px] text-on-surface-variant">System Manager</p>
</div>
<?php if (
    !empty($_SESSION["avatar"]) &&
    file_exists(__DIR__ . "/../uploads/avatars/" . $_SESSION["avatar"])
): ?>
<div class="w-10 h-10 rounded-full border border-primary/30 flex items-center justify-center bg-surface-container-high text-primary group-hover:border-primary transition-colors overflow-hidden">
<img src="../uploads/avatars/<?= htmlspecialchars(
    $_SESSION["avatar"],
) ?>" class="w-full h-full object-cover text-primary">
</div>
<?php else: ?>
<div class="w-10 h-10 rounded-full border border-primary/30 flex items-center justify-center bg-surface-container-high text-primary group-hover:border-primary transition-colors">
<span class="material-symbols-outlined">person</span>
</div>
<?php endif; ?>
</div>
</div>
</header>
<!-- Content Area -->
<div class="flex-1 overflow-y-auto p-lg bg-surface-dim custom-scrollbar">
<div class="max-w-4xl mx-auto">
<!-- Page Header -->
<div class="mb-lg">
<div class="flex items-center space-x-3 mb-2">
<a class="text-on-surface-variant hover:text-primary transition-colors flex items-center" href="kapster.php">
<span class="material-symbols-outlined">arrow_back</span>
</a>
<span class="font-label-caps text-label-caps text-on-surface-variant">Management / Barbers / New</span>
</div>
<h2 class="font-headline-lg text-headline-lg text-on-surface mb-2">Add New Barber</h2>
<p class="text-on-surface-variant">Register a new professional grooming specialist to the Barber.co roster.</p>
</div>

<?php if ($error): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyimpan',
                text: '<?= htmlspecialchars($error, ENT_QUOTES) ?>',
                background: '#1e2020',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50',
                iconColor: '#ffb4ab'
            });
        });
    </script>
<?php endif; ?>
<?php if ($success): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: '<?= htmlspecialchars($success, ENT_QUOTES) ?>',
                background: '#1e2020',
                color: '#e2e2e2',
                confirmButtonColor: '#f2ca50',
                iconColor: '#f2ca50'
            });
        });
    </script>
<?php endif; ?>

<!-- Registration Form Card -->
<div class="bg-surface-container-low border border-outline-variant rounded-xl border-gold-accent shadow-2xl overflow-hidden">
<div class="p-md border-b border-outline-variant flex items-center justify-between">
<div class="flex items-center space-x-3">
<span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">person_add</span>
<h3 class="font-headline-md text-headline-md">Register New Barber</h3>
</div>
<span class="px-3 py-1 bg-primary/10 text-primary font-label-caps text-[10px] rounded border border-primary/20">AURA SYSTEM ID: 0924-X</span>
</div>
<form method="POST" class="p-lg space-y-lg">
<div class="grid grid-cols-1 md:grid-cols-2 gap-md">

<!-- Left Col: Basic Details -->
<div class="space-y-md">
    <div class="space-y-1.5 p-group">
        <label class="font-label-caps text-label-caps text-on-surface-variant block transition-colors">Full Name</label>
        <input name="nama" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:opacity-30" placeholder="e.g., Julian Sterling" type="text"/>
    </div>
    <div class="space-y-1.5 p-group">
        <label class="font-label-caps text-label-caps text-on-surface-variant block transition-colors">Specialty</label>
        <select name="spesialisasi" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all appearance-none cursor-pointer">
            <option disabled="" selected="" value="">Select area of expertise</option>
            <option value="Haircut">Haircut</option>
            <option value="Shaving">Shaving</option>
            <option value="Coloring">Coloring</option>
            <option value="All-Round">All-Round</option>
        </select>
    </div>
</div>

<!-- Right Col: Account & Access -->
<div class="space-y-md">
    <div class="space-y-1.5 p-group">
        <label class="font-label-caps text-label-caps text-on-surface-variant block transition-colors">System Username</label>
        <div class="relative">
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined text-lg">alternate_email</span>
        <input name="username" required class="w-full bg-surface border border-outline-variant rounded-lg pl-12 pr-4 py-3 text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:opacity-30" placeholder="e.g., julian_sterling" type="text"/>
        </div>
    </div>
    <div class="space-y-1.5 p-group">
        <label class="font-label-caps text-label-caps text-on-surface-variant block transition-colors">Temporary Password</label>
        <div class="relative">
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant material-symbols-outlined text-lg">lock</span>
        <input name="password" required class="w-full bg-surface border border-outline-variant rounded-lg pl-12 pr-4 py-3 text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all placeholder:opacity-30" placeholder="Enter secure password" type="password"/>
        </div>
    </div>
</div>

<!-- Bio Section / Status -->
<div class="col-span-full space-y-1.5 p-group">
<label class="font-label-caps text-label-caps text-on-surface-variant block transition-colors">Initial Status</label>
<select name="status" required class="w-full bg-surface border border-outline-variant rounded-lg px-4 py-3 text-on-surface focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all appearance-none cursor-pointer">
    <option value="aktif">Aktif</option>
    <option value="cuti">Cuti / Off</option>
</select>
</div>
</div>
<!-- Form Actions -->
<div class="pt-lg border-t border-outline-variant flex flex-col md:flex-row md:items-center justify-end space-y-3 md:space-y-0 md:space-x-md">
<a href="kapster.php" class="px-md py-3 text-on-surface-variant hover:text-on-surface font-label-caps text-label-caps transition-colors cursor-pointer active:scale-95 text-center">
                                Cancel
                            </a>
<button name="simpan" type="submit" class="bg-primary text-on-primary px-lg py-3 rounded font-bold font-label-caps text-label-caps shadow-lg hover:brightness-110 active:scale-95 transition-all flex items-center justify-center space-x-2">
<span>Add Barber to Team</span>
<span class="material-symbols-outlined text-sm">chevron_right</span>
</button>
</div>
</form>
</div>
<!-- Guidance Footer -->
<div class="mt-lg grid grid-cols-1 md:grid-cols-3 gap-md opacity-60">
<div class="flex items-start space-x-3">
<span class="material-symbols-outlined text-primary">verified_user</span>
<div>
<p class="font-label-caps text-[10px] text-on-surface">Compliance</p>
<p class="text-[12px]">All team members are subject to the Aura Professional Standard verification.</p>
</div>
</div>
<div class="flex items-start space-x-3">
<span class="material-symbols-outlined text-primary">schedule</span>
<div>
<p class="font-label-caps text-[10px] text-on-surface">Auto-Sync</p>
<p class="text-[12px]">New profiles are instantly added to the public booking system.</p>
</div>
</div>
<div class="flex items-start space-x-3">
<span class="material-symbols-outlined text-primary">cloud_done</span>
<div>
<p class="font-label-caps text-[10px] text-on-surface">Data Privacy</p>
<p class="text-[12px]">Encrypted data handling following Barber.co professional protocols.</p>
</div>
</div>
</div>
</div>
</div>
<!-- Footer Shell -->
<footer class="bg-surface border-t border-outline-variant w-full py-md px-md">
<div class="max-w-container-max mx-auto flex flex-col md:flex-row justify-between items-center text-on-surface-variant">
<p class="font-body-sm text-body-sm mb-4 md:mb-0">© 2026 Barber.co Professional. All rights reserved.</p>
<div class="flex space-x-md font-label-caps text-label-caps">
<a class="hover:text-primary transition-colors cursor-pointer" href="#">Privacy Policy</a>
<a class="hover:text-primary transition-colors cursor-pointer" href="#">Terms of Service</a>
<div class="flex items-center space-x-2 text-primary">
<span class="w-1.5 h-1.5 bg-primary rounded-full animate-pulse"></span>
<span class="cursor-pointer">System Status: Operational</span>
</div>
</div>
</div>
</footer>
</main>
<!-- Decorative background elements -->
<div class="fixed top-0 right-0 w-[600px] h-[600px] bg-primary/5 blur-[120px] -z-10 pointer-events-none"></div>
<div class="fixed bottom-0 left-0 w-[400px] h-[400px] bg-surface-container-highest/10 blur-[100px] -z-10 pointer-events-none"></div>
<script>
        // Hover effects for input groups
        const inputGroups = document.querySelectorAll('.p-group');
        inputGroups.forEach(group => {
            const input = group.querySelector('input, select, textarea');
            const label = group.querySelector('label');
            if(input && label) {
                input.addEventListener('focus', () => {
                    label.classList.replace('text-on-surface-variant', 'text-primary');
                });
                input.addEventListener('blur', () => {
                    label.classList.replace('text-primary', 'text-on-surface-variant');
                });
            }
        });
    </script>
</body>
</html>
