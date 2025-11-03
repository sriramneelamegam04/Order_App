-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 07:52 AM
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
-- Database: `order_web`
--

-- --------------------------------------------------------

--
-- Table structure for table `business_types`
--

CREATE TABLE `business_types` (
  `business_type_id` int(11) NOT NULL,
  `org_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `business_types`
--

INSERT INTO `business_types` (`business_type_id`, `org_id`, `name`, `description`, `is_system`, `created_at`) VALUES
(1, NULL, 'Restaurant 🍽️', 'Food ordering business', 1, '2025-09-19 12:41:46'),
(2, NULL, 'Supermarket 🛒', 'Supermarket business', 1, '2025-09-19 12:41:46'),
(3, NULL, 'Fruit Shop 🍎', 'Fruit business', 1, '2025-09-19 12:41:46'),
(4, NULL, 'Hardware 🛠️', 'Hardware tools and items', 1, '2025-09-19 12:41:46'),
(5, NULL, 'Bakery & Sweets 🥐', 'Bakery and sweet shop', 1, '2025-09-19 12:41:46'),
(6, NULL, 'Clothing / Textile 👕', 'Clothing and textile shop', 1, '2025-09-19 12:41:46'),
(7, NULL, 'Electronics Shop 🔌', 'Electronics and appliances', 1, '2025-09-19 12:41:46'),
(8, NULL, 'Pharmacy / Medical Shop 💊', 'Medical and pharmacy store', 1, '2025-09-19 12:41:46'),
(9, NULL, 'Stationery Shop ✏️', 'Stationery and books shop', 1, '2025-09-19 12:41:46'),
(10, 2, 'Gym', 'Fitness and training business', 0, '2025-09-19 12:49:25'),
(11, 1, 'naturals', 'nature', 0, '2025-09-24 16:00:29');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cart_id`, `session_id`, `user_id`, `created_at`) VALUES
(1, '8mr2mm3he1d62mh3p5lqbmb3pj', 0, '2025-09-23 12:33:59'),
(2, 'ef07epds42s0igsmkm84gqdmsi', 0, '2025-09-23 16:51:29'),
(3, 'sess_68d28637ce8c10.37767151', 0, '2025-09-23 17:06:45'),
(4, 'sess_68d28686e81200.54892577', 0, '2025-09-23 17:08:09'),
(5, 'sess_68d28836255228.58798614', 0, '2025-09-23 17:15:15'),
(6, 'sess_68d289841b8159.40300388', 0, '2025-09-23 17:20:49'),
(7, 'sess_68d289de809c69.63883199', 0, '2025-09-23 17:22:14'),
(8, 'sess_68d28a722dd482.01729481', 0, '2025-09-23 17:25:35'),
(9, 'sess_68d28bf55a8332.84955223', 0, '2025-09-23 17:31:09'),
(10, 'sess_68d3770be71914.70351776', 0, '2025-09-24 10:14:19'),
(11, 'sess_68d37877b2b089.57848192', 0, '2025-09-24 10:20:18'),
(12, 'sess_68d378b44b1975.07930334', 0, '2025-09-24 10:21:22'),
(13, 'sess_68d3793cb92953.30133451', 0, '2025-09-24 10:23:37'),
(14, 'sess_68d37a15596690.54599940', 0, '2025-09-24 10:27:10'),
(15, 'sess_68d37e530381e8.50882129', 0, '2025-09-24 10:45:15'),
(16, 'sess_68d37f9dd0a3f9.97144863', 0, '2025-09-24 10:50:47'),
(17, 'sess_68d381156f88d2.92534977', 0, '2025-09-24 10:57:01'),
(18, 'sess_68d38195233004.83736067', 0, '2025-09-24 10:59:09'),
(19, 'sess_68d384bf9925e4.98870884', 0, '2025-09-24 11:12:56'),
(20, 'sess_68d4e3851435a2.44236074', 0, '2025-09-25 12:18:06'),
(21, 'sess_68d6381512c421.83012927', 0, '2025-09-26 12:23:15'),
(22, 'sess_68fc74543bb7e1.38397708', 0, '2025-10-25 12:26:13'),
(23, 'sess_6905a1b95f35b6.48935904', 0, '2025-11-01 11:30:21'),
(24, 'sess_6905b6efeeaeb4.21096534', 0, '2025-11-01 13:29:50');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `qty`, `subtotal`) VALUES
(2, 1, 3, 3.00, 60.00),
(3, 1, 5, 2.00, 100.00),
(4, 1, 6, 2.00, 20.00),
(5, 1, 2, 1.00, 60.00),
(9, 2, 1, 3.00, 150.00),
(10, 2, 2, 3.00, 180.00),
(11, 2, 3, 3.00, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `user_id`, `business_type_id`, `name`, `created_at`) VALUES
(1, 4, 5, 'north indian', '2025-09-29 10:36:58'),
(2, 4, 5, 'italian', '2025-09-29 10:40:29'),
(3, 4, 5, 'fast food', '2025-09-29 10:58:26'),
(4, 4, 5, 'beverages', '2025-09-29 10:58:26'),
(6, 4, 5, 'hyderabadi', '2025-09-29 10:58:26'),
(7, 4, 5, 'desserts', '2025-09-29 10:58:26'),
(8, 4, 5, 'chinese', '2025-09-29 12:20:27'),
(9, 4, 5, 'main course', '2025-09-29 12:38:12'),
(10, 4, 5, 'main course', '2025-09-29 12:44:26'),
(11, 1, 5, 'chinese', '2025-10-07 10:27:56'),
(12, 1, 5, 'starters', '2025-10-07 10:33:08'),
(13, 2, 1, 'chinese', '2025-10-07 10:37:26'),
(14, 2, 1, 'starters', '2025-10-07 10:37:26'),
(15, 2, 1, 'north indian', '2025-10-07 11:27:30'),
(16, 2, 1, 'italian', '2025-10-07 11:27:30'),
(17, 2, 1, 'fast food', '2025-10-07 11:27:30'),
(18, 2, 1, 'beverages', '2025-10-07 11:27:30'),
(19, 2, 1, 'south indian', '2025-10-07 11:27:30'),
(20, 2, 1, 'hyderabadi', '2025-10-07 11:27:30'),
(21, 2, 1, 'desserts', '2025-10-07 11:27:30'),
(22, 6, 5, 'north indian', '2025-10-07 11:43:31'),
(23, 6, 5, 'italian', '2025-10-07 11:43:31'),
(24, 6, 5, 'fast food', '2025-10-07 11:43:31'),
(25, 6, 5, 'beverages', '2025-10-07 11:43:31'),
(26, 6, 5, 'south indian', '2025-10-07 11:43:31'),
(27, 6, 5, 'hyderabadi', '2025-10-07 11:43:31'),
(28, 6, 5, 'desserts', '2025-10-07 11:43:31'),
(29, 6, 5, 'fruits', '2025-10-10 12:32:45'),
(30, 6, 5, 'dairy', '2025-10-10 12:57:23'),
(31, 6, 5, 'vegetables', '2025-10-10 12:57:25'),
(32, 10, 1, 'fruits', '2025-10-25 12:19:39'),
(33, 10, 1, 'dairy', '2025-10-25 12:19:41'),
(34, 10, 1, 'vegetables', '2025-10-25 12:21:04');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qr_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_mobile` varchar(15) DEFAULT NULL,
  `status` enum('pending','paid','cod') DEFAULT 'pending',
  `total` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('COD','UPI') DEFAULT 'COD',
  `razorpay_order_id` varchar(100) DEFAULT NULL,
  `razorpay_payment_id` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_received` tinyint(1) DEFAULT 0,
  `received_by_staff_id` int(11) DEFAULT NULL,
  `received_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `qr_id`, `customer_name`, `customer_mobile`, `status`, `total`, `payment_method`, `razorpay_order_id`, `razorpay_payment_id`, `created_at`, `is_received`, `received_by_staff_id`, `received_at`) VALUES
(1, 2, 1, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-20 12:55:02', 0, NULL, NULL),
(2, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-20 13:43:17', 0, NULL, NULL),
(3, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 12:11:19', 0, NULL, NULL),
(4, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 12:21:35', 0, NULL, NULL),
(5, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 12:21:52', 0, NULL, NULL),
(6, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 12:25:23', 0, NULL, NULL),
(7, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 16:50:21', 0, NULL, NULL),
(8, 2, NULL, 'Raj Kumar', '9876543210', 'pending', 220.00, 'COD', NULL, NULL, '2025-09-23 16:57:23', 0, NULL, NULL),
(9, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:01:20', 0, NULL, NULL),
(10, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:15', 0, NULL, NULL),
(11, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:19', 0, NULL, NULL),
(12, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:20', 0, NULL, NULL),
(13, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:21', 0, NULL, NULL),
(14, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:23', 0, NULL, NULL),
(15, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:24', 0, NULL, NULL),
(16, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:04:04', 0, NULL, NULL),
(17, 2, 4, 'Sriram', '9360552619', 'pending', 390.00, 'COD', NULL, NULL, '2025-09-23 17:06:23', 0, NULL, NULL),
(18, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', 'order_68d286ba1aad6', NULL, '2025-09-23 17:07:42', 0, NULL, NULL),
(19, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', 'order_68d28859341c1', NULL, '2025-09-23 17:14:54', 0, NULL, NULL),
(20, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-23 17:20:28', 0, NULL, NULL),
(21, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-23 17:21:58', 0, NULL, NULL),
(22, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-23 17:24:26', 0, NULL, NULL),
(23, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-23 17:30:53', 0, NULL, NULL),
(24, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:13:55', 0, NULL, NULL),
(25, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:19:59', 0, NULL, NULL),
(26, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:21:00', 0, NULL, NULL),
(27, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:23:16', 0, NULL, NULL),
(28, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:26:53', 0, NULL, NULL),
(29, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:44:59', 0, NULL, NULL),
(30, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:50:29', 0, NULL, NULL),
(31, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:56:45', 0, NULL, NULL),
(32, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', 'order_68d381af1d8e3', NULL, '2025-09-24 10:58:53', 0, NULL, NULL),
(33, 2, 4, 'Sriram', '9360552619', 'paid', 430.00, 'UPI', 'order_68d384f391107', NULL, '2025-09-24 11:12:23', 0, NULL, NULL),
(34, 4, 5, 'Mullai Malar', '955623147', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-25 12:03:56', 0, NULL, NULL),
(35, 4, 5, 'mullai malar', '955623147', 'pending', 1680.00, 'COD', NULL, NULL, '2025-09-25 12:09:01', 0, NULL, NULL),
(36, 5, 6, 'mullai malar', '955623147', 'pending', 1890.00, 'COD', NULL, NULL, '2025-09-26 12:22:05', 0, NULL, NULL),
(37, 5, 6, 'mullai malar', '955623147', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-30 12:09:03', 0, NULL, NULL),
(38, 2, 4, 'mullai malar', '955623147', 'pending', 0.00, 'COD', NULL, NULL, '2025-10-10 11:56:08', 0, NULL, NULL),
(39, 2, 4, 'mullai malar', '955623147', 'pending', 0.00, 'COD', NULL, NULL, '2025-10-10 11:56:52', 0, NULL, NULL),
(40, 2, 4, 'mullai malar', '955623147', 'pending', 0.00, 'COD', NULL, NULL, '2025-10-10 13:15:24', 0, NULL, NULL),
(41, 2, 4, 'mullai malar', '955623147', 'pending', 0.00, 'COD', NULL, NULL, '2025-10-25 11:51:39', 0, NULL, NULL),
(42, 10, 10, 'mullai malar', '955623147', 'paid', 2323.00, 'COD', NULL, NULL, '2025-10-25 12:25:16', 0, NULL, NULL),
(43, 10, 10, 'murugan', '9865721234', 'pending', 3858.00, 'COD', NULL, NULL, '2025-11-01 11:29:21', 1, NULL, '2025-11-01 07:50:18'),
(44, 10, 10, 'vijay', '9876543210', 'pending', 48300.00, 'COD', NULL, NULL, '2025-11-01 13:02:57', 1, NULL, '2025-11-01 09:00:36');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `qty`, `subtotal`) VALUES
(1, 8, 1, 2.00, 100.00),
(2, 8, 2, 1.00, 60.00),
(3, 8, 3, 3.00, 60.00),
(4, 17, 1, 3.00, 150.00),
(5, 17, 2, 3.00, 180.00),
(6, 17, 3, 3.00, 60.00),
(7, 18, 1, 3.00, 150.00),
(8, 18, 2, 3.00, 180.00),
(9, 18, 3, 5.00, 100.00),
(10, 19, 1, 3.00, 150.00),
(11, 19, 2, 3.00, 180.00),
(12, 19, 3, 5.00, 100.00),
(13, 20, 1, 3.00, 150.00),
(14, 20, 2, 3.00, 180.00),
(15, 20, 3, 5.00, 100.00),
(16, 21, 1, 3.00, 150.00),
(17, 21, 2, 3.00, 180.00),
(18, 21, 3, 5.00, 100.00),
(19, 22, 1, 3.00, 150.00),
(20, 22, 2, 3.00, 180.00),
(21, 22, 3, 5.00, 100.00),
(22, 23, 1, 3.00, 150.00),
(23, 23, 2, 3.00, 180.00),
(24, 23, 3, 5.00, 100.00),
(25, 24, 1, 3.00, 150.00),
(26, 24, 2, 3.00, 180.00),
(27, 24, 3, 5.00, 100.00),
(28, 25, 1, 3.00, 150.00),
(29, 25, 2, 3.00, 180.00),
(30, 25, 3, 5.00, 100.00),
(31, 26, 1, 3.00, 150.00),
(32, 26, 2, 3.00, 180.00),
(33, 26, 3, 5.00, 100.00),
(34, 27, 1, 3.00, 150.00),
(35, 27, 2, 3.00, 180.00),
(36, 27, 3, 5.00, 100.00),
(37, 28, 1, 3.00, 150.00),
(38, 28, 2, 3.00, 180.00),
(39, 28, 3, 5.00, 100.00),
(40, 29, 1, 3.00, 150.00),
(41, 29, 2, 3.00, 180.00),
(42, 29, 3, 5.00, 100.00),
(43, 30, 1, 3.00, 150.00),
(44, 30, 2, 3.00, 180.00),
(45, 30, 3, 5.00, 100.00),
(46, 31, 1, 3.00, 150.00),
(47, 31, 2, 3.00, 180.00),
(48, 31, 3, 5.00, 100.00),
(49, 32, 1, 3.00, 150.00),
(50, 32, 2, 3.00, 180.00),
(51, 32, 3, 5.00, 100.00),
(52, 33, 1, 3.00, 150.00),
(53, 33, 2, 3.00, 180.00),
(54, 33, 3, 5.00, 100.00),
(55, 35, 10, 3.00, 900.00),
(56, 35, 11, 3.00, 180.00),
(57, 35, 12, 5.00, 600.00),
(58, 36, 23, 3.00, 900.00),
(59, 36, 24, 3.00, 540.00),
(60, 36, 25, 5.00, 450.00),
(61, 42, 1, 3.00, 150.00),
(62, 42, 2, 3.00, 180.00),
(63, 42, 3, 5.00, 100.00),
(64, 42, 98, 3.00, 450.00),
(65, 42, 99, 3.00, 333.00),
(66, 42, 100, 5.00, 1110.00),
(67, 43, 98, 5.00, 750.00),
(68, 43, 99, 8.00, 888.00),
(69, 43, 100, 10.00, 2220.00),
(70, 44, 98, 100.00, 15000.00),
(71, 44, 99, 100.00, 11100.00),
(72, 44, 100, 100.00, 22200.00);

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `name`, `mobile`, `otp_hash`, `expires_at`, `created_at`) VALUES
(1, '', '9876543210', '$2y$10$5jjnkWstPO6//pGRap0fEe5wJDXI5N0zHN808EW0C1MYs.vQDuqqe', '2025-09-18 16:13:24', '2025-09-18 16:08:24'),
(2, '', '9360552619', '$2y$10$9tBSiSNctSOr3XI3dJPlreEKqVc85HVL8SJ0N.t0Zkdi5M5qbUeqi', '2025-09-18 16:16:48', '2025-09-18 16:11:48'),
(3, '', '9360552619', '$2y$10$kbN.KSgtcCGUJcOEYVb3nOqsTNGkhy0mkPNeWFxa1c4Ns8fSPqZJW', '2025-09-18 16:24:26', '2025-09-18 16:19:26'),
(4, '', '9876543210', '$2y$10$1gLniBccnQUlk5QHOyBTQus2QLxa8E8fi7A.ZGE6GYZvaWMvNes.u', '2025-09-19 12:30:11', '2025-09-19 12:25:11'),
(5, '', '9876543210', '$2y$10$UzUigErdFUc5PDNBRL.1k.vBnDW1XB31RryOEzcWeLdoVsGw4Cz5q', '2025-09-19 15:56:17', '2025-09-19 15:51:17'),
(6, '', '9876543210', '$2y$10$x8Ra/llCEb4dDijzT1Haqec0UxnhicRTYIOumSylr6ybKXFOgka8W', '2025-09-20 12:55:23', '2025-09-20 12:50:23'),
(7, '', '9876543210', '$2y$10$KGo5.SMZ8PQXGLTSbzptyOKk.aLkUWxelewz6SjkSSVcHFvO1TcmS', '2025-09-23 12:15:08', '2025-09-23 12:10:08'),
(8, '', '9876543210', '$2y$10$w.WxwgABWgjb1LaAS3qHIuka1JZOaxfTtQoFeKRLFXIsjdsu.Pt.C', '2025-09-23 15:52:26', '2025-09-23 15:47:26'),
(9, '', '9876543210', '$2y$10$CripamI2H2hA53wpSbIjuOJ05SkqzfvuuXPPwfKZTESZsjESJyYO.', '2025-09-24 10:21:46', '2025-09-24 10:16:46'),
(10, '', '9360552619', '$2y$10$GjjuYeaZ0iDxtEeTshUiLuMJuhK//Jn7lIgbUdF8hw.DrPZelEw7K', '2025-09-24 15:42:48', '2025-09-24 15:37:48'),
(11, '', '9360556666', '$2y$10$uYPzOad9azjsryvwnczYSOEU188S5xfZ2kfgBJi9Vl56GXN1.FWqG', '2025-09-24 16:22:04', '2025-09-24 16:17:04'),
(12, '', '9360556662', '$2y$10$FE6MdhOMvYjb46dQAfMzmu1yaraemKnrIvw6Vja54NipvIT9wzhsq', '2025-09-24 16:29:06', '2025-09-24 16:24:06'),
(13, '', '9360556662', '$2y$10$wRjrdTEetwHYoimrEMqPje1Emn4Y1Y281KI/XnVnpso6iZobiIMVe', '2025-09-25 10:58:00', '2025-09-25 10:53:00'),
(14, '', '9360556662', '$2y$10$QmYzfQcDDZ/qAScmqmWNvuhR10wLJKcl9bCm4PlYfdzr520I/HA1i', '2025-09-25 11:11:18', '2025-09-25 11:06:18'),
(15, '', '9360556662', '$2y$10$sdG1ZkN2.Y7wTsEDP0nqHOKmx5Gg3GfScekQXuZgg9a4TWKCJ3LYK', '2025-09-26 12:18:30', '2025-09-26 12:13:30'),
(16, '', '9360556622', '$2y$10$uLjb8Gt77GH/At./z6Ok3.sUJgjRj3jMnId6OY9rtnyo9FUg65yJa', '2025-09-26 12:18:47', '2025-09-26 12:13:47'),
(17, '', '9360556662', '$2y$10$vhiwDqSD5ucII7AQHZWq0u25yweuzfop/OBztkE.kdAcylWsQBoG.', '2025-09-29 10:40:15', '2025-09-29 10:35:15'),
(18, '', '9360556662', '$2y$10$hsQ6aTn4KyG.FqFDDd2wkuWQC.Vr1XxmSLf6YCedQD9gCiaxFVB1W', '2025-09-30 12:08:54', '2025-09-30 12:03:54'),
(19, '', '9360552619', '$2y$10$yN1RQIrdKsiu.PPyZh./aeXnMAg0Q.ioJxWAm0eKwihKEMr6Ir9ti', '2025-10-07 10:31:47', '2025-10-07 10:26:47'),
(20, '', '9876543210', '$2y$10$DHXWiAqtUucwuLZmjlhXaeyIpt660T7W8HsfjYkv1bnDsNGINXUVC', '2025-10-07 10:41:18', '2025-10-07 10:36:18'),
(21, '', '9003673183', '$2y$10$wnC2TgkYekwnt9Yi15JpCe4GaflNDW//risBGCSgHcesuPQZne8HC', '2025-10-07 11:39:06', '2025-10-07 11:34:06'),
(22, '', '9876543210', '$2y$10$W21nbsTRUhGOBA.chfwxe.PqIRnM/EccJ.0iuP65lJkUexaTvR6Hu', '2025-10-10 11:58:38', '2025-10-10 11:53:38'),
(23, '', '9003673183', '$2y$10$hpH2hVK/rjRBC0JJ6bCsPuqPvBKoprAJLFO4EsWMTI6wf7i5jUQ9u', '2025-10-10 12:19:22', '2025-10-10 12:14:22'),
(24, '', '9003673183', '$2y$10$p1EHF1lxo5jnaw4epr3Cr.6aw/kp7OwvXx4ONW2Cq3Zal.NkYZInC', '2025-10-10 15:40:16', '2025-10-10 15:35:16'),
(25, '', '9003675555', '$2y$10$UZJWzuT0UckTkh2nUOueSeOV1RJkD02dukUotih37OfAM/rY.ufGa', '2025-10-10 15:49:13', '2025-10-10 15:44:13'),
(26, '', '9003675556', '$2y$10$K2IHNmxqRXDrJ9FRufLueOF7VrOkJYxckJFY6k71OVYw0p1ObJGOK', '2025-10-10 15:55:49', '2025-10-10 15:50:49'),
(27, 'Guna', '9003675557', '$2y$10$fkdcJq2Mf8QyGyPJJ4ktX.FQ4GmtcT2tpuCe.xK9LvrGKTBiqkd0i', '2025-10-10 15:59:31', '2025-10-10 15:54:31'),
(28, 'Guna', '9003675557', '$2y$10$iBNilTFdftPGoQHVicuBVegkqyEhxurPYR6FF.UoKidxj6BSek3wi', '2025-10-10 16:22:45', '2025-10-10 16:17:45'),
(29, 'Bala', '9807060504', '$2y$10$HulQ7xLkIIqr6TW/AIOvyOm3Tc/EJgg/9fdqLfVFQDDejjK/xA5cK', '2025-10-25 12:22:11', '2025-10-25 12:17:11'),
(30, 'Bala', '9807060504', '$2y$10$UYtbsc3Xhnog.348SnpaMuaI.oOwc7T6teXGJ6CRvhJlkOpUW9ac2', '2025-11-01 11:23:14', '2025-11-01 11:18:14'),
(31, 'Bala', '9807060504', '$2y$10$28uHIv2MjfJvejbUIN6zmuTsXJAlkB0kE5MGyCXb5GtZV.fHpLAAS', '2025-11-03 12:11:59', '2025-11-03 12:06:59');

-- --------------------------------------------------------

--
-- Table structure for table `otp_sessions`
--

CREATE TABLE `otp_sessions` (
  `id` int(11) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_sessions`
--

INSERT INTO `otp_sessions` (`id`, `mobile`, `slug`, `otp`, `expires_at`, `created_at`) VALUES
(1, '9876543210', 'f586897889', '6521', '2025-11-03 07:29:05', '2025-11-01 07:28:06');

-- --------------------------------------------------------

--
-- Table structure for table `payment_credentials`
--

CREATE TABLE `payment_credentials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `encrypted_key` text DEFAULT NULL,
  `encrypted_secret` text DEFAULT NULL,
  `iv` text DEFAULT NULL,
  `owner_key_hash` varchar(128) DEFAULT NULL,
  `payments_enabled` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_credentials`
--

INSERT INTO `payment_credentials` (`id`, `user_id`, `created_at`, `encrypted_key`, `encrypted_secret`, `iv`, `owner_key_hash`, `payments_enabled`) VALUES
(1, 2, '2025-09-24 10:50:09', '7CaurTwEC8vc0bEkp/51l0iklcfMhCy3wwhFNaFtW/E=', '1N73Ke3nGMLNwCGMqtKg21iwLylKrE+ndDvMKtK3I74=', 'hyyYeEknnxflFExJIjksyg==::J8QCcBlH0jf3zxJH/owCJA==', '5', 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_type_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `user_id`, `business_type_id`, `name`, `price`, `unit`, `image`, `description`, `created_at`, `category_id`, `subcategory_id`, `updated_at`, `is_active`) VALUES
(1, 2, 1, 'Dosa', 50.00, 'Plate', NULL, NULL, '2025-09-19 15:52:33', NULL, NULL, NULL, 1),
(2, 2, 1, 'Masala Dosa', 60.00, 'Plate', NULL, NULL, '2025-09-19 15:56:55', NULL, NULL, NULL, 1),
(3, 2, 1, 'Idly', 20.00, 'Plate', NULL, NULL, '2025-09-19 16:21:08', NULL, NULL, NULL, 1),
(4, 2, 1, 'Poori', 40.00, 'Plate', NULL, NULL, '2025-09-19 16:21:08', NULL, NULL, NULL, 1),
(5, 2, 1, 'Pongal', 50.00, 'Plate', NULL, NULL, '2025-09-19 16:21:08', NULL, NULL, NULL, 1),
(6, 2, 1, 'Vada', 10.00, 'Plate', NULL, NULL, '2025-09-19 16:21:08', NULL, NULL, NULL, 1),
(7, 2, 1, 'Rava Dosa', 40.00, 'Plate', NULL, NULL, '2025-09-19 16:21:08', NULL, NULL, NULL, 1),
(8, 2, 1, 'Podi Idly', 80.00, 'Plate', NULL, NULL, '2025-09-19 16:21:08', NULL, NULL, '2025-10-10 11:57:01', 0),
(10, 4, 5, 'apple', 300.00, 'dozen', NULL, NULL, '2025-09-25 11:09:18', NULL, NULL, NULL, 1),
(11, 4, 5, 'banana', 60.00, 'dozen', NULL, NULL, '2025-09-25 11:16:11', NULL, NULL, NULL, 1),
(12, 4, 5, 'mushroom pizza', 180.00, 'plate', NULL, NULL, '2025-09-25 11:16:11', 2, 2, NULL, 1),
(13, 4, 5, 'mango', 300.00, 'kg', NULL, NULL, '2025-09-25 11:16:11', NULL, NULL, NULL, 1),
(15, 4, 5, 'pineapple', 90.00, 'pcs', NULL, NULL, '2025-09-25 11:16:11', NULL, NULL, NULL, 1),
(16, 4, 5, 'watermelon', 40.00, 'kg', NULL, NULL, '2025-09-25 11:16:11', NULL, NULL, NULL, 1),
(17, 4, 5, 'papaya', 70.00, 'kg', NULL, NULL, '2025-09-25 11:16:11', NULL, NULL, NULL, 1),
(18, 4, 5, 'strawberry', 450.00, 'kg', NULL, NULL, '2025-09-25 11:16:11', NULL, NULL, NULL, 1),
(19, 4, 5, 'guava', 110.00, 'kg', NULL, NULL, '2025-09-25 11:16:11', NULL, NULL, NULL, 1),
(20, 5, 5, 'apple', 250.00, 'kg', NULL, NULL, '2025-09-26 12:18:55', NULL, NULL, NULL, 1),
(21, 5, 5, 'banana', 60.00, 'dozen', NULL, NULL, '2025-09-26 12:19:39', NULL, NULL, NULL, 1),
(22, 5, 5, 'orange', 120.00, 'kg', NULL, NULL, '2025-09-26 12:19:39', NULL, NULL, NULL, 1),
(23, 5, 5, 'mango', 300.00, 'kg', NULL, NULL, '2025-09-26 12:19:39', NULL, NULL, NULL, 1),
(24, 5, 5, 'grapes', 180.00, 'kg', NULL, NULL, '2025-09-26 12:19:39', NULL, NULL, NULL, 1),
(25, 5, 5, 'pineapple', 90.00, 'pcs', NULL, NULL, '2025-09-26 12:19:39', NULL, NULL, NULL, 1),
(26, 5, 5, 'watermelon', 40.00, 'kg', NULL, NULL, '2025-09-26 12:19:39', NULL, NULL, NULL, 1),
(27, 5, 5, 'papaya', 70.00, 'kg', NULL, NULL, '2025-09-26 12:19:39', NULL, NULL, NULL, 1),
(28, 5, 5, 'strawberry', 450.00, 'kg', NULL, NULL, '2025-09-26 12:19:39', NULL, NULL, NULL, 1),
(29, 5, 5, 'guava', 110.00, 'kg', NULL, NULL, '2025-09-26 12:19:39', NULL, NULL, NULL, 1),
(30, 4, 5, 'butter naan', 50.00, 'plate', NULL, NULL, '2025-09-29 10:36:58', 1, 1, NULL, 1),
(31, 4, 5, 'paneer butter masala', 180.00, 'plate', NULL, NULL, '2025-09-29 10:58:26', 1, 3, NULL, 1),
(32, 4, 5, 'veg burger', 120.00, 'piece', NULL, NULL, '2025-09-29 10:58:26', 3, 4, NULL, 1),
(33, 4, 5, 'cold coffee', 100.00, 'glass', NULL, NULL, '2025-09-29 10:58:26', 4, 5, NULL, 1),
(34, 4, 5, 'masala dosa', 80.00, 'plate', NULL, NULL, '2025-09-29 10:58:26', NULL, NULL, NULL, 1),
(35, 4, 5, 'chicken biryani', 250.00, 'plate', NULL, NULL, '2025-09-29 10:58:26', 6, 7, NULL, 1),
(36, 4, 5, 'french fries', 90.00, 'plate', NULL, NULL, '2025-09-29 10:58:26', 3, 8, NULL, 1),
(37, 4, 5, 'green tea', 60.00, 'cup', NULL, NULL, '2025-09-29 10:58:26', 4, 9, NULL, 1),
(38, 4, 5, 'ice cream', 70.00, 'cup', NULL, NULL, '2025-09-29 10:58:26', 7, 10, NULL, 1),
(39, 4, 5, 'veg hakka', 130.00, 'plate', NULL, NULL, '2025-09-29 12:25:37', 9, 12, NULL, 1),
(40, 4, 5, 'veg hakka noodles', 120.00, 'plate', NULL, NULL, '2025-09-29 12:28:12', NULL, NULL, NULL, 1),
(41, 4, 5, 'veg hakka noodles', 120.00, 'plate', NULL, NULL, '2025-09-29 12:30:27', 8, 11, NULL, 1),
(42, 4, 5, 'veg hakka noodles', 120.00, 'plate', NULL, NULL, '2025-09-29 12:44:26', 9, 12, NULL, 1),
(43, 4, 5, 'paneer butter masala', 250.00, 'plate', NULL, NULL, '2025-09-29 12:44:26', 9, 13, NULL, 1),
(44, 4, 5, 'chicken biryani', 200.00, 'plate', NULL, NULL, '2025-09-29 12:44:26', 9, 14, NULL, 1),
(45, 4, 5, 'chocolate cake', 150.00, 'slice', NULL, NULL, '2025-09-29 12:44:26', 7, 15, NULL, 1),
(46, 4, 5, 'veg spring roll', 50.00, 'pcs', NULL, NULL, '2025-09-29 12:44:26', 10, 16, NULL, 1),
(47, 1, 5, 'veg fried rice', 120.00, 'plate', NULL, NULL, '2025-10-07 10:33:08', 11, 17, NULL, 1),
(48, 1, 5, 'paneer tikka', 150.00, 'plate', NULL, NULL, '2025-10-07 10:33:08', 12, 18, NULL, 1),
(49, 2, 1, 'veg fried rice', 120.00, 'plate', NULL, NULL, '2025-10-07 10:37:26', 13, 19, NULL, 1),
(50, 2, 1, 'paneer tikka', 150.00, 'plate', NULL, NULL, '2025-10-07 10:37:26', 14, 20, NULL, 1),
(51, 2, 1, 'veg fried chicken rice', 120.00, 'plate', NULL, NULL, '2025-10-07 10:47:48', 13, 19, NULL, 1),
(52, 2, 1, 'paneer tikka aathan aama', 150.00, 'plate', NULL, NULL, '2025-10-07 10:47:48', 14, 20, NULL, 1),
(53, 2, 1, 'veg fried chicken rice 222', 120.00, 'plate', NULL, NULL, '2025-10-07 10:53:13', 13, 19, NULL, 1),
(54, 2, 1, 'paneer tikka aathan aama222', 150.00, 'plate', NULL, NULL, '2025-10-07 10:53:13', 14, 20, NULL, 1),
(55, 2, 1, 'veg fried chicken rice 222222222', 120.00, 'plate', NULL, NULL, '2025-10-07 11:00:21', 13, 19, NULL, 1),
(56, 2, 1, 'paneer tikka aathan aama22222222', 150.00, 'plate', NULL, NULL, '2025-10-07 11:00:21', 14, 20, NULL, 1),
(57, 2, 1, 'veg fried chicken rice 2', 120.00, 'plate', 'products/uploads/2025-10-07/prod_68e4a9685bb18.png', NULL, '2025-10-07 11:17:20', 13, 19, NULL, 1),
(58, 2, 1, 'paneer tikka aathan aam', 150.00, 'plate', 'products/uploads/2025-10-07/prod_68e4a96862868.png', NULL, '2025-10-07 11:17:20', 14, 20, NULL, 1),
(59, 2, 1, 'veg fried chicken rice 21', 120.00, 'plate', 'products/uploads/2025-10-07/prod_68e4ab268d90c.png', NULL, '2025-10-07 11:24:46', 13, 19, NULL, 1),
(60, 2, 1, 'paneer tikka aathan aam 1', 150.00, 'plate', 'products/uploads/2025-10-07/prod_68e4ab268ef9d.png', NULL, '2025-10-07 11:24:46', 14, 20, NULL, 1),
(61, 2, 1, 'butter naan1', 50.00, 'plate', 'products/uploads/2025-10-07/prod_68e4abcac8709.png', NULL, '2025-10-07 11:27:30', 15, 21, NULL, 1),
(62, 2, 1, 'paneer butter masala1', 180.00, 'plate', 'products/uploads/2025-10-07/prod_68e4abcaca320.png', NULL, '2025-10-07 11:27:30', 15, 22, NULL, 1),
(63, 2, 1, 'mushroom pizza1', 200.00, 'plate', 'products/uploads/2025-10-07/prod_68e4abcad0d56.png', NULL, '2025-10-07 11:27:30', 16, 23, NULL, 1),
(64, 2, 1, 'veg burger1', 120.00, 'piece', NULL, NULL, '2025-10-07 11:27:30', 17, 24, NULL, 1),
(65, 2, 1, 'cold coffee', 100.00, 'glass', NULL, NULL, '2025-10-07 11:27:30', 18, 25, NULL, 1),
(66, 2, 1, 'masala dosa', 80.00, 'plate', NULL, NULL, '2025-10-07 11:27:30', 19, 26, NULL, 1),
(67, 2, 1, 'chicken biryani', 250.00, 'plate', NULL, NULL, '2025-10-07 11:27:30', 20, 27, NULL, 1),
(68, 2, 1, 'french fries', 90.00, 'plate', NULL, NULL, '2025-10-07 11:27:30', 17, 28, NULL, 1),
(69, 2, 1, 'green tea', 60.00, 'cup', NULL, NULL, '2025-10-07 11:27:30', 18, 29, NULL, 1),
(70, 2, 1, 'ice cream', 70.00, 'cup', NULL, NULL, '2025-10-07 11:27:30', 21, 30, NULL, 1),
(71, 6, 5, 'butter naan1', 50.00, 'piece', 'products/uploads/2025-10-07/prod_68e4af8bdd9e9.png', NULL, '2025-10-07 11:43:31', 22, 31, NULL, 1),
(72, 6, 5, 'paneer butter masala1', 180.00, 'piece', 'products/uploads/2025-10-07/prod_68e4af8bdf132.png', NULL, '2025-10-07 11:43:31', 22, 32, NULL, 1),
(73, 6, 5, 'mushroom pizza1', 200.00, 'piece', 'products/uploads/2025-10-07/prod_68e4af8be0d9f.png', NULL, '2025-10-07 11:43:31', 23, 33, NULL, 1),
(74, 6, 5, 'veg burger1', 120.00, 'piece', NULL, NULL, '2025-10-07 11:43:31', 24, 34, NULL, 1),
(75, 6, 5, 'cold coffee', 100.00, 'piece', NULL, NULL, '2025-10-07 11:43:31', 25, 35, NULL, 1),
(76, 6, 5, 'masala dosa', 80.00, 'piece', NULL, NULL, '2025-10-07 11:43:31', 26, 36, NULL, 1),
(77, 6, 5, 'chicken biryani', 250.00, 'piece', NULL, NULL, '2025-10-07 11:43:31', 27, 37, NULL, 1),
(78, 6, 5, 'french fries', 90.00, 'piece', NULL, NULL, '2025-10-07 11:43:31', 24, 38, NULL, 1),
(79, 6, 5, 'green tea', 60.00, 'piece', NULL, NULL, '2025-10-07 11:43:31', 25, 39, NULL, 1),
(80, 6, 5, 'ice cream', 70.00, 'piece', NULL, NULL, '2025-10-07 11:43:31', 28, 40, NULL, 1),
(81, 6, 5, 'butter naan12', 50.00, 'piece', 'products/uploads/2025-10-07/prod_68e4b179b3e5c.png', NULL, '2025-10-07 11:51:45', 22, 31, NULL, 1),
(82, 6, 5, 'paneer butter masala12', 180.00, 'piece', 'products/uploads/2025-10-07/prod_68e4b179b59e5.png', NULL, '2025-10-07 11:51:45', 22, 32, NULL, 1),
(83, 6, 5, 'mushroom pizza12', 200.00, 'piece', 'products/uploads/2025-10-07/prod_68e4b179b7265.png', NULL, '2025-10-07 11:51:45', 23, 33, NULL, 1),
(84, 6, 5, 'butter naan123', 50.00, 'piece', 'products/uploads/2025-10-07/prod_68e4b41117324.png', NULL, '2025-10-07 12:02:49', 22, 31, NULL, 1),
(85, 6, 5, 'paneer butter masala123', 180.00, 'piece', 'products/uploads/2025-10-07/prod_68e4b4111ae38.png', NULL, '2025-10-07 12:02:49', 22, 32, NULL, 1),
(86, 6, 5, 'mushroom pizza123', 200.00, 'piece', 'products/uploads/2025-10-07/prod_68e4b4111d6ca.png', NULL, '2025-10-07 12:02:49', 23, 33, NULL, 1),
(87, 6, 5, 'veg hakka noodles', 120.00, 'piece', NULL, NULL, '2025-10-07 12:05:17', NULL, NULL, NULL, 1),
(88, 6, 5, 'apple1', 80.00, 'kg', 'products/uploads/2025-10-10/prod_68e8b46871b91.jpg', NULL, '2025-10-10 12:53:21', 29, 41, NULL, 1),
(89, 6, 5, 'banana1', 50.00, 'dozen', 'products/uploads/2025-10-10/prod_68e8b4693fd20.jpg', NULL, '2025-10-10 12:53:22', 29, 41, NULL, 1),
(90, 6, 5, 'milk1', 60.00, 'ltr', NULL, 'schavsccvufv', '2025-10-10 12:57:24', 30, 42, NULL, 1),
(91, 6, 5, 'cheese1', 250.00, 'kg', NULL, 'jwvcuwdvu', '2025-10-10 12:57:25', 30, 42, NULL, 1),
(92, 6, 5, 'tomato1', 40.00, 'kg', 'products/uploads/2025-10-10/prod_68e8b55d6720a.jpg', 'digwuid', '2025-10-10 12:57:26', 31, 43, NULL, 1),
(93, 6, 5, 'potato1', 30.00, 'kg', NULL, 'sssqefqwqywyw', '2025-10-10 12:57:27', 31, 43, NULL, 1),
(94, 6, 5, 'turmeric', 60.00, 'kg', 'products/uploads/2025-10-10/prod_68e8b75a7e5c1.jpg', 'podaaa aandavaney namma pakkam', '2025-10-10 13:05:54', 25, 44, NULL, 1),
(95, 10, 1, 'apple1', 80.00, 'kg', 'products/uploads/2025-10-25/prod_68fc7303ed656.jpg', 'allalaoihfab', '2025-10-25 12:19:40', 32, 45, NULL, 1),
(96, 10, 1, 'banana1', 50.00, 'dozen', 'products/uploads/2025-10-25/prod_68fc7304b8d89.jpg', 'fqcqyfuqyud', '2025-10-25 12:19:41', 32, 45, NULL, 1),
(97, 10, 1, 'apple10', 800.00, 'kg', 'products/uploads/2025-10-25/prod_68fc73545c25f.jpg', 'allalaoihfab', '2025-10-25 12:21:01', 32, 45, NULL, 1),
(98, 10, 1, 'banana10', 150.00, 'dozen', 'products/uploads/2025-10-25/prod_68fc735526e67.jpg', 'fqcqyfuqyud', '2025-10-25 12:21:02', 32, 45, NULL, 1),
(99, 10, 1, 'milk new10', 111.00, 'ltr', 'products/uploads/2025-10-25/prod_68fc73560e6c9.jpg', 'schavsccvufv', '2025-10-25 12:21:03', 33, 46, NULL, 1),
(100, 10, 1, 'cheese new10', 222.00, 'kg', 'products/uploads/2025-10-25/prod_68fc735729ac3.jpg', 'jwvcuwdvu', '2025-10-25 12:21:04', 33, 46, NULL, 1),
(101, 10, 1, 'tomato new10', 33.00, 'kg', 'products/uploads/2025-10-25/prod_68fc7358a8e4a.jpg', 'digwuid', '2025-10-25 12:21:05', 34, 47, NULL, 1),
(102, 10, 1, 'potato new10', 35.00, 'kg', 'products/uploads/2025-10-25/prod_68fc735984447.jpg', 'sssqefqwqywyw', '2025-10-25 12:21:06', 34, 47, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `qr_codes`
--

CREATE TABLE `qr_codes` (
  `qr_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_type_id` int(11) NOT NULL,
  `qr_slug` varchar(100) NOT NULL,
  `table_no` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_codes`
--

INSERT INTO `qr_codes` (`qr_id`, `user_id`, `business_type_id`, `qr_slug`, `table_no`, `created_at`) VALUES
(1, 2, 1, 'd226071eb4', '1', '2025-09-20 12:51:56'),
(2, 2, 1, 'ccf195eb5f', '2', '2025-09-20 13:28:02'),
(3, 2, 1, '07c542a226', '2', '2025-09-20 13:29:13'),
(4, 2, 1, '1d645e7b8e', '2', '2025-09-20 13:32:52'),
(5, 4, 5, '20af83c85b', '1', '2025-09-25 12:01:32'),
(6, 5, 5, 'a26821067c', '1', '2025-09-26 12:20:36'),
(7, 4, 5, 'c9292df746', '2', '2025-09-30 12:04:23'),
(8, 10, 1, '49c361e3e8', '1', '2025-10-25 12:21:38'),
(9, 10, 1, 'a058847336', '1', '2025-10-25 12:22:41'),
(10, 10, 1, 'f586897889', '1', '2025-10-25 12:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `qr_scans`
--

CREATE TABLE `qr_scans` (
  `scan_id` int(11) NOT NULL,
  `qr_id` int(11) NOT NULL,
  `scanned_at` datetime DEFAULT current_timestamp(),
  `device_info` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qr_sessions`
--

CREATE TABLE `qr_sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_sessions`
--

INSERT INTO `qr_sessions` (`id`, `session_id`, `slug`, `mobile`, `customer_name`, `created_at`) VALUES
(1, 'sess_6905b6efeeaeb4.21096534', 'f586897889', '9876543210', 'vijay', '2025-11-01 07:29:51');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `last_activity` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `token`, `last_activity`) VALUES
(1, 1, '5ecdb83ca5bfa2062749b9757800556c9c91421f3f8efa88c6ecd8031bc41ff9', '2025-09-18 16:20:04'),
(3, 2, '32af6de8f2ea2636c6e2b18bd4427ceb6c2001724fd2f6889f770c79ab7b8b80', '2025-09-19 16:27:43'),
(4, 2, '4fd6c26159b652cd296dbe4361c55af6386e6cc69df83ce4289d215b1502e683', '2025-09-20 13:32:52'),
(5, 2, 'a8f78c478ed94b068c1a8c005b9c4df0cdab3c871e2d2faf015141065ccef89f', '2025-09-23 12:10:25'),
(6, 2, '63d16fa3592f1a6c239fbe13b9d341443be3900bc82e09fa26660996b764b03d', '2025-09-23 16:18:25'),
(9, 3, '06e89694236625e50d417db463c8e3b0e59b8ef954fa924e1ba0e82e0d4a6fe5', '2025-09-24 16:23:28'),
(10, 4, '0e757644f86a484f37424802304049fe23c92abbac6d9ad93c0ed01348e245b7', '2025-09-24 16:41:05'),
(11, 4, 'baea1c383c3c2976547e579241923a7ddf6ed4f94ee1333adf7a80e0c58bb5d9', '2025-09-25 10:55:35'),
(12, 4, 'b1e32829b11a3067f5bf224667f0e5366d8aafa57a591d332f77d8ffbab88a52', '2025-09-25 12:40:54'),
(13, 5, '47d9bb16b80effcf9cfe29443d22eae981ad660ca8d0184289d4f16930974ccc', '2025-09-26 12:20:36'),
(14, 4, '29d74222d1d06130a6fdca10db31056021675784c6405e615aaa76ef400923e2', '2025-09-29 13:05:38'),
(15, 4, 'd3242e4ebf51b6526abab41ec42fd7e96e1a36fb32ef72e410b5c1d6cfbb827e', '2025-09-30 12:04:23'),
(16, 1, 'c43f7c68fd19efd1f2104bd43d9f3bc9d9bb81fe90be582b08dbb8e5cb15f3d8', '2025-10-07 10:33:08'),
(17, 2, 'c891ed4d7309078e0dc30c1d93d8c1b44f215c4e91cdfae2b54bdb69e6f203dd', '2025-10-07 11:42:06'),
(18, 6, 'ad4c78e257519e29204d94974677a79755515f166b73b2ab1f529686bf334599', '2025-10-07 12:50:54'),
(19, 2, '70ae77441920544ff22ac474fb73e4ec2f9ea0843ba4d047559478cf59175c7b', '2025-10-10 12:13:36'),
(20, 6, '1fc4a0c4b5c771742d6e1cd62cf34000c31caed00d0d918b3672e2ef96668144', '2025-10-10 13:05:54'),
(21, 7, '09d000c4a9fec8b278beb5c37df742b5c2de74f22d5b59688465187dd08de619', '2025-10-10 15:44:48'),
(22, 7, '03e0806b4582fc66e3733d7701386c28e85f69ef63fcc1dd9bd081b373cf7660', '2025-10-10 15:45:05'),
(23, 7, 'c4db3a112dec3a47860bfea279b0866cf2d6b3deb9f29439e1d8ebd48d4fced2', '2025-10-10 15:45:20'),
(24, 7, '29bf48f094bd1086b8afce875b37a3d54cca7fb5e09fbe85a6506550a2855f0b', '2025-10-10 15:45:21'),
(25, 7, 'add4a86da8febf1b75594e0fc984b4ee0173c42a729e941f90f816f5d2c68ab4', '2025-10-10 15:45:22'),
(26, 7, 'acb05384e7787c9cb6094617222651f1b5a088f4ce021b74de75dc030500959e', '2025-10-10 15:50:37'),
(27, 8, '92d89925d80f25edeb69d3d6a28eb5b73d14f90fc347759c772de1b9aeea21ef', '2025-10-10 15:51:06'),
(28, 9, '4df97f62e3be5326b4433dba0a20108462213db3f81d6ade6cac3db9efda3b8e', '2025-10-10 15:55:20'),
(29, 9, '6fc86da846d13eb4d58c40155fabe57fdf3ac570e41e5d259d9dec7226b115a4', '2025-10-10 16:30:23'),
(30, 10, '176a9958c490ea5d4eae086d6baa781509252490abe0ad8f3b0f4b6cc03d62dd', '2025-10-25 12:33:49'),
(31, 10, '9a146115c4030d2af8c06c18c3ae57d69fd7536cfa37f63c754605630088fed4', '2025-11-01 12:37:34'),
(32, 10, 'adc554c347143394f7381214e05a0dd22d9fdd731d59b806150449b955b92f54', '2025-11-01 13:28:50'),
(33, 10, 'b8eeaccd0492975176b3ac04cfc4b168f115fad6dd4017357381305b03e2e503', '2025-11-03 12:20:30');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `display_name` varchar(150) DEFAULT NULL,
  `role` enum('waiter','chef','manager') DEFAULT 'waiter',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `user_id`, `username`, `password_hash`, `display_name`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 10, 'Sunil', '$2y$10$JT1v3JkshDVmUZ14E83f4uwB1MTpFDglrFLMWcm1qzK4IrFveZkp.', 'mairuu', 'waiter', 1, '2025-11-01 12:37:34', '2025-11-03 12:19:31');

-- --------------------------------------------------------

--
-- Table structure for table `staff_sessions`
--

CREATE TABLE `staff_sessions` (
  `id` bigint(20) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `token` varchar(128) NOT NULL,
  `last_activity` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff_sessions`
--

INSERT INTO `staff_sessions` (`id`, `staff_id`, `token`, `last_activity`, `created_at`) VALUES
(3, 2, '0a7045f9457309da0b2eda43669aae25686f88a9e6c10221133d5329624a6eaa', '2025-11-03 12:19:31', '2025-11-03 12:15:56');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `subcategory_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`subcategory_id`, `category_id`, `name`, `created_at`) VALUES
(1, 1, 'breads', '2025-09-29 10:36:58'),
(2, 2, 'pizza', '2025-09-29 10:40:29'),
(3, 1, 'curries', '2025-09-29 10:58:26'),
(4, 3, 'burgers', '2025-09-29 10:58:26'),
(5, 4, 'coffee', '2025-09-29 10:58:26'),
(7, 6, 'biryani', '2025-09-29 10:58:26'),
(8, 3, 'snacks', '2025-09-29 10:58:26'),
(9, 4, 'tea', '2025-09-29 10:58:26'),
(10, 7, 'noodles', '2025-09-29 10:58:26'),
(11, 8, 'noodles', '2025-09-29 12:23:18'),
(12, 9, 'noodles', '2025-09-29 12:38:12'),
(13, 9, 'curries', '2025-09-29 12:44:26'),
(14, 9, 'rice', '2025-09-29 12:44:26'),
(15, 7, 'cakes', '2025-09-29 12:44:26'),
(16, 10, 'snacks', '2025-09-29 12:44:26'),
(17, 11, 'veg', '2025-10-07 10:27:56'),
(18, 12, 'veg', '2025-10-07 10:33:08'),
(19, 13, 'veg', '2025-10-07 10:37:26'),
(20, 14, 'veg', '2025-10-07 10:37:26'),
(21, 15, 'breads', '2025-10-07 11:27:30'),
(22, 15, 'curries', '2025-10-07 11:27:30'),
(23, 16, 'pizza', '2025-10-07 11:27:30'),
(24, 17, 'burgers', '2025-10-07 11:27:30'),
(25, 18, 'coffee', '2025-10-07 11:27:30'),
(26, 19, 'dosa', '2025-10-07 11:27:30'),
(27, 20, 'biryani', '2025-10-07 11:27:30'),
(28, 17, 'snacks', '2025-10-07 11:27:30'),
(29, 18, 'tea', '2025-10-07 11:27:30'),
(30, 21, 'ice creams', '2025-10-07 11:27:30'),
(31, 22, 'breads', '2025-10-07 11:43:31'),
(32, 22, 'curries', '2025-10-07 11:43:31'),
(33, 23, 'pizza', '2025-10-07 11:43:31'),
(34, 24, 'burgers', '2025-10-07 11:43:31'),
(35, 25, 'coffee', '2025-10-07 11:43:31'),
(36, 26, 'dosa', '2025-10-07 11:43:31'),
(37, 27, 'biryani', '2025-10-07 11:43:31'),
(38, 24, 'snacks', '2025-10-07 11:43:31'),
(39, 25, 'tea', '2025-10-07 11:43:31'),
(40, 28, 'ice creams', '2025-10-07 11:43:31'),
(41, 29, 'fresh fruits', '2025-10-10 12:32:45'),
(42, 30, 'milk products', '2025-10-10 12:57:23'),
(43, 31, 'fresh vegetables', '2025-10-10 12:57:25'),
(44, 25, 'milkshakes', '2025-10-10 13:05:54'),
(45, 32, 'fresh fruits', '2025-10-25 12:19:39'),
(46, 33, 'milk products', '2025-10-25 12:19:41'),
(47, 34, 'fresh vegetables', '2025-10-25 12:21:04');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `sub_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan` enum('free','yearly') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','expired') DEFAULT 'active',
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`sub_id`, `user_id`, `plan`, `start_date`, `end_date`, `status`, `meta`) VALUES
(1, 2, 'free', '2025-09-19', '2025-09-26', 'expired', '{\"allowed_waiters\": -1}'),
(2, 1, 'free', '2025-09-24', '2025-10-01', 'active', '{\"allowed_waiters\": -1}'),
(3, 3, 'free', '2025-09-24', '2025-10-01', 'active', '{\"allowed_waiters\": -1}'),
(4, 4, 'free', '2025-09-24', '2025-10-01', 'active', '{\"allowed_waiters\": -1}'),
(5, 5, 'free', '2025-09-26', '2025-10-03', 'active', '{\"allowed_waiters\": -1}'),
(6, 6, 'free', '2025-10-07', '2025-10-14', 'active', '{\"allowed_waiters\": -1}'),
(7, 10, 'free', '2025-10-25', '2025-11-01', 'active', '{\"allowed_waiters\": -1}');

-- --------------------------------------------------------

--
-- Table structure for table `template_fields`
--

CREATE TABLE `template_fields` (
  `field_id` int(11) NOT NULL,
  `business_type_id` int(11) NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `field_type` enum('text','number','kg','litre','piece','gram') DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `template_fields`
--

INSERT INTO `template_fields` (`field_id`, `business_type_id`, `field_name`, `field_type`) VALUES
(1, 1, 'Plate', 'number'),
(2, 1, 'Bowl', 'number'),
(3, 1, 'Qty', 'number'),
(4, 1, 'Litres', 'litre'),
(5, 2, 'Kg', 'kg'),
(6, 2, 'Gram', 'gram'),
(7, 2, 'Packet', 'number'),
(8, 2, 'Piece', 'number'),
(9, 2, 'Litres', 'litre'),
(10, 3, 'Kg', 'kg'),
(11, 3, 'Dozen', 'number'),
(12, 3, 'Piece', 'number'),
(13, 3, 'Box', 'number'),
(14, 4, 'Qty', 'number'),
(15, 4, 'Metre', 'number'),
(16, 4, 'Litre', 'litre'),
(17, 4, 'Piece', 'number'),
(18, 4, 'Pack', 'number'),
(19, 5, 'Kg', 'kg'),
(20, 5, 'Piece', 'number'),
(21, 5, 'Box', 'number'),
(22, 6, 'Piece', 'number'),
(23, 6, 'Metre', 'number'),
(24, 6, 'Set', 'number'),
(25, 6, 'Size (S/M/L/XL)', 'text'),
(26, 7, 'Piece', 'number'),
(27, 7, 'Pack', 'number'),
(28, 7, 'Warranty', 'text'),
(29, 8, 'Strip', 'number'),
(30, 8, 'Bottle', 'number'),
(31, 8, 'Tube', 'number'),
(32, 8, 'Box', 'number'),
(33, 9, 'Piece', 'number'),
(34, 9, 'Packet', 'number'),
(35, 9, 'Dozen', 'number'),
(36, 10, 'Trainer Name', 'text'),
(37, 11, 'gram', 'gram'),
(38, 11, 'kg', 'kg'),
(39, 5, 'dozen', ''),
(41, 5, 'ltr', 'text'),
(42, 1, 'kg', 'text'),
(43, 1, 'dozen', 'text'),
(44, 1, 'ltr', 'text');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `photo` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `selected_template_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `mobile`, `name`, `photo`, `created_at`, `selected_template_id`) VALUES
(1, '9360552619', 'dheeran sriram', '', '2025-09-18 16:20:04', 5),
(2, '9876543210', 'arun kumar', '', '2025-09-19 12:25:51', 1),
(3, '9360556666', 'thulasi', '', '2025-09-24 16:17:41', 5),
(4, '9360556662', 'thulasi1', '', '2025-09-24 16:24:35', 5),
(5, '9360556622', 'karthick', '', '2025-09-26 12:14:18', 5),
(6, '9003673183', 'neelamegam', '', '2025-10-07 11:34:48', 5),
(7, '9003675555', 'gu', '', '2025-10-10 15:44:48', NULL),
(8, '9003675556', 'gu', '', '2025-10-10 15:51:06', NULL),
(9, '9003675557', 'g', 'uploads/1760094023_Screenshot 2024-10-09 210518.png', '2025-10-10 15:55:20', NULL),
(10, '9807060504', 'bala', '', '2025-10-25 12:17:44', 1);

-- --------------------------------------------------------

--
-- Table structure for table `webhook_logs`
--

CREATE TABLE `webhook_logs` (
  `id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `payload` text NOT NULL,
  `received_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `business_types`
--
ALTER TABLE `business_types`
  ADD PRIMARY KEY (`business_type_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `business_type_id` (`business_type_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `orders_ibfk_1` (`qr_id`),
  ADD KEY `fk_orders_received_by` (`received_by_staff_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `otp_sessions`
--
ALTER TABLE `otp_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_mobile_slug` (`mobile`,`slug`);

--
-- Indexes for table `payment_credentials`
--
ALTER TABLE `payment_credentials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `business_type_id` (`business_type_id`),
  ADD KEY `fk_products_category` (`category_id`),
  ADD KEY `fk_products_subcategory` (`subcategory_id`);

--
-- Indexes for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD PRIMARY KEY (`qr_id`),
  ADD UNIQUE KEY `qr_slug` (`qr_slug`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `business_type_id` (`business_type_id`);

--
-- Indexes for table `qr_scans`
--
ALTER TABLE `qr_scans`
  ADD PRIMARY KEY (`scan_id`),
  ADD KEY `qr_id` (`qr_id`);

--
-- Indexes for table `qr_sessions`
--
ALTER TABLE `qr_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_session` (`session_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `staff_sessions`
--
ALTER TABLE `staff_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`subcategory_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`sub_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `template_fields`
--
ALTER TABLE `template_fields`
  ADD PRIMARY KEY (`field_id`),
  ADD KEY `business_type_id` (`business_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `mobile` (`mobile`);

--
-- Indexes for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `business_types`
--
ALTER TABLE `business_types`
  MODIFY `business_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `otp_sessions`
--
ALTER TABLE `otp_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_credentials`
--
ALTER TABLE `payment_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `qr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `qr_scans`
--
ALTER TABLE `qr_scans`
  MODIFY `scan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qr_sessions`
--
ALTER TABLE `qr_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff_sessions`
--
ALTER TABLE `staff_sessions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `sub_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `template_fields`
--
ALTER TABLE `template_fields`
  MODIFY `field_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `webhook_logs`
--
ALTER TABLE `webhook_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `categories_ibfk_2` FOREIGN KEY (`business_type_id`) REFERENCES `business_types` (`business_type_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_received_by` FOREIGN KEY (`received_by_staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`qr_id`) REFERENCES `qr_codes` (`qr_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_credentials`
--
ALTER TABLE `payment_credentials`
  ADD CONSTRAINT `payment_credentials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_subcategory` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`business_type_id`) REFERENCES `business_types` (`business_type_id`);

--
-- Constraints for table `qr_codes`
--
ALTER TABLE `qr_codes`
  ADD CONSTRAINT `qr_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `qr_codes_ibfk_2` FOREIGN KEY (`business_type_id`) REFERENCES `business_types` (`business_type_id`);

--
-- Constraints for table `qr_scans`
--
ALTER TABLE `qr_scans`
  ADD CONSTRAINT `qr_scans_ibfk_1` FOREIGN KEY (`qr_id`) REFERENCES `qr_codes` (`qr_id`);

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_sessions`
--
ALTER TABLE `staff_sessions`
  ADD CONSTRAINT `staff_sessions_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `template_fields`
--
ALTER TABLE `template_fields`
  ADD CONSTRAINT `template_fields_ibfk_1` FOREIGN KEY (`business_type_id`) REFERENCES `business_types` (`business_type_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
