-- Donation Management System Database Setup
-- This file contains the complete database schema for the Donation Management System
-- 
-- Installation Instructions:
-- 1. Import this file into your MySQL server (it will create the database automatically)
-- 2. Update database credentials in PHP files if needed (default: localhost, root, no password)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `donation_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Select the database
USE `donation_system`;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `documentary_photos`
-- Stores documentary photos uploaded by organizations
--

CREATE TABLE `documentary_photos` (
  `id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `photo_path` varchar(500) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
-- Tracks donations made by individuals to organizations
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `individual_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `donation_type` enum('money','clothes','food','blood','other') NOT NULL DEFAULT 'money',
  `amount` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `donation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('completed') DEFAULT 'completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `help_requests`
-- Manages help requests from individuals to organizations
--

CREATE TABLE `help_requests` (
  `id` int(11) NOT NULL,
  `individual_id` int(11) NOT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `amount_requested` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `response_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `individuals`
-- Stores individual user accounts and information
--

CREATE TABLE `individuals` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_photo` varchar(500) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt_time` timestamp NULL DEFAULT NULL,
  `account_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
-- Stores organization accounts and information
--

CREATE TABLE `organizations` (
  `id` int(11) NOT NULL,
  `organization_name` varchar(100) NOT NULL,
  `organization_type` varchar(50) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `profile_photo` varchar(500) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt_time` timestamp NULL DEFAULT NULL,
  `account_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
-- Manages password reset tokens for account recovery
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Indexes for table `documentary_photos`
--
ALTER TABLE `documentary_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organization_id` (`organization_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `individual_id` (`individual_id`),
  ADD KEY `organization_id` (`organization_id`);

--
-- Indexes for table `help_requests`
--
ALTER TABLE `help_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `individual_id` (`individual_id`),
  ADD KEY `organization_id` (`organization_id`);

--
-- Indexes for table `individuals`
--
ALTER TABLE `individuals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_email` (`email`);

-- --------------------------------------------------------

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `documentary_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `help_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `individuals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `organizations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Foreign key constraints
--

ALTER TABLE `documentary_photos`
  ADD CONSTRAINT `documentary_photos_ibfk_1` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `donations_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

ALTER TABLE `help_requests`
  ADD CONSTRAINT `help_requests_ibfk_1` FOREIGN KEY (`individual_id`) REFERENCES `individuals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `help_requests_ibfk_2` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Migration: Ensure profile_photo column exists in organizations table
-- This adds the column if it doesn't exist (for existing databases)
--

SET @dbname = DATABASE();
SET @tablename = 'organizations';
SET @columnname = 'profile_photo';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1', -- Column exists, do nothing
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' varchar(500) DEFAULT NULL')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
