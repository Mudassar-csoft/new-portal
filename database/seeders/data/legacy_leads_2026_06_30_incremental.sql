-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 30, 2026 at 06:52 AM
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
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `campus_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('Pending','Not Interested','Enrolled','Transferred','Registered') NOT NULL,
  `name` varchar(255) NOT NULL,
  `primary_contact` varchar(255) NOT NULL,
  `guardian_contact` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `country_id` bigint(20) UNSIGNED NOT NULL,
  `state_id` bigint(20) UNSIGNED DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `area` varchar(255) DEFAULT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `marketing_source` varchar(255) NOT NULL,
  `probability` varchar(255) DEFAULT NULL,
  `remarks` varchar(5000) DEFAULT NULL,
  `next_follow_up` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `origin` varchar(255) NOT NULL DEFAULT 'Walkin',
  `classes` varchar(255) NOT NULL DEFAULT 'in-campus'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `user_id`, `assigned_user_id`, `program_id`, `campus_id`, `status`, `name`, `primary_contact`, `guardian_contact`, `email`, `country_id`, `state_id`, `city`, `area`, `gender`, `marketing_source`, `probability`, `remarks`, `next_follow_up`, `created_at`, `updated_at`, `origin`, `classes`) VALUES
(23890, 131, NULL, 42, 8, 'Pending', 'Hafsa', '03004227428', NULL, NULL, 167, NULL, 'Faisalabad', 'Millat Chowk Campus', 'Male', 'Other', '60', 'She visits for office course with her friend. will confirm in few days.', '2026-06-30 12:00:00', '2026-06-29 05:20:20', '2026-06-29 05:20:20', 'Walk-in', 'incampus'),
(23891, 65, NULL, 86, 19, 'Pending', 'Athar Mudassir', '03281008479', NULL, 'atharmudassir40@gmail.com', 167, NULL, 'Lahore', 'Johar Town', 'Male', 'Career team', '40', 'interested in shopify', '2026-06-29 13:04:00', '2026-06-29 07:04:24', '2026-06-29 07:04:24', 'Walk-in', 'incampus'),
(23892, 111, NULL, 47, 7, 'Pending', 'Dawood', '03174687081', NULL, NULL, 167, NULL, 'Faisalabad', 'Faisalabad', 'Male', 'Facebook', '100', 'he was saying he will let you know after discuss with friends', '2026-07-01 12:14:00', '2026-06-29 07:21:46', '2026-06-29 07:21:46', 'Walk-in', 'incampus');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_leads_next_follow_up` (`next_follow_up`),
  ADD KEY `idx_leads_status` (`status`),
  ADD KEY `idx_leads_campus_id` (`campus_id`),
  ADD KEY `idx_leads_assigned_user_id` (`assigned_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23893;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `assigned_user_id` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
