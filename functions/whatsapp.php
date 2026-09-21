<?php
/**
 * Helper WhatsApp Gateway (Fonnte) & Direct WA
 * Barber.co System
 */

if (!isset($conn)) {
    $dbPath = __DIR__ . "/../config/database.php";
    if (file_exists($dbPath)) {
        require_once $dbPath;
    }
}

/**
 * Mengambil konfigurasi WhatsApp dari database
 */
function get_pengaturan_wa()
{
    global $conn;
    $default = [
        'token_fonnte' => '',
        'nomor_admin' => '',
        'status_wa' => 1,
        'notif_booking' => 1,
        'notif_panggilan' => 1,
        'notif_selesai' => 1
    ];

    if (!$conn) {
        return $default;
    }

    $res = @mysqli_query($conn, "SELECT * FROM `pengaturan_wa` WHERE id = 1 LIMIT 1");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        return array_merge($default, $row);
    }

    return $default;
}

/**
 * Format nomor HP Indonesia menjadi standar internasional WhatsApp (628xxx)
 */
function format_nomor_wa($nohp)
{
    if (empty($nohp)) {
        return '';
    }

    // Ambil hanya digit angka
    $clean = preg_replace('/[^0-9]/', '', (string)$nohp);

    if (empty($clean)) {
        return '';
    }

    // Jika diawali 0, ganti dengan 62
    if (strpos($clean, '0') === 0) {
        $clean = '62' . substr($clean, 1);
    }
    // Jika diawali 8 (tanpa 0 atau 62), tambahkan 62 di depan
    elseif (strpos($clean, '8') === 0) {
        $clean = '62' . $clean;
    }

    // Validasi panjang nomor (minimal 10 digit untuk 628...)
    if (strlen($clean) < 10) {
        return '';
    }

    return $clean;
}

/**
 * Menghasilkan link Direct WhatsApp (wa.me)
 */
function buat_link_wa($nohp, $pesan = '')
{
    $target = format_nomor_wa($nohp);
    if (empty($target)) {
        return '#';
    }
    return 'https://wa.me/' . $target . (!empty($pesan) ? '?text=' . rawurlencode($pesan) : '');
}

/**
 * Mengirim pesan WhatsApp via Fonnte Gateway API
 */
function kirim_wa_fonnte($nohp, $pesan, $custom_token = null)
{
    $target = format_nomor_wa($nohp);
    if (empty($target)) {
        return [
            'success' => false,
            'message' => 'Nomor WhatsApp tidak valid.'
        ];
    }

    $config = get_pengaturan_wa();
    $token = !empty($custom_token) ? trim($custom_token) : trim($config['token_fonnte'] ?? '');

    if (empty($token)) {
        return [
            'success' => false,
            'message' => 'Token API Fonnte belum dikonfigurasi di Pengaturan WhatsApp.'
        ];
    }

    // Periksa apakah cURL tersedia
    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'message' => 'Ekstensi PHP cURL tidak aktif di server.'
        ];
    }

    $curl = curl_init();
    $payload = [
        'target' => $target,
        'message' => $pesan,
        'countryCode' => '62'
    ];

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Authorization: ' . $token
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($curl);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($curlError) {
        return [
            'success' => false,
            'message' => 'Gagal koneksi ke server Fonnte: ' . $curlError
        ];
    }

    $resData = json_decode($response, true);
    if (isset($resData['status']) && $resData['status'] === true) {
        return [
            'success' => true,
            'message' => 'Pesan WhatsApp berhasil dikirim!',
            'data' => $resData
        ];
    }

    $detailErr = $resData['reason'] ?? ($resData['message'] ?? 'Respon error dari gateway.');
    return [
        'success' => false,
        'message' => 'Fonnte: ' . $detailErr,
        'raw' => $response
    ];
}

/**
 * Format dan kirim notifikasi berdasarkan skenario antrean
 * $tipe: 'booking_pelanggan' | 'booking_admin' | 'panggilan_giliran' | 'selesai_antrean'
 */
function kirim_notifikasi_wa($tipe, $data)
{
    $config = get_pengaturan_wa();

    // Jika fitur WA utama dinonaktifkan
    if ((int)($config['status_wa'] ?? 1) === 0) {
        return ['success' => false, 'message' => 'Fitur WhatsApp Gateway sedang dinonaktifkan.'];
    }

    $nama_pelanggan = $data['nama_pelanggan'] ?? 'Pelanggan';
    $no_hp_pelanggan = $data['no_hp_pelanggan'] ?? '';
    $no_antrian = $data['no_antrian'] ?? '-';
    $nama_layanan = $data['nama_layanan'] ?? 'Layanan Grooming';
    $harga = number_format((float)($data['harga'] ?? 0), 0, ',', '.');
    $nama_barber = $data['nama_barber'] ?? 'Kapster Bertugas';
    $tanggal = $data['tanggal'] ?? date('d-m-Y');
    $metode = $data['metode_pembayaran'] ?? 'Tunai';
    $url_struk = $data['url_struk'] ?? '';

    $pesan = '';
    $target = '';

    switch ($tipe) {
        case 'booking_pelanggan':
            if ((int)($config['notif_booking'] ?? 1) === 0) return ['success' => false, 'message' => 'Notif booking dinonaktifkan.'];
            $target = $no_hp_pelanggan;
            $pesan = "💈 *BARBER.CO - TIKET ANTREAN* 💈\n"
                   . "------------------------------------\n"
                   . "Halo, *{$nama_pelanggan}*!\n"
                   . "Antrean Anda berhasil terdaftar di sistem kami.\n\n"
                   . "📌 *No. Antrean:* #{$no_antrian}\n"
                   . "✂️ *Layanan:* {$nama_layanan}\n"
                   . "💰 *Biaya:* Rp {$harga}\n"
                   . "👤 *Kapster:* {$nama_barber}\n"
                   . "📅 *Tanggal:* {$tanggal}\n"
                   . "⏱️ *Status:* Menunggu Giliran\n\n"
                   . "Mohon untuk hadir di outlet kami sebelum nomor antrean Anda dipanggil. Kami akan mengirimkan notifikasi saat giliran Anda tiba.\n\n"
                   . "Terima kasih atas kepercayaan Anda di *Barber.co*! ✨";
            break;

        case 'booking_admin':
            if ((int)($config['notif_booking'] ?? 1) === 0) return ['success' => false, 'message' => 'Notif booking dinonaktifkan.'];
            $target = $config['nomor_admin'] ?? '';
            if (empty($target)) return ['success' => false, 'message' => 'Nomor WhatsApp admin belum diisi.'];
            $pesan = "🔔 *BARBER.CO - ANTREAN MASUK* 🔔\n"
                   . "------------------------------------\n"
                   . "Ada pelanggan baru yang mengambil antrean:\n\n"
                   . "📌 *No. Antrean:* #{$no_antrian}\n"
                   . "👤 *Pelanggan:* {$nama_pelanggan} (" . ($no_hp_pelanggan ?: 'Tanpa No. HP') . ")\n"
                   . "✂️ *Layanan:* {$nama_layanan} (Rp {$harga})\n"
                   . "💈 *Kapster:* {$nama_barber}\n"
                   . "📅 *Tanggal:* {$tanggal}\n\n"
                   . "Silakan pantau antrean langsung dari Dashboard Admin / Barber.";
            break;

        case 'panggilan_giliran':
            if ((int)($config['notif_panggilan'] ?? 1) === 0) return ['success' => false, 'message' => 'Notif panggilan dinonaktifkan.'];
            $target = $no_hp_pelanggan;
            $pesan = "💈 *GILIRAN PANGKAS ANDA TELAH TIBA!* 💈\n"
                   . "------------------------------------\n"
                   . "Halo, *{$nama_pelanggan}*!\n"
                   . "Nomor antrean Anda *#{$no_antrian}* sekarang sedang *DIPANGGIL*.\n\n"
                   . "✂️ *Layanan:* {$nama_layanan}\n"
                   . "👤 *Kapster:* {$nama_barber}\n\n"
                   . "Silakan segera bersiap dan menempati kursi kapster untuk memulai sesi perawatan rambut terbaik Anda.\n\n"
                   . "_Barber.co - Crafting Your Signature Look_ 💈";
            break;

        case 'selesai_antrean':
            if ((int)($config['notif_selesai'] ?? 1) === 0) return ['success' => false, 'message' => 'Notif selesai dinonaktifkan.'];
            $target = $no_hp_pelanggan;
            $pesan = "✨ *TERIMA KASIH TELAH BERKUNJUNG!* ✨\n"
                   . "------------------------------------\n"
                   . "Halo, *{$nama_pelanggan}*,\n"
                   . "Sesi pangkas dan grooming Anda telah selesai.\n\n"
                   . "📌 *No. Antrean:* #{$no_antrian}\n"
                   . "✂️ *Layanan:* {$nama_layanan}\n"
                   . "👤 *Kapster:* {$nama_barber}\n"
                   . "💰 *Total Biaya:* Rp {$harga}\n"
                   . "💳 *Metode Pembayaran:* {$metode}\n"
                   . "✅ *Status:* LUNAS\n\n";

            if (!empty($url_struk)) {
                $pesan .= "📄 *Struk Digital:* {$url_struk}\n\n";
            }

            $pesan .= "Semoga Anda puas dengan penampilan baru Anda! Jangan lupa berkunjung kembali di waktu yang akan datang. ✂️✨";
            break;

        default:
            return ['success' => false, 'message' => 'Tipe notifikasi tidak dikenal.'];
    }

    if (empty($target)) {
        return ['success' => false, 'message' => 'Nomor tujuan WhatsApp kosong.'];
    }

    return kirim_wa_fonnte($target, $pesan);
}
