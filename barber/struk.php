<?php
include "_bootstrap.php";

if (!isset($_GET["id"])) {
    header("Location: dashboard.php");
    exit();
}

$transaksi_id = (int) $_GET["id"];

// Query disesuaikan agar barber melihat struk dari antreannya sendiri
$query = "SELECT t.*, a.tanggal, a.no_antrian, 
                 COALESCE(NULLIF(u.nama, ''), u.username) AS nama_pelanggan,
                 l.nama_layanan, l.harga, b.nama AS nama_barber
          FROM transaksi t
          JOIN antrian a ON t.antrian_id = a.id
          JOIN users u ON a.pelanggan_id = u.id_user
          JOIN layanan l ON a.layanan_id = l.id
          LEFT JOIN barber b ON a.barber_id = b.id
          WHERE t.id = $transaksi_id AND a.barber_id = '$barberId'";

$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: dashboard.php");
    exit();
}

$data = mysqli_fetch_assoc($result);
$barberName = $data["nama_barber"] ?? "Artisan";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $transaksi_id ?> | Barber.co</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #121414; font-family: 'Space Mono', monospace; color: #000; }
        .receipt-paper {
            background-color: #fff;
            width: 100%;
            max-width: 320px;
            margin: 40px auto;
            padding: 24px 20px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.35);
            position: relative;
        }
        /* Top jagged edge of receipt */
        .receipt-paper::before {
            content: "";
            position: absolute;
            top: -6px;
            left: 0;
            right: 0;
            height: 6px;
            background: radial-gradient(circle, transparent, transparent 50%, #fff 50%, #fff 100%) 0 0 / 12px 6px repeat-x;
        }
        /* Bottom jagged edge */
        .receipt-paper::after {
            content: "";
            position: absolute;
            bottom: -6px;
            left: 0;
            right: 0;
            height: 6px;
            background: radial-gradient(circle, transparent, transparent 50%, #fff 50%, #fff 100%) 0 0 / 12px 6px repeat-x;
            transform: rotate(180deg);
        }
        .dotted-line { border-bottom: 2px dotted #000; margin: 16px 0; }
        
        @media print {
            body { background-color: #fff; margin: 0; padding: 0; }
            .receipt-paper { box-shadow: none; margin: 0; max-width: 100%; border: none; }
            .receipt-paper::before, .receipt-paper::after { display: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="receipt-paper">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold tracking-tighter">BARBER.CO</h1>
            <p class="text-xs mt-1">Premium Artisan Platform</p>
            <p class="text-[10px] mt-1">Jl. Artisan Raya No. 99</p>
        </div>
        
        <div class="dotted-line"></div>

        <!-- Meta -->
        <div class="text-xs space-y-1">
            <div class="flex justify-between">
                <span>TRX:</span>
                <span>#<?= str_pad(
                    $transaksi_id,
                    6,
                    "0",
                    STR_PAD_LEFT,
                ) ?></span>
            </div>
            <div class="flex justify-between">
                <span>DATE:</span>
                <span><?= date(
                    "d/m/Y",
                    strtotime($data["waktu_bayar"]),
                ) ?></span>
            </div>
            <div class="flex justify-between">
                <span>TIME:</span>
                <span><?= date(
                    "H:i:s",
                    strtotime($data["waktu_bayar"]),
                ) ?></span>
            </div>
            <div class="flex justify-between">
                <span>ARTISAN:</span>
                <span class="uppercase"><?= htmlspecialchars(
                    $barberName,
                ) ?></span>
            </div>
        </div>

        <div class="dotted-line"></div>

        <!-- Customer & Service -->
        <div class="text-xs mb-2 font-bold uppercase">PELANGGAN: <?= htmlspecialchars(
            $data["nama_pelanggan"],
        ) ?> (Q: <?= $data["no_antrian"] ?>)</div>
        <div class="text-xs mb-4">
            <div class="flex justify-between font-bold">
                <span class="uppercase flex-1"><?= htmlspecialchars(
                    $data["nama_layanan"],
                ) ?></span>
                <span class="whitespace-nowrap ml-2">Rp <?= number_format(
                    $data["harga"] ?? $data["total_harga"],
                    0,
                    ",",
                    ".",
                ) ?></span>
            </div>
            <div class="text-[10px] mt-1">Status: LUNAS &bull; <span class="uppercase">Method: <?= htmlspecialchars(
                $data["metode_pembayaran"] ?? "CASH",
            ) ?></span></div>
        </div>

        <div class="dotted-line"></div>

        <div class="flex justify-between items-end mt-4 mb-6">
            <span class="text-xs font-bold">TOTAL:</span>
            <span class="text-lg font-bold">Rp <?= number_format(
                $data["total_harga"],
                0,
                ",",
                ".",
            ) ?></span>
        </div>

        <div class="text-center text-[10px] space-y-1">
            <p>TERIMA KASIH ATAS KUNJUNGAN ANDA</p>
            <p>barber.co - Stay Sharp.</p>
        </div>
    </div>

    <div class="text-center mt-8 no-print mb-8">
        <button onclick="window.print()" class="border border-[#f2ca50] bg-[#f2ca50] text-black px-6 py-2 text-xs font-bold uppercase tracking-widest mr-2 hover:bg-transparent hover:text-[#f2ca50]">
            Print Struk
        </button>
        <a href="dashboard.php" class="border border-[#4d4635] px-6 py-2 text-xs font-bold uppercase tracking-widest text-[#d0c5af] hover:border-[#f2ca50] hover:text-[#f2ca50] transition-colors">
            Kembali
        </a>
    </div>

    <!-- Auto trigger print logic on load -->
    <script>
        window.addEventListener('load', function() {
            <?php if (
                isset($_GET["auto_print"]) &&
                $_GET["auto_print"] == "1"
            ): ?>
            window.print();
            <?php endif; ?>
        });
    </script>
</body>
</html>
