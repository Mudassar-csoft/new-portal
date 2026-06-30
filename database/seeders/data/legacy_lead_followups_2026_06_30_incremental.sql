-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 30, 2026 at 06:51 AM
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
-- Table structure for table `lead_follow_ups`
--

CREATE TABLE `lead_follow_ups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `follow_up_method` varchar(255) NOT NULL,
  `status` enum('Pending','Not Interested','Enrolled') NOT NULL,
  `follow_up_status` enum('Followed','Not Followed') NOT NULL DEFAULT 'Not Followed',
  `next_follow_up` datetime DEFAULT NULL,
  `probability` varchar(20) DEFAULT NULL,
  `remarks` varchar(5000) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `campus_id` bigint(20) UNSIGNED DEFAULT NULL,
  `lead_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_follow_ups`
--

INSERT INTO `lead_follow_ups` (`id`, `user_id`, `lead_id`, `follow_up_method`, `status`, `follow_up_status`, `next_follow_up`, `probability`, `remarks`, `created_at`, `updated_at`, `campus_id`, `lead_type`) VALUES
(123087, 131, 23890, 'Walk-in', 'Pending', 'Not Followed', '2026-06-30 12:00:00', '60', 'She visits for office course with her friend. will confirm in few days.', '2026-06-29 05:20:20', '2026-06-29 05:20:20', 8, 'training'),
(123088, 131, 23627, 'call', 'Not Interested', 'Not Followed', NULL, '0', 'She don\'t want to do any course right now due to personal reason. She didnot told me reason.', '2026-06-29 05:46:43', '2026-06-29 05:46:43', 8, 'training'),
(123089, 131, 23708, 'call', 'Not Interested', 'Not Followed', NULL, '0', 'He is from Chiniot.   He cant come so far. Thats why cnt join.', '2026-06-29 05:53:32', '2026-06-29 05:53:32', 8, 'training'),
(123090, 131, 23601, 'call', 'Pending', 'Not Followed', '2026-07-02 12:00:00', '60', 'He will visit tomorrow.', '2026-06-29 06:01:13', '2026-06-29 06:01:13', 8, 'training'),
(123091, 131, 23723, 'call', 'Not Interested', 'Not Followed', NULL, '0', 'He is not willing to do course right now', '2026-06-29 06:08:12', '2026-06-29 06:08:12', 8, 'training'),
(123092, 131, 23719, 'call', 'Not Interested', 'Not Followed', NULL, '0', 'He is doing job cant manage course.', '2026-06-29 06:10:30', '2026-06-29 06:10:30', 8, 'training'),
(123093, 131, 23724, 'call', 'Pending', 'Not Followed', '2026-07-20 12:00:00', '50', 'Not answering call. dropped message on WhatsApp.', '2026-06-29 06:17:20', '2026-06-29 06:17:20', 8, 'training'),
(123094, 131, 23734, 'call', 'Not Interested', 'Not Followed', NULL, '0', 'She said she cant pay fee and can pay only 20k. we offered her 40% off but she is still not interested.', '2026-06-29 06:28:43', '2026-06-29 06:28:43', 8, 'training'),
(123095, 131, 23764, 'call', 'Pending', 'Not Followed', '2026-07-13 12:00:00', '50', 'Not answering call. dropped message on WhatsApp.', '2026-06-29 06:57:37', '2026-06-29 06:57:37', 8, 'training'),
(123096, 131, 23798, 'call', 'Pending', 'Followed', '2026-08-20 12:00:00', '50', 'interested in textile course.', '2026-06-29 07:01:34', '2026-06-29 07:05:17', 8, 'training'),
(123097, 65, 23891, 'Walk-in', 'Pending', 'Not Followed', '2026-06-29 13:04:00', '40', 'interested in shopify', '2026-06-29 07:04:24', '2026-06-29 07:04:24', 19, 'training'),
(123098, 131, 23798, 'call', 'Not Interested', 'Not Followed', NULL, '0', 'He want as adegree thats why not interested.', '2026-06-29 07:05:17', '2026-06-29 07:05:17', 8, 'training'),
(123099, 131, 23800, 'call', 'Not Interested', 'Not Followed', NULL, '0', 'He is not willing to do web course right now he said He just took information . and in future if he want then contact again', '2026-06-29 07:15:36', '2026-06-29 07:15:36', 8, 'training'),
(123100, 111, 23892, 'Walk-in', 'Pending', 'Not Followed', '2026-07-01 12:14:00', '100', 'he was saying he will let you know after discuss with friends', '2026-06-29 07:21:46', '2026-06-29 07:21:46', 7, 'training'),
(123101, 131, 23815, 'call', 'Pending', 'Not Followed', '2026-07-02 12:00:00', '50', 'he will visit tomorrow', '2026-06-29 07:31:05', '2026-06-29 07:31:05', 8, 'training'),
(123102, 131, 23816, 'call', 'Pending', 'Not Followed', '2026-07-12 12:00:00', '50', 'Not answering call. dropped message on WhatsApp.', '2026-06-29 07:46:50', '2026-06-29 07:46:50', 8, 'training'),
(123103, 131, 23850, 'call', 'Not Interested', 'Not Followed', NULL, '0', 'She don\'t want to do any course right now due to personal reason. she is not interested', '2026-06-29 07:48:34', '2026-06-29 07:48:34', 8, 'training'),
(123104, 131, 23888, 'call', 'Pending', 'Not Followed', '2026-07-02 12:00:00', '60', 'She knows basic Office mngement. she will visit today to confirm what out line we cover in advance excel.', '2026-06-29 07:58:42', '2026-06-29 07:58:42', 8, 'training'),
(123105, 131, 23889, 'call', 'Not Interested', 'Not Followed', NULL, '0', 'he is not interested after consulting with family.', '2026-06-29 08:00:28', '2026-06-29 08:00:28', 8, 'training'),
(123106, 128, 23804, 'call', 'Pending', 'Not Followed', '2026-07-06 12:00:00', '40', 'she said she will visit on next week so i will follow her up', '2026-06-29 08:13:39', '2026-06-29 08:13:39', 9, 'training'),
(123107, 128, 23794, 'call', 'Pending', 'Not Followed', '2026-06-30 11:15:00', '20', 'call her plus texted her but no response', '2026-06-29 08:16:08', '2026-06-29 08:16:08', 9, 'training'),
(123108, 128, 23652, 'call', 'Pending', 'Not Followed', '2026-06-30 11:20:00', '30', 'called him plus texted him o follow up so i will update him accordingy', '2026-06-29 08:19:48', '2026-06-29 08:19:48', 9, 'training'),
(123109, 128, 23791, 'whatsapp', 'Pending', 'Not Followed', '2026-07-01 12:10:00', '50', 'as i was talking to him so he informed me that he will visit', '2026-06-29 08:21:52', '2026-06-29 08:21:52', 9, 'training'),
(123110, 128, 23385, 'whatsapp', 'Pending', 'Not Followed', '2026-06-30 13:24:00', '60', 'called him but no response so i texted him over whtsp for follow up', '2026-06-29 08:25:24', '2026-06-29 08:25:24', 9, 'training'),
(123111, 128, 23868, 'call', 'Pending', 'Not Followed', '2026-06-30 13:27:00', '50', 'called him but no response so texted him to followup so i will update here accordingly', '2026-06-29 08:28:03', '2026-06-29 08:28:03', 9, 'training'),
(123112, 128, 23874, 'whatsapp', 'Pending', 'Not Followed', '2026-06-30 13:32:00', '20', 'she was not picking my call so i texted her over whtsp and she said she visit today or tomo may be so i shared location with her', '2026-06-29 08:33:06', '2026-06-29 08:33:06', 9, 'training'),
(123113, 128, 23869, 'whatsapp', 'Pending', 'Not Followed', '2026-06-30 13:36:00', '20', 'She isnt responding my calls so i texted her over whtsp to know her interest', '2026-06-29 08:38:24', '2026-06-29 08:38:24', 9, 'training'),
(123114, 128, 23870, 'call', 'Pending', 'Not Followed', '2026-06-30 13:00:00', '100', 'she will visit tomorrow', '2026-06-29 08:42:26', '2026-06-29 08:42:26', 9, 'training'),
(123115, 128, 23872, 'call', 'Pending', 'Not Followed', '2026-06-30 13:29:00', '20', 'called her but no respons so i texted her', '2026-06-29 08:46:10', '2026-06-29 08:46:10', 9, 'training'),
(123116, 128, 23873, 'call', 'Pending', 'Not Followed', '2026-06-30 13:29:00', '20', 'not responding on call so i texetd him', '2026-06-29 08:51:23', '2026-06-29 08:51:23', 9, 'training'),
(123117, 128, 23844, 'call', 'Pending', 'Not Followed', '2026-06-30 12:11:00', '20', 'he visited so he told me he will confirm by tomorrow', '2026-06-29 08:53:26', '2026-06-29 08:53:26', 9, 'training'),
(123118, 128, 23878, 'call', 'Pending', 'Not Followed', '2026-07-01 13:00:00', '50', 'he will visit in comming days', '2026-06-29 08:59:00', '2026-06-29 08:59:00', 9, 'training'),
(123119, 128, 23863, 'call', 'Pending', 'Not Followed', '2026-06-30 13:01:00', '20', 'her husband picked the call and informed me that he will call v=bck me in evening', '2026-06-29 09:04:28', '2026-06-29 09:04:28', 9, 'training'),
(123120, 128, 23716, 'call', 'Pending', 'Not Followed', '2026-06-30 13:27:00', '20', 'called him but no response so i texted him', '2026-06-29 09:28:38', '2026-06-29 09:28:38', 9, 'training'),
(123121, 128, 23845, 'whatsapp', 'Pending', 'Not Followed', '2026-06-30 14:30:00', '20', 'no response on call so texted her over whtsp', '2026-06-29 09:30:57', '2026-06-29 09:30:57', 9, 'training');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lead_follow_ups`
--
ALTER TABLE `lead_follow_ups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lead_follow_ups_user_id` (`user_id`,`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lead_follow_ups`
--
ALTER TABLE `lead_follow_ups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123122;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
