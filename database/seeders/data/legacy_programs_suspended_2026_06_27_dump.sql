-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2026 at 06:16 AM
-- Server version: 11.8.8-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u800447295_career_olda`
--

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `course_code` varchar(255) DEFAULT NULL,
  `diploma_code` varchar(255) DEFAULT NULL,
  `certification_code` varchar(255) DEFAULT NULL,
  `fee` varchar(255) NOT NULL,
  `duration` int(11) NOT NULL,
  `discount_limit` varchar(255) DEFAULT NULL,
  `outline` varchar(255) DEFAULT NULL,
  `prerequisite` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `status` enum('On Going','Suspended','Inactive') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `user_id`, `program_type`, `title`, `course_code`, `diploma_code`, `certification_code`, `fee`, `duration`, `discount_limit`, `outline`, `prerequisite`, `remarks`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(8, 105, 'Short Course', 'Microsoft Office Management', 'OM', NULL, NULL, '30000', 8, '20', '1664431833.42391130_1483923238375647_7690495193845334016_o.jpg', 'NA', 'NA', 'Suspended', '2022-09-29 11:10:33', '2026-01-12 12:04:40', NULL),
(9, 1, 'Short Course', 'WordPress Developer', 'WD', NULL, NULL, '20000', 8, '20', '1664431890.42439865_1483923358375635_5929505641243607040_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 11:11:30', '2022-09-29 11:11:30', NULL),
(10, 1, 'Short Course', 'Graphics Designing', 'GD', NULL, NULL, '30000', 12, '20', '1664433778.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 11:42:58', '2024-06-25 21:00:19', NULL),
(11, 1, 'Short Course', 'UI/UX Designing', 'UI', NULL, NULL, '20000', 8, '20', '1664434146.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 11:49:06', '2022-09-29 11:49:06', NULL),
(12, 1, 'Short Course', 'Web Applications Developer (Full Stack)', 'AD', NULL, NULL, '30000', 16, '20', '1664434273.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 11:51:13', '2022-09-29 11:51:13', NULL),
(13, 1, 'Short Course', 'Website Designing', 'FE', NULL, NULL, '20000', 8, '20', '1664434375.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 11:52:55', '2022-09-29 11:52:55', NULL),
(14, 1, 'Short Course', 'Website Development - Python & Django', 'PD', NULL, NULL, '30000', 12, '20', '1664434623.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 11:57:03', '2022-09-29 11:57:03', NULL),
(15, 1, 'Short Course', 'Video Editing & Motion Graphics', 'VE', NULL, NULL, '40000', 12, '20', '1664434717.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 11:58:37', '2022-09-29 11:58:37', NULL),
(16, 1, 'Short Course', 'Digital Marketing & SEO', 'DM', NULL, NULL, '30000', 8, '20', '1664448660.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 15:51:00', '2022-09-29 15:51:00', NULL),
(17, 1, 'Short Course', 'Mobile App Development - Flutter & Dart', 'FD', NULL, NULL, '30000', 12, '20', '1664448719.42439865_1483923358375635_5929505641243607040_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 15:51:59', '2022-09-29 15:51:59', NULL),
(18, 1, 'Short Course', 'Android Applications Development - Kotlin', 'AM', NULL, NULL, '30000', 12, '20', '1664448775.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 15:52:55', '2022-09-29 15:52:55', NULL),
(19, 1, 'Short Course', 'iOS Applications Development - Swift', 'IS', NULL, NULL, '30000', 12, '20', '1664448826.42439865_1483923358375635_5929505641243607040_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 15:53:46', '2022-09-29 15:53:46', NULL),
(20, 1, 'Short Course', 'Mobile App Development - React Native', 'RN', NULL, NULL, '40000', 12, '20', '1664448923.42439865_1483923358375635_5929505641243607040_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 15:55:23', '2022-09-29 15:55:23', NULL),
(21, 1, 'Short Course', 'CCNA 200-301 (Implementing and Administering Cisco Solutions)', 'CA', NULL, NULL, '25000', 12, '20', '1664448968.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 15:56:08', '2022-09-29 15:56:08', NULL),
(22, 1, 'Short Course', 'Spoken English', 'SE', NULL, NULL, '20000', 12, '20', '1664449017.41709776_1473567056077932_154915407327657984_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 15:56:57', '2022-09-29 15:56:57', NULL),
(23, 1, 'Short Course', 'IELTS Test Preparation', 'IE', NULL, NULL, '20000', 8, '20', '1664449061.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 15:57:41', '2022-09-29 15:57:41', NULL),
(24, 1, 'Short Course', 'AutoCAD Civil 3D', 'AT', NULL, NULL, '25000', 12, '20', '1664449273.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 16:01:13', '2022-09-29 16:01:13', NULL),
(25, 1, 'Short Course', 'Autodesk Revit', 'RT', NULL, NULL, '40000', 12, '20', '1664449316.42439865_1483923358375635_5929505641243607040_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 16:01:56', '2024-09-19 07:09:52', NULL),
(26, 1, 'Short Course', 'Python Programming', 'PY', NULL, NULL, '20000', 8, '20', '1664449355.41695207_1473566989411272_9025605483116888064_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 16:02:35', '2022-09-29 16:02:35', NULL),
(27, 1, 'Short Course', 'C++ Programming', 'CP', NULL, NULL, '20000', 8, '20', '1664449399.41709776_1473567056077932_154915407327657984_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 16:03:19', '2022-09-29 16:03:19', NULL),
(28, 1, 'Short Course', 'Java Programming', 'JV', NULL, NULL, '20000', 8, '20', '1664449534.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 16:05:34', '2022-09-29 16:05:34', NULL),
(29, 1, 'Short Course', 'Duolingo Test Preparation', 'DU', NULL, NULL, '20000', 8, '20', '1664449686.42439865_1483923358375635_5929505641243607040_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 16:08:06', '2022-09-29 16:08:06', NULL),
(30, 1, 'Short Course', 'Linguaskill Test Preparation', 'LG', NULL, NULL, '20000', 8, '20', '1664449726.42391130_1483923238375647_7690495193845334016_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 16:08:46', '2022-09-29 16:08:46', NULL),
(31, 1, 'Short Course', 'Pearson PTE', 'PT', NULL, NULL, '20000', 8, '20', '1664449785.41709776_1473567056077932_154915407327657984_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 16:09:45', '2022-09-29 16:09:45', NULL),
(32, 1, 'Short Course', 'OET Test Preparation', 'OT', NULL, NULL, '25000', 8, '20', '1664449817.41695207_1473566989411272_9025605483116888064_o.jpg', NULL, NULL, 'Suspended', '2022-09-29 16:10:17', '2022-09-29 16:10:17', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
