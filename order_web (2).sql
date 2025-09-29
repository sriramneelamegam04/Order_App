-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2025 at 09:36 AM
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
(21, 'sess_68d6381512c421.83012927', 0, '2025-09-26 12:23:15');

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
(10, 4, 5, 'main course', '2025-09-29 12:44:26');

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
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `qr_id`, `customer_name`, `customer_mobile`, `status`, `total`, `payment_method`, `razorpay_order_id`, `razorpay_payment_id`, `created_at`) VALUES
(1, 2, 1, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-20 12:55:02'),
(2, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-20 13:43:17'),
(3, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 12:11:19'),
(4, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 12:21:35'),
(5, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 12:21:52'),
(6, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 12:25:23'),
(7, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 16:50:21'),
(8, 2, NULL, 'Raj Kumar', '9876543210', 'pending', 220.00, 'COD', NULL, NULL, '2025-09-23 16:57:23'),
(9, 2, 4, 'Sriram', '9876543210', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:01:20'),
(10, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:15'),
(11, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:19'),
(12, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:20'),
(13, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:21'),
(14, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:23'),
(15, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:03:24'),
(16, 2, 4, 'Sriram', '9360552619', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-23 17:04:04'),
(17, 2, 4, 'Sriram', '9360552619', 'pending', 390.00, 'COD', NULL, NULL, '2025-09-23 17:06:23'),
(18, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', 'order_68d286ba1aad6', NULL, '2025-09-23 17:07:42'),
(19, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', 'order_68d28859341c1', NULL, '2025-09-23 17:14:54'),
(20, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-23 17:20:28'),
(21, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-23 17:21:58'),
(22, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-23 17:24:26'),
(23, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-23 17:30:53'),
(24, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:13:55'),
(25, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:19:59'),
(26, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:21:00'),
(27, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:23:16'),
(28, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:26:53'),
(29, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:44:59'),
(30, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:50:29'),
(31, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', NULL, NULL, '2025-09-24 10:56:45'),
(32, 2, 4, 'Sriram', '9360552619', 'pending', 430.00, 'UPI', 'order_68d381af1d8e3', NULL, '2025-09-24 10:58:53'),
(33, 2, 4, 'Sriram', '9360552619', 'paid', 430.00, 'UPI', 'order_68d384f391107', NULL, '2025-09-24 11:12:23'),
(34, 4, 5, 'Mullai Malar', '955623147', 'pending', 0.00, 'COD', NULL, NULL, '2025-09-25 12:03:56'),
(35, 4, 5, 'mullai malar', '955623147', 'pending', 1680.00, 'COD', NULL, NULL, '2025-09-25 12:09:01'),
(36, 5, 6, 'mullai malar', '955623147', 'pending', 1890.00, 'COD', NULL, NULL, '2025-09-26 12:22:05');

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
(60, 36, 25, 5.00, 450.00);

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` int(11) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `mobile`, `otp_hash`, `expires_at`, `created_at`) VALUES
(1, '9876543210', '$2y$10$5jjnkWstPO6//pGRap0fEe5wJDXI5N0zHN808EW0C1MYs.vQDuqqe', '2025-09-18 16:13:24', '2025-09-18 16:08:24'),
(2, '9360552619', '$2y$10$9tBSiSNctSOr3XI3dJPlreEKqVc85HVL8SJ0N.t0Zkdi5M5qbUeqi', '2025-09-18 16:16:48', '2025-09-18 16:11:48'),
(3, '9360552619', '$2y$10$kbN.KSgtcCGUJcOEYVb3nOqsTNGkhy0mkPNeWFxa1c4Ns8fSPqZJW', '2025-09-18 16:24:26', '2025-09-18 16:19:26'),
(4, '9876543210', '$2y$10$1gLniBccnQUlk5QHOyBTQus2QLxa8E8fi7A.ZGE6GYZvaWMvNes.u', '2025-09-19 12:30:11', '2025-09-19 12:25:11'),
(5, '9876543210', '$2y$10$UzUigErdFUc5PDNBRL.1k.vBnDW1XB31RryOEzcWeLdoVsGw4Cz5q', '2025-09-19 15:56:17', '2025-09-19 15:51:17'),
(6, '9876543210', '$2y$10$x8Ra/llCEb4dDijzT1Haqec0UxnhicRTYIOumSylr6ybKXFOgka8W', '2025-09-20 12:55:23', '2025-09-20 12:50:23'),
(7, '9876543210', '$2y$10$KGo5.SMZ8PQXGLTSbzptyOKk.aLkUWxelewz6SjkSSVcHFvO1TcmS', '2025-09-23 12:15:08', '2025-09-23 12:10:08'),
(8, '9876543210', '$2y$10$w.WxwgABWgjb1LaAS3qHIuka1JZOaxfTtQoFeKRLFXIsjdsu.Pt.C', '2025-09-23 15:52:26', '2025-09-23 15:47:26'),
(9, '9876543210', '$2y$10$CripamI2H2hA53wpSbIjuOJ05SkqzfvuuXPPwfKZTESZsjESJyYO.', '2025-09-24 10:21:46', '2025-09-24 10:16:46'),
(10, '9360552619', '$2y$10$GjjuYeaZ0iDxtEeTshUiLuMJuhK//Jn7lIgbUdF8hw.DrPZelEw7K', '2025-09-24 15:42:48', '2025-09-24 15:37:48'),
(11, '9360556666', '$2y$10$uYPzOad9azjsryvwnczYSOEU188S5xfZ2kfgBJi9Vl56GXN1.FWqG', '2025-09-24 16:22:04', '2025-09-24 16:17:04'),
(12, '9360556662', '$2y$10$FE6MdhOMvYjb46dQAfMzmu1yaraemKnrIvw6Vja54NipvIT9wzhsq', '2025-09-24 16:29:06', '2025-09-24 16:24:06'),
(13, '9360556662', '$2y$10$wRjrdTEetwHYoimrEMqPje1Emn4Y1Y281KI/XnVnpso6iZobiIMVe', '2025-09-25 10:58:00', '2025-09-25 10:53:00'),
(14, '9360556662', '$2y$10$QmYzfQcDDZ/qAScmqmWNvuhR10wLJKcl9bCm4PlYfdzr520I/HA1i', '2025-09-25 11:11:18', '2025-09-25 11:06:18'),
(15, '9360556662', '$2y$10$sdG1ZkN2.Y7wTsEDP0nqHOKmx5Gg3GfScekQXuZgg9a4TWKCJ3LYK', '2025-09-26 12:18:30', '2025-09-26 12:13:30'),
(16, '9360556622', '$2y$10$uLjb8Gt77GH/At./z6Ok3.sUJgjRj3jMnId6OY9rtnyo9FUg65yJa', '2025-09-26 12:18:47', '2025-09-26 12:13:47'),
(17, '9360556662', '$2y$10$vhiwDqSD5ucII7AQHZWq0u25yweuzfop/OBztkE.kdAcylWsQBoG.', '2025-09-29 10:40:15', '2025-09-29 10:35:15');

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
  `created_at` datetime DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `user_id`, `business_type_id`, `name`, `price`, `unit`, `created_at`, `category_id`, `subcategory_id`) VALUES
(1, 2, 1, 'Dosa', 50.00, 'Plate', '2025-09-19 15:52:33', NULL, NULL),
(2, 2, 1, 'Masala Dosa', 60.00, 'Plate', '2025-09-19 15:56:55', NULL, NULL),
(3, 2, 1, 'Idly', 20.00, 'Plate', '2025-09-19 16:21:08', NULL, NULL),
(4, 2, 1, 'Poori', 40.00, 'Plate', '2025-09-19 16:21:08', NULL, NULL),
(5, 2, 1, 'Pongal', 50.00, 'Plate', '2025-09-19 16:21:08', NULL, NULL),
(6, 2, 1, 'Vada', 10.00, 'Plate', '2025-09-19 16:21:08', NULL, NULL),
(7, 2, 1, 'Rava Dosa', 40.00, 'Plate', '2025-09-19 16:21:08', NULL, NULL),
(8, 2, 1, 'Podi Idly', 80.00, 'Plate', '2025-09-19 16:21:08', NULL, NULL),
(10, 4, 5, 'apple', 300.00, 'dozen', '2025-09-25 11:09:18', NULL, NULL),
(11, 4, 5, 'banana', 60.00, 'dozen', '2025-09-25 11:16:11', NULL, NULL),
(12, 4, 5, 'mushroom pizza', 180.00, 'plate', '2025-09-25 11:16:11', 2, 2),
(13, 4, 5, 'mango', 300.00, 'kg', '2025-09-25 11:16:11', NULL, NULL),
(15, 4, 5, 'pineapple', 90.00, 'pcs', '2025-09-25 11:16:11', NULL, NULL),
(16, 4, 5, 'watermelon', 40.00, 'kg', '2025-09-25 11:16:11', NULL, NULL),
(17, 4, 5, 'papaya', 70.00, 'kg', '2025-09-25 11:16:11', NULL, NULL),
(18, 4, 5, 'strawberry', 450.00, 'kg', '2025-09-25 11:16:11', NULL, NULL),
(19, 4, 5, 'guava', 110.00, 'kg', '2025-09-25 11:16:11', NULL, NULL),
(20, 5, 5, 'apple', 250.00, 'kg', '2025-09-26 12:18:55', NULL, NULL),
(21, 5, 5, 'banana', 60.00, 'dozen', '2025-09-26 12:19:39', NULL, NULL),
(22, 5, 5, 'orange', 120.00, 'kg', '2025-09-26 12:19:39', NULL, NULL),
(23, 5, 5, 'mango', 300.00, 'kg', '2025-09-26 12:19:39', NULL, NULL),
(24, 5, 5, 'grapes', 180.00, 'kg', '2025-09-26 12:19:39', NULL, NULL),
(25, 5, 5, 'pineapple', 90.00, 'pcs', '2025-09-26 12:19:39', NULL, NULL),
(26, 5, 5, 'watermelon', 40.00, 'kg', '2025-09-26 12:19:39', NULL, NULL),
(27, 5, 5, 'papaya', 70.00, 'kg', '2025-09-26 12:19:39', NULL, NULL),
(28, 5, 5, 'strawberry', 450.00, 'kg', '2025-09-26 12:19:39', NULL, NULL),
(29, 5, 5, 'guava', 110.00, 'kg', '2025-09-26 12:19:39', NULL, NULL),
(30, 4, 5, 'butter naan', 50.00, 'plate', '2025-09-29 10:36:58', 1, 1),
(31, 4, 5, 'paneer butter masala', 180.00, 'plate', '2025-09-29 10:58:26', 1, 3),
(32, 4, 5, 'veg burger', 120.00, 'piece', '2025-09-29 10:58:26', 3, 4),
(33, 4, 5, 'cold coffee', 100.00, 'glass', '2025-09-29 10:58:26', 4, 5),
(34, 4, 5, 'masala dosa', 80.00, 'plate', '2025-09-29 10:58:26', NULL, NULL),
(35, 4, 5, 'chicken biryani', 250.00, 'plate', '2025-09-29 10:58:26', 6, 7),
(36, 4, 5, 'french fries', 90.00, 'plate', '2025-09-29 10:58:26', 3, 8),
(37, 4, 5, 'green tea', 60.00, 'cup', '2025-09-29 10:58:26', 4, 9),
(38, 4, 5, 'ice cream', 70.00, 'cup', '2025-09-29 10:58:26', 7, 10),
(39, 4, 5, 'veg hakka', 130.00, 'plate', '2025-09-29 12:25:37', 9, 12),
(40, 4, 5, 'veg hakka noodles', 120.00, 'plate', '2025-09-29 12:28:12', NULL, NULL),
(41, 4, 5, 'veg hakka noodles', 120.00, 'plate', '2025-09-29 12:30:27', 8, 11),
(42, 4, 5, 'veg hakka noodles', 120.00, 'plate', '2025-09-29 12:44:26', 9, 12),
(43, 4, 5, 'paneer butter masala', 250.00, 'plate', '2025-09-29 12:44:26', 9, 13),
(44, 4, 5, 'chicken biryani', 200.00, 'plate', '2025-09-29 12:44:26', 9, 14),
(45, 4, 5, 'chocolate cake', 150.00, 'slice', '2025-09-29 12:44:26', 7, 15),
(46, 4, 5, 'veg spring roll', 50.00, 'pcs', '2025-09-29 12:44:26', 10, 16);

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
(6, 5, 5, 'a26821067c', '1', '2025-09-26 12:20:36');

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
(7, 2, '15c0594fbe8f925878b3685d950db7896a1608adb5b2d5d883d6ab48a2da49ab', '2025-09-24 12:49:19'),
(8, 1, '353846c65b9ff5d7c32f71a421c4cc4924a677be8cc843bc9a755adf0fa9c031', '2025-09-24 16:15:41'),
(9, 3, '06e89694236625e50d417db463c8e3b0e59b8ef954fa924e1ba0e82e0d4a6fe5', '2025-09-24 16:23:28'),
(10, 4, '0e757644f86a484f37424802304049fe23c92abbac6d9ad93c0ed01348e245b7', '2025-09-24 16:41:05'),
(11, 4, 'baea1c383c3c2976547e579241923a7ddf6ed4f94ee1333adf7a80e0c58bb5d9', '2025-09-25 10:55:35'),
(12, 4, 'b1e32829b11a3067f5bf224667f0e5366d8aafa57a591d332f77d8ffbab88a52', '2025-09-25 12:40:54'),
(13, 5, '47d9bb16b80effcf9cfe29443d22eae981ad660ca8d0184289d4f16930974ccc', '2025-09-26 12:20:36'),
(14, 4, '29d74222d1d06130a6fdca10db31056021675784c6405e615aaa76ef400923e2', '2025-09-29 13:05:38');

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
(16, 10, 'snacks', '2025-09-29 12:44:26');

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
  `status` enum('active','expired') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`sub_id`, `user_id`, `plan`, `start_date`, `end_date`, `status`) VALUES
(1, 2, 'free', '2025-09-19', '2025-09-26', 'active'),
(2, 1, 'free', '2025-09-24', '2025-10-01', 'active'),
(3, 3, 'free', '2025-09-24', '2025-10-01', 'active'),
(4, 4, 'free', '2025-09-24', '2025-10-01', 'active'),
(5, 5, 'free', '2025-09-26', '2025-10-03', 'active');

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
(39, 5, 'dozen', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `selected_template_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `mobile`, `name`, `created_at`, `selected_template_id`) VALUES
(1, '9360552619', 'dheeran sriram', '2025-09-18 16:20:04', 5),
(2, '9876543210', 'Arun Kumar', '2025-09-19 12:25:51', 1),
(3, '9360556666', 'thulasi', '2025-09-24 16:17:41', 5),
(4, '9360556662', 'thulasi1', '2025-09-24 16:24:35', 5),
(5, '9360556622', 'karthick', '2025-09-26 12:14:18', 5);

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
  ADD KEY `orders_ibfk_1` (`qr_id`);

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
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payment_credentials`
--
ALTER TABLE `payment_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `qr_codes`
--
ALTER TABLE `qr_codes`
  MODIFY `qr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `qr_scans`
--
ALTER TABLE `qr_scans`
  MODIFY `scan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `subcategory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `sub_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `template_fields`
--
ALTER TABLE `template_fields`
  MODIFY `field_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
