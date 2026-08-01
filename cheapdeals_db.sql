-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th8 01, 2026 lúc 10:02 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `cheapdeals_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `event_type` varchar(80) NOT NULL,
  `entity_type` varchar(40) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `outcome` varchar(30) NOT NULL DEFAULT 'Success',
  `details` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_id`, `event_type`, `entity_type`, `entity_id`, `outcome`, `details`, `created_at`) VALUES
(1, 3, 'payment.completed', 'order', 15, 'Success', 'App payment approved; receipt CD-20260731-11910604', '2026-07-31 11:12:30'),
(2, 3, 'payment.completed', 'order', 16, 'Success', 'App payment approved; receipt CD-20260731-11910604', '2026-07-31 11:12:30'),
(3, 2, 'catalogue.package.archived', 'package', 1, 'Success', NULL, '2026-07-31 11:16:12'),
(4, 2, 'catalogue.package.reactivated', 'package', 1, 'Success', NULL, '2026-07-31 11:16:22'),
(5, 2, 'catalogue.package.updated', 'package', 1, 'Success', 'Starter Mobile', '2026-07-31 11:16:30'),
(6, 3, 'subscription.updated', 'subscription', 3, 'Success', 'Changed to package #11: Fibre Max 500', '2026-07-31 12:31:56'),
(7, 3, 'subscription.updated', 'subscription', 3, 'Success', 'Changed to package #11: Fibre Max 500', '2026-07-31 12:32:19'),
(8, 3, 'subscription.updated', 'subscription', 3, 'Success', 'Changed to deal #1: Double Starter', '2026-07-31 16:54:29'),
(9, 3, 'payment.completed', 'order', 17, 'Success', 'App payment approved; receipt CD-20260801-CCF8C4ED', '2026-08-01 06:46:03'),
(10, 3, 'payment.completed', 'order', 18, 'Success', 'App payment approved; receipt CD-20260801-CCF8C4ED', '2026-08-01 06:46:03'),
(11, 6, 'payment.completed', 'order', 19, 'Success', 'App payment approved; receipt CD-20260801-168C72B6', '2026-08-01 07:39:52'),
(12, 2, 'offer.updated', 'offer', 8, 'Success', '{\"code\":\"PLAN10\",\"discount_percent\":10,\"expiry_date\":\"2026-08-16\"}', '2026-08-01 07:41:33'),
(13, 2, 'offer.archived', 'offer', 8, 'Success', '{\"code\":\"PLAN10\"}', '2026-08-01 07:41:37'),
(14, 2, 'offer.reactivated', 'offer', 8, 'Success', '{\"code\":\"PLAN10\"}', '2026-08-01 07:41:39'),
(15, 2, 'offer.created', 'offer', 9, 'Success', '{\"code\":\"AWDAW24\",\"discount_percent\":10,\"expiry_date\":\"2026-08-06\"}', '2026-08-01 07:58:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `deals`
--

CREATE TABLE `deals` (
  `id` int(11) NOT NULL,
  `deal_name` varchar(120) NOT NULL,
  `deal_type` enum('DoublePackage','TriplePackage') NOT NULL,
  `normal_price` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) DEFAULT 100,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `deals`
--

INSERT INTO `deals` (`id`, `deal_name`, `deal_type`, `normal_price`, `price`, `description`, `stock`, `image`, `is_active`, `created_at`) VALUES
(1, 'Double Starter', 'DoublePackage', 22.98, 19.53, 'An affordable starter bundle combining Mobile Starter and Tablet Starter.', 100, 'images/double-starter.png', 1, '2026-07-23 07:59:20'),
(2, 'Double Essential', 'DoublePackage', 40.98, 34.83, 'A balanced bundle combining Mobile Essential and Home Broadband 100.', 89, 'images/double-essential.png', 1, '2026-07-23 07:59:20'),
(3, 'Double Plus', 'DoublePackage', 51.98, 44.18, 'A faster bundle combining Mobile Plus and Fibre Plus 300.', 80, 'images/double-plus.png', 1, '2026-07-23 07:59:20'),
(4, 'Double Premium', 'DoublePackage', 77.98, 66.28, 'A premium bundle combining Unlimited Mobile Max and Ultra Fibre 900.', 60, 'images/double-premium.png', 1, '2026-07-23 07:59:20'),
(5, 'Triple Starter', 'TriplePackage', 42.97, 36.52, 'An affordable bundle combining mobile, broadband and tablet services.', 90, 'images/triple-starter.png', 1, '2026-07-23 07:59:20'),
(6, 'Triple Plus', 'TriplePackage', 71.97, 61.17, 'A balanced bundle with high mobile data, fast fibre broadband and tablet data.', 75, 'images/triple-plus.png', 1, '2026-07-23 07:59:20'),
(7, 'Triple Ultimate', 'TriplePackage', 102.97, 87.52, 'The highest-tier CheapDeals bundle with premium mobile, ultra-fast broadband and tablet data.', 55, 'images/triple-ultimate.png', 1, '2026-07-23 07:59:20');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `deal_packages`
--

CREATE TABLE `deal_packages` (
  `deal_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `deal_packages`
--

INSERT INTO `deal_packages` (`deal_id`, `package_id`) VALUES
(1, 1),
(1, 14),
(2, 3),
(2, 5),
(3, 6),
(3, 10),
(4, 2),
(4, 12),
(5, 1),
(5, 9),
(5, 14),
(6, 4),
(6, 7),
(6, 10),
(7, 2),
(7, 12),
(7, 17);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `enquiries`
--

CREATE TABLE `enquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `status` enum('Pending','Answered') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `enquiries`
--

INSERT INTO `enquiries` (`id`, `user_id`, `package_id`, `subject`, `message`, `reply`, `status`, `created_at`) VALUES
(2, 3, 1, 'alo', 'xdsds', 'hello', 'Answered', '2026-07-29 08:29:55'),
(3, 3, 1, 'cc', 'sss', '777', 'Answered', '2026-07-29 08:30:17'),
(4, NULL, 1, 'dff', 'ẻg', NULL, 'Pending', '2026-07-31 02:33:15'),
(5, 6, 1, 'ưad', 'rgedrt sèefe', NULL, 'Pending', '2026-08-01 07:40:54');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `offers`
--

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `discount_percent` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `offers`
--

INSERT INTO `offers` (`id`, `code`, `description`, `discount_percent`, `expiry_date`, `is_active`) VALUES
(8, 'PLAN10', '10% off any single-category package.', 10, '2026-08-16', 1),
(9, 'AWDAW24', 'ASDSAEF3412DSFS', 10, '2026-08-06', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `offer_usage`
--

CREATE TABLE `offer_usage` (
  `id` int(11) NOT NULL,
  `offer_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `offer_usage`
--

INSERT INTO `offer_usage` (`id`, `offer_id`, `user_id`, `used`) VALUES
(1, 8, 3, 1),
(2, 8, 6, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `total` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `final_total` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Paid','Cancelled') DEFAULT 'Pending',
  `order_channel` varchar(20) NOT NULL DEFAULT 'Website',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `package_id`, `quantity`, `total`, `discount`, `final_total`, `status`, `order_channel`, `created_by`, `created_at`) VALUES
(1, 1, 2, 1, 29.99, 0.00, 29.99, 'Paid', 'Website', NULL, '2026-07-01 09:11:08'),
(2, 3, 5, 1, 15.99, 0.00, 15.99, 'Pending', 'Website', NULL, '2026-07-29 06:48:35'),
(3, 3, 5, 1, 15.99, 0.00, 15.99, 'Paid', 'Website', NULL, '2026-07-29 06:49:18'),
(4, 3, 1, 1, 12.99, 0.00, 12.99, 'Paid', 'Website', NULL, '2026-07-29 07:32:43'),
(5, 3, 5, 1, 15.99, 0.00, 15.99, 'Paid', 'Website', NULL, '2026-07-29 07:32:43'),
(6, 3, 6, 1, 19.99, 0.00, 19.99, 'Paid', 'Website', NULL, '2026-07-29 07:32:43'),
(7, 3, 7, 1, 23.99, 0.00, 23.99, 'Paid', 'Website', NULL, '2026-07-29 07:32:43'),
(8, 3, 1, 1, 12.99, 0.00, 12.99, 'Paid', 'Website', NULL, '2026-07-29 07:41:32'),
(9, 3, 1, 1, 25.98, 0.00, 25.98, 'Paid', 'Website', NULL, '2026-07-30 07:21:37'),
(10, 3, 5, 1, 15.99, 0.00, 15.99, 'Paid', 'Website', NULL, '2026-07-30 07:21:37'),
(11, 3, 6, 1, 19.99, 0.00, 19.99, 'Paid', 'Website', NULL, '2026-07-30 07:21:37'),
(12, 3, 5, 1, 15.99, 0.00, 15.99, 'Paid', 'Website', NULL, '2026-07-30 07:24:46'),
(13, 3, 5, 1, 15.99, 0.00, 15.99, 'Paid', 'Website', NULL, '2026-07-30 09:04:19'),
(14, 3, 1, 1, 12.99, 0.00, 12.99, 'Paid', 'Website', NULL, '2026-07-30 09:30:28'),
(15, 3, 5, 1, 15.99, 3.76, 12.23, 'Paid', 'App', NULL, '2026-07-31 11:12:30'),
(16, 3, 7, 1, 23.99, 5.64, 18.35, 'Paid', 'App', NULL, '2026-07-31 11:12:30'),
(17, 3, 5, 1, 15.99, 2.40, 13.59, 'Paid', 'App', NULL, '2026-08-01 06:46:03'),
(18, 3, 3, 1, 24.99, 3.75, 21.24, 'Paid', 'App', NULL, '2026-08-01 06:46:03'),
(19, 6, 13, 1, 6.99, 1.64, 5.35, 'Paid', 'App', NULL, '2026-08-01 07:39:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `package_name` varchar(100) DEFAULT NULL,
  `category` enum('Mobile','Broadband','Tablet') DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `minutes` int(11) DEFAULT NULL,
  `sms` int(11) DEFAULT NULL,
  `data_gb` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) DEFAULT 100,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `packages`
--

INSERT INTO `packages` (`id`, `package_name`, `category`, `price`, `minutes`, `sms`, `data_gb`, `description`, `stock`, `image`, `is_active`) VALUES
(1, 'Starter Mobile', 'Mobile', 12.99, 250, 500, 8, 'Budget mobile plan with enough data and calls for everyday use.', 100, 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=1200&q=80', 1),
(2, 'Unlimited Mobile Max', 'Mobile', 29.99, 5000, 5000, 80, 'High data mobile package for streaming, social media, and work on the go.', 80, 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&fit=crop&w=1200&q=80', 1),
(3, 'Home Broadband 100', 'Broadband', 24.99, 0, 0, 500, 'Reliable broadband package for households, online classes, and remote work.', 59, 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=80', 1),
(4, 'Tablet Data Share', 'Tablet', 15.99, 0, 100, 25, 'Flexible tablet data package for entertainment, browsing, and travel.', 75, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=1200&q=80', 1),
(5, 'Mobile Essential', 'Mobile', 15.99, 1000, 1000, 15, 'A balanced mobile plan for everyday calls, messaging, social media and internet browsing.', 98, 'images/mobile-essential.png', 1),
(6, 'Mobile Plus', 'Mobile', 19.99, 2000, 2000, 30, 'A flexible mobile package with extra data for streaming, navigation and frequent social media use.', 95, 'images/mobile-plus.png', 1),
(7, 'Mobile Max', 'Mobile', 23.99, 3000, 3000, 50, 'A high-data mobile package suitable for video streaming, hotspot use and customers who use mobile data regularly.', 89, 'images/mobile-max.png', 1),
(8, 'Mobile Premium', 'Mobile', 26.99, 5000, 5000, 70, 'A premium mobile package for heavy streaming, gaming, downloads and frequent hotspot use.', 85, 'images/mobile-premium.png', 1),
(9, 'Broadband Starter 50', 'Broadband', 19.99, 0, 0, 250, 'Affordable home broadband package suitable for browsing, email, online study and light streaming.', 100, 'images/broadband-starter-50.png', 1),
(10, 'Fibre Plus 300', 'Broadband', 31.99, 0, 0, 1000, 'Fast fibre broadband designed for families, remote working, gaming and HD video streaming.', 90, 'images/fibre-plus-300.png', 1),
(11, 'Fibre Max 500', 'Broadband', 37.99, 0, 0, 2000, 'High-performance fibre broadband for busy households with multiple connected devices.', 80, 'images/fibre-max-500.png', 1),
(12, 'Ultra Fibre 900', 'Broadband', 47.99, 0, 0, 5000, 'Premium ultra-fast fibre broadband for gaming, 4K streaming, smart homes and heavy internet users.', 70, 'images/ultra-fibre-900.png', 1),
(13, 'Tablet Mini', 'Tablet', 6.99, 0, 0, 3, 'A low-cost tablet data plan for occasional browsing, email and light internet use.', 119, 'images/tablet-mini.png', 1),
(14, 'Tablet Starter', 'Tablet', 9.99, 0, 0, 10, 'A starter tablet plan for browsing, reading, messaging and entertainment while travelling.', 115, 'images/tablet-starter.png', 1),
(15, 'Tablet Essential', 'Tablet', 12.99, 0, 0, 20, 'An everyday tablet package for online study, video calls, cloud documents and regular browsing.', 105, 'images/tablet-essential.png', 1),
(16, 'Tablet Max', 'Tablet', 20.99, 0, 0, 60, 'A high-data tablet package for frequent streaming, remote working and multimedia use.', 90, 'images/tablet-max.png', 1),
(17, 'Tablet Unlimited', 'Tablet', 24.99, 0, 0, 100, 'Our largest tablet data package for customers who regularly use their tablet for work, study and entertainment.', 80, 'images/tablet-unlimited.png', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('Success','Failed') DEFAULT NULL,
  `verification_status` varchar(20) DEFAULT NULL,
  `verification_reference` varchar(60) DEFAULT NULL,
  `verification_message` varchar(255) DEFAULT NULL,
  `receipt_ref` varchar(40) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `amount`, `payment_method`, `payment_status`, `verification_status`, `verification_reference`, `verification_message`, `receipt_ref`, `payment_date`) VALUES
(1, 1, 29.99, 'Bank transfer', 'Success', NULL, NULL, NULL, 'CD-20260701-000001', '2026-07-01 09:11:15'),
(2, 3, 15.69, 'Credit card', 'Success', NULL, NULL, NULL, 'CD-20260729-000002', '2026-07-29 06:49:48'),
(3, 4, 12.99, 'Credit card', 'Success', NULL, NULL, NULL, 'CD-20260729-000003', '2026-07-29 07:32:43'),
(4, 5, 15.99, 'Credit card', 'Success', NULL, NULL, NULL, 'CD-20260729-000004', '2026-07-29 07:32:43'),
(5, 6, 19.99, 'Credit card', 'Success', NULL, NULL, NULL, 'CD-20260729-000005', '2026-07-29 07:32:43'),
(6, 7, 23.99, 'Credit card', 'Success', NULL, NULL, NULL, 'CD-20260729-000006', '2026-07-29 07:32:43'),
(7, 8, 12.99, 'Mastercard ending 2212', 'Success', NULL, NULL, NULL, 'CD-20260729-000007', '2026-07-29 07:41:32'),
(8, 9, 25.98, 'Mastercard ending 2212', 'Success', NULL, NULL, NULL, 'CD-20260730-000008', '2026-07-30 07:21:37'),
(9, 10, 15.99, 'Mastercard ending 2212', 'Success', NULL, NULL, NULL, 'CD-20260730-000009', '2026-07-30 07:21:37'),
(10, 11, 19.99, 'Mastercard ending 2212', 'Success', NULL, NULL, NULL, 'CD-20260730-000010', '2026-07-30 07:21:37'),
(11, 12, 15.99, 'Mastercard ending 2212', 'Success', NULL, NULL, NULL, 'CD-20260730-000011', '2026-07-30 07:24:46'),
(12, 13, 15.99, 'Mastercard ending 2212', 'Success', NULL, NULL, NULL, 'CD-20260730-918BF96D', '2026-07-30 09:04:19'),
(13, 14, 12.99, 'Mastercard ending 2212', 'Success', NULL, NULL, NULL, 'CD-20260730-2E69F7AA', '2026-07-30 09:30:28'),
(14, 15, 12.23, 'Visa ending 1919', 'Success', 'Approved', 'VC-20260731181230-F41262E3', 'Saved card token verified by VISAcheck sandbox.', 'CD-20260731-11910604', '2026-07-31 11:12:30'),
(15, 16, 18.35, 'Visa ending 1919', 'Success', 'Approved', 'VC-20260731181230-F41262E3', 'Saved card token verified by VISAcheck sandbox.', 'CD-20260731-11910604', '2026-07-31 11:12:30'),
(16, 17, 13.59, 'JCB ending 0000', 'Success', 'Approved', 'VC-20260801134603-5A8C4837', 'Saved card token verified by VISAcheck.', 'CD-20260801-CCF8C4ED', '2026-08-01 06:46:03'),
(17, 18, 21.24, 'JCB ending 0000', 'Success', 'Approved', 'VC-20260801134603-5A8C4837', 'Saved card token verified by VISAcheck.', 'CD-20260801-CCF8C4ED', '2026-08-01 06:46:03'),
(18, 19, 5.35, 'Mastercard ending 4444', 'Success', 'Approved', 'VC-20260801143952-F52F622A', 'Saved card token verified by VISAcheck.', 'CD-20260801-168C72B6', '2026-08-01 07:39:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_type` varchar(20) NOT NULL,
  `card_last4` varchar(4) NOT NULL,
  `card_expiry` varchar(5) NOT NULL,
  `verification_status` varchar(20) DEFAULT NULL,
  `verification_reference` varchar(60) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `user_id`, `card_type`, `card_last4`, `card_expiry`, `verification_status`, `verification_reference`, `is_default`) VALUES
(7, 3, 'Mastercard', '2212', '12/12', NULL, NULL, 0),
(8, 3, 'Visa', '1919', '09/29', NULL, NULL, 0),
(9, 3, 'JCB', '0000', '12/29', 'Approved', 'VC-20260731235300-6E96CE4E', 1),
(10, 6, 'Mastercard', '4444', '12/29', 'Approved', 'VC-20260801143855-627E292C', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `package_id` int(11) DEFAULT NULL,
  `deal_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `renewal_date` date DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `package_id`, `deal_id`, `status`, `started_at`, `renewal_date`, `updated_at`) VALUES
(1, 3, NULL, 2, 'Active', '2026-07-31 12:31:56', '2026-09-01', '2026-08-01 06:46:03'),
(2, 6, 13, NULL, 'Active', '2026-08-01 07:39:52', '2026-09-01', '2026-08-01 07:39:52');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `credit_card` varchar(50) DEFAULT NULL,
  `role` enum('customer','csr','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `card_type` varchar(20) DEFAULT NULL,
  `card_expiry` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `phone`, `address`, `credit_card`, `role`, `created_at`, `card_type`, `card_expiry`) VALUES
(1, 'Demo Customer', 'customer@example.com', '$2y$10$YDjmmyI09icfFURwJBUnBuTVLG4tA9V1TuKRjNtz.8Ln58Q6cpZ6i', '+84 900 000 000', 'Hanoi, Vietnam', NULL, 'customer', '2026-07-01 09:04:31', NULL, NULL),
(2, 'Admin User', 'admin@example.com', '$2y$10$6BCUoGos9xqIsE1tFFsIgedJaFHcsXyZoiaB7y8DSKW.XTRvuIhl.', '+84 911 111 111', 'Ho Chi Minh City, Vietnam', NULL, 'admin', '2026-07-01 09:04:31', NULL, NULL),
(3, 'doankhanhdang', 'khanhdang@gmail.com', '$2y$10$rYRCgPcj80N0WrlJ1J8tJ.T8eErpWxTOkx09zPPuFeB6e3IfHnpaa', '+84 0984056724', 'quan 1', NULL, 'customer', '2026-07-14 08:04:52', 'Visa', NULL),
(4, 'vo thien phuc', 'thienphuc@gmail.com', '$2y$10$S1/S/0i.KioeSd2A3aAC4.pwpfV33xHpU59dxptegOVR555Byg6Pi', '090999999999', 'dsidjidanj', NULL, 'customer', '2026-07-30 07:16:32', NULL, NULL),
(5, 'CSR User', 'csr@example.com', '$2y$10$6C0oufQEUxx93QPeWYsFbu1PVMKUIgouVXYKR6wVGtwGRTBlD0HCm', '+84 922 222 222', 'Da Nang, Vietnam', NULL, 'csr', '2026-07-31 11:06:23', NULL, NULL),
(6, 'Tan', 'tan@gmail.com', '$2y$10$7zUI4Zc.r5taIaWomBUFdOX9qYiLco7dupIxizPOftyb3ln6XmdpS', '123123123', '123123123', NULL, 'customer', '2026-08-01 07:37:51', NULL, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_created` (`created_at`),
  ADD KEY `idx_audit_entity` (`entity_type`,`entity_id`),
  ADD KEY `fk_audit_actor` (`actor_id`);

--
-- Chỉ mục cho bảng `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_deal_name` (`deal_name`);

--
-- Chỉ mục cho bảng `deal_packages`
--
ALTER TABLE `deal_packages`
  ADD PRIMARY KEY (`deal_id`,`package_id`),
  ADD KEY `fk_deal_packages_package` (`package_id`);

--
-- Chỉ mục cho bảng `enquiries`
--
ALTER TABLE `enquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Chỉ mục cho bảng `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_offers_code` (`code`),
  ADD KEY `idx_offers_visibility` (`is_active`,`expiry_date`);

--
-- Chỉ mục cho bảng `offer_usage`
--
ALTER TABLE `offer_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offer_id` (`offer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Chỉ mục cho bảng `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_payments_receipt_ref` (`receipt_ref`);

--
-- Chỉ mục cho bảng `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_subscription_user` (`user_id`),
  ADD KEY `fk_subscription_package` (`package_id`),
  ADD KEY `fk_subscription_deal` (`deal_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `deals`
--
ALTER TABLE `deals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `enquiries`
--
ALTER TABLE `enquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `offer_usage`
--
ALTER TABLE `offer_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT cho bảng `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT cho bảng `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_actor` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `deal_packages`
--
ALTER TABLE `deal_packages`
  ADD CONSTRAINT `fk_deal_packages_deal` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_deal_packages_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `enquiries`
--
ALTER TABLE `enquiries`
  ADD CONSTRAINT `enquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `enquiries_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`);

--
-- Các ràng buộc cho bảng `offer_usage`
--
ALTER TABLE `offer_usage`
  ADD CONSTRAINT `offer_usage_ibfk_1` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`),
  ADD CONSTRAINT `offer_usage_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`);

--
-- Các ràng buộc cho bảng `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Các ràng buộc cho bảng `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `fk_subscription_deal` FOREIGN KEY (`deal_id`) REFERENCES `deals` (`id`),
  ADD CONSTRAINT `fk_subscription_package` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`),
  ADD CONSTRAINT `fk_subscription_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
