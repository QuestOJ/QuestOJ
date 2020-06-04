-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 29, 2020 at 01:22 PM
-- Server version: 5.7.29-0ubuntu0.18.04.1
-- PHP Version: 7.2.24-0ubuntu0.18.04.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `manage`
--
CREATE DATABASE IF NOT EXISTS `manage` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `manage`;

-- --------------------------------------------------------

--
-- Table structure for table `manage_client`
--

CREATE TABLE `manage_client` (
  `id` int(11) NOT NULL,
  `clientID` text NOT NULL,
  `clientSecret` text NOT NULL,
  `lastUpdate` datetime NOT NULL,
  `start` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `manage_logs`
--

CREATE TABLE `manage_logs` (
  `id` int(11) NOT NULL,
  `logLevel` text NOT NULL,
  `logHash` text NOT NULL,
  `logText` text NOT NULL,
  `logCode` text NOT NULL,
  `logUrl` text NOT NULL,
  `logInfo` text NOT NULL,
  `execStack` text NOT NULL,
  `clientKey` text NOT NULL,
  `fieldGet` text,
  `fieldPost` text,
  `fieldCookie` text,
  `fieldSession` text,
  `guestIP` text NOT NULL,
  `guestOS` text NOT NULL,
  `guestBrowser` text NOT NULL,
  `userID` text NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `manage_system`
--

CREATE TABLE `manage_system` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `value` text NOT NULL,
  `autoload` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `manage_task`
--

CREATE TABLE `manage_task` (
  `id` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `taskid` bigint(16) NOT NULL,
  `name` text,
  `description` text,
  `status` text,
  `startTime` datetime NOT NULL,
  `endTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `manage_task_job`
--

CREATE TABLE `manage_task_job` (
  `id` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `taskid` bigint(16) NOT NULL,
  `jobid` int(11) NOT NULL,
  `attempt` int(11) NOT NULL,
  `status` text COLLATE utf8mb4_unicode_ci,
  `name` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `stdout` text COLLATE utf8mb4_unicode_ci,
  `stderr` text COLLATE utf8mb4_unicode_ci,
  `online` tinyint(4) NOT NULL DEFAULT '1',
  `startTime` datetime NOT NULL,
  `endTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manage_task_online`
--

CREATE TABLE `manage_task_online` (
  `id` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `taskid` bigint(16) NOT NULL,
  `jobid` int(11) NOT NULL,
  `attempt` int(11) NOT NULL,
  `uploadIndex` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `manage_client`
--
ALTER TABLE `manage_client`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `manage_logs`
--
ALTER TABLE `manage_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `manage_system`
--
ALTER TABLE `manage_system`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `manage_task`
--
ALTER TABLE `manage_task`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `manage_task_job`
--
ALTER TABLE `manage_task_job`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `manage_task_online`
--
ALTER TABLE `manage_task_online`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `manage_client`
--
ALTER TABLE `manage_client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manage_logs`
--
ALTER TABLE `manage_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manage_system`
--
ALTER TABLE `manage_system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manage_task`
--
ALTER TABLE `manage_task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manage_task_job`
--
ALTER TABLE `manage_task_job`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manage_task_online`
--
ALTER TABLE `manage_task_online`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
