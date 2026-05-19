-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 10:36 AM
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
-- Database: `db_service_report`
--

-- --------------------------------------------------------

--
-- Table structure for table `service_reports`
--

CREATE TABLE `service_reports` (
  `id` int(11) NOT NULL,
  `report_number` varchar(50) NOT NULL,
  `report_date` date NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `facility_type` varchar(100) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `model` varchar(100) NOT NULL,
  `sn_code` varchar(100) DEFAULT '-',
  `last_calibration` varchar(50) DEFAULT '-',
  `distributor` varchar(100) DEFAULT '-',
  `year` varchar(10) DEFAULT '-',
  `service_type` varchar(50) DEFAULT 'Perbaikan',
  `physical_test` tinyint(4) DEFAULT 0,
  `function_test` tinyint(4) DEFAULT 0,
  `accessories_check` tinyint(4) DEFAULT 0,
  `parameter_setting` tinyint(4) DEFAULT 0,
  `mechanical_check` tinyint(4) DEFAULT 0,
  `warming_check` tinyint(4) DEFAULT 0,
  `troubleshooting` tinyint(4) DEFAULT 0,
  `problem_solution` text NOT NULL,
  `description` text DEFAULT NULL,
  `result_status` enum('Berfungsi Baik','Tidak Berfungsi','Berfungsi Tidak Sempurna') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_reports`
--

INSERT INTO `service_reports` (`id`, `report_number`, `report_date`, `room_name`, `facility_type`, `brand`, `model`, `sn_code`, `last_calibration`, `distributor`, `year`, `service_type`, `physical_test`, `function_test`, `accessories_check`, `parameter_setting`, `mechanical_check`, `warming_check`, `troubleshooting`, `problem_solution`, `description`, `result_status`, `created_at`, `updated_at`) VALUES
(4, '115/SR/V/2026', '2026-05-18', 'Ranap Karamunting (Bayi)', 'Keyboard', 'Genius', '-', '-', '-', '-', '-', 'Perbaikan', 0, 0, 1, 0, 0, 0, 0, 'Keyboard tidak merespon saat di klik, sudah di lakukan pergantian baterai tapi tetap tidak\n bisa sehingga tidak berfungsi dengan baik.\n', 'Perlu dilakukan penggantian perangkat (keyboard) agar dapat berfungsi optimal.', 'Tidak Berfungsi', '2026-05-19 03:06:15', '2026-05-19 03:06:15'),
(5, '116/SR/V/2026', '2026-05-19', 'Poli Gigi', 'Printer', 'Canon', '3730', '-', '-', '-', '-', 'Perbaikan', 0, 0, 1, 0, 0, 0, 0, 'Catridge pembuangan penuh, dan catridge warna rusak (saat memprint yang muncul hanya satu warna)Catridge pembuangan penuh, dan catridge warna rusak (saat memprint yang muncul hanya satu warna)Catridge pembuangan penuh, dan catridge warna rusak (saat memprint yang muncul hanya satu warna)', 'Catridge pembuangan penuh, dan catridge warna rusak (saat memprint yang muncul hanya satu warna)Catridge pembuangan penuh, dan catridge warna rusak (saat memprint yang muncul hanya satu warna)', 'Berfungsi Baik', '2026-05-19 03:56:26', '2026-05-19 03:57:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `service_reports`
--
ALTER TABLE `service_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `report_number` (`report_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `service_reports`
--
ALTER TABLE `service_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
