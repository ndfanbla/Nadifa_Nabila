-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 12, 2025 at 11:48 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kerusakan`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang_di_gudang`
--

CREATE TABLE `barang_di_gudang` (
  `id` int(11) NOT NULL,
  `no_urut` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `merek` varchar(100) NOT NULL,
  `nomor_barang` varchar(100) DEFAULT NULL,
  `kelengkapan_barang` text DEFAULT NULL,
  `kondisi` enum('baik','rusak_ringan','tidak_bisa_diperbaiki') DEFAULT 'baik',
  `keterangan` text DEFAULT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_barang` enum('belum_kembali','sudah_kembali','di_ruangan','di_gudang') DEFAULT 'di_gudang'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barang_di_ruangan`
--

CREATE TABLE `barang_di_ruangan` (
  `id` int(11) NOT NULL,
  `no_urut` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `ruangan` varchar(100) NOT NULL,
  `merek` varchar(100) NOT NULL,
  `nomor_barang` varchar(100) DEFAULT NULL,
  `kelengkapan_barang` text DEFAULT NULL,
  `kondisi` enum('baik','rusak_ringan','tidak_bisa_diperbaiki') DEFAULT 'baik',
  `keterangan` text DEFAULT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_barang` enum('belum_kembali','sudah_kembali','di_ruangan','di_gudang') DEFAULT 'di_ruangan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang_di_ruangan`
--

INSERT INTO `barang_di_ruangan` (`id`, `no_urut`, `nama_barang`, `ruangan`, `merek`, `nomor_barang`, `kelengkapan_barang`, `kondisi`, `keterangan`, `bukti_foto`, `created_at`, `status_barang`) VALUES
(1, 7, 'Komputer', 'Depo Obat', 'Acer Aspire C27', 'LP001', NULL, 'tidak_bisa_diperbaiki', 'form-data', '69181394c47bc_1763185556.png', '2025-11-24 08:59:53', 'di_ruangan'),
(2, 11, 'Komputer', 'PPI Lt.3', 'Axioo K5', '', NULL, 'rusak_ringan', 'Sudah kembali (Diruang PDE Pak Enggar)', NULL, '2025-11-24 09:01:28', 'di_ruangan'),
(3, 10, 'INI ADALAH CONTOH', 'INI ADALAH CONTOH', 'INI ADALAH CONTOH', 'INI ADALAH CONTOH', NULL, 'baik', 'A', '692422b878433_1763975864.jpg', '2025-11-24 09:17:44', 'di_ruangan'),
(4, 8, 'Komputer', 'Depo Obat', 'Acer Aspire C27', 'LP001', NULL, 'baik', '', NULL, '2025-11-24 23:59:44', 'di_ruangan'),
(5, 9, 'Printer', '(Tidak ada keterangan)', 'Printer Canon G3010', 'INI ADALAH CONTOH', NULL, 'baik', '', NULL, '2025-11-25 00:00:44', 'di_ruangan');

-- --------------------------------------------------------

--
-- Table structure for table `belum_kembali_dari_service`
--

CREATE TABLE `belum_kembali_dari_service` (
  `id` int(11) NOT NULL,
  `no_urut` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `ruangan` varchar(100) NOT NULL,
  `merek` varchar(100) NOT NULL,
  `tempat_service` varchar(100) NOT NULL,
  `tanggal_diambil_dari_ruangan` date DEFAULT NULL,
  `tanggal_service` date NOT NULL,
  `kelengkapan_barang` text DEFAULT NULL,
  `masalah` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `nomor_barang` varchar(100) DEFAULT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL,
  `tanggal_kembali` date NOT NULL,
  `kondisi_sebelum_service` enum('baik','rusak_ringan','tidak_bisa_diperbaiki') DEFAULT 'baik',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_barang` enum('belum_kembali','sudah_kembali','di_ruangan','di_gudang') DEFAULT 'belum_kembali'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `belum_kembali_dari_service`
--

INSERT INTO `belum_kembali_dari_service` (`id`, `no_urut`, `nama_barang`, `ruangan`, `merek`, `tempat_service`, `tanggal_diambil_dari_ruangan`, `tanggal_service`, `kelengkapan_barang`, `masalah`, `keterangan`, `nomor_barang`, `bukti_foto`, `tanggal_kembali`, `kondisi_sebelum_service`, `created_at`, `status_barang`) VALUES
(1, 1, 'Komputer', 'Depo Obat', 'Acer Aspire C27', 'Pak Doddy', '2025-11-22', '2024-09-06', 'form-data', 'form-data', 'form-data', 'LP001', '69181394c47bc_1763185556.png', '2024-09-06', 'rusak_ringan', '2025-11-15 05:45:56', 'belum_kembali'),
(2, 2, 'aawa', 'rwreww', 'Acer Aspire C27', 'Pak Doddy', '2025-11-22', '2024-09-06', 'wer', 'werw', 'wer', 'LP001', NULL, '2024-09-06', 'baik', '2025-11-15 05:47:05', 'belum_kembali'),
(6, 3, 'aweaweaweaw', 'q32423', 'Acer Aspire C27', 'Pak Doddy', '2025-11-22', '2024-09-06', 'sdf', 'sdf', 'sdf', 'LP001', NULL, '2024-09-06', 'baik', '2025-11-15 06:34:02', 'belum_kembali');

-- --------------------------------------------------------

--
-- Table structure for table `kerusakan_komputer`
--

CREATE TABLE `kerusakan_komputer` (
  `id` int(11) NOT NULL,
  `no_urut` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `ruangan` varchar(100) NOT NULL,
  `milik` varchar(100) NOT NULL,
  `merek` varchar(100) NOT NULL,
  `tempat_service` varchar(100) NOT NULL,
  `tanggal_diantar_diambil` date NOT NULL,
  `nomor_barang` varchar(100) DEFAULT NULL,
  `kelengkapan_barang` text DEFAULT NULL,
  `masalah` text DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('belum_kembali','sudah_kembali') DEFAULT 'belum_kembali'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kerusakan_komputer`
--

INSERT INTO `kerusakan_komputer` (`id`, `no_urut`, `nama_barang`, `ruangan`, `milik`, `merek`, `tempat_service`, `tanggal_diantar_diambil`, `nomor_barang`, `kelengkapan_barang`, `masalah`, `keterangan`, `bukti_foto`, `created_at`, `status`) VALUES
(2, 0, 'PC Desktops', '', 'HRD', 'HP', 'Teknisi External', '2024-01-10', 'PC002', 'CPU + Monitor + Keyboard + Mouse', 'Blue screen dan restart terus', 'Sudah diperbaiki, ganti RAM', NULL, '2025-10-21 05:02:11', 'belum_kembali'),
(3, 0, 'Printer', '', 'Marketing', 'Canon', 'Service Center Canon', '2024-01-20', 'PR003ww', 'Printer + Kabel USB', 'Kertas sering macet', 'Menunggu spare part', '68f740491f083_1761034313.jpeg', '2025-10-21 05:02:11', 'belum_kembali'),
(9, 0, 'Laptop nadifa', '', 'Bagian Keuangan', 'Dell', 'Service Center IT', '2024-01-11', 'LP001', 'wad', 'adwaeda', 'wad', '68f73f19b9a90_1761034009.jpg', '2025-10-21 08:06:49', 'belum_kembali'),
(10, 0, 'Printer', '', 'Nadifa', 'Epson', 'Dokter Printer', '2025-10-14', '16238726482', 'Tidak ada kabel', 'Tidak bisa memprint', 'Sudah kembali dari service', '68fa07bb81374_1761216443.jpeg', '2025-10-23 10:47:23', 'sudah_kembali'),
(13, 0, 'Laptop DelladaSDFS', '', 'SDFSD', 'SD', 'SDF', '2025-10-08', 'DSF', 'SD', 'SD', 'SD', '68fa0b0e83984_1761217294.jpg', '2025-10-23 11:01:34', 'sudah_kembali'),
(18, 0, 'qa', '', 'qa', 'qa', 'qa', '2025-11-20', 'qa', 'qa', 'qa', 'qa', '6917ff7168614_1763180401.png', '2025-11-15 04:20:01', 'sudah_kembali'),
(19, 0, 'aaaaaaa', '', 'aaaaaaa', 'aaaaaaa', 'aaaaaaa', '2025-12-01', 'aaaaaaa', 'aaaaaaa', 'aaaaaaa', 'aaaaaaa', '69180e23d4663_1763184163.png', '2025-11-15 05:22:43', 'sudah_kembali');

-- --------------------------------------------------------

--
-- Table structure for table `sudah_kembali_dari_service`
--

CREATE TABLE `sudah_kembali_dari_service` (
  `id` int(11) NOT NULL,
  `no_urut` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `ruangan` varchar(100) NOT NULL,
  `merek` varchar(100) NOT NULL,
  `tempat_service` varchar(100) NOT NULL,
  `tanggal_diambil_dari_ruangan` date DEFAULT NULL,
  `tanggal_service` date NOT NULL,
  `kelengkapan_barang` text DEFAULT NULL,
  `masalah` text NOT NULL,
  `keterangan` text DEFAULT NULL,
  `nomor_barang` varchar(100) DEFAULT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL,
  `tanggal_kembali` date NOT NULL,
  `kondisi_setelah_service` enum('baik','rusak_ringan','tidak_bisa_diperbaiki','diganti_unit') DEFAULT 'baik',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_barang` enum('belum_kembali','sudah_kembali','di_ruangan','di_gudang') DEFAULT 'sudah_kembali'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sudah_kembali_dari_service`
--

INSERT INTO `sudah_kembali_dari_service` (`id`, `no_urut`, `nama_barang`, `ruangan`, `merek`, `tempat_service`, `tanggal_diambil_dari_ruangan`, `tanggal_service`, `kelengkapan_barang`, `masalah`, `keterangan`, `nomor_barang`, `bukti_foto`, `tanggal_kembali`, `kondisi_setelah_service`, `created_at`, `status_barang`) VALUES
(1, 1, 'Komputer', 'Depo Obat', 'Acer Aspire C27', 'Pak Doddy', NULL, '2024-09-06', 'Kabel Power (Tidak Ada)', 'Fleksibel Kedip 192.168.77.118', 'Sudah kembali (Diruang Instalasi Pemusaran Jenazah)', '', '6917fcf3093c9_1763179763.png', '2024-09-06', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(2, 2, 'Komputer', 'PPI Lt.3', 'Axioo K5', 'Pak Doddy', NULL, '2024-09-06', 'Kabel Power (Tidak Ada)', 'Layar Pecah', 'Sudah kembali (Diruang PDE Pak Enggar)', NULL, NULL, '2024-09-06', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(3, 3, 'Komputer', 'Poli Saraf (Dokter Tara)', 'Axioo K7', 'Penyedia', NULL, '2024-11-13', 'Kabel Power (Tidak Ada)', 'Black Screen (LCD)', 'Sudah kembali tgl 03 Maret 2025 (keruang ranap vip lt.4 gedung baru)', '', '69180de455772_1763184100.png', '2025-03-03', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(4, 4, 'Komputer', 'IGD', 'Axioo', 'Garansi (Penyedia)', NULL, '2025-05-23', 'Kabel Power (Tidak Ada)', 'Komputer Tidak Bisa Menyala', 'Sudah kembali tgl 01 Agustus 2025 (Sudah kembalikan ke IGD)', NULL, NULL, '2025-08-01', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(5, 5, 'Printer', '(Tidak ada keterangan)', 'Printer Canon G3010', 'Pak Doddy', NULL, '2024-09-06', 'Kabel USB (Tidak Ada)', 'Tidak bisa P08', 'Sudah kembali tgl 25 Maret 2025 Malam Hari (Masih di coba, diruangan)', NULL, NULL, '2025-03-25', 'rusak_ringan', '2025-10-23 07:55:08', 'sudah_kembali'),
(6, 6, 'Printer', 'Admin IBS', 'Printer Canon G3010', 'Pak Doddy', NULL, '2024-09-06', 'Kabel USB (Tidak Ada)', 'Kode P01', 'Sudah kembali tgl 25 Maret 2025 Malam Hari (Masih di coba, diruangan)', NULL, NULL, '2025-03-25', 'rusak_ringan', '2025-10-23 07:55:08', 'sudah_kembali'),
(7, 7, 'Printer', 'Kasir Rajal', 'Printer Canon G4770', 'Dokter Printer', NULL, '2025-03-01', 'Kabel Power (Tidak Ada)', 'Hasil cetak miring, warna kurang bagus (printer kemasukan paper klip)', 'Sudah kembali tgl 04 Maret 2025 (Sudah di kembalikan ke kasir rajal)', NULL, NULL, '2025-03-04', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(8, 8, 'Printer', 'Poli Jantung', 'Printer Epson L121', 'Dokter Printer', NULL, '2025-03-10', 'Kabel Power (Ada)', 'Roller kasat/aus', 'Sudah kembali tgl 14 Maret 2025 (Sudah di kembalikan kepoli jantung)', NULL, NULL, '2025-03-14', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(9, 9, 'Printer', 'Ranap Rambai Lt.4', 'Printer Canon G3730', 'Klaim Garansi (Agus Setyawan)', NULL, '2025-03-03', 'Kabel Power (Tidak Ada)', 'Printer tidak bisa menarik kertas', 'Sudah kembali tgl 16 Mei 2025', NULL, NULL, '2025-05-16', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(10, 10, 'Printer', 'Humas Lt.3', 'Printer Canon G3730', 'Klaim Garansi (Agus Setyawan)', NULL, '2025-03-03', 'Kabel Power (Tidak Ada)', 'Printer tidak bisa menarik kertas', 'Sudah kembali tgl 16 Mei 2025 (masih diruangan)', NULL, NULL, '2025-05-16', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(11, 11, 'Printer', 'Admin IGD', 'Printer Canon G3730', 'Dokter Printer', NULL, '2025-05-07', 'Kabel Power (Tidak Ada)', 'Printer tidak bisa menarik kertas', 'Sudah kembali tgl 21 Mei 2025 (Sudah kembalikan ke Admin IGD)', NULL, NULL, '2025-05-21', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(12, 12, 'Printer', 'Ranap Ramania Lt.5', 'Printer Canon G3730', 'Dokter Printer', NULL, '2025-05-07', 'Kabel Power (Tidak Ada)', 'Printer tidak bisa menarik kertas', 'Sudah kembali tgl 21 Mei 2025 (Sudah kembalikan ke Ranap Ramania)', NULL, NULL, '2025-05-21', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(13, 13, 'Printer', 'Depo Obat RWJ', 'Printer Canon G3010', 'Dokter Printer', NULL, '2025-05-21', 'Kabel Power (Tidak Ada)', 'Error P02/P03 (keterangan paper jammed, tapi bisa memprint 1 kertas)', 'Sudah kembali tgl 03 Juni 2025 di kasih tambahan botol di bagian belakang sebagai pembuangan tintahnya. (Sudah kembalikan ke Depo Obat RWJ)', NULL, NULL, '2025-06-03', 'rusak_ringan', '2025-10-23 07:55:08', 'sudah_kembali'),
(14, 14, 'Printer', 'Ranap Rambai Lt.4', 'Printer Epson L3210', 'Service Center Epson', NULL, '2025-06-13', 'Kabel Power (Tidak Ada)', 'Printer tidak bisa memprint', 'Sudah kembali tgl 18 Juni 2025 (Sudah kembalikan ke Ranap Rambai)', NULL, NULL, '2025-06-18', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(15, 15, 'Printer', 'Ranap Ramania lt.5', 'Printer Canon G4770', 'Dokter Printer', NULL, '2025-08-15', 'Kabel (Tidak Ada)', 'Error Kode 6000', 'Sudah dikembalikan ke ranap ramania tanggal 27 Agustus 2025', NULL, NULL, '2025-08-27', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(16, 16, 'Komputer', 'Poli Penyakit Dalam', 'Acer Veriton Z4', 'Penyedia', NULL, '2025-09-01', 'Kabel (Tidak Ada)', 'Layar glints', 'Sudah kembali tgl 27 September 2025 (Dalam kondisi tidak bisa di perbaiki, dan pada bagian atas layar ada lcd yg kena)', NULL, NULL, '2025-09-27', 'tidak_bisa_diperbaiki', '2025-10-23 07:55:08', 'sudah_kembali'),
(17, 17, 'Komputer', 'Poli Penyakit Dalam', 'Acer Veriton Z4', 'Penyedia', NULL, '2025-09-01', 'Kabel (Tidak Ada)', 'Tidak bisa menyala/mati total', 'Sudah kembali tanggal 27 Agustus 2025', NULL, NULL, '2025-08-27', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(18, 18, 'Printer', 'Ranap Rambutan lt.3', 'Printer Epson L3210', 'Dokter Printer', NULL, '2025-09-29', 'Kabel (Tidak Ada)', 'Bisa memprint tapi tidak bisa fotocopy, dan lampu indikator menyala keduanya.', 'Sudah dikembalikan ke ranap rambutan tanggal 16 Oktober 2025', NULL, NULL, '2025-10-16', 'baik', '2025-10-23 07:55:08', 'sudah_kembali'),
(19, 19, 'Printer', 'Ranap Hambawang nifas lt.2', 'Printer Canon G3010', 'Dokter Printer', NULL, '2025-09-29', 'Kabel (Tidak Ada)', 'Error kode P10, dan kapas tintah pembuangan penuh (pernah bocor)', 'Sudah kembali tgl 16 Oktober 2025 (Dalam kondisi tidak bisa di perbaiki, dan printer mati total)', NULL, NULL, '2025-10-16', 'tidak_bisa_diperbaiki', '2025-10-23 07:55:08', 'sudah_kembali'),
(20, 20, 'Komputer', 'Depo Obat', 'Acer Aspire C27', 'Pak Doddy', NULL, '2024-09-06', '3r', 'wer', 'wr', '', '6918184b068fe_1763186763.jpg', '2024-09-06', 'baik', '2025-11-15 06:06:03', 'sudah_kembali'),
(21, 21, 'aweaweaweaw', 'q32423', 'Acer Aspire C27', 'Pak Doddy', '2025-11-22', '2024-09-06', 'z', 'z', 'z', 'LP001', NULL, '2024-09-06', 'baik', '2025-11-15 06:26:47', 'sudah_kembali'),
(25, 22, 'aweaweaweaw', 'q32423', 'Acer Aspire C27', 'Pak Doddy', '2025-11-22', '2024-09-06', 'rsa', 'aerw', 'awer', 'LP001', '69181e00c4c12_1763188224.jpeg', '2024-09-06', 'baik', '2025-11-15 06:30:24', 'sudah_kembali'),
(27, 23, 'Printer', '(Tidak ada keterangan)', 'Printer Canon G3010', 'Pak Doddy', '2025-11-25', '2024-09-06', '', 'vjnaGH', '', 'INI ADALAH CONTOH', NULL, '2025-03-25', 'baik', '2025-11-25 00:00:16', 'sudah_kembali');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang_di_gudang`
--
ALTER TABLE `barang_di_gudang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `barang_di_ruangan`
--
ALTER TABLE `barang_di_ruangan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `belum_kembali_dari_service`
--
ALTER TABLE `belum_kembali_dari_service`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kerusakan_komputer`
--
ALTER TABLE `kerusakan_komputer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sudah_kembali_dari_service`
--
ALTER TABLE `sudah_kembali_dari_service`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang_di_gudang`
--
ALTER TABLE `barang_di_gudang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barang_di_ruangan`
--
ALTER TABLE `barang_di_ruangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `belum_kembali_dari_service`
--
ALTER TABLE `belum_kembali_dari_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `kerusakan_komputer`
--
ALTER TABLE `kerusakan_komputer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `sudah_kembali_dari_service`
--
ALTER TABLE `sudah_kembali_dari_service`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
