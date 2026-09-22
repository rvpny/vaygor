-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20251008.967007883e
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 20, 2026 at 09:43 AM
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
  `booking_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` int UNSIGNED NOT NULL,
  `id_field` int UNSIGNED NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rate_service` int NOT NULL,
  `rate_comfort` int NOT NULL,
  `rate_place` int NOT NULL,
  `review` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_code`, `id_user`, `id_field`, `booking_date`, `start_time`, `end_time`, `total_price`, `status`, `rate_service`, `rate_comfort`, `rate_place`, `review`) VALUES
(1, 'BOOK-20260917-001', 2, 1, '2026-09-20', '16:00:00', '18:00:00', 30000.00, 'confirmed', 5, 4, 3, 'Makasih pakcik hamstring jadi doms. Tapi gatau karna main bola atau bola bola');

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `capacity` int DEFAULT '10',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `name`, `location`, `description`, `capacity`, `price`, `image`) VALUES
(1, 'Lapangan Pancuranmas', 'Kota Magelang, Jawa Tengah', 'Lapangan futsal yang dapat digunakan untuk latihan, pertandingan, dan kegiatan olahraga.', 10, 15000.00, 'pancuranmas.jpg'),
(2, 'Lapangan HVMan', 'Mertoyudan, Jawa Tengah', 'lapangan terbaik di magelang jawa tengah. Punya Pacik jelek mnding disini', 20, 150.00, 'hvman.webp');

-- --------------------------------------------------------

--
-- Table structure for table `fields_kat`
--

CREATE TABLE `fields_kat` (
  `id` int NOT NULL,
  `id_field` int NOT NULL,
  `id_kat` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fields_kat`
--

INSERT INTO `fields_kat` (`id`, `id_field`, `id_kat`) VALUES
(1, 1, 2),
(2, 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `field_schedules`
--

CREATE TABLE `field_schedules` (
  `id` int UNSIGNED NOT NULL,
  `field_id` int UNSIGNED NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
(7, 1, 'Sunday', '08:00:00', '23:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kat` int NOT NULL,
  `name_kat` varchar(50) NOT NULL,
  `logo_kat` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kat`, `name_kat`, `logo_kat`) VALUES
(1, 'Soccer', 'soccer.png'),
(2, 'Mini Soccer', 'mini.webp'),
(3, 'Indoor', 'indoor.jpg'),
(4, 'Outdoor', 'outdoor.jpg'),
(5, 'Badminton', 'badminton.jpg'),
(6, 'Basket', 'basket.jpeg'),
(7, 'Tennis', 'tennis.png'),
(8, 'Padel', 'padel.png'),
(9, 'Billiard', 'billiard.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int UNSIGNED NOT NULL,
  `booking_id` int UNSIGNED NOT NULL,
  `payment_status` enum('unpaid','pending','paid','failed','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `payment_status`, `amount`, `payment_date`, `created_at`) VALUES
(1, 1, 'paid', 30000.00, '2026-09-17 19:30:00', '2026-09-17 13:59:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@futsalmagelang.com', '081234567890', '$2y$10$REPLACE_DENGAN_PASSWORD_HASH', 'admin', 'active', '2026-09-17 13:59:34', '2026-09-17 13:59:34'),
(2, 'Rava Prayoga', 'rava@example.com', '081234567891', '$2y$10$REPLACE_DENGAN_PASSWORD_HASH', 'user', 'active', '2026-09-17 13:59:34', '2026-09-17 13:59:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_code` (`booking_code`),
  ADD KEY `user_id` (`id_user`),
  ADD KEY `field_id` (`id_field`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fields_kat`
--
ALTER TABLE `fields_kat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `field_schedules`
--
ALTER TABLE `field_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kat`);

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
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fields_kat`
--
ALTER TABLE `fields_kat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `field_schedules`
--
ALTER TABLE `field_schedules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`id_field`) REFERENCES `fields` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

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
