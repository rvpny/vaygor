-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 17, 2026 at 11:59 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_futsal`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int UNSIGNED NOT NULL,
  `booking_code` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `field_id` int UNSIGNED NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration` int NOT NULL DEFAULT '1',
  `price_per_hour` decimal(12,2) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_code`, `user_id`, `field_id`, `booking_date`, `start_time`, `end_time`, `duration`, `price_per_hour`, `total_price`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'BOOK-20260917-001', 2, 1, '2026-09-20', '16:00:00', '18:00:00', 2, 15000.00, 30000.00, 'confirmed', 'Latihan futsal', '2026-09-17 13:59:34', '2026-09-17 13:59:34');

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `field_type` enum('indoor','outdoor') COLLATE utf8mb4_unicode_ci DEFAULT 'indoor',
  `surface` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` int DEFAULT '10',
  `price_per_hour` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('available','maintenance','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `name`, `location`, `description`, `field_type`, `surface`, `capacity`, `price_per_hour`, `status`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Lapangan Pancuranmas', 'Kota Magelang, Jawa Tengah', 'Lapangan futsal yang dapat digunakan untuk latihan, pertandingan, dan kegiatan olahraga.', 'indoor', 'Vinyl', 10, 15000.00, 'available', 'pancuranmas.jpg', '2026-09-17 13:59:34', '2026-09-17 13:59:34'),
(2, 'Lapangan Merdeka Outdoor', 'Kota Magelang, Jawa Tengah', 'Lapangan outdoor rumput sintetis, cocok untuk sparing sore.', 'outdoor', 'Rumput Sintetis', 12, 20000.00, 'available', NULL, '2026-09-18 10:00:00', '2026-09-18 10:00:00'),
(3, 'Lapangan Tidar Futsal', 'Magelang Utara, Jawa Tengah', 'Lapangan indoor vinyl dengan lampu terang.', 'indoor', 'Vinyl', 10, 25000.00, 'available', NULL, '2026-09-18 10:00:00', '2026-09-18 10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `field_schedules`
--

CREATE TABLE `field_schedules` (
  `id` int UNSIGNED NOT NULL,
  `field_id` int UNSIGNED NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `open_time` time NOT NULL,
  `close_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `field_schedules`
--

INSERT INTO `field_schedules` (`id`, `field_id`, `day_of_week`, `open_time`, `close_time`) VALUES
(1, 1, 'Monday', '08:00:00', '22:00:00'),
(2, 1, 'Tuesday', '08:00:00', '22:00:00'),
(3, 1, 'Wednesday', '08:00:00', '22:00:00'),
(4, 1, 'Thursday', '08:00:00', '22:00:00'),
(5, 1, 'Friday', '08:00:00', '22:00:00'),
(6, 1, 'Saturday', '08:00:00', '23:00:00'),
(7, 1, 'Sunday', '08:00:00', '23:00:00'),
(8, 2, 'Monday', '08:00:00', '22:00:00'),
(9, 2, 'Tuesday', '08:00:00', '22:00:00'),
(10, 2, 'Wednesday', '08:00:00', '22:00:00'),
(11, 2, 'Thursday', '08:00:00', '22:00:00'),
(12, 2, 'Friday', '08:00:00', '22:00:00'),
(13, 2, 'Saturday', '08:00:00', '23:00:00'),
(14, 2, 'Sunday', '08:00:00', '23:00:00'),
(15, 3, 'Monday', '08:00:00', '22:00:00'),
(16, 3, 'Tuesday', '08:00:00', '22:00:00'),
(17, 3, 'Wednesday', '08:00:00', '22:00:00'),
(18, 3, 'Thursday', '08:00:00', '22:00:00'),
(19, 3, 'Friday', '08:00:00', '22:00:00'),
(20, 3, 'Saturday', '08:00:00', '23:00:00'),
(21, 3, 'Sunday', '08:00:00', '23:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int UNSIGNED NOT NULL,
  `booking_id` int UNSIGNED NOT NULL,
  `payment_method` enum('cash','transfer','qris') COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_status` enum('unpaid','pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_date` datetime DEFAULT NULL,
  `proof_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `payment_method`, `payment_status`, `amount`, `payment_date`, `proof_image`, `created_at`, `updated_at`) VALUES
(1, 1, 'qris', 'paid', 30000.00, '2026-09-17 19:30:00', NULL, '2026-09-17 13:59:34', '2026-09-17 13:59:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@futsalmagelang.com', '081234567890', '$2y$12$6GuOivcIDdah4BDeb9J7oOW1nLb6xfER9JXUR8rILTeKEYz2V.OaG', 'admin', 'active', '2026-09-17 13:59:34', '2026-09-17 13:59:34'),
(2, 'Rava Prayoga', 'rava@example.com', '081234567891', '$2y$12$Zsod291NZRvtSIVPzhCzc.dkmrmsPc/F/q1eP863BCyrAUM6WYaQK', 'user', 'active', '2026-09-17 13:59:34', '2026-09-17 13:59:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `field_schedules`
--
ALTER TABLE `field_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `field_schedules`
--
ALTER TABLE `field_schedules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `field_schedules`
--
ALTER TABLE `field_schedules`
  ADD CONSTRAINT `field_schedules_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
