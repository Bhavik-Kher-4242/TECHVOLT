-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 26, 2026 at 03:42 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `techvolt`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `address_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pincode` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`address_id`),
  KEY `fk_addresses_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`address_id`, `user_id`, `full_name`, `phone`, `address_line`, `city`, `state`, `pincode`) VALUES
(1, 1, 'Rahul Kher', '9427184242', 'limdavali sheri main bajar sayla', 'surendranagar', 'Gujrat', '363430'),
(5, 1, 'Bhavik Hakabhai Kher', '9904293131', 'Main Street, Bhagvanpar, Limbadi', 'Limbadi', 'Gujrat', '363421'),
(7, 3, 'Ankit Chavda ', '7016132329', 'goradiya hanuman, Sayla', 'surendranagar', 'Gujarat', '363430'),
(8, 8, 'Dhruv Desai', '7016132329', 'Joravarnagar', 'surendranagar', 'Gujrat', '363020'),
(9, 9, 'Shivamgiri J Goswami', '7016132329', 'Godawari', 'surendranagar', 'Gujrat', '363430'),
(10, 10, 'Bhavik Hakabhai Kher', '9876543210', 'Limdawali sheri, Sayla', 'surendranagar', 'Gujarat', '363430');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `cart_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  KEY `fk_carts_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cart_id`, `user_id`, `created_at`, `updated_at`) VALUES
(6, 1, '2026-09-11 11:18:26', '2026-09-11 11:18:26'),
(7, 3, '2026-09-17 08:34:36', '2026-09-17 08:34:36'),
(8, 8, '2026-09-23 10:50:00', '2026-09-23 10:50:00'),
(9, 9, '2026-09-23 11:57:01', '2026-09-23 11:57:01'),
(10, 10, '2026-09-23 16:33:39', '2026-09-23 16:33:39');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `cart_item_id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_item_id`),
  UNIQUE KEY `cart_id` (`cart_id`,`product_id`),
  KEY `fk_cart_items_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `quantity`, `created_at`) VALUES
(171, 7, 2, 1, '2026-09-24 03:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `slug`, `description`, `status`, `created_at`) VALUES
(1, 'Microcontrollers', 'microcontrollers', 'Microcontroller boards and development platforms used for embedded systems, automation, robotics and electronics projects.', 'active', '2026-08-21 08:31:56'),
(2, 'Accessories', 'accessories', 'Electronic accessories, cables, connectors, adapters and other useful components for electronics projects.', 'active', '2026-08-21 08:33:00'),
(3, 'Displays', 'displays', 'LCD, OLED, TFT and other display modules used for showing text, graphics and data in electronic projects.', 'active', '2026-08-21 08:33:20'),
(4, 'ICs', 'ics', 'Integrated circuits for digital, analog, power management and other electronic applications.', 'active', '2026-08-21 08:33:41'),
(5, 'Motors & Actuators', 'motors-actuators', 'DC motors, servo motors, stepper motors and other actuators used in robotics, automation and motion control projects.', 'active', '2026-08-21 08:34:08'),
(6, 'Sensors', 'sensors', 'Electronic sensors for detecting temperature, distance, motion, light, humidity, pressure and other physical conditions.', 'active', '2026-08-21 08:34:29'),
(7, 'Power Modules', 'power-modules', 'Power supply modules, voltage regulators, converters and other power management components for electronic projects.', 'active', '2026-08-21 08:34:50'),
(8, 'Passive Components', 'passive-components', 'Resistors, capacitors, inductors and other passive electronic components used in circuits and electronic projects.', 'active', '2026-08-21 08:35:16'),
(9, 'Single Board Computers', 'single-board-computers', 'Compact single-board computers used for programming, IoT, robotics, automation, networking and embedded applications.', 'active', '2026-08-21 08:35:35');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `fk_contact_messages_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`message_id`, `user_id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(4, 3, 'Bhavik Hakabhai Kher', 'kheranu0650@gmail.com', 'I Love this Website', 'I love this so much besause of this website feature', '2026-09-17 12:42:23'),
(5, 3, 'Bhavik Hakabhai Kher', 'kheranu0650@gmail.com', 'I Love this Website', 'I love this so much besause of this website feature', '2026-09-17 12:42:42'),
(6, 10, 'Rahul Kher', 'kherrahul720@gmail.com', 'This is sample long message for this site', 'Lorem ipsum dolor sit amet consectetur, adipisicing elit. Nostrum, explicabo cumque nisi non earum aperiam incidunt rem deleniti culpa, eveniet dolorum eum qui repudiandae fugit quas natus quasi ipsa nam!\r\nConsequuntur quibusdam expedita harum quisquam aspernatur debitis consectetur, atque architecto repellendus soluta ut, maiores modi laudantium quis! Distinctio voluptatem, qui eum dolorum sed nobis quaerat culpa, minima possimus pariatur excepturi.\r\nPorro eligendi itaque repellat dolores quasi debitis rerum rem libero nam placeat eum autem exercitationem, mollitia nisi optio beatae commodi quas qui architecto blanditiis quam similique accusamus. Aliquid, temporibus unde!\r\nFuga quasi delectus eligendi commodi aliquam dolor velit reiciendis atque! Explicabo veniam fuga cumque nam voluptas, debitis nobis numquam? Sint eaque velit harum distinctio? Officiis saepe voluptates voluptatum perspiciatis labore.\r\nIn amet earum illum, deserunt aperiam placeat modi, recusandae eveniet, fugit accusamus ipsa nihil. Ut, vero dolorum quidem illo culpa expedita dolor corrupti modi quod? Qui consequuntur assumenda sunt ratione.', '2026-09-24 14:14:39');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `address_id` int NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_charge` decimal(10,2) NOT NULL,
  `gst_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` enum('cod','online') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `order_status` enum('pending','confirmed','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `shipping_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `fk_orders_user` (`user_id`),
  KEY `fk_orders_address` (`address_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `address_id`, `subtotal`, `delivery_charge`, `gst_amount`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `shipping_name`, `shipping_phone`, `shipping_address`, `created_at`) VALUES
(10, 1, 5, 457.00, 100.00, 31.99, 589.00, 'cod', 'pending', 'delivered', 'Bhavik Hakabhai Kher', '9904293131', 'Main Street, Bhagvanpar, Limbadi', '2026-09-16 11:57:00'),
(12, 8, 8, 2030.00, 50.00, 142.10, 2222.10, 'cod', 'pending', 'delivered', 'Dhruv Desai', '7016132329', 'Joravarnagar', '2026-09-23 10:50:43'),
(13, 8, 8, 647.00, 50.00, 45.29, 742.29, 'cod', 'pending', 'pending', 'Dhruv Desai', '7016132329', 'Joravarnagar', '2026-09-23 11:43:26'),
(14, 9, 9, 946.00, 200.00, 66.22, 1212.22, 'cod', 'pending', 'cancelled', 'Shivamgiri J Goswami', '7016132329', 'Godawari', '2026-09-23 11:57:40'),
(15, 1, 5, 49.00, 50.00, 3.43, 102.43, 'cod', 'pending', 'pending', 'Bhavik Hakabhai Kher', '9904293131', 'Main Street, Bhagvanpar, Limbadi', '2026-09-23 13:47:29'),
(16, 1, 5, 3.00, 50.00, 0.21, 53.21, 'cod', 'pending', 'pending', 'Bhavik Hakabhai Kher', '9904293131', 'Main Street, Bhagvanpar, Limbadi', '2026-09-23 13:49:39'),
(17, 1, 5, 30.00, 50.00, 2.10, 82.10, 'cod', 'pending', 'pending', 'Bhavik Hakabhai Kher', '9904293131', 'Main Street, Bhagvanpar, Limbadi', '2026-09-23 13:54:24'),
(18, 1, 1, 9.00, 50.00, 0.63, 59.63, 'cod', 'pending', 'pending', 'Rahul Kher', '9427184242', 'limdavali sheri main bajar sayla', '2026-09-23 13:54:58'),
(19, 1, 5, 12.00, 50.00, 0.84, 62.84, 'cod', 'pending', 'pending', 'Bhavik Hakabhai Kher', '9904293131', 'Main Street, Bhagvanpar, Limbadi', '2026-09-23 13:59:58'),
(20, 1, 5, 12.00, 50.00, 0.84, 62.84, 'cod', 'pending', 'pending', 'Bhavik Hakabhai Kher', '9904293131', 'Main Street, Bhagvanpar, Limbadi', '2026-09-23 14:00:55'),
(21, 1, 5, 6.00, 50.00, 0.42, 56.42, 'cod', 'pending', 'pending', 'Bhavik Hakabhai Kher', '9904293131', 'Main Street, Bhagvanpar, Limbadi', '2026-09-23 14:03:12'),
(22, 1, 5, 9.00, 50.00, 0.63, 59.63, 'cod', 'pending', 'pending', 'Bhavik Hakabhai Kher', '9904293131', 'Main Street, Bhagvanpar, Limbadi', '2026-09-23 14:10:57'),
(23, 10, 10, 1166.00, 50.00, 81.62, 1297.62, 'cod', 'pending', 'pending', 'Bhavik Hakabhai Kher', '9876543210', 'Limdawali sheri, Sayla', '2026-09-23 16:36:39'),
(25, 10, 10, 832.00, 100.00, 58.24, 990.24, 'cod', 'pending', 'cancelled', 'Bhavik Hakabhai Kher', '9876543210', 'Limdawali sheri, Sayla', '2026-09-23 16:39:51'),
(26, 10, 10, 712.00, 50.00, 49.84, 811.84, 'cod', 'pending', 'confirmed', 'Bhavik Hakabhai Kher', '9876543210', 'Limdawali sheri, Sayla', '2026-09-23 16:40:18'),
(27, 10, 10, 299.00, 200.00, 20.93, 519.93, 'cod', 'pending', 'shipped', 'Bhavik Hakabhai Kher', '9876543210', 'Limdawali sheri, Sayla', '2026-09-23 16:41:02'),
(28, 10, 10, 6748.00, 100.00, 472.36, 7320.36, 'cod', 'pending', 'delivered', 'Bhavik Hakabhai Kher', '9876543210', 'Limdawali sheri, Sayla', '2026-09-23 16:41:32'),
(29, 3, 7, 747.00, 200.00, 52.29, 999.29, 'cod', 'pending', 'cancelled', 'Ankit Chavda ', '7016132329', 'goradiya hanuman, Sayla', '2026-09-24 03:01:24'),
(30, 3, 7, 495.00, 50.00, 34.65, 579.65, 'cod', 'pending', 'delivered', 'Ankit Chavda ', '7016132329', 'goradiya hanuman, Sayla', '2026-09-24 03:02:25'),
(31, 1, 1, 598.00, 50.00, 41.86, 689.86, 'cod', 'pending', 'confirmed', 'Rahul Kher', '9427184242', 'limdavali sheri main bajar sayla', '2026-09-25 02:13:59');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `product_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` int NOT NULL,
  `quantity` int NOT NULL,
  `subtotal` int NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `fk_order_items_order` (`order_id`),
  KEY `fk_order_items_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `subtotal`) VALUES
(17, 10, 12, '20x4 LCD Display', 249, 1, 249),
(18, 10, 10, 'Male to Male Jumper Wires', 79, 1, 79),
(19, 10, 9, '830 Point Breadboard', 129, 1, 129),
(24, 12, 1, 'Arduino Uno R3', 799, 1, 799),
(25, 12, 4, 'Arduino Nano', 499, 1, 499),
(26, 12, 5, 'ESP8266 NodeMCU', 349, 1, 349),
(27, 12, 6, '16x2 LCD Display', 149, 1, 149),
(28, 12, 7, '0.96 inch OLED Display', 199, 1, 199),
(29, 12, 8, 'NE555 Timer IC', 35, 1, 35),
(30, 13, 3, 'HC-SR04 Ultrasonic Sensor', 99, 1, 99),
(31, 13, 5, 'ESP8266 NodeMCU', 349, 1, 349),
(32, 13, 7, '0.96 inch OLED Display', 199, 1, 199),
(33, 14, 3, 'HC-SR04 Ultrasonic Sensor', 99, 1, 99),
(34, 14, 4, 'Arduino Nano', 499, 1, 499),
(35, 14, 6, '16x2 LCD Display', 149, 1, 149),
(36, 14, 7, '0.96 inch OLED Display', 199, 1, 199),
(37, 15, 14, 'LM358 Operational Amplifier IC', 25, 1, 25),
(38, 15, 22, '1N4007 Diode', 3, 8, 24),
(39, 16, 22, '1N4007 Diode', 3, 1, 3),
(40, 17, 22, '1N4007 Diode', 3, 10, 30),
(41, 18, 22, '1N4007 Diode', 3, 3, 9),
(42, 19, 22, '1N4007 Diode', 3, 4, 12),
(43, 20, 22, '1N4007 Diode', 3, 4, 12),
(44, 21, 22, '1N4007 Diode', 3, 2, 6),
(45, 22, 22, '1N4007 Diode', 3, 3, 9),
(46, 23, 1, 'Arduino Uno R3', 799, 1, 799),
(47, 23, 6, '16x2 LCD Display', 149, 2, 298),
(48, 23, 24, 'LM2596 Buck Converter', 69, 1, 69),
(52, 25, 3, 'HC-SR04 Ultrasonic Sensor', 99, 1, 99),
(53, 25, 4, 'Arduino Nano', 499, 1, 499),
(54, 25, 7, '0.96 inch OLED Display', 199, 1, 199),
(55, 25, 8, 'NE555 Timer IC', 35, 1, 35),
(56, 26, 5, 'ESP8266 NodeMCU', 349, 1, 349),
(57, 26, 8, 'NE555 Timer IC', 35, 1, 35),
(58, 26, 10, 'Male to Male Jumper Wires', 79, 1, 79),
(59, 26, 12, '20x4 LCD Display', 249, 1, 249),
(60, 27, 19, '5V DC Gear Motor', 149, 1, 149),
(61, 27, 20, '10uF Electrolytic Capacitor', 5, 10, 50),
(62, 27, 21, '100uF Electrolytic Capacitor', 8, 10, 80),
(63, 27, 23, '220 Ohm Resistor', 2, 10, 20),
(64, 28, 28, 'PIR Motion Sensor', 65, 1, 65),
(65, 28, 29, 'LDR Light Sensor Module', 35, 1, 35),
(66, 28, 30, 'Raspberry Pi 4 Model B', 6499, 1, 6499),
(67, 28, 32, 'L298N Dual H-Bridge Motor Driver Module', 149, 1, 149),
(68, 29, 3, 'HC-SR04 Ultrasonic Sensor', 99, 1, 99),
(69, 29, 4, 'Arduino Nano', 499, 1, 499),
(70, 29, 32, 'L298N Dual H-Bridge Motor Driver Module', 149, 1, 149),
(71, 30, 3, 'HC-SR04 Ultrasonic Sensor', 99, 5, 495),
(72, 31, 2, 'ESP32 DevKit V1', 499, 1, 499),
(73, 31, 3, 'HC-SR04 Ultrasonic Sensor', 99, 1, 99);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int NOT NULL,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` int NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `rating` decimal(2,1) NOT NULL DEFAULT '0.0',
  `review_count` int NOT NULL DEFAULT '0',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `fk_products_category` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category_id`, `brand`, `model_number`, `sku`, `short_description`, `description`, `price`, `stock`, `rating`, `review_count`, `status`, `featured`, `created_at`) VALUES
(1, 'Arduino Uno R3', 1, 'Arduino', 'A000066', 'TV-MCU-0001', 'ATmega328P based development board for electronics projects and prototyping.', 'Arduino Uno R3 is a popular ATmega328P-based microcontroller development board designed for electronics projects, prototyping, automation, and learning embedded systems. It provides digital and analog I/O pins for connecting sensors, LEDs, motors, displays, and other electronic components. Its simple programming environment makes it suitable for beginners as well as experienced makers.', 799, 30, 4.5, 12, 'active', 0, '2026-08-21 09:04:03'),
(2, 'ESP32 DevKit V1', 1, 'Espressif', 'ESP32-WROOM-32', 'TV-MCU-0002', 'Wi-Fi and Bluetooth enabled development board based on the ESP32 microcontroller.', 'ESP32 DevKit V1 is a powerful development board based on the ESP32-WROOM-32 module. It features a dual-core processor along with built-in Wi-Fi and Bluetooth connectivity, making it suitable for IoT applications, wireless automation, smart devices, and embedded projects. The board provides multiple GPIO pins and interfaces for connecting sensors and peripherals.', 499, 9, 5.0, 10, 'active', 1, '2026-08-21 09:05:22'),
(3, 'HC-SR04 Ultrasonic Sensor', 6, 'Generic', 'HC-SR04', 'TV-SEN-0001', 'Ultrasonic distance sensor for measuring object distance in robotics and automation projects.', 'HC-SR04 is an ultrasonic distance measuring sensor commonly used in robotics, automation, obstacle detection, and Arduino projects. It uses ultrasonic waves to measure the distance between the sensor and an object. The sensor provides separate trigger and echo pins and can measure distances from approximately 2cm to 400cm.', 99, 42, 4.5, 18, 'active', 0, '2026-08-21 09:06:10'),
(4, 'Arduino Nano', 1, 'Arduino', 'A000005', 'TV-MCU-0004', 'Compact ATmega328P based microcontroller development board.', 'Arduino Nano is a compact ATmega328P-based development board designed for projects where space is limited. It provides multiple digital and analog I/O pins for connecting sensors, displays, motors, and other electronic components. Its small size makes it suitable for compact prototypes, embedded projects, robotics, and automation applications.', 499, 18, 4.6, 12, 'active', 1, '2026-08-29 06:45:27'),
(5, 'ESP8266 NodeMCU', 1, 'NodeMCU', 'ESP8266-12E', 'TV-MCU-0005', 'Wi-Fi enabled development board based on ESP8266.', 'ESP8266 NodeMCU is a compact Wi-Fi-enabled development board designed for Internet of Things and wireless electronics projects. It provides an ESP8266 microcontroller with built-in Wi-Fi connectivity and multiple GPIO pins. The board is suitable for smart home systems, web-controlled devices, sensor monitoring, and IoT prototypes.', 349, 7, 4.5, 18, 'active', 1, '2026-08-29 06:45:27'),
(6, '16x2 LCD Display', 3, 'Generic', 'LCD1602', 'TV-DIS-0001', '16x2 character LCD module with parallel interface.', 'The 16x2 LCD Display is a character display module capable of displaying 16 characters in each of its two rows. It is commonly used with Arduino, microcontrollers, and embedded systems to display sensor readings, messages, values, and system information. Its simple interface and LED backlight make it useful for a wide range of electronics projects.', 149, 36, 4.4, 10, 'active', 0, '2026-08-29 06:45:27'),
(7, '0.96 inch OLED Display', 3, 'Generic', 'OLED-096-I2C', 'TV-DIS-0002', 'Compact I2C OLED display module for embedded projects.', 'The 0.96 inch OLED Display is a compact 128x64 pixel display module suitable for embedded and portable electronics projects. It provides a sharp OLED display and commonly uses the I2C interface, allowing it to communicate with microcontrollers using only a few connections. It can be used to display sensor readings, text, icons, and project status information.', 199, 29, 4.7, 15, 'active', 0, '2026-08-29 06:45:27'),
(8, 'NE555 Timer IC', 4, 'Texas Instruments', 'NE555P', 'TV-IC-0001', 'Versatile timer IC for timing and oscillator circuits.', 'NE555 Timer IC is a versatile timer and oscillator integrated circuit widely used in electronic circuits. It can operate in monostable and astable modes for generating time delays, pulses, and oscillating signals. The IC is useful for LED flashers, pulse generators, timers, alarms, and many other basic electronics applications.', 35, 98, 4.5, 8, 'active', 0, '2026-08-29 06:45:27'),
(9, '830 Point Breadboard', 2, 'Generic', 'MB-102', 'TV-ACC-0001', 'Solderless 830 point breadboard for electronics prototyping.', 'The 830 Point Breadboard is a solderless prototyping board designed for quickly assembling and testing electronic circuits without soldering. It provides multiple tie points and power rails for connecting resistors, ICs, sensors, microcontrollers, and other components. It is ideal for electronics experiments, prototyping, and development.', 129, 0, 4.5, 14, 'active', 0, '2026-08-29 06:47:19'),
(10, 'Male to Male Jumper Wires', 2, 'Generic', 'JW-MM-40', 'TV-ACC-0002', '40-piece male-to-male jumper wire set.', 'Male to Male Jumper Wires are flexible Dupont-style wires used to make temporary electrical connections between breadboards, development boards, sensors, and electronic modules. Their standard 2.54mm pin spacing makes them compatible with many common prototyping systems. They are useful for Arduino, Raspberry Pi, and general electronics projects.', 79, 79, 4.4, 9, 'active', 0, '2026-08-29 06:47:19'),
(11, 'USB Type-B Cable', 2, 'Generic', 'USB-B-1M', 'TV-ACC-0003', 'USB Type-B cable for Arduino and compatible boards.', 'USB Type-B Cable is commonly used to connect compatible development boards, printers, and other electronic devices to a computer or USB power source. This cable supports USB 2.0 data communication and can be used for programming compatible microcontroller boards as well as transferring data and supplying power.', 99, 45, 4.3, 7, 'active', 0, '2026-08-29 06:47:19'),
(12, '20x4 LCD Display', 3, 'Generic', 'LCD2004', 'TV-DIS-0003', '20x4 character LCD module for embedded projects.', 'The 20x4 LCD Display is a character display module capable of showing 20 characters across four rows. It provides more display space than a standard 16x2 LCD, making it suitable for projects that need to show multiple readings or larger amounts of information. It can be used with Arduino, microcontrollers, and other embedded systems.', 249, 24, 4.5, 11, 'active', 0, '2026-08-29 06:47:19'),
(13, 'TM1637 4 Digit Display', 3, 'Generic', 'TM1637-4D', 'TV-DIS-0004', 'Four digit seven segment display module with TM1637 driver.', 'The TM1637 4 Digit Display is a compact seven-segment LED module designed for displaying numeric values such as counters, timers, temperatures, and sensor readings. Its TM1637 driver uses a simple two-wire serial interface, reducing the number of microcontroller pins required. It is suitable for Arduino and other embedded electronics projects.', 89, 6, 4.4, 6, 'active', 0, '2026-08-29 06:47:19'),
(14, 'LM358 Operational Amplifier IC', 4, 'Texas Instruments', 'LM358P', 'TV-IC-0002', 'Dual operational amplifier IC for analog circuits.', 'LM358 is a dual operational amplifier IC commonly used for signal amplification, filtering, voltage comparison, and analog signal processing. It contains two independent operational amplifiers in a single package and can operate from a single power supply. It is widely used in sensor circuits, audio circuits, measurement systems, and general analog electronics.', 25, 99, 4.6, 13, 'active', 0, '2026-08-29 06:47:19'),
(15, 'L293D Motor Driver IC', 4, 'STMicroelectronics', 'L293D', 'TV-IC-0003', 'Dual H-bridge motor driver IC for DC motors.', 'L293D is a four-channel motor driver IC designed to control DC motors, relays, and other inductive loads. It allows a microcontroller to control motor direction and operation while providing a separate motor supply. The IC is commonly used in robotics, automation, Arduino projects, and small motor-control applications.', 45, 75, 4.7, 19, 'active', 0, '2026-08-29 06:47:19'),
(16, 'ULN2003A Darlington Driver IC', 4, 'Texas Instruments', 'ULN2003A', 'TV-IC-0004', 'Seven channel Darlington transistor array driver.', 'ULN2003A is a seven-channel Darlington transistor array designed for driving higher-current loads from low-power digital signals. It can be used to control relays, motors, LEDs, solenoids, and other loads that cannot be driven directly by a microcontroller. Its integrated protection diodes make it useful in inductive load switching applications.', 30, 5, 4.5, 10, 'active', 0, '2026-08-29 06:47:19'),
(17, 'SG90 Micro Servo Motor', 5, 'TowerPro', 'SG90', 'TV-MOT-0001', 'Lightweight 180 degree micro servo motor.', 'SG90 is a compact micro servo motor commonly used in robotics, automation, model projects, and Arduino applications. It provides approximately 180 degrees of controlled rotation and can be operated using a PWM control signal. Its small size and lightweight design make it suitable for mechanisms, robotic arms, steering systems, and moving parts.', 129, 40, 4.6, 22, 'active', 0, '2026-08-29 06:47:19'),
(18, 'MG996R Servo Motor', 5, 'TowerPro', 'MG996R', 'TV-MOT-0002', 'High torque metal gear servo motor.', 'MG996R is a high-torque digital servo motor designed for applications requiring stronger and more precise movement than small micro servos. It provides approximately 180 degrees of rotation and supports PWM control. It is suitable for robotics, robotic arms, RC models, automation mechanisms, and other projects requiring higher torque.', 499, 0, 4.5, 12, 'active', 0, '2026-08-29 06:47:19'),
(19, '5V DC Gear Motor', 5, 'Generic', 'DC-GM-5V', 'TV-MOT-0003', '5V geared DC motor for robotics applications.', 'The 5V DC Gear Motor is a compact geared motor designed to provide controlled rotational movement for small robotics and automation projects. Its integrated gearbox increases torque while reducing output speed, making it suitable for wheels, mechanisms, small robots, and other motor-driven applications. The motor direction can be controlled by reversing its polarity.', 149, 34, 4.3, 8, 'active', 0, '2026-08-29 06:47:19'),
(20, '10uF Electrolytic Capacitor', 8, 'Generic', 'CAP-10UF-25V', 'TV-PAS-0001', '10uF 25V electrolytic capacitor.', 'The 10uF Electrolytic Capacitor is a polarized capacitor commonly used for filtering, smoothing, decoupling, and timing applications in electronic circuits. Its compact through-hole design makes it easy to use on breadboards and PCBs. It can help reduce voltage fluctuations and stabilize power supply lines in various electronics projects.', 5, 190, 4.4, 5, 'active', 0, '2026-08-29 06:47:19'),
(21, '100uF Electrolytic Capacitor', 8, 'Generic', 'CAP-100UF-25V', 'TV-PAS-0002', '100uF 25V electrolytic capacitor.', 'The 100uF Electrolytic Capacitor is a polarized capacitor commonly used for power supply filtering, voltage smoothing, decoupling, and energy storage applications. Its higher capacitance makes it useful for reducing fluctuations in DC power circuits. The through-hole package allows convenient installation on breadboards and printed circuit boards.', 8, 170, 4.5, 6, 'active', 0, '2026-08-29 06:47:19'),
(22, '1N4007 Diode', 8, 'Generic', '1N4007', 'TV-PAS-0003', 'General purpose 1A rectifier diode.', '1N4007 is a general-purpose rectifier diode commonly used for converting AC to DC, reverse-polarity protection, flyback protection, and basic switching applications. It supports a high reverse voltage rating and is suitable for many low-frequency power supply and protection circuits. Its standard through-hole package makes it easy to use in prototypes.', 3, 20, 4.6, 15, 'active', 0, '2026-08-29 06:47:19'),
(23, '220 Ohm Resistor', 8, 'Generic', 'RES-220R', 'TV-PAS-0004', '220 ohm through-hole resistor for electronic circuits.', 'The 220 Ohm Resistor is a common passive electronic component used to limit current and control voltage in electronic circuits. It is frequently used with LEDs, microcontrollers, sensors, and other components to provide appropriate current protection. Its carbon-film construction and through-hole format make it suitable for breadboards and general-purpose circuit design.', 2, 490, 4.5, 8, 'active', 0, '2026-08-29 06:47:19'),
(24, 'LM2596 Buck Converter', 7, 'Generic', 'LM2596', 'TV-PWR-0001', 'Adjustable DC-DC step-down buck converter module.', 'LM2596 Buck Converter is an adjustable DC-DC step-down power module designed to convert a higher input voltage into a lower regulated output voltage. It can provide an adjustable output and is commonly used to power microcontrollers, sensors, displays, and other electronic circuits. The module is useful for battery-powered systems and power supply projects.', 69, 48, 4.6, 17, 'active', 0, '2026-08-29 06:47:19'),
(25, 'MT3608 Boost Converter', 7, 'Generic', 'MT3608', 'TV-PWR-0002', 'Adjustable DC-DC boost converter module.', 'MT3608 Boost Converter is a compact DC-DC step-up power module designed to increase a lower input voltage to a higher adjustable output voltage. It is useful in battery-powered projects and electronic circuits where the available supply voltage is lower than the required operating voltage. Its compact size makes it suitable for portable and embedded applications.', 59, 45, 4.5, 10, 'active', 0, '2026-08-29 06:47:19'),
(26, 'TP4056 Battery Charging Module', 7, 'Generic', 'TP4056', 'TV-PWR-0003', 'Lithium-ion battery charging module with protection.', 'TP4056 Battery Charging Module is designed for charging single-cell lithium-ion batteries from a 5V power source. It provides a controlled charging process and commonly includes protection features against overcharging and over-discharging. The module is useful for rechargeable battery projects, portable electronics, IoT devices, and DIY power systems.', 35, 70, 4.6, 18, 'active', 0, '2026-08-29 06:47:19'),
(27, 'DHT11 Temperature Humidity Sensor', 6, 'Generic', 'DHT11', 'TV-SEN-0004', 'Digital temperature and humidity sensor module.', 'DHT11 is a digital sensor module capable of measuring temperature and relative humidity. It provides digital output that can be easily read by Arduino, ESP8266, ESP32, and other microcontrollers. The sensor is commonly used in weather monitoring, environmental sensing, home automation, and IoT projects.', 79, 0, 4.4, 16, 'active', 1, '2026-08-29 06:47:19'),
(28, 'PIR Motion Sensor', 6, 'Generic', 'HC-SR501', 'TV-SEN-0002', 'PIR sensor module for human motion detection.', 'PIR Motion Sensor is designed to detect movement by sensing changes in infrared radiation produced by people and other warm objects. It provides a digital output signal when motion is detected. The module is commonly used in security systems, automatic lighting, smart home projects, and motion-activated devices.', 65, 44, 4.5, 14, 'active', 0, '2026-08-29 06:47:19'),
(29, 'LDR Light Sensor Module', 6, 'Generic', 'LDR-MODULE', 'TV-SEN-0003', 'Light-dependent resistor sensor module.', 'LDR Light Sensor Module is designed to detect changes in surrounding light intensity using a light-dependent resistor. It can provide both analog and digital outputs and includes adjustable sensitivity for different applications. The module is suitable for automatic lighting, brightness detection, Arduino projects, and other light-sensitive electronic systems.', 35, 59, 4.3, 9, 'active', 0, '2026-08-29 06:47:19'),
(30, 'Raspberry Pi 4 Model B', 9, 'Raspberry Pi', 'RPI4-4GB', 'TV-SBC-0001', 'Powerful single-board computer with 4GB RAM.', 'Raspberry Pi 4 Model B is a powerful single-board computer suitable for programming, electronics, networking, multimedia, and embedded computing projects. It features a quad-core processor, multiple connectivity options, wireless networking, and a 40-pin GPIO interface. It can be used for home automation, servers, IoT systems, programming projects, and educational applications.', 6499, 7, 4.8, 25, 'active', 1, '2026-08-29 06:47:19'),
(31, 'Raspberry Pi Zero 2 W', 9, 'Raspberry Pi', 'RPIZ2W', 'TV-SBC-0002', 'Compact wireless single-board computer.', 'Raspberry Pi Zero 2 W is a compact single-board computer designed for space-constrained computing and embedded applications. It provides a quad-core ARM processor, wireless connectivity, and a 40-pin GPIO interface. Its small form factor makes it suitable for IoT devices, automation projects, portable systems, and lightweight Linux-based applications.', 1899, 12, 4.7, 18, 'active', 0, '2026-08-29 06:47:19'),
(32, 'L298N Dual H-Bridge Motor Driver Module', 5, 'Generic', 'L298N', 'TV-MOT-004', 'Dual H-Bridge motor driver module for controlling DC motors and stepper motors.', 'L298N Dual H-Bridge Motor Driver Module is a motor control module designed to drive DC motors and stepper motors using a microcontroller. It supports independent control of two DC motors and provides direction and speed control through its H-bridge circuitry. The module includes screw terminals for motor and power connections and is commonly used in Arduino, robotics, automation, and DIY electronics projects.', 149, 33, 0.0, 0, 'active', 0, '2026-09-20 14:49:38'),
(34, '18650 Li-ion Battery Holder', 2, 'Generic', '18650-1S-HOLDER', 'TV-ACC-004', 'Single-cell 18650 lithium-ion battery holder for electronics and DIY projects.', '18650 Li-ion Battery Holder is a compact battery holder designed to securely hold a single 18650 lithium-ion battery. It provides convenient power connections for DIY electronics, Arduino projects, portable devices, robotics, and battery-powered prototypes. The holder includes positive and negative terminals for easy connection to electronic circuits.', 29, 0, 0.0, 0, 'active', 0, '2026-09-20 15:51:46');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  KEY `fk_product_images_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `image_path`, `alt_text`, `is_primary`, `sort_order`, `created_at`) VALUES
(1, 1, 'assets\\images\\Products\\Microcontrollers\\Arduino-uno-R3\\1.jpeg', 'Arduino Uno R3 development board', 0, 1, '2026-08-21 09:29:42'),
(2, 2, 'assets\\images\\Products\\Microcontrollers\\ESP32\\main.webp', 'ESP32 DevKit V1 development board', 1, 1, '2026-08-21 09:31:24'),
(3, 3, 'assets\\images\\Products\\Sensors\\Ultrasonic-Sensor-HC‑SR04\\1.webp', 'HC-SR04 ultrasonic distance sensor', 1, 1, '2026-08-21 09:32:47'),
(4, 4, 'assets\\images\\Products\\Microcontrollers\\Ardino-Nano\\1.jpg', 'Arduino Nano R3 Development Board', 1, 1, '2026-09-08 10:21:57'),
(5, 5, 'assets\\images\\Products\\Microcontrollers\\ESP8266\\1.webp', 'ESP8266 NodeMCU Development Board', 1, 1, '2026-09-08 10:24:04'),
(6, 6, 'assets\\images\\Products\\Displays\\16x02-lcd\\1.webp', '16x2 LCD Display Module', 1, 1, '2026-09-08 10:25:19'),
(7, 7, 'assets\\images\\Products\\Displays\\0.96-oled\\1.webp', '0.96 Inch OLED Display Module', 1, 1, '2026-09-08 10:26:48'),
(8, 8, 'assets\\images\\Products\\Integrated-circuits\\ne555\\1.webp', 'NE555 Timer IC', 1, 1, '2026-09-08 10:28:12'),
(9, 9, 'assets\\images\\Products\\Accessories\\830-point-breadboard\\1.webp', '830 Point Solderless Breadboard', 1, 1, '2026-09-08 10:30:12'),
(10, 10, 'assets\\images\\Products\\Accessories\\male-to-male-jumper-wires.webp', 'Male to Male Jumper Wires', 1, 1, '2026-09-08 10:31:51'),
(11, 11, 'assets\\images\\Products\\Accessories\\USB-Type-B-cable.jpg', 'USB Type-B Cable', 1, 1, '2026-09-08 10:33:46'),
(12, 12, 'assets\\images\\Products\\Displays\\20x4-lcd\\1.webp', '20x4 LCD Display Module', 1, 1, '2026-09-08 10:34:54'),
(13, 13, 'assets\\images\\Products\\Displays\\tm1637-7-segment\\1.webp', 'TM1637 4 Digit Display Module', 1, 1, '2026-09-08 10:36:48'),
(14, 14, 'assets\\images\\Products\\Integrated-circuits\\lm358\\1.webp', 'LM358 Operational Amplifier IC', 1, 1, '2026-09-08 10:38:03'),
(15, 15, 'assets\\images\\Products\\Integrated-circuits\\l293d\\1.webp', 'L293D Motor Driver IC', 1, 1, '2026-09-08 10:39:28'),
(16, 16, 'assets\\images\\Products\\Integrated-circuits\\uln2003a\\1.webp', 'ULN2003A Darlington Driver IC', 1, 1, '2026-09-08 10:41:11'),
(17, 17, 'assets\\images\\Products\\Motors-actuators\\sg90-servo\\1.webp', 'SG90 Micro Servo Motor', 1, 1, '2026-09-08 10:43:25'),
(18, 18, 'assets\\images\\Products\\Motors-actuators\\mg996r-servo\\1.jpg', 'MG996R Metal Gear Servo Motor', 1, 1, '2026-09-08 10:43:25'),
(19, 19, 'assets\\images\\Products\\Motors-actuators\\5v-dc-gear-motor\\1.webp', '5V DC Gear Motor', 1, 1, '2026-09-08 10:45:35'),
(20, 20, 'assets\\images\\Products\\Passive-components\\Capacitors\\10uf\\1.webp', '10uF Electrolytic Capacitor', 1, 1, '2026-09-08 10:45:35'),
(21, 21, 'assets\\images\\Products\\Passive-components\\Capacitors\\100uf\\1.webp', '100uF Electrolytic Capacitor', 1, 1, '2026-09-08 10:47:16'),
(22, 22, 'assets\\images\\Products\\Passive-components\\Diodes\\1n4007\\1.webp', '1N4007 Rectifier Diode', 1, 1, '2026-09-08 10:47:16'),
(23, 23, 'assets\\images\\Products\\Passive-components\\Resistors\\220-ohm\\1.jpg', '220 Ohm Resistor', 1, 1, '2026-09-08 10:49:31'),
(24, 24, 'assets\\images\\Products\\Power-modules\\lm2596\\1.webp', 'LM2596 Buck Converter Module', 1, 1, '2026-09-08 10:49:31'),
(25, 25, 'assets\\images\\Products\\Power-modules\\mt3608\\1.webp', 'MT3608 Boost Converter Module', 1, 1, '2026-09-08 10:52:47'),
(26, 26, 'assets\\images\\Products\\Power-modules\\tp4056\\1.webp', 'TP4056 Battery Charging Module', 1, 1, '2026-09-08 10:55:40'),
(27, 27, 'assets\\images\\Products\\Sensors\\DHT11-Temperature-Humidity-Sensor\\1.webp', 'DHT11 Temperature Humidity Sensor', 1, 1, '2026-09-08 10:55:40'),
(28, 28, 'assets\\images\\Products\\Sensors\\PIR-Motion-Sensor\\1.webp', 'PIR Motion Sensor Module', 1, 1, '2026-09-08 10:57:13'),
(29, 29, 'assets\\images\\Products\\Sensors\\LDR light sensor\\1.webp', 'LDR Light Sensor Module', 1, 1, '2026-09-08 10:57:13'),
(30, 30, 'assets\\images\\Products\\Single-board-computers\\Raspberry-Pi-4\\1.jpg', 'Raspberry Pi 4 Model B', 1, 1, '2026-09-08 10:59:00'),
(31, 31, 'assets\\images\\Products\\Single-board-computers\\Raspberry-Pi-Zero\\1.jpg', 'Raspberry Pi Zero 2 W', 1, 1, '2026-09-08 10:59:00'),
(32, 1, 'assets\\images\\Products\\Microcontrollers\\Ardino-Uno\\1.jpg', 'Arduino Uno R3', 0, 2, '2026-09-20 09:55:42'),
(35, 1, 'assets/images/Products/Microcontrollers/Arduino-uno-R3/6.jpeg', 'Arduino Uno R3', 0, 6, '2026-09-20 11:21:40'),
(38, 1, 'assets/images/Products/Microcontrollers/Arduino-uno-R3/7.jpg', 'Arduino Uno R3 V1', 1, 7, '2026-09-20 12:52:50'),
(39, 32, 'assets/images/Products/5/L298N Dual H-Bridge Motor Driver Module/1.jpg', 'L298N Dual H-Bridge Motor Driver Module', 1, 1, '2026-09-20 14:49:38'),
(40, 32, 'assets/images/Products/5/L298N Dual H-Bridge Motor Driver Module/2.jpg', 'L298N Dual H-Bridge Motor Driver Module', 0, 2, '2026-09-20 14:49:38'),
(41, 32, 'assets/images/Products/5/L298N Dual H-Bridge Motor Driver Module/3.jpg', 'L298N Dual H-Bridge Motor Driver Module', 0, 3, '2026-09-20 14:49:39'),
(45, 34, 'assets/images/Products/Accessories/18650 Li-ion Battery Holder/1.jpg', '18650 Li-ion Battery Holder', 0, 1, '2026-09-20 15:51:46'),
(46, 34, 'assets/images/Products/Accessories/18650 Li-ion Battery Holder/2.jpg', '18650 Li-ion Battery Holder', 1, 2, '2026-09-20 15:51:46');

-- --------------------------------------------------------

--
-- Table structure for table `product_specifications`
--

DROP TABLE IF EXISTS `product_specifications`;
CREATE TABLE IF NOT EXISTS `product_specifications` (
  `specification_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `spec_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `spec_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`specification_id`),
  KEY `fk_product_specifications_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=260 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_specifications`
--

INSERT INTO `product_specifications` (`specification_id`, `product_id`, `spec_name`, `spec_value`, `sort_order`) VALUES
(7, 2, 'Microcontroller', 'ESP32-WROOM-32', 1),
(8, 2, 'Processor', 'Dual-Core 32-bit', 2),
(9, 2, 'Operating Voltage', '3.3V', 3),
(10, 2, 'Wi-Fi', '802.11 b/g/n', 4),
(11, 2, 'Bluetooth', 'Bluetooth 4.2 BLE', 5),
(12, 2, 'GPIO Pins', '30', 6),
(13, 3, 'Sensor Type', 'Ultrasonic', 1),
(14, 3, 'Operating Voltage', '5V', 2),
(15, 3, 'Measuring Range', '2cm - 400cm', 3),
(16, 3, 'Accuracy', 'Approx. 3mm', 4),
(17, 3, 'Frequency', '40kHz', 5),
(18, 3, 'Interface', 'Trig / Echo', 6),
(19, 4, 'Microcontroller', 'ATmega328P', 1),
(20, 4, 'Operating Voltage', '5V', 2),
(21, 4, 'Input Voltage', '7-12V', 3),
(22, 4, 'Digital I/O Pins', '22', 4),
(23, 4, 'Analog Input Pins', '8', 5),
(24, 4, 'Flash Memory', '32KB', 6),
(25, 5, 'Microcontroller', 'ESP8266', 1),
(26, 5, 'Processor', '32-bit Tensilica', 2),
(27, 5, 'Operating Voltage', '3.3V', 3),
(28, 5, 'Wi-Fi', '802.11 b/g/n', 4),
(29, 5, 'Flash Memory', '4MB', 5),
(30, 5, 'GPIO Pins', '11', 6),
(31, 6, 'Display Type', 'Character LCD', 1),
(32, 6, 'Display Size', '16 x 2 Characters', 2),
(33, 6, 'Operating Voltage', '5V', 3),
(34, 6, 'Interface', 'Parallel', 4),
(35, 6, 'Character Size', '5 x 8 Dots', 5),
(36, 6, 'Backlight', 'LED', 6),
(37, 7, 'Display Type', 'OLED', 1),
(38, 7, 'Display Size', '0.96 Inch', 2),
(39, 7, 'Resolution', '128 x 64 Pixels', 3),
(40, 7, 'Operating Voltage', '3.3V - 5V', 4),
(41, 7, 'Interface', 'I2C', 5),
(42, 7, 'Driver IC', 'SSD1306', 6),
(43, 8, 'IC Type', 'Timer IC', 1),
(44, 8, 'Operating Voltage', '4.5V - 16V', 2),
(45, 8, 'Supply Current', 'Approx. 10mA', 3),
(46, 8, 'Timer Mode', 'Monostable / Astable', 4),
(47, 8, 'Number of Timers', '1', 5),
(48, 8, 'Package', 'DIP-8', 6),
(49, 9, 'Type', 'Solderless Breadboard', 1),
(50, 9, 'Tie Points', '830', 2),
(51, 9, 'Operating Voltage', 'Up to 36V', 3),
(52, 9, 'Material', 'ABS Plastic', 4),
(53, 9, 'Terminal Strips', '63', 5),
(54, 9, 'Power Rails', '2', 6),
(55, 10, 'Connector Type', 'Male to Male', 1),
(56, 10, 'Wire Type', 'Dupont Jumper Wire', 2),
(57, 10, 'Pin Spacing', '2.54mm', 3),
(58, 10, 'Wire Length', '20cm', 4),
(59, 10, 'Quantity', '40 Wires', 5),
(60, 10, 'Application', 'Breadboard / Arduino', 6),
(61, 11, 'Connector Type', 'USB Type-A to Type-B', 1),
(62, 11, 'USB Version', 'USB 2.0', 2),
(63, 11, 'Data Transfer', 'Up to 480Mbps', 3),
(64, 11, 'Cable Length', '1.5m', 4),
(65, 11, 'Connector 1', 'USB Type-A Male', 5),
(66, 11, 'Connector 2', 'USB Type-B Male', 6),
(67, 12, 'Display Type', 'Character LCD', 1),
(68, 12, 'Display Size', '20 x 4 Characters', 2),
(69, 12, 'Operating Voltage', '5V', 3),
(70, 12, 'Interface', 'Parallel', 4),
(71, 12, 'Character Size', '5 x 8 Dots', 5),
(72, 12, 'Backlight', 'LED', 6),
(73, 13, 'Display Type', '7-Segment LED', 1),
(74, 13, 'Display Size', '4 Digit', 2),
(75, 13, 'Operating Voltage', '3.3V - 5V', 3),
(76, 13, 'Interface', '2-Wire Serial', 4),
(77, 13, 'Driver IC', 'TM1637', 5),
(78, 13, 'Display Color', 'Red', 6),
(79, 14, 'IC Type', 'Dual Operational Amplifier', 1),
(80, 14, 'Operating Voltage', '3V - 32V', 2),
(81, 14, 'Number of Channels', '2', 3),
(82, 14, 'Supply Type', 'Single / Dual Supply', 4),
(83, 14, 'Package', 'DIP-8', 5),
(84, 14, 'Input Type', 'Differential', 6),
(85, 15, 'IC Type', 'Motor Driver', 1),
(86, 15, 'Motor Supply Voltage', '4.5V - 36V', 2),
(87, 15, 'Logic Voltage', '5V', 3),
(88, 15, 'Output Channels', '4', 4),
(89, 15, 'Peak Output Current', '1.2A', 5),
(90, 15, 'Package', 'DIP-16', 6),
(91, 16, 'IC Type', 'Darlington Driver', 1),
(92, 16, 'Number of Channels', '7', 2),
(93, 16, 'Output Voltage', 'Up to 50V', 3),
(94, 16, 'Output Current', 'Up to 500mA', 4),
(95, 16, 'Input Type', 'TTL / CMOS Compatible', 5),
(96, 16, 'Package', 'DIP-16', 6),
(97, 17, 'Motor Type', 'Micro Servo', 1),
(98, 17, 'Operating Voltage', '4.8V - 6V', 2),
(99, 17, 'Rotation Angle', '180°', 3),
(100, 17, 'Operating Speed', '0.1 sec / 60°', 4),
(101, 17, 'Stall Torque', '1.8kg-cm', 5),
(102, 17, 'Control Signal', 'PWM', 6),
(103, 18, 'Motor Type', 'Digital Servo', 1),
(104, 18, 'Operating Voltage', '4.8V - 7.2V', 2),
(105, 18, 'Rotation Angle', '180°', 3),
(106, 18, 'Operating Speed', '0.17 sec / 60°', 4),
(107, 18, 'Stall Torque', '9.4kg-cm', 5),
(108, 18, 'Control Signal', 'PWM', 6),
(109, 19, 'Motor Type', 'DC Gear Motor', 1),
(110, 19, 'Operating Voltage', '5V', 2),
(111, 19, 'No Load Speed', 'Approx. 200 RPM', 3),
(112, 19, 'Gearbox Type', 'Metal Gearbox', 4),
(113, 19, 'Gear Ratio', 'Approx. 1:48', 5),
(114, 19, 'Direction Control', 'Polarity Reversal', 6),
(115, 20, 'Capacitance', '10uF', 1),
(116, 20, 'Capacitor Type', 'Electrolytic', 2),
(117, 20, 'Voltage Rating', '25V', 3),
(118, 20, 'Tolerance', '±20%', 4),
(119, 20, 'Polarity', 'Polarized', 5),
(120, 20, 'Mounting Type', 'Through Hole', 6),
(121, 21, 'Capacitance', '100uF', 1),
(122, 21, 'Capacitor Type', 'Electrolytic', 2),
(123, 21, 'Voltage Rating', '25V', 3),
(124, 21, 'Tolerance', '±20%', 4),
(125, 21, 'Polarity', 'Polarized', 5),
(126, 21, 'Mounting Type', 'Through Hole', 6),
(127, 22, 'Diode Type', 'Rectifier Diode', 1),
(128, 22, 'Maximum Reverse Voltage', '1000V', 2),
(129, 22, 'Average Forward Current', '1A', 3),
(130, 22, 'Forward Voltage', 'Approx. 1.1V', 4),
(131, 22, 'Package', 'DO-41', 5),
(132, 22, 'Mounting Type', 'Through Hole', 6),
(133, 23, 'Resistance', '220 Ohm', 1),
(134, 23, 'Resistor Type', 'Carbon Film', 2),
(135, 23, 'Power Rating', '0.25W', 3),
(136, 23, 'Tolerance', '±5%', 4),
(137, 23, 'Mounting Type', 'Through Hole', 5),
(138, 23, 'Color Code', 'Red-Red-Brown-Gold', 6),
(139, 24, 'Module Type', 'DC-DC Buck Converter', 1),
(140, 24, 'Input Voltage', '4V - 35V', 2),
(141, 24, 'Output Voltage', '1.25V - 30V', 3),
(142, 24, 'Maximum Output Current', '3A', 4),
(143, 24, 'Conversion Type', 'Step Down', 5),
(144, 24, 'Adjustable Output', 'Yes', 6),
(145, 25, 'Module Type', 'DC-DC Boost Converter', 1),
(146, 25, 'Input Voltage', '2V - 24V', 2),
(147, 25, 'Output Voltage', '5V - 28V', 3),
(148, 25, 'Maximum Output Current', '2A', 4),
(149, 25, 'Conversion Type', 'Step Up', 5),
(150, 25, 'Adjustable Output', 'Yes', 6),
(151, 26, 'Module Type', 'Li-Ion Battery Charger', 1),
(152, 26, 'Input Voltage', '5V', 2),
(153, 26, 'Charging Voltage', '4.2V', 3),
(154, 26, 'Charging Current', 'Up to 1A', 4),
(155, 26, 'Battery Type', 'Single Cell Li-Ion', 5),
(156, 26, 'Protection', 'Overcharge / Overdischarge', 6),
(157, 27, 'Sensor Type', 'Temperature & Humidity', 1),
(158, 27, 'Operating Voltage', '3.3V - 5V', 2),
(159, 27, 'Temperature Range', '0°C - 50°C', 3),
(160, 27, 'Humidity Range', '20% - 90% RH', 4),
(161, 27, 'Temperature Accuracy', '±2°C', 5),
(162, 27, 'Interface', 'Digital', 6),
(163, 28, 'Sensor Type', 'PIR Motion Sensor', 1),
(164, 28, 'Operating Voltage', '5V - 20V', 2),
(165, 28, 'Detection Range', 'Up to 7m', 3),
(166, 28, 'Detection Angle', 'Approx. 120°', 4),
(167, 28, 'Output Type', 'Digital', 5),
(168, 28, 'Trigger Mode', 'Repeatable / Single', 6),
(169, 29, 'Sensor Type', 'Light Sensor', 1),
(170, 29, 'Operating Voltage', '3.3V - 5V', 2),
(171, 29, 'Sensor Element', 'LDR', 3),
(172, 29, 'Output Type', 'Analog / Digital', 4),
(173, 29, 'Sensitivity', 'Adjustable', 5),
(174, 29, 'Interface', 'AO / DO', 6),
(175, 30, 'Processor', 'Broadcom BCM2711 Quad-Core', 1),
(176, 30, 'CPU Speed', '1.5GHz', 2),
(177, 30, 'RAM', '4GB', 3),
(178, 30, 'Wi-Fi', 'Dual-Band 802.11ac', 4),
(179, 30, 'Bluetooth', 'Bluetooth 5.0 BLE', 5),
(180, 30, 'GPIO Pins', '40', 6),
(181, 31, 'Processor', 'Quad-Core ARM Cortex-A53', 1),
(182, 31, 'CPU Speed', '1GHz', 2),
(183, 31, 'RAM', '512MB', 3),
(184, 31, 'Wi-Fi', '2.4GHz 802.11 b/g/n', 4),
(185, 31, 'Bluetooth', 'Bluetooth 4.2 BLE', 5),
(186, 31, 'GPIO Pins', '40', 6),
(224, 32, 'Driver IC', 'L298N', 1),
(225, 32, 'Motor Channels', '2', 2),
(226, 32, 'Motor Type', 'DC Motor / Stepper Motor', 3),
(227, 32, 'Motor Supply Voltage', '5V–35V', 4),
(228, 32, 'Logic Voltage', '5V', 5),
(229, 32, 'Continuous Output Current', 'Up to 2A per channel', 6),
(230, 34, 'Battery Type', '18650 Li-ion', 1),
(231, 34, 'Battery Cells', '1', 2),
(232, 34, 'Configuration', '1S', 3),
(233, 34, 'Nominal Voltage', '3.7V', 4),
(234, 34, 'Compatible Battery', '18650 Rechargeable Cell', 5),
(235, 34, 'Mounting Type', 'Panel / Project Mount', 6),
(254, 1, 'Microcontroller', 'ATmega328P', 1),
(255, 1, 'Operating Voltage', '5V', 2),
(256, 1, 'Digital I/O Pins', '14', 3),
(257, 1, 'Analog Input Pins', '6', 4),
(258, 1, 'Flash Memory', '32kb', 5),
(259, 1, 'Clock Speed', '16MHz', 6);

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

DROP TABLE IF EXISTS `returns`;
CREATE TABLE IF NOT EXISTS `returns` (
  `return_id` int NOT NULL AUTO_INCREMENT,
  `order_item_id` int NOT NULL,
  `user_id` int NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `return_status` enum('requested','approved','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`return_id`),
  KEY `fk_returns_user` (`user_id`),
  KEY `fk_returns_order_item` (`order_item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`return_id`, `order_item_id`, `user_id`, `reason`, `description`, `return_status`, `created_at`, `updated_at`) VALUES
(1, 17, 1, 'damaged', 'The Product is Damaged', 'completed', '2026-09-03 10:24:15', NULL),
(2, 25, 8, 'not-as-described', 'Product does not match description', 'rejected', '2026-09-23 10:51:32', '2026-09-23 11:21:19'),
(3, 28, 8, 'damaged', 'This product is damaged', 'approved', '2026-09-23 10:51:55', NULL),
(4, 27, 8, 'damaged', '', 'requested', '2026-09-23 11:00:42', '2026-09-23 11:20:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `password`, `profile_image`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rahul Hakabhai Kher', 'Rahul_Kher', 'kherrahul720@gmail.com', '$2y$10$Y2nIeUeOMnb3VtCBA8MYBudBl07T7YmIwTP9.XSLJP70HyjxvysAe', 'assets/images/Profile/user_1_1789730128.jpeg', 'customer', 'active', '2026-08-27 06:03:37', '2026-09-18 11:15:28'),
(3, 'Ankit Pankajbhai Chavda', 'Ankit_Chavda', 'chavdaankit720@gmail.com', '$2y$10$3ptlnC.1Np3Mz4XAI8yx.OckpEk9NOV5533Q/U.I6KmRQLsi7nN96', 'assets/images/profile/user_3_1789634057.jpeg', 'customer', 'active', '2026-08-27 06:17:24', '2026-08-27 06:17:24'),
(7, 'Bhavik Kher', 'Bhavik_kher', 'bhavik720@gmail.com', '$2a$10$SMx01ntBWC08Wmu6xFn5UOQVwM/e1AktiRCsRvge1l2/jE2S99rEG', NULL, 'admin', 'active', '2026-08-27 09:43:09', '2026-08-27 09:43:09'),
(8, 'Dhruv V Desai', 'desaidhruv2109', 'dd1447817@gmail.com', '$2y$10$xUfxoDzUYuccLA31HOxXleYnUUNPnU4WjhIWj/nv4PFIeKMIGNRJG', NULL, 'customer', 'active', '2026-09-23 09:59:41', '2026-09-23 09:59:41'),
(9, 'Shivam J Goswami', 'shivamgiri2207', 'shivamgiri2207@gmail.com', '$2y$10$20sGufM7uYT93K5XxqPzuuu4h.8clGbLcELHbvq83yYQhMusMoENy', NULL, 'customer', 'active', '2026-09-23 10:00:41', '2026-09-23 10:00:41'),
(10, 'Bhavik H Kher', 'Bhavik_H_Kher720', 'kherbhavik4242@gmail.com', '$2y$10$gm20EcvFaIuMR24qAHYcrOh3GXVFsqmMPzfE8GLY7/sDHxHQmZ3ca', NULL, 'customer', 'active', '2026-09-23 16:24:22', '2026-09-23 16:24:22'),
(11, 'Smit Bhalani', 'smitsoni', 'smitbhalani@gmail.com', '$2y$10$75ibwNjfM53GwkXitaPlReFITC4zqp93SNsiLdS3WkqrfPDbMGOL6', NULL, 'customer', 'active', '2026-09-26 03:39:50', '2026-09-26 03:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE IF NOT EXISTS `wishlist` (
  `wishlist_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wishlist_id`),
  UNIQUE KEY `user_id` (`user_id`,`product_id`),
  KEY `fk_wishlist_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `user_id`, `product_id`, `created_at`, `updated_at`) VALUES
(14, 1, 3, '2026-09-17 07:10:47', '2026-09-17 07:10:47'),
(31, 3, 3, '2026-09-17 12:16:48', '2026-09-17 12:16:48'),
(35, 3, 1, '2026-09-17 13:43:29', '2026-09-17 13:43:29'),
(36, 1, 2, '2026-09-18 10:44:39', '2026-09-18 10:44:39'),
(37, 10, 1, '2026-09-23 16:30:40', '2026-09-23 16:30:40'),
(38, 10, 2, '2026-09-23 16:30:41', '2026-09-23 16:30:41'),
(39, 10, 3, '2026-09-23 16:30:43', '2026-09-23 16:30:43'),
(41, 10, 18, '2026-09-23 16:30:50', '2026-09-23 16:30:50'),
(42, 3, 2, '2026-09-24 02:59:04', '2026-09-24 02:59:04'),
(43, 3, 32, '2026-09-24 03:00:13', '2026-09-24 03:00:13');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `fk_contact_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`address_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_product_images_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_specifications`
--
ALTER TABLE `product_specifications`
  ADD CONSTRAINT `fk_product_specifications_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `fk_returns_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_returns_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
