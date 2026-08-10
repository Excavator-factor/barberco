-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 23 Jul 2026 pada 03.34
-- Versi server: 8.4.3
-- Versi PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Basis data: `barber_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `antrian`
--

CREATE TABLE `antrian` (
  `id` int NOT NULL,
  `pelanggan_id` int NOT NULL,
  `barber_id` int DEFAULT NULL,
  `layanan_id` int NOT NULL,
  `no_antrian` int NOT NULL,
  `status_antrian` varchar(50) NOT NULL DEFAULT 'menunggu',
  `waktu_dibuat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `antrian`
--

INSERT INTO `antrian` (`id`, `pelanggan_id`, `barber_id`, `layanan_id`, `no_antrian`, `status_antrian`, `waktu_dibuat`, `tanggal`) VALUES
(1, 3, NULL, 2, 1, 'selesai', '2026-07-22 14:10:01', '2026-07-22'),
(2, 3, NULL, 2, 2, 'selesai', '2026-07-22 14:31:23', '2026-07-22'),
(3, 3, NULL, 3, 3, 'selesai', '2026-07-22 14:44:02', '2026-07-22'),
(4, 3, NULL, 2, 4, 'selesai', '2026-07-22 15:36:51', '2026-07-22'),
(5, 3, NULL, 1, 5, 'selesai', '2026-07-22 15:36:58', '2026-07-22'),
(6, 3, NULL, 3, 6, 'selesai', '2026-07-22 15:37:01', '2026-07-22'),
(7, 3, NULL, 4, 7, 'selesai', '2026-07-22 15:37:03', '2026-07-22'),
(8, 3, NULL, 4, 8, 'proses', '2026-07-22 16:09:09', '2026-07-22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `barber`
--

CREATE TABLE `barber` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `nama` varchar(30) NOT NULL,
  `spesialisasi` varchar(255) DEFAULT NULL,
  `status` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `id` int NOT NULL,
  `nama_layanan` varchar(255) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `harga` int NOT NULL,
  `durasi` int NOT NULL DEFAULT '30'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `layanan`
--

INSERT INTO `layanan` (`id`, `nama_layanan`, `deskripsi`, `harga`, `durasi`) VALUES
(1, 'Classic Cut', 'Potongan presisi dan modern bergaya klasik. Termasuk pencucian rambut, premium styling, dan pijat relaksasi.', 150000, 45),
(2, 'The Artisan Shave', 'Pengalaman cukur mewah tradisional dengan pijat handuk hangat, krim khusus, dan aftershave eksklusif.', 120000, 30),
(3, 'Beard Sculpt', 'Perawatan detail janggut/kumis menggunakan trimmer presisi, pisau cukur lurus, dan berpadu beard oil beraroma maskulin.', 80000, 20),
(4, 'Hair Treatment & Wash', 'Relaksasi optimal dengan pencucian rambut mendalam, eksfoliasi kulit kepala, tonik penyegar, dan pengeringan natural.', 95000, 30);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int NOT NULL,
  `antrian_id` int NOT NULL,
  `total_harga` int NOT NULL,
  `metode_pembayaran` varchar(20) NOT NULL DEFAULT 'cash',
  `status_pembayaran` varchar(11) NOT NULL,
  `waktu_bayar` datetime(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `antrian_id`, `total_harga`, `metode_pembayaran`, `status_pembayaran`, `waktu_bayar`) VALUES
(1, 3, 80000, 'cash', 'lunas', '2026-07-22 14:45:37.000000'),
(2, 4, 120000, 'cash', 'lunas', '2026-07-22 15:37:34.000000'),
(3, 5, 150000, 'cash', 'lunas', '2026-07-22 15:38:27.000000'),
(4, 6, 80000, 'cash', 'lunas', '2026-07-22 16:09:50.000000'),
(5, 7, 95000, 'debit', 'lunas', '2026-07-22 16:16:14.000000');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(11) NOT NULL,
  `nama` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `nama`) VALUES
(1, 'admin', '123', 'admin', ''),
(2, 'barber', '123', 'barber', ''),
(3, 'pelanggan', '123', 'pelanggan', '');

--
-- Indeks untuk tabel yang dibuang
--

--
-- Indeks untuk tabel `antrian`
--
ALTER TABLE `antrian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_antrian_pelanggan` (`pelanggan_id`),
  ADD KEY `fk_antrian_barber` (`barber_id`),
  ADD KEY `fk_antrian_layanan` (`layanan_id`);

--
-- Indeks untuk tabel `barber`
--
ALTER TABLE `barber`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_barber_user` (`user_id`);

--
-- Indeks untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transaksi_antrian` (`antrian_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `antrian`
--
ALTER TABLE `antrian`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `barber`
--
ALTER TABLE `barber`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `layanan`
--
ALTER TABLE `layanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `antrian`
--
ALTER TABLE `antrian`
  ADD CONSTRAINT `fk_antrian_barber` FOREIGN KEY (`barber_id`) REFERENCES `barber` (`id`),
  ADD CONSTRAINT `fk_antrian_layanan` FOREIGN KEY (`layanan_id`) REFERENCES `layanan` (`id`),
  ADD CONSTRAINT `fk_antrian_pelanggan` FOREIGN KEY (`pelanggan_id`) REFERENCES `users` (`id_user`);

--
-- Ketidakleluasaan untuk tabel `barber`
--
ALTER TABLE `barber`
  ADD CONSTRAINT `fk_barber_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_antrian` FOREIGN KEY (`antrian_id`) REFERENCES `antrian` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
