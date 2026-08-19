const fs = require('fs');

// 1. Modifikasi admin/_bootstrap.php
const bootPath = 'c:/laragon/www/barbershop/admin/_bootstrap.php';
let bootContent = fs.readFileSync(bootPath, 'utf8');

const notifFunc = `
function admin_ensure_notifications_table($conn): void
{
    static $checked = false;
    if ($checked) return;
    $checked = true;
    $sql = "CREATE TABLE IF NOT EXISTS admin_notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pesan VARCHAR(255) NOT NULL,
        url VARCHAR(255) DEFAULT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    @mysqli_query($conn, $sql);
}

// Ensure trigger
admin_ensure_notifications_table($conn);
`;

if (!bootContent.includes('admin_ensure_notifications_table')) {
    // Append before the first function declaration or at the end
    const target = 'function admin_ensure_layanan_image_column';
    const idx = bootContent.indexOf(target);
    if (idx !== -1) {
        bootContent = bootContent.substring(0, idx) + notifFunc + '\n' + bootContent.substring(idx);
        fs.writeFileSync(bootPath, bootContent);
        console.log('Updated _bootstrap.php');
    }
}

// 2. Modifikasi functions/auth.php
const authPath = 'c:/laragon/www/barbershop/functions/auth.php';
let authContent = fs.readFileSync(authPath, 'utf8');

const regSuccessTarget = 'if (mysqli_stmt_execute($stmt)) {';
if (authContent.includes(regSuccessTarget) && !authContent.includes('admin_notifications')) {
    const regInjection = `if (mysqli_stmt_execute($stmt)) {
                // Hook Notifikasi Admin
                $notifMsg = "Pelanggan baru terdaftar: " . mysqli_real_escape_string($conn, $nama);
                $notifUrl = "../admin/pengguna.php?t=pelanggan";
                @mysqli_query($conn, "INSERT INTO admin_notifications (pesan, url) VALUES ('$notifMsg', '$notifUrl')");
`;
    authContent = authContent.replace(regSuccessTarget, regInjection);
    fs.writeFileSync(authPath, authContent);
    console.log('Updated auth.php');
}

// 3. Modifikasi admin/_chrome.php
const chromePath = 'c:/laragon/www/barbershop/admin/_chrome.php';
let chromeContent = fs.readFileSync(chromePath, 'utf8');

// Modifikasi links array dan tambah lencana notifikasi (badge)
if (!chromeContent.includes('admin_notifications')) {
    const renderSidebarTarget = 'function admin_render_sidebar(string $active): void\n{';
    const sidebarInjection = `function admin_render_sidebar(string $active): void
{
    global $conn;
    $unreadCount = 0;
    if ($conn) {
        $qNotif = @mysqli_query($conn, "SELECT COUNT(*) as unread FROM admin_notifications WHERE is_read = 0");
        if ($qNotif && $row = mysqli_fetch_assoc($qNotif)) {
            $unreadCount = (int)$row['unread'];
        }
    }
`;
    chromeContent = chromeContent.replace(renderSidebarTarget, sidebarInjection);

    // Add notifikasi to $links
    const linksTarget = '["layanan.php", "inventory_2", "layanan", "Layanan"],';
    const linksInjection = '["layanan.php", "inventory_2", "layanan", "Layanan"],\n        ["notifikasi.php", "notifications", "notifikasi", "Notifikasi"],';
    chromeContent = chromeContent.replace(linksTarget, linksInjection);

    // Add badge
    const badgeTarget = '($label,\n                        ) ?></span>';
    const badgeTargetFallback = '($label) ?></span>'; // Post-prettier formatting can be varied

    // We will do a generic replace for the end of the a-tag block
    const aTagEnd = '?></span>\n                    </a>';
    const badgeInjection = `?></span>
                        <?php if ($key === 'notifikasi' && $unreadCount > 0): ?>
                            <span class="ml-auto bg-error text-on-error text-[10px] font-bold px-2 py-0.5 rounded-full"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>`;
    chromeContent = chromeContent.replace(aTagEnd, badgeInjection);

    // For mobile nav
    const mobileLinksTarget = '["layanan.php", "inventory_2", "layanan"],';
    const mobileLinksInjection = '["layanan.php", "inventory_2", "layanan"],\n        ["notifikasi.php", "notifications", "notifikasi"],';
    chromeContent = chromeContent.replace(mobileLinksTarget, mobileLinksInjection);

    fs.writeFileSync(chromePath, chromeContent);
    console.log('Updated _chrome.php');
}
