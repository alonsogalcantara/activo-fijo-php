-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 13-02-2026 a las 20:16:09
-- Versión del servidor: 8.3.0
-- Versión de PHP: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `asset_manager`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_name` varchar(150) NOT NULL,
  `username` varchar(150) DEFAULT NULL,
  `password` varchar(150) DEFAULT NULL,
  `provider` varchar(150) DEFAULT NULL,
  `contract_ref` varchar(150) DEFAULT NULL,
  `renewal_date` date DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `assigned_to` int DEFAULT NULL,
  `observations` text,
  `cost` decimal(10,2) DEFAULT '0.00',
  `currency` varchar(10) DEFAULT 'MXN',
  `frequency` varchar(50) DEFAULT 'Mensual',
  `account_type` varchar(50) DEFAULT 'Individual',
  `max_licenses` int DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `account_members`
--

DROP TABLE IF EXISTS `account_members`;
CREATE TABLE IF NOT EXISTS `account_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(20) DEFAULT 'normal',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `assets`
--

DROP TABLE IF EXISTS `assets`;
CREATE TABLE IF NOT EXISTS `assets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `description` text,
  `processor` varchar(100) DEFAULT NULL,
  `ram` varchar(50) DEFAULT NULL,
  `storage` varchar(50) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(10,2) DEFAULT '0.00',
  `status` varchar(50) DEFAULT 'Disponible',
  `assigned_to` int DEFAULT NULL,
  `photo_filename` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `device_user` text,
  `device_password` text,
  `color` varchar(50) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `gender_cut` varchar(20) DEFAULT NULL,
  `license_plate` varchar(20) DEFAULT NULL,
  `vin` varchar(50) DEFAULT NULL,
  `vehicle_year` int DEFAULT NULL,
  `mileage` decimal(10,2) DEFAULT '0.00',
  `dimensions` varchar(100) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `batch_number` varchar(50) DEFAULT NULL,
  `min_stock` int DEFAULT '0',
  `acquisition_type` varchar(50) DEFAULT 'Compra' COMMENT 'Tipo de adquisición: Compra o Arrendamiento',
  `leasing_company` varchar(255) DEFAULT NULL COMMENT 'Nombre de la empresa arrendadora si aplica',
  `accumulated_depreciation_override` decimal(10,2) DEFAULT NULL COMMENT 'Valor manual para sobrescribir la depreciación calculada',
  `disposal_date` date DEFAULT NULL COMMENT 'Fecha efectiva de la baja',
  `disposal_price` decimal(10,2) DEFAULT '0.00' COMMENT 'Precio de venta recuperado (si hubo venta)',
  `disposal_reason` varchar(50) DEFAULT NULL COMMENT 'Motivo: Venta, Robo, Obsolescencia, Donación, Chatarra',
  `book_value_at_disposal` decimal(10,2) DEFAULT NULL COMMENT 'Valor en libros congelado al momento de la baja',
  `cost_center` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `serial_number` (`serial_number`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asset_credentials`
--

DROP TABLE IF EXISTS `asset_credentials`;
CREATE TABLE IF NOT EXISTS `asset_credentials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `username` varchar(150) DEFAULT NULL,
  `password` varchar(150) DEFAULT NULL,
  `security_question` varchar(255) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asset_history`
--

DROP TABLE IF EXISTS `asset_history`;
CREATE TABLE IF NOT EXISTS `asset_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `related_user_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  KEY `related_user_id` (`related_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `actor_username` varchar(100) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  `old_value` text,
  `new_value` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `filename` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidents`
--

DROP TABLE IF EXISTS `incidents`;
CREATE TABLE IF NOT EXISTS `incidents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `incident_date` date DEFAULT NULL,
  `description` text,
  `resolution_type` varchar(100) DEFAULT NULL,
  `resolution_notes` text,
  `cost` decimal(10,2) DEFAULT '0.00',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_capex` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maintenance_logs`
--

DROP TABLE IF EXISTS `maintenance_logs`;
CREATE TABLE IF NOT EXISTS `maintenance_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `reason` varchar(255) NOT NULL,
  `diagnosis` text,
  `cost` decimal(10,2) DEFAULT '0.00',
  `technician` varchar(150) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Abierto',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `service_payments`
--

DROP TABLE IF EXISTS `service_payments`;
CREATE TABLE IF NOT EXISTS `service_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `invoice_file` varchar(255) DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE IF NOT EXISTS `stock_movements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `movement_type` varchar(20) DEFAULT NULL,
  `quantity` int NOT NULL,
  `related_user_id` int DEFAULT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `second_last_name` varchar(100) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Activo',
  `role` varchar(50) DEFAULT 'General',
  `phone` varchar(50) DEFAULT NULL,
  `entry_date` date DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `password_hash` varchar(255) DEFAULT NULL,
  `system_role` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
