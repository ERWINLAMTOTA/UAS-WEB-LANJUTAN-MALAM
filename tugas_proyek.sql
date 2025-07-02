-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 24 Jun 2025 pada 16.40
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tugas_proyek`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `komentar_tugas`
--

CREATE TABLE `komentar_tugas` (
  `id` int(11) NOT NULL,
  `id_tugas` int(11) DEFAULT NULL,
  `id_pengguna` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `tanggal_komentar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengguna`
--

CREATE TABLE `pengguna` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `kata_sandi` varchar(255) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengguna`
--

INSERT INTO `pengguna` (`id`, `nama`, `email`, `kata_sandi`, `gambar`, `tanggal_daftar`) VALUES
(4, 'Admin', 'admin@gmail.com', '$2y$10$dF3nJ5o8pUzxTFNaJd4PF.rJL0NKMPelrol2yjGvDcouIiItYjB26', '6858dd757acea_login-bg.jpg', '2025-06-22 17:00:00'),
(7, 'Erwin', 'erwin@gmail.com', '$2y$10$bv3FBPRAe0VV/9BiIMImFe9VitcZA9pwkouTm9bT7iPlsIS3uM796', '6858e051d1630_logo.jpg', '2025-06-22 23:51:40'),
(8, 'Nabil', 'nabil@gmail.com', '$2y$10$OyWl1QogvFGilGouNX6ykOIVPaYQbcZph5SFFAvPWqjuAzWmoJa2q', '68591629291ac_pokemon-ball-house-anime-digital-art-4k-wallpaper-uhdpaper.com-68@1@o.jpg', '2025-06-23 03:54:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `proyek`
--

CREATE TABLE `proyek` (
  `id` int(11) NOT NULL,
  `nama_proyek` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_tenggat` date DEFAULT NULL,
  `id_pengguna` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `proyek`
--

INSERT INTO `proyek` (`id`, `nama_proyek`, `deskripsi`, `tanggal_mulai`, `tanggal_tenggat`, `id_pengguna`) VALUES
(3, 'Proyek1', 'tes aja', '2025-06-24', '2025-06-17', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_status_tugas`
--

CREATE TABLE `riwayat_status_tugas` (
  `id` int(11) NOT NULL,
  `id_tugas` int(11) DEFAULT NULL,
  `status_lama` enum('Pending','In Progress','Completed') DEFAULT NULL,
  `status_baru` enum('Pending','In Progress','Completed') DEFAULT NULL,
  `tanggal_perubahan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat_status_tugas`
--

INSERT INTO `riwayat_status_tugas` (`id`, `id_tugas`, `status_lama`, `status_baru`, `tanggal_perubahan`) VALUES
(1, 7, 'Completed', 'In Progress', '2025-06-23 05:42:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `todo`
--

CREATE TABLE `todo` (
  `id` int(11) NOT NULL,
  `todo` varchar(255) NOT NULL,
  `id_pengguna` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `todo`
--

INSERT INTO `todo` (`id`, `todo`, `id_pengguna`) VALUES
(2, 'Tes Tugas', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tugas`
--

CREATE TABLE `tugas` (
  `id` int(11) NOT NULL,
  `nama_tugas` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_tenggat` date DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `gambar` varchar(255) NOT NULL,
  `id_proyek` int(11) DEFAULT NULL,
  `id_pengguna` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tugas`
--

INSERT INTO `tugas` (`id`, `nama_tugas`, `deskripsi`, `tanggal_mulai`, `tanggal_tenggat`, `status`, `gambar`, `id_proyek`, `id_pengguna`) VALUES
(7, 'Tugas 1', 'tes', '2025-06-23', '2025-06-24', 'In Progress', '6858cf985ea5c_login-bg.jpg', 3, 4),
(8, 'Tugas 2', 'agst', '2025-06-24', '2025-06-25', 'In Progress', '6858f9794c195_pokemon-ball-house-anime-digital-art-4k-wallpaper-uhdpaper.com-68@1@o.jpg', 3, 4);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `komentar_tugas`
--
ALTER TABLE `komentar_tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tugas` (`id_tugas`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `proyek`
--
ALTER TABLE `proyek`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `riwayat_status_tugas`
--
ALTER TABLE `riwayat_status_tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tugas` (`id_tugas`);

--
-- Indeks untuk tabel `todo`
--
ALTER TABLE `todo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indeks untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_proyek` (`id_proyek`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `komentar_tugas`
--
ALTER TABLE `komentar_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `proyek`
--
ALTER TABLE `proyek`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `riwayat_status_tugas`
--
ALTER TABLE `riwayat_status_tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `todo`
--
ALTER TABLE `todo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `komentar_tugas`
--
ALTER TABLE `komentar_tugas`
  ADD CONSTRAINT `komentar_tugas_ibfk_1` FOREIGN KEY (`id_tugas`) REFERENCES `tugas` (`id`),
  ADD CONSTRAINT `komentar_tugas_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`);

--
-- Ketidakleluasaan untuk tabel `proyek`
--
ALTER TABLE `proyek`
  ADD CONSTRAINT `proyek_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_status_tugas`
--
ALTER TABLE `riwayat_status_tugas`
  ADD CONSTRAINT `riwayat_status_tugas_ibfk_1` FOREIGN KEY (`id_tugas`) REFERENCES `tugas` (`id`);

--
-- Ketidakleluasaan untuk tabel `todo`
--
ALTER TABLE `todo`
  ADD CONSTRAINT `todo_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`id_proyek`) REFERENCES `proyek` (`id`),
  ADD CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
