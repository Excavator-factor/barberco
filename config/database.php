<?php
date_default_timezone_set("Asia/Jakarta");

// Konfigurasi Database
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            $value = trim($value, "\"'");
            $_ENV[$name] = $value;
        }
    }
}

$host = $_ENV['DB_HOST'] ?? "localhost";
$user = $_ENV['DB_USER'] ?? "root";
$pass = $_ENV['DB_PASS'] ?? "";
$db   = $_ENV['DB_NAME'] ?? "barber_db";

// Matikan exception otomatis untuk mysqli (agar bisa menangkap error dengan baik)
mysqli_report(MYSQLI_REPORT_OFF);

// Langsung sertakan $db pada koneksi
$conn = @mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    if (!file_exists($envPath)) {
        die("<h1>Koneksi Database Gagal (500)</h1><p>Sepertinya file <b>.env</b> tidak ditemukan di server. Harap upload file .env secara manual ke file manager (htdocs) di InfinityFree.</p>");
    } else {
        die("<h1>Koneksi Database Gagal (500)</h1><p>" . mysqli_connect_error() . "</p><p>Pastikan kredensial di dalam file .env sudah benar.</p>");
    }
}


// Auto-import struktur awal dari barber_db.sql jika database belum memiliki tabel.
$checkUsersTbl = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if ($checkUsersTbl && mysqli_num_rows($checkUsersTbl) == 0) {
    $schemaFile = __DIR__ . "/../barber_db.sql";
    $sqlContent = is_readable($schemaFile)
        ? file_get_contents($schemaFile)
        : false;
    if ($sqlContent !== false) {
        if (!mysqli_multi_query($conn, $sqlContent)) {
            die("Gagal mengimpor struktur barber_db: " . mysqli_error($conn));
        }
        while (mysqli_more_results($conn) && mysqli_next_result($conn)) {
        }
    } else {
        die(
            "File database barber_db.sql tidak ditemukan atau tidak dapat dibaca."
        );
    }
}

mysqli_set_charset($conn, "utf8mb4");

// Migrasi ringan untuk database lama yang dibuat sebelum kolom `nama` ada.
$namaColumn = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'nama'");
if ($namaColumn && mysqli_num_rows($namaColumn) === 0) {
    if (
        !mysqli_query(
            $conn,
            "ALTER TABLE `users` ADD COLUMN `nama` VARCHAR(100) NOT NULL DEFAULT ''",
        )
    ) {
        die("Gagal memperbarui struktur tabel users: " . mysqli_error($conn));
    }
}

// Password dari pendaftaran memakai password_hash(), sehingga membutuhkan
// ruang lebih dari skema lama yang hanya menyediakan VARCHAR(20).
$passwordColumn = mysqli_query(
    $conn,
    "SHOW COLUMNS FROM `users` LIKE 'password'",
);
if ($passwordColumn && ($passwordInfo = mysqli_fetch_assoc($passwordColumn))) {
    if (stripos($passwordInfo["Type"], "varchar(255)") === false) {
        if (
            !mysqli_query(
                $conn,
                "ALTER TABLE `users` MODIFY `password` VARCHAR(255) NOT NULL",
            )
        ) {
            die("Gagal memperbarui kolom password: " . mysqli_error($conn));
        }
    }
}

// Status barber dapat menggunakan nilai "aktif" atau "nonaktif".
$barberStatusColumn = mysqli_query(
    $conn,
    "SHOW COLUMNS FROM `barber` LIKE 'status'",
);
if (
    $barberStatusColumn &&
    ($barberStatus = mysqli_fetch_assoc($barberStatusColumn))
) {
    if (stripos($barberStatus["Type"], "varchar(10)") === false) {
        if (
            !mysqli_query(
                $conn,
                "ALTER TABLE `barber` MODIFY `status` VARCHAR(10) NOT NULL",
            )
        ) {
            die(
                "Gagal memperbarui kolom status barber: " . mysqli_error($conn)
            );
        }
    }
}

// Deskripsi layanan adalah data katalog.
$serviceDescriptionColumn = mysqli_query(
    $conn,
    "SHOW COLUMNS FROM `layanan` LIKE 'deskripsi'",
);
if (
    $serviceDescriptionColumn &&
    mysqli_num_rows($serviceDescriptionColumn) === 0
) {
    if (
        !mysqli_query(
            $conn,
            "ALTER TABLE `layanan` ADD COLUMN `deskripsi` VARCHAR(255) NULL AFTER `nama_layanan`",
        )
    ) {
        die(
            "Gagal menambahkan kolom deskripsi layanan: " . mysqli_error($conn)
        );
    }
}

// Migrasi untuk menambahkan metode_pembayaran jika belum ada
$metodeColumn = mysqli_query(
    $conn,
    "SHOW COLUMNS FROM `transaksi` LIKE 'metode_pembayaran'",
);
if ($metodeColumn && mysqli_num_rows($metodeColumn) === 0) {
    if (
        !mysqli_query(
            $conn,
            "ALTER TABLE `transaksi` ADD COLUMN `metode_pembayaran` VARCHAR(20) NOT NULL DEFAULT 'cash' AFTER `total_harga`",
        )
    ) {
        die(
            "Gagal memperbarui struktur tabel transaksi: " . mysqli_error($conn)
        );
    }
}

// Bukti pembayaran disimpan sebagai nama file pada tabel transaksi.
$buktiColumn = mysqli_query(
    $conn,
    "SHOW COLUMNS FROM `transaksi` LIKE 'bukti_pembayaran'",
);
if ($buktiColumn && mysqli_num_rows($buktiColumn) === 0) {
    if (
        !mysqli_query(
            $conn,
            "ALTER TABLE `transaksi` ADD COLUMN `bukti_pembayaran` VARCHAR(255) NULL AFTER `metode_pembayaran`",
        )
    ) {
        die(
            "Gagal memperbarui struktur tabel transaksi: " . mysqli_error($conn)
        );
    }
}

// Akun barber lama dibuatkan profil otomatis
mysqli_query(
    $conn,
    "INSERT INTO `barber` (`user_id`, `nama`, `spesialisasi`, `status`)
    SELECT u.`id_user`, COALESCE(NULLIF(u.`nama`, ''), u.`username`), 'Umum', 'aktif'
    FROM `users` u
    LEFT JOIN `barber` b ON b.`user_id` = u.`id_user`
    WHERE u.`role` = 'barber' AND b.`id` IS NULL",
);

// Profil pengguna dapat dilengkapi dengan avatar opsional.
$avatarColumn = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'avatar'");
if ($avatarColumn && mysqli_num_rows($avatarColumn) === 0) {
    if (
        !mysqli_query(
            $conn,
            "ALTER TABLE `users` ADD COLUMN `avatar` VARCHAR(255) NULL AFTER `nama`",
        )
    ) {
        die(
            "Gagal menambahkan kolom avatar pada tabel users: " .
                mysqli_error($conn)
        );
    }
}

// Kolom untuk melacak aktivitas terakhir
$aktivitasColumn = mysqli_query(
    $conn,
    "SHOW COLUMNS FROM `users` LIKE 'terakhir_aktivitas'",
);
if ($aktivitasColumn && mysqli_num_rows($aktivitasColumn) === 0) {
    if (
        !mysqli_query(
            $conn,
            "ALTER TABLE `users` ADD COLUMN `terakhir_aktivitas` DATETIME NULL AFTER `avatar`",
        )
    ) {
        die(
            "Gagal menambahkan kolom terakhir_aktivitas: " . mysqli_error($conn)
        );
    }
}

// Kolom untuk soft-delete layanan
$isDeletedLayanan = @mysqli_query($conn, "SHOW COLUMNS FROM `layanan` LIKE 'is_deleted'");
if (!$isDeletedLayanan || mysqli_num_rows($isDeletedLayanan) == 0) {
    @mysqli_query($conn, "ALTER TABLE `layanan` ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0");
}

// Kolom untuk soft-delete users/kapster
$isDeletedUsers = @mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'is_deleted'");
if (!$isDeletedUsers || mysqli_num_rows($isDeletedUsers) == 0) {
    @mysqli_query($conn, "ALTER TABLE `users` ADD COLUMN `is_deleted` TINYINT(1) NOT NULL DEFAULT 0");
}
?>
