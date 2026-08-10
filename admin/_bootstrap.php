<?php
include_once '../config/database.php';
include_once '../config/helper.php';

check_login('admin');

function admin_ensure_layanan_image_column($conn): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $columns = @mysqli_query($conn, "SHOW COLUMNS FROM layanan LIKE 'gambar'");
    if (!$columns || mysqli_num_rows($columns) == 0) {
        @mysqli_query($conn, "ALTER TABLE layanan ADD COLUMN gambar VARCHAR(255) NULL AFTER deskripsi");
    }

    $desc = @mysqli_query($conn, "SHOW COLUMNS FROM layanan LIKE 'deskripsi'");
    if ($desc && mysqli_num_rows($desc) > 0) {
        $row = mysqli_fetch_assoc($desc);
        if (stripos($row['Type'], 'varchar') !== false) {
            @mysqli_query($conn, "ALTER TABLE layanan MODIFY COLUMN deskripsi TEXT");
        }
    }
}

function admin_scalar_query($conn, string $sql): int
{
    $result = mysqli_query($conn, $sql);
    $row = $result ? mysqli_fetch_assoc($result) : ['total' => 0];
    return (int) ($row['total'] ?? 0);
}

function admin_sidebar_link(string $href, string $icon, string $label, bool $active = false): string
{
    $classes = $active
        ? 'sidebar-link active flex items-center gap-4 px-5 py-4 text-[11px] font-bold uppercase tracking-widest no-underline'
        : 'sidebar-link flex items-center gap-4 px-5 py-4 text-[11px] font-bold uppercase tracking-widest text-neutral-500 no-underline';

    return '<a class="' . $classes . '" href="' . htmlspecialchars($href) . '"><span class="material-symbols-outlined">' . htmlspecialchars($icon) . '</span>' . htmlspecialchars($label) . '</a>';
}

function admin_month_names(): array
{
    return [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
}

$checked = admin_ensure_layanan_image_column($conn);

$today = date('Y-m-d');
$months = admin_month_names();
$days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$dateLabel = $days[(int) date('w')] . ', ' . date('d') . ' ' . $months[(int) date('n')] . ' ' . date('Y');

$adminDashboardStats = [
    'totalUsers' => admin_scalar_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'pelanggan'"),
    'totalServices' => admin_scalar_query($conn, 'SELECT COUNT(*) AS total FROM layanan'),
    'liveQueue' => admin_scalar_query($conn, "SELECT COUNT(*) AS total FROM antrian WHERE tanggal = '$today' AND status_antrian IN ('menunggu', 'proses')"),
    'bookingsToday' => admin_scalar_query($conn, "SELECT COUNT(*) AS total FROM antrian WHERE tanggal = '$today'"),
    'revenueToday' => admin_scalar_query($conn, "SELECT COALESCE(SUM(t.total_harga), 0) AS total FROM transaksi t JOIN antrian a ON a.id = t.antrian_id WHERE a.tanggal = '$today' AND t.status_pembayaran = 'lunas'"),
    'revenueMonth' => admin_scalar_query($conn, "SELECT COALESCE(SUM(t.total_harga), 0) AS total FROM transaksi t JOIN antrian a ON a.id = t.antrian_id WHERE a.tanggal >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND a.tanggal < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH) AND t.status_pembayaran = 'lunas'"),
    'revenueYear' => admin_scalar_query($conn, "SELECT COALESCE(SUM(t.total_harga), 0) AS total FROM transaksi t JOIN antrian a ON a.id = t.antrian_id WHERE YEAR(a.tanggal) = YEAR(CURDATE()) AND t.status_pembayaran = 'lunas'"),
    'activeBarbers' => admin_scalar_query($conn, "SELECT COUNT(*) AS total FROM barber WHERE status = 'aktif'"),
    'completedToday' => admin_scalar_query($conn, "SELECT COUNT(*) AS total FROM antrian WHERE tanggal = '$today' AND status_antrian = 'selesai'"),
];

$dailyCapacity = max($adminDashboardStats['activeBarbers'] * 8, 1);
$productivity = min(100, (int) round(($adminDashboardStats['completedToday'] / $dailyCapacity) * 100));

$adminQueues = mysqli_query($conn, "SELECT a.*, COALESCE(NULLIF(u.nama, ''), NULLIF(u.username, ''), 'Pelanggan') AS nama_pelanggan,
               l.nama_layanan, COALESCE(b.nama, 'Belum dipilih') AS nama_barber
               FROM antrian a
               JOIN users u ON a.pelanggan_id = u.id_user
               JOIN layanan l ON a.layanan_id = l.id
               LEFT JOIN barber b ON a.barber_id = b.id
               WHERE a.tanggal = '$today'
               ORDER BY FIELD(a.status_antrian, 'proses', 'menunggu', 'selesai'), a.no_antrian ASC");

$adminServices = mysqli_query($conn, 'SELECT * FROM layanan ORDER BY id DESC');
$adminCustomers = mysqli_query($conn, "SELECT id_user, nama, username FROM users WHERE role = 'pelanggan' ORDER BY COALESCE(NULLIF(nama, ''), username) ASC");
$adminBarberEditors = mysqli_query($conn, 'SELECT id, nama, spesialisasi, status FROM barber ORDER BY nama ASC');
$adminBarbers = mysqli_query($conn, "SELECT b.id, b.nama, b.status, b.spesialisasi, u.username,
    SUM(CASE WHEN a.tanggal = '$today' AND a.status_antrian = 'selesai' THEN 1 ELSE 0 END) AS sesi_selesai,
    MAX(CASE WHEN a.tanggal = '$today' AND a.status_antrian = 'proses' THEN 1 ELSE 0 END) AS sedang_melayani
    FROM barber b 
    JOIN users u ON b.user_id = u.id_user
    LEFT JOIN antrian a ON a.barber_id = b.id
    GROUP BY b.id, b.nama, b.status, b.spesialisasi, u.username ORDER BY b.nama ASC");
$adminMonthlyRevenueQuery = mysqli_query($conn, "SELECT MONTH(a.tanggal) AS month_number, COALESCE(SUM(t.total_harga), 0) AS total FROM transaksi t JOIN antrian a ON a.id = t.antrian_id WHERE YEAR(a.tanggal) = YEAR(CURDATE()) AND t.status_pembayaran = 'lunas' GROUP BY MONTH(a.tanggal) ORDER BY month_number");
$adminMonthlyRevenue = array_fill(1, 12, 0);
if ($adminMonthlyRevenueQuery) {
    while ($monthlyRow = mysqli_fetch_assoc($adminMonthlyRevenueQuery)) {
        $adminMonthlyRevenue[(int) $monthlyRow['month_number']] = (int) $monthlyRow['total'];
    }
}
