<?php
include "../config/database.php";
include "../config/helper.php";
check_login("admin");

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["create_user"])) {
    $nama = mysqli_real_escape_string($conn, $_POST["nama"]);
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $role = mysqli_real_escape_string($conn, $_POST["role"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);

    if ($role === "customer") {
        $role = "pelanggan";
    } elseif ($role === "staff") {
        $role = "admin"; // We map staff to admin in the system
    } else {
        $role = "pelanggan";
    }

    if ($username === "" || $password === "") {
        $error = "Username dan password wajib diisi.";
    } else {
        $check = mysqli_query(
            $conn,
            "SELECT id_user FROM users WHERE username = '$username' LIMIT 1",
        );
        if ($check && mysqli_num_rows($check) > 0) {
            $error = "Username sudah digunakan.";
        } else {
            $insert = mysqli_query(
                $conn,
                "INSERT INTO users (username, password, role, nama) VALUES ('$username', '$password', '$role', '$nama')",
            );
            if ($insert) {
                $success = "Akun pengguna baru berhasil ditambahkan.";
            } else {
                $error = "Gagal menyimpan data pengguna.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Barber.co | Create User Account</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&amp;family=Inter:wght@400;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
        body {
            background-color: #121414;
            color: #e2e2e2;
            -webkit-font-smoothing: antialiased;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #121414;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4d4635;
            border-radius: 2px;
        }
        .form-input-gold:focus {
            outline: none;
            border-color: #f2ca50;
            box-shadow: 0 0 0 1px #f2ca50;
        }

        /* Smooth Page Transitions */
        body { overflow-x: hidden; animation: pageFadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; opacity: 0; }
        @keyframes pageFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        body.page-fade-out { animation: pageFadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes pageFadeOut { from { opacity: 1; transform: translateY(0); } to { opacity: 0; transform: translateY(-8px); } }
    </style>
</head>
<body class="font-body-md text-body-md overflow-hidden">
<!-- Main Content Area -->
<main class="w-full min-h-screen flex flex-col relative overflow-y-auto bg-surface-dim">
<!-- Top Navigation Bar -->
<header class="flex justify-between items-center px-md w-full sticky top-0 z-40 bg-surface h-16 border-b border-outline-variant">
<div class="flex items-center gap-md">
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant text-sm">search</span>
<input class="bg-surface-container-low border border-outline-variant rounded-full pl-9 pr-4 py-1.5 text-body-sm w-64 focus:border-primary focus:ring-0 outline-none transition-all" placeholder="Search clients or tools..." type="text"/>
</div>
</div>
<div class="flex items-center gap-md">
<div class="h-8 w-px bg-outline-variant"></div>
<div class="flex items-center gap-sm cursor-pointer active:opacity-80 transition-opacity">
<?php if (
    !empty($_SESSION["avatar"]) &&
    file_exists(__DIR__ . "/../uploads/avatars/" . $_SESSION["avatar"])
): ?>
<div class="w-8 h-8 rounded-full border border-primary flex items-center justify-center bg-surface-container overflow-hidden">
<img src="../uploads/avatars/<?= htmlspecialchars(
    $_SESSION["avatar"],
) ?>" class="w-full h-full object-cover">
</div>
<?php else: ?>
<div class="w-8 h-8 rounded-full border border-primary flex items-center justify-center bg-surface-container text-primary">
<span class="material-symbols-outlined text-sm">person</span>
</div>
<?php endif; ?>
<span class="font-label-caps text-label-caps text-primary uppercase"><?= htmlspecialchars(
    $_SESSION["username"] ?? "Admin",
) ?></span>
</div>
</div>
</header>
<!-- Content Canvas -->
<div class="p-lg max-w-4xl mx-auto w-full">
<!-- Breadcrumb / Header -->
<div class="mb-lg flex justify-between items-end">
<div>
<div class="flex items-center gap-xs text-on-surface-variant mb-2">
<span class="font-label-caps text-[10px] uppercase tracking-widest">Management</span>
<span class="material-symbols-outlined text-xs">chevron_right</span>
<span class="font-label-caps text-[10px] uppercase tracking-widest text-primary">Add User</span>
</div>
<h2 class="font-headline-lg text-headline-lg text-on-surface">User Management</h2>
</div>
<a href="pelanggan.php" class="flex items-center gap-xs text-on-surface-variant hover:text-primary transition-colors cursor-pointer py-2">
<span class="material-symbols-outlined text-sm">arrow_back</span>
<span class="font-label-caps text-label-caps">Back to User List</span>
</a>
</div>

<?php if ($error): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Data Gagal Disimpan',
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

<!-- Create User Card -->
<div class="bg-surface-container border border-outline-variant border-t-2 border-t-primary relative overflow-hidden shadow-2xl">
<div class="p-md border-b border-outline-variant bg-surface-container-high/30">
<h3 class="font-headline-md text-headline-md text-on-surface">Create User Account</h3>
<p class="text-body-sm text-on-surface-variant mt-1">Provide the essential information to register a new member to the Barber.co platform.</p>
</div>
<div class="p-md lg:p-lg">
<form method="POST" class="space-y-md">
<input type="hidden" name="role" id="role-input" value="customer">

<div class="grid grid-cols-1 md:grid-cols-2 gap-md">
<!-- Full Name -->
<div class="space-y-base p-group">
<label class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider block transition-colors">Full Name</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant transition-colors group-icon">person</span>
<input name="nama" required class="w-full bg-surface border border-outline-variant rounded py-3 pl-11 pr-4 text-on-surface form-input-gold transition-all" placeholder="e.g. Julian Wright" type="text"/>
</div>
</div>
<!-- Username -->
<div class="space-y-base p-group">
<label class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider block transition-colors">System Username</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant transition-colors group-icon">alternate_email</span>
<input name="username" required class="w-full bg-surface border border-outline-variant rounded py-3 pl-11 pr-4 text-on-surface form-input-gold transition-all" placeholder="julian_wright" type="text"/>
</div>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-md">
<!-- Password -->
<div class="space-y-base p-group">
<label class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider block transition-colors">Password</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant transition-colors group-icon">lock</span>
<input name="password" required class="w-full bg-surface border border-outline-variant rounded py-3 pl-11 pr-12 text-on-surface form-input-gold transition-all" placeholder="••••••••••••" type="password"/>
</div>
</div>
<!-- Account Type Toggle -->
<div class="space-y-base">
<label class="font-label-caps text-label-caps text-on-surface-variant uppercase tracking-wider block">Account Type</label>
<div class="flex h-[46px] p-1 bg-surface-container-high rounded border border-outline-variant">
<button class="flex-1 rounded font-label-caps text-label-caps flex items-center justify-center transition-all bg-primary text-on-primary shadow-lg" id="toggle-customer" onclick="setAccountType('customer')" type="button">
                                        Customer
                                    </button>
<button class="flex-1 rounded font-label-caps text-label-caps flex items-center justify-center transition-all text-on-surface-variant hover:text-on-surface" id="toggle-staff" onclick="setAccountType('staff')" type="button">
                                        Staff
                                    </button>
</div>
</div>
</div>

<div class="pt-lg flex flex-col md:flex-row gap-md">
<button name="create_user" type="submit" class="flex-1 bg-primary text-on-primary font-label-caps text-label-caps py-4 rounded-lg flex items-center justify-center gap-sm hover:brightness-110 active:scale-[0.98] transition-all">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">person_add</span>
                                Create Account
                            </button>
<a href="pelanggan.php" class="flex-1 border border-primary text-primary font-label-caps text-label-caps py-4 rounded-lg flex items-center justify-center gap-sm hover:bg-primary/10 active:scale-[0.98] transition-all">
<span class="material-symbols-outlined">close</span>
                                Cancel &amp; Discard
                            </a>
</div>
</form>
</div>
</div>
<!-- Additional Context Information -->
<div class="mt-lg grid grid-cols-1 md:grid-cols-3 gap-md opacity-80 mb-8">
<div class="p-md border border-outline-variant bg-surface-container-low flex flex-col items-center text-center gap-sm">
<span class="material-symbols-outlined text-primary text-3xl">verified_user</span>
<h4 class="font-label-caps text-label-caps text-on-surface">Secure Onboarding</h4>
<p class="text-[11px] text-on-surface-variant">Verified encryption for all personal data.</p>
</div>
<div class="p-md border border-outline-variant bg-surface-container-low flex flex-col items-center text-center gap-sm">
<span class="material-symbols-outlined text-primary text-3xl">mail_lock</span>
<h4 class="font-label-caps text-label-caps text-on-surface">System Verification</h4>
<p class="text-[11px] text-on-surface-variant">Accounts are immediately ready to use.</p>
</div>
<div class="p-md border border-outline-variant bg-surface-container-low flex flex-col items-center text-center gap-sm">
<span class="material-symbols-outlined text-primary text-3xl">loyalty</span>
<h4 class="font-label-caps text-label-caps text-on-surface">Membership Sync</h4>
<p class="text-[11px] text-on-surface-variant">Auto-enroll in Barber.co loyalty perks.</p>
</div>
</div>
</div>
<!-- Footer -->
<footer class="mt-auto border-t border-outline-variant bg-surface py-md">
<div class="flex flex-col md:flex-row justify-between items-center px-md max-w-container-max mx-auto w-full">
<div class="font-label-caps text-label-caps text-primary mb-md md:mb-0">© 2026 Barber.co Professional. All rights reserved.</div>
</div>
</footer>
</main>
<script>
        function setAccountType(type) {
            const customerBtn = document.getElementById('toggle-customer');
            const staffBtn = document.getElementById('toggle-staff');
            document.getElementById('role-input').value = type;
            
            if (type === 'customer') {
                customerBtn.classList.add('bg-primary', 'text-on-primary', 'shadow-lg');
                customerBtn.classList.remove('text-on-surface-variant');
                staffBtn.classList.remove('bg-primary', 'text-on-primary', 'shadow-lg');
                staffBtn.classList.add('text-on-surface-variant');
            } else {
                staffBtn.classList.add('bg-primary', 'text-on-primary', 'shadow-lg');
                staffBtn.classList.remove('text-on-surface-variant');
                customerBtn.classList.remove('bg-primary', 'text-on-primary', 'shadow-lg');
                customerBtn.classList.add('text-on-surface-variant');
            }
        }

        // Apply Gold coloring on focus
        document.querySelectorAll('.p-group input').forEach(input => {
            input.addEventListener('focus', function() {
                const icon = this.parentElement.querySelector('.group-icon');
                const label = this.closest('.p-group').querySelector('label');
                if (icon) icon.style.color = '#f2ca50';
                if (label) label.classList.replace('text-on-surface-variant', 'text-primary');
            });
            input.addEventListener('blur', function() {
                const icon = this.parentElement.querySelector('.group-icon');
                const label = this.closest('.p-group').querySelector('label');
                if (icon) icon.style.color = '';
                if (label) label.classList.replace('text-primary', 'text-on-surface-variant');
            });
        });
    </script>
</body>
</html>
