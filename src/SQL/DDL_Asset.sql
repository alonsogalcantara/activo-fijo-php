-- asset_manager.account_members definition

CREATE TABLE IF NOT EXISTS `account_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.accounts definition

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
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.admins definition

CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(20) DEFAULT 'normal',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.asset_credentials definition

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
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.asset_history definition

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
) ENGINE=MyISAM AUTO_INCREMENT=176 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.assets definition

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
) ENGINE=MyISAM AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.audit_logs definition

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
) ENGINE=MyISAM AUTO_INCREMENT=101 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.documents definition

CREATE TABLE IF NOT EXISTS `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `entity_id` int NOT NULL,
  `entity_type` enum('asset','account','user') NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `uploaded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`entity_id`),
  KEY `idx_entity` (`entity_type`,`entity_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.incidents definition

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
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.maintenance_logs definition

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.service_payments definition

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.stock_movements definition

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
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- asset_manager.users definition

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
) ENGINE=MyISAM AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
