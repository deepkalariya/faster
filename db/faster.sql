-- phpMyAdmin SQL Dump
-- version 4.8.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 03, 2023 at 07:50 AM
-- Server version: 10.1.32-MariaDB
-- PHP Version: 7.2.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `faster`
--

-- --------------------------------------------------------

--
-- Table structure for table `auth`
--

CREATE TABLE `auth` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `auth`
--

INSERT INTO `auth` (`id`, `username`, `password`) VALUES
(2, 'admin', '6f5393979d674de36c433b47b7d8908e');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `city` varchar(50) NOT NULL,
  `contact` varchar(15) NOT NULL,
  `margin` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `complete_items`
--

CREATE TABLE `complete_items` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `add_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('m','p') NOT NULL DEFAULT 'p',
  `rate` decimal(10,4) NOT NULL,
  `parts` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `options`
--

CREATE TABLE `options` (
  `id` int(11) NOT NULL,
  `parameter` char(64) DEFAULT NULL,
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `options`
--

INSERT INTO `options` (`id`, `parameter`, `value`) VALUES
(1, 'start_inv_no', '115');

-- --------------------------------------------------------

--
-- Table structure for table `parties`
--

CREATE TABLE `parties` (
  `id` int(11) NOT NULL,
  `party_name` int(11) DEFAULT NULL,
  `invoice_number` int(11) DEFAULT NULL,
  `items` text,
  `invoice_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `inv_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `sale_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stock_category`
--

CREATE TABLE `stock_category` (
  `id` int(11) NOT NULL,
  `cat_name` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `stock_category`
--

INSERT INTO `stock_category` (`id`, `cat_name`) VALUES
(1, 'SAPTING = 30MM'),
(2, 'ROTAR'),
(3, 'SAPTING = 25MM'),
(4, 'SAPTING = 14MM'),
(5, 'SAPTING = 18MM'),
(6, 'SAPTING = 32MM'),
(7, 'BODI PAIP 92.5'),
(8, 'BODI PAIP 92'),
(9, 'BODI PAIP 137'),
(10, 'BODI PAIP 138'),
(11, 'PAMP PAIP'),
(12, 'BODI 92.5'),
(13, 'BODI 92'),
(14, 'BODI 137'),
(15, 'BODI 138'),
(16, 'SPER'),
(17, 'V-4 BAUL'),
(18, 'DECCAN BAUL'),
(19, 'DECCAN EMILAR'),
(20, 'PAMP CASTING 92.5'),
(21, 'BODI CASTING 92 ECCO 23X30X30'),
(22, 'BODI CASTING 92 MIDAL ECCO 23X30X30'),
(23, 'BODI CASTING 92.5 26.5X32X35'),
(24, 'BODI CASTING 92.5 26.5X36X35'),
(25, 'PAMP CASTING 92 ECCO-1'),
(26, 'PAMP CASTING 92 ECCO-2'),
(27, 'CASTING V-7'),
(28, 'CASTING DECCAN'),
(29, 'STEMPING'),
(30, 'BUSING'),
(31, 'LEMINESHAN'),
(32, 'COPPAR');

-- --------------------------------------------------------

--
-- Table structure for table `stock_in`
--

CREATE TABLE `stock_in` (
  `id` int(11) NOT NULL,
  `parts_id` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `add_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `stock_in`
--

INSERT INTO `stock_in` (`id`, `parts_id`, `stock`, `add_date`) VALUES
(1, 1, 50, '2023-04-29 15:28:38'),
(2, 2, 1, '2023-04-29 15:30:21'),
(3, 2, 5, '2023-04-29 15:30:37'),
(4, 4, 1, '2023-05-06 11:52:10'),
(5, 5, 1, '2023-05-06 12:05:34'),
(6, 6, 1, '2023-05-06 12:06:25'),
(7, 7, 1, '2023-05-06 12:07:10'),
(8, 8, 1, '2023-05-06 17:06:57'),
(9, 9, 1, '2023-05-06 17:08:54'),
(10, 10, 1, '2023-05-06 17:10:09'),
(11, 11, 1, '2023-05-06 17:10:48'),
(12, 12, 1, '2023-05-06 17:12:45'),
(13, 13, 1, '2023-05-06 17:13:50'),
(14, 14, 1, '2023-05-06 17:14:26'),
(15, 15, 1, '2023-05-06 17:17:28'),
(16, 16, 1, '2023-05-06 17:18:32'),
(17, 17, 1, '2023-05-07 16:44:56'),
(18, 18, 1, '2023-05-07 16:45:28'),
(19, 19, 1, '2023-05-07 16:45:54'),
(20, 20, 1, '2023-05-07 16:46:30'),
(21, 21, 1, '2023-05-07 16:47:12'),
(22, 22, 1, '2023-05-07 16:47:40'),
(23, 23, 1, '2023-05-07 16:50:59'),
(24, 24, 1, '2023-05-07 16:51:30'),
(25, 25, 1, '2023-05-07 16:52:01'),
(26, 26, 1, '2023-05-07 16:52:33'),
(27, 27, 1, '2023-05-07 16:53:06'),
(28, 28, 1, '2023-05-07 16:53:58'),
(29, 29, 1, '2023-05-07 16:54:35'),
(30, 30, 1, '2023-05-07 16:57:42'),
(31, 31, 1, '2023-05-07 16:58:08'),
(32, 32, 1, '2023-05-07 16:58:59'),
(33, 33, 1, '2023-05-07 17:00:24'),
(34, 34, 1, '2023-05-12 16:02:50'),
(35, 35, 1, '2023-05-12 16:04:21'),
(36, 36, 1, '2023-05-12 16:11:50');

-- --------------------------------------------------------

--
-- Table structure for table `stock_out`
--

CREATE TABLE `stock_out` (
  `id` int(11) NOT NULL,
  `parts_id` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `used_date` datetime NOT NULL,
  `com_item_id` int(11) NOT NULL,
  `main_item_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `stock_out`
--

INSERT INTO `stock_out` (`id`, `parts_id`, `stock`, `used_date`, `com_item_id`, `main_item_id`) VALUES
(3, 1, 1, '2023-04-29 19:42:02', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `total_items`
--

CREATE TABLE `total_items` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `total_stock`
--

CREATE TABLE `total_stock` (
  `id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `parts_name` varchar(100) NOT NULL,
  `is_peta_item` enum('0','1') NOT NULL DEFAULT '0',
  `peta_items` text NOT NULL,
  `stock` int(11) NOT NULL,
  `alert_limit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `total_stock`
--

INSERT INTO `total_stock` (`id`, `cat_id`, `parts_name`, `is_peta_item`, `peta_items`, `stock`, `alert_limit`) VALUES
(3, 1, '0.5 HP 368', '1', '', 1, 50),
(4, 1, '0.75 HP 388', '1', '', 1, 50),
(5, 1, '1 HP 438', '1', '', 1, 50),
(6, 1, '1.5 HP 488', '1', '', 1, 50),
(7, 1, '2 HP 538', '1', '', 1, 50),
(8, 1, '3 HP 563', '1', '', 1, 50),
(9, 1, '3 HP FULL 438', '1', '', 1, 50),
(10, 1, '5 HP 638', '1', '', 1, 50),
(11, 1, '6 HP 688', '1', '', 1, 50),
(12, 3, '0.5 HP 343', '1', '', 1, 50),
(13, 3, '0.75 HP 363', '1', '', 1, 50),
(14, 3, '1 HP 413', '1', '', 1, 50),
(15, 3, '1.5 HP 469', '1', '', 1, 50),
(16, 3, '2 HP 519', '1', '', 1, 50),
(17, 4, '236', '1', '', 1, 50),
(18, 4, '260', '1', '', 1, 50),
(19, 4, '305', '1', '', 1, 50),
(20, 4, '275', '1', '', 1, 50),
(21, 4, '351', '1', '', 1, 50),
(22, 4, '420', '1', '', 1, 50),
(23, 4, '315', '1', '', 1, 50),
(24, 4, '325', '1', '', 1, 50),
(25, 4, '375', '1', '', 1, 50),
(26, 4, '490', '1', '', 1, 50),
(27, 4, '535', '1', '', 1, 50),
(28, 4, '380', '1', '', 1, 50),
(29, 4, '415', '1', '', 1, 50),
(30, 4, '435', '1', '', 1, 50),
(31, 4, '450', '1', '', 1, 50),
(32, 4, '680', '1', '', 1, 50),
(33, 4, '455', '1', '', 1, 50),
(34, 4, '350', '0', '', 1, 50),
(35, 4, '360', '0', '', 1, 50),
(36, 4, '325', '0', '', 1, 50);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `auth`
--
ALTER TABLE `auth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complete_items`
--
ALTER TABLE `complete_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `options`
--
ALTER TABLE `options`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parties`
--
ALTER TABLE `parties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_category`
--
ALTER TABLE `stock_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_in`
--
ALTER TABLE `stock_in`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_out`
--
ALTER TABLE `stock_out`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `total_items`
--
ALTER TABLE `total_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item` (`item_id`) USING BTREE;

--
-- Indexes for table `total_stock`
--
ALTER TABLE `total_stock`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `auth`
--
ALTER TABLE `auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complete_items`
--
ALTER TABLE `complete_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `options`
--
ALTER TABLE `options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parties`
--
ALTER TABLE `parties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_category`
--
ALTER TABLE `stock_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `stock_in`
--
ALTER TABLE `stock_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `stock_out`
--
ALTER TABLE `stock_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `total_items`
--
ALTER TABLE `total_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `total_stock`
--
ALTER TABLE `total_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
