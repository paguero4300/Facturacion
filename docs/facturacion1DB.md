-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.43 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para facturacion
CREATE DATABASE IF NOT EXISTS `facturacion` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `facturacion`;

-- Volcando estructura para tabla facturacion.brands
CREATE TABLE IF NOT EXISTS `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de la marca',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción de la marca',
  `logo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL del logo de la marca',
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Sitio web de la marca',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Estado activo/inactivo',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_brand_name` (`company_id`,`name`),
  KEY `brands_created_by_foreign` (`created_by`),
  KEY `brands_company_id_status_index` (`company_id`,`status`),
  KEY `brands_name_index` (`name`),
  KEY `brands_created_at_index` (`created_at`),
  CONSTRAINT `brands_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `brands_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre de la categoría',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción de la categoría',
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Color hexadecimal para la categoría',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icono de la categoría',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Estado activo/inactivo',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_category_name` (`company_id`,`name`),
  KEY `categories_created_by_foreign` (`created_by`),
  KEY `categories_company_id_status_index` (`company_id`,`status`),
  KEY `categories_name_index` (`name`),
  KEY `categories_created_at_index` (`created_at`),
  CONSTRAINT `categories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.clients
CREATE TABLE IF NOT EXISTS `clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `document_type` enum('0','1','4','6','7','A','B','C') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de documento según catálogo 06 SUNAT',
  `document_number` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número de documento',
  `business_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Razón social / Nombres y apellidos',
  `commercial_name` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre comercial',
  `address` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dirección',
  `district` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Distrito',
  `province` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Provincia',
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Departamento',
  `country_code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PE' COMMENT 'Código de país ISO',
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código postal',
  `ubigeo` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código UBIGEO SUNAT',
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_person` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Persona de contacto',
  `credit_limit` decimal(12,2) NOT NULL DEFAULT '0.00',
  `payment_days` int NOT NULL DEFAULT '0',
  `client_type` enum('regular','vip','wholesale','retail') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
  `status` enum('active','inactive','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Observaciones',
  `additional_data` json DEFAULT NULL COMMENT 'Datos adicionales JSON',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_client_document` (`company_id`,`document_type`,`document_number`),
  KEY `clients_company_id_status_index` (`company_id`,`status`),
  KEY `clients_document_type_document_number_index` (`document_type`,`document_number`),
  KEY `clients_business_name_index` (`business_name`),
  KEY `clients_created_at_index` (`created_at`),
  KEY `clients_created_by_foreign` (`created_by`),
  CONSTRAINT `clients_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `clients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.companies
CREATE TABLE IF NOT EXISTS `companies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ruc` char(11) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'RUC - 11 characters as per SUNAT',
  `business_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Razón social',
  `commercial_name` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nombre comercial',
  `address` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Dirección fiscal',
  `district` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Distrito',
  `province` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Provincia',
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Departamento',
  `country_code` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PE' COMMENT 'Código de país ISO',
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código postal',
  `ubigeo` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código UBIGEO SUNAT',
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_regime` enum('RER','GENERAL','MYPE') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Régimen tributario',
  `certificate_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta del certificado digital',
  `certificate_password` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Password del certificado (encriptado)',
  `ose_provider` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Proveedor OSE',
  `ose_endpoint` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Endpoint OSE',
  `ose_username` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Usuario OSE',
  `ose_password` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Password OSE (encriptado)',
  `qpse_config_token` text COLLATE utf8mb4_unicode_ci COMMENT 'Token de configuración QPse para crear empresa',
  `qpse_access_token` text COLLATE utf8mb4_unicode_ci COMMENT 'Token de acceso QPse para operaciones',
  `qpse_token_expires_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha de expiración del token de acceso',
  `qpse_last_response` json DEFAULT NULL COMMENT 'Última respuesta de QPse para debug',
  `status` enum('active','inactive','suspended') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `sunat_production` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ambiente SUNAT: false=beta, true=producción',
  `factiliza_token` text COLLATE utf8mb4_unicode_ci,
  `additional_config` json DEFAULT NULL COMMENT 'Configuración adicional JSON',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Indica si la empresa está activa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `companies_ruc_unique` (`ruc`),
  KEY `companies_status_index` (`status`),
  KEY `companies_tax_regime_index` (`tax_regime`),
  KEY `companies_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.document_numbering_control
CREATE TABLE IF NOT EXISTS `document_numbering_control` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `document_series_id` bigint unsigned NOT NULL,
  `document_type` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de documento',
  `series` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Serie',
  `last_number` bigint unsigned NOT NULL COMMENT 'Último número usado',
  `last_used_at` timestamp NOT NULL COMMENT 'Fecha último uso',
  `last_document_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hash último documento',
  `sequence_version` bigint unsigned NOT NULL DEFAULT '1' COMMENT 'Versión secuencia (optimistic locking)',
  `reserved_numbers` json DEFAULT NULL COMMENT 'Números reservados JSON',
  `reserved_until` timestamp NULL DEFAULT NULL COMMENT 'Reserva válida hasta',
  `sequence_integrity` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Integridad secuencial',
  `gaps_detected` json DEFAULT NULL COMMENT 'Saltos detectados JSON',
  `last_integrity_check` timestamp NULL DEFAULT NULL COMMENT 'Última verificación integridad',
  `emergency_mode` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Modo emergencia activo',
  `emergency_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo modo emergencia',
  `emergency_started_at` timestamp NULL DEFAULT NULL COMMENT 'Inicio modo emergencia',
  `documents_issued_today` int unsigned NOT NULL DEFAULT '0' COMMENT 'Documentos emitidos hoy',
  `documents_issued_month` int unsigned NOT NULL DEFAULT '0' COMMENT 'Documentos emitidos mes actual',
  `stats_date` date DEFAULT NULL COMMENT 'Fecha última actualización estadísticas',
  `lock_token` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token bloqueo exclusivo',
  `locked_until` timestamp NULL DEFAULT NULL COMMENT 'Bloqueado hasta',
  `locked_by_process` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Proceso que bloqueó',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_company_series_control` (`company_id`,`document_series_id`),
  UNIQUE KEY `unique_company_document_control` (`company_id`,`document_type`,`series`),
  KEY `document_numbering_control_document_series_id_foreign` (`document_series_id`),
  KEY `document_numbering_control_company_id_document_type_index` (`company_id`,`document_type`),
  KEY `document_numbering_control_last_used_at_index` (`last_used_at`),
  KEY `document_numbering_control_emergency_mode_index` (`emergency_mode`),
  KEY `document_numbering_control_locked_until_index` (`locked_until`),
  KEY `document_numbering_control_sequence_version_index` (`sequence_version`),
  CONSTRAINT `document_numbering_control_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_numbering_control_document_series_id_foreign` FOREIGN KEY (`document_series_id`) REFERENCES `document_series` (`id`) ON DELETE CASCADE,
  CONSTRAINT `check_last_number_positive` CHECK ((`last_number` >= 0)),
  CONSTRAINT `check_sequence_version_positive` CHECK ((`sequence_version` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.document_series
CREATE TABLE IF NOT EXISTS `document_series` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `document_type` enum('01','03','07','08','09','12','13','18','31','40','41') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de comprobante según catálogo 01 SUNAT',
  `series` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Serie del documento (ej: F001, B001)',
  `description` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Descripción de la serie',
  `current_number` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'Número correlativo actual',
  `initial_number` bigint unsigned NOT NULL DEFAULT '1' COMMENT 'Número inicial',
  `final_number` bigint unsigned NOT NULL DEFAULT '99999999' COMMENT 'Número final',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Serie por defecto para este tipo de documento',
  `is_electronic` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Serie electrónica (SUNAT)',
  `is_contingency` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Serie de contingencia',
  `pos_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código punto de venta',
  `pos_description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Descripción punto de venta',
  `validation_rules` json DEFAULT NULL COMMENT 'Reglas de validación específicas JSON',
  `status` enum('active','inactive','suspended','exhausted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `valid_from` date DEFAULT NULL COMMENT 'Válido desde',
  `valid_until` date DEFAULT NULL COMMENT 'Válido hasta',
  `authorization_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de autorización',
  `authorization_date` date DEFAULT NULL COMMENT 'Fecha de autorización',
  `documents_issued` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'Documentos emitidos',
  `last_used_at` timestamp NULL DEFAULT NULL COMMENT 'Última vez usado',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Observaciones',
  `additional_config` json DEFAULT NULL COMMENT 'Configuración adicional JSON',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_company_document_series` (`company_id`,`document_type`,`series`),
  UNIQUE KEY `unique_default_series` (`company_id`,`document_type`,`is_default`),
  KEY `document_series_company_id_document_type_status_index` (`company_id`,`document_type`,`status`),
  KEY `document_series_status_index` (`status`),
  KEY `document_series_is_electronic_index` (`is_electronic`),
  KEY `document_series_created_at_index` (`created_at`),
  CONSTRAINT `document_series_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `check_number_range` CHECK (((`current_number` >= `initial_number`) and (`current_number` <= `final_number`))),
  CONSTRAINT `check_series_format` CHECK ((((`document_type` = _utf8mb4'01') and regexp_like(`series`,_utf8mb4'^F[0-9]{3}$')) or ((`document_type` = _utf8mb4'03') and regexp_like(`series`,_utf8mb4'^B[0-9]{3}$')) or ((`document_type` = _utf8mb4'07') and regexp_like(`series`,_utf8mb4'^[FB]C[0-9]{2}$')) or ((`document_type` = _utf8mb4'08') and regexp_like(`series`,_utf8mb4'^[FB]D[0-9]{2}$')) or (`document_type` not in (_utf8mb4'01',_utf8mb4'03',_utf8mb4'07',_utf8mb4'08'))))
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.exchange_rates
CREATE TABLE IF NOT EXISTS `exchange_rates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL COMMENT 'Fecha del tipo de cambio',
  `buy_rate` decimal(10,6) NOT NULL COMMENT 'Tipo de cambio compra',
  `sell_rate` decimal(10,6) NOT NULL COMMENT 'Tipo de cambio venta',
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'factiliza' COMMENT 'Fuente del tipo de cambio',
  `raw_data` json DEFAULT NULL COMMENT 'Datos originales de la API',
  `fetched_at` timestamp NOT NULL COMMENT 'Fecha y hora de consulta',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exchange_rates_date_unique` (`date`),
  KEY `exchange_rates_date_source_index` (`date`,`source`),
  KEY `exchange_rates_fetched_at_index` (`fetched_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.exports
CREATE TABLE IF NOT EXISTS `exports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exporter` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `exports_user_id_foreign` (`user_id`),
  CONSTRAINT `exports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.failed_import_rows
CREATE TABLE IF NOT EXISTS `failed_import_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `data` json NOT NULL,
  `import_id` bigint unsigned NOT NULL,
  `validation_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `failed_import_rows_import_id_foreign` (`import_id`),
  CONSTRAINT `failed_import_rows_import_id_foreign` FOREIGN KEY (`import_id`) REFERENCES `imports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para procedimiento facturacion.GetNextDocumentNumber
DELIMITER //
CREATE PROCEDURE `GetNextDocumentNumber`(
                IN p_company_id BIGINT UNSIGNED,
                IN p_series_id BIGINT UNSIGNED,
                OUT p_next_number BIGINT UNSIGNED,
                OUT p_success BOOLEAN
            )
BEGIN
                DECLARE v_current_number BIGINT UNSIGNED DEFAULT 0;
                DECLARE v_max_number BIGINT UNSIGNED DEFAULT 0;
                DECLARE v_sequence_version BIGINT UNSIGNED DEFAULT 0;
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET p_success = FALSE;
                    SET p_next_number = 0;
                END;
                
                START TRANSACTION;
                
                -- Get current state with lock
                SELECT last_number, sequence_version
                INTO v_current_number, v_sequence_version
                FROM document_numbering_control 
                WHERE company_id = p_company_id AND document_series_id = p_series_id
                FOR UPDATE;
                
                -- Get max number from series
                SELECT final_number INTO v_max_number
                FROM document_series 
                WHERE id = p_series_id;
                
                -- Calculate next number
                SET p_next_number = v_current_number + 1;
                
                -- Check if we have available numbers
                IF p_next_number <= v_max_number THEN
                    -- Update control table
                    UPDATE document_numbering_control 
                    SET 
                        last_number = p_next_number,
                        last_used_at = NOW(),
                        sequence_version = sequence_version + 1,
                        documents_issued_today = documents_issued_today + 1,
                        documents_issued_month = documents_issued_month + 1
                    WHERE 
                        company_id = p_company_id 
                        AND document_series_id = p_series_id
                        AND sequence_version = v_sequence_version;
                    
                    -- Update series current number
                    UPDATE document_series 
                    SET 
                        current_number = p_next_number,
                        documents_issued = documents_issued + 1,
                        last_used_at = NOW()
                    WHERE id = p_series_id;
                    
                    SET p_success = TRUE;
                ELSE
                    SET p_success = FALSE;
                    SET p_next_number = 0;
                END IF;
                
                COMMIT;
            END//
DELIMITER ;

-- Volcando estructura para tabla facturacion.imports
CREATE TABLE IF NOT EXISTS `imports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `completed_at` timestamp NULL DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `importer` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processed_rows` int unsigned NOT NULL DEFAULT '0',
  `total_rows` int unsigned NOT NULL,
  `successful_rows` int unsigned NOT NULL DEFAULT '0',
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `imports_user_id_foreign` (`user_id`),
  CONSTRAINT `imports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.inventory_movements
CREATE TABLE IF NOT EXISTS `inventory_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `type` enum('OPENING','IN','OUT','TRANSFER','ADJUST') COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_warehouse_id` bigint unsigned DEFAULT NULL,
  `to_warehouse_id` bigint unsigned DEFAULT NULL,
  `qty` decimal(12,4) NOT NULL,
  `reason` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ref_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `idempotency_key` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `movement_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_company_idem` (`company_id`,`idempotency_key`),
  KEY `inventory_movements_user_id_foreign` (`user_id`),
  KEY `idx_company_date` (`company_id`,`movement_date`),
  KEY `idx_product_date` (`product_id`,`movement_date`),
  KEY `idx_from_date` (`from_warehouse_id`,`movement_date`),
  KEY `idx_to_date` (`to_warehouse_id`,`movement_date`),
  KEY `idx_type` (`type`),
  CONSTRAINT `inventory_movements_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_movements_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventory_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventory_movements_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventory_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_mov_from_to_diff` CHECK (((`from_warehouse_id` is null) or (`to_warehouse_id` is null) or (`from_warehouse_id` <> `to_warehouse_id`))),
  CONSTRAINT `chk_mov_qty_pos` CHECK ((`qty` > 0)),
  CONSTRAINT `chk_mov_type_endpoints` CHECK ((((`type` in (_utf8mb4'OPENING',_utf8mb4'IN')) and (`from_warehouse_id` is null) and (`to_warehouse_id` is not null)) or ((`type` = _utf8mb4'OUT') and (`from_warehouse_id` is not null) and (`to_warehouse_id` is null)) or ((`type` = _utf8mb4'TRANSFER') and (`from_warehouse_id` is not null) and (`to_warehouse_id` is not null)) or ((`type` = _utf8mb4'ADJUST') and ((`from_warehouse_id` is null) <> (`to_warehouse_id` is null)))))
) ENGINE=InnoDB AUTO_INCREMENT=8236 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.invoices
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `document_series_id` bigint unsigned NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `series` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Serie del documento',
  `number` bigint unsigned NOT NULL COMMENT 'Número correlativo',
  `full_number` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número completo (serie-número)',
  `document_type` enum('01','03','07','08','09') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de comprobante',
  `issue_date` date NOT NULL COMMENT 'Fecha de emisión',
  `issue_time` time NOT NULL COMMENT 'Hora de emisión',
  `due_date` date DEFAULT NULL COMMENT 'Fecha de vencimiento',
  `currency_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PEN' COMMENT 'Código de moneda ISO',
  `exchange_rate` decimal(10,6) NOT NULL DEFAULT '1.000000' COMMENT 'Tipo de cambio',
  `client_document_type` enum('0','1','4','6','7','A','B','C') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo documento cliente',
  `client_document_number` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número documento cliente',
  `client_business_name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Razón social cliente',
  `client_address` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dirección cliente',
  `client_email` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email cliente',
  `operation_type` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0101' COMMENT 'Tipo de operación según catálogo 51',
  `subtotal` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Subtotal (base imponible)',
  `tax_exempt_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto exonerado',
  `unaffected_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto inafecto',
  `free_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto gratuito',
  `igv_rate` decimal(5,4) NOT NULL DEFAULT '0.1800' COMMENT 'Tasa IGV aplicada',
  `igv_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto IGV',
  `isc_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto ISC',
  `other_taxes_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Otros tributos',
  `total_charges` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Total cargos',
  `total_discounts` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Total descuentos',
  `global_discount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Descuento global',
  `total_amount` decimal(12,2) NOT NULL COMMENT 'Importe total',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto pagado',
  `pending_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Saldo pendiente',
  `payment_method` enum('cash','card','transfer','check','credit','deposit','yape','plin','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de operación para pagos digitales (Yape, Plin, etc.)',
  `payment_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Teléfono asociado al pago digital',
  `payment_condition` enum('immediate','credit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate' COMMENT 'Condición de pago',
  `credit_days` int NOT NULL DEFAULT '0' COMMENT 'Días de crédito',
  `reference_document_type` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo documento referencia',
  `reference_series` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Serie documento referencia',
  `reference_number` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número documento referencia',
  `reference_date` date DEFAULT NULL COMMENT 'Fecha documento referencia',
  `modification_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo de modificación',
  `modification_type` enum('01','02','03','04','05','06','07','08','09','10','11','12') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de modificación para notas',
  `sunat_status` enum('pending','sent','accepted','rejected','observed','cancelled','voided') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Estado en SUNAT',
  `sunat_response_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código respuesta SUNAT',
  `sunat_response_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción respuesta SUNAT',
  `sunat_sent_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha envío SUNAT',
  `sunat_processed_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha procesado SUNAT',
  `cdr_zip_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta archivo CDR',
  `xml_signed_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta XML firmado',
  `pdf_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta PDF generado',
  `hash_sign` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Hash de la firma digital',
  `qr_code` text COLLATE utf8mb4_unicode_ci COMMENT 'Datos código QR',
  `status` enum('draft','issued','sent','paid','partial_paid','overdue','cancelled','voided') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_contingency` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Documento de contingencia',
  `contingency_reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo contingencia',
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `cancelled_by` bigint unsigned DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `observations` text COLLATE utf8mb4_unicode_ci COMMENT 'Observaciones',
  `additional_data` json DEFAULT NULL COMMENT 'Datos adicionales JSON',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invoice_number` (`company_id`,`series`,`number`),
  UNIQUE KEY `unique_full_number` (`full_number`,`company_id`),
  KEY `invoices_document_series_id_foreign` (`document_series_id`),
  KEY `invoices_updated_by_foreign` (`updated_by`),
  KEY `invoices_cancelled_by_foreign` (`cancelled_by`),
  KEY `invoices_company_id_document_type_status_index` (`company_id`,`document_type`,`status`),
  KEY `invoices_client_id_status_index` (`client_id`,`status`),
  KEY `invoices_issue_date_index` (`issue_date`),
  KEY `invoices_due_date_index` (`due_date`),
  KEY `invoices_sunat_status_index` (`sunat_status`),
  KEY `invoices_status_index` (`status`),
  KEY `invoices_payment_condition_index` (`payment_condition`),
  KEY `invoices_created_by_index` (`created_by`),
  KEY `invoices_created_at_index` (`created_at`),
  KEY `idx_invoices_reference` (`reference_document_type`,`reference_series`,`reference_number`),
  CONSTRAINT `invoices_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_document_series_id_foreign` FOREIGN KEY (`document_series_id`) REFERENCES `document_series` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.invoice_details
CREATE TABLE IF NOT EXISTS `invoice_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `line_number` smallint unsigned NOT NULL COMMENT 'Número de línea en el documento',
  `product_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código del producto',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Descripción del producto/servicio',
  `additional_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción adicional',
  `unit_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NIU' COMMENT 'Código unidad medida',
  `unit_description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UNIDAD (BIENES)' COMMENT 'Descripción unidad medida',
  `quantity` decimal(12,4) NOT NULL COMMENT 'Cantidad',
  `unit_price` decimal(12,4) NOT NULL COMMENT 'Precio unitario sin impuestos',
  `unit_value` decimal(12,4) NOT NULL COMMENT 'Valor unitario sin impuestos',
  `line_discount_percentage` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT 'Porcentaje descuento línea',
  `line_discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto descuento línea',
  `line_charge_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto cargo línea',
  `gross_amount` decimal(12,2) NOT NULL COMMENT 'Importe bruto (cantidad × precio)',
  `net_amount` decimal(12,2) NOT NULL COMMENT 'Importe neto (después desc/cargos)',
  `tax_type` enum('10','11','12','13','14','15','16','17','20','21','30','31','32','33','34','35','36','37','40') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '10' COMMENT 'Tipo afectación IGV',
  `igv_rate` decimal(5,4) NOT NULL DEFAULT '0.1800' COMMENT 'Tasa IGV',
  `igv_base_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Base imponible IGV',
  `igv_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto IGV',
  `isc_type` enum('01','02','03') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de ISC',
  `isc_rate` decimal(8,6) NOT NULL DEFAULT '0.000000' COMMENT 'Tasa o monto fijo ISC',
  `isc_base_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Base imponible ISC',
  `isc_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto ISC',
  `other_taxes_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Otros tributos',
  `total_taxes` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Total impuestos línea',
  `line_total` decimal(12,2) NOT NULL COMMENT 'Total línea (neto + impuestos)',
  `batch_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de lote',
  `expiry_date` date DEFAULT NULL COMMENT 'Fecha vencimiento',
  `serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de serie',
  `is_free` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ítem gratuito',
  `free_reason` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Motivo ítem gratuito',
  `quote_detail_id` bigint unsigned DEFAULT NULL COMMENT 'ID detalle cotización origen',
  `order_detail_id` bigint unsigned DEFAULT NULL COMMENT 'ID detalle pedido origen',
  `line_notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Observaciones de línea',
  `additional_attributes` json DEFAULT NULL COMMENT 'Atributos adicionales JSON',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invoice_line` (`invoice_id`,`line_number`),
  KEY `invoice_details_invoice_id_index` (`invoice_id`),
  KEY `invoice_details_product_id_index` (`product_id`),
  KEY `invoice_details_product_code_index` (`product_code`),
  KEY `invoice_details_tax_type_index` (`tax_type`),
  KEY `invoice_details_is_free_index` (`is_free`),
  KEY `invoice_details_created_at_index` (`created_at`),
  CONSTRAINT `invoice_details_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_details_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `check_positive_prices` CHECK (((`unit_price` >= 0) and (`unit_value` >= 0))),
  CONSTRAINT `check_positive_quantity` CHECK ((`quantity` > 0)),
  CONSTRAINT `check_tax_rate_range` CHECK (((`igv_rate` >= 0) and (`igv_rate` <= 1)))
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.payment_installments
CREATE TABLE IF NOT EXISTS `payment_installments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `installment_number` smallint unsigned NOT NULL COMMENT 'Número de cuota',
  `amount` decimal(12,2) NOT NULL COMMENT 'Monto de la cuota',
  `due_date` date NOT NULL COMMENT 'Fecha de vencimiento',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto pagado',
  `pending_amount` decimal(12,2) NOT NULL COMMENT 'Saldo pendiente',
  `status` enum('pending','paid','partial_paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha de pago',
  `payment_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Referencia de pago',
  `late_fee_rate` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT 'Tasa mora diaria',
  `late_fee_amount` decimal(12,2) NOT NULL DEFAULT '0.00' COMMENT 'Monto mora acumulada',
  `days_overdue` int NOT NULL DEFAULT '0' COMMENT 'Días de atraso',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Observaciones',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invoice_installment` (`invoice_id`,`installment_number`),
  KEY `payment_installments_invoice_id_status_index` (`invoice_id`,`status`),
  KEY `payment_installments_due_date_index` (`due_date`),
  KEY `payment_installments_status_index` (`status`),
  CONSTRAINT `payment_installments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `check_paid_amount_limit` CHECK (((`paid_amount` >= 0) and (`paid_amount` <= `amount`))),
  CONSTRAINT `check_positive_amount` CHECK ((`amount` > 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código interno del producto',
  `name` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Descripción del producto/servicio',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción detallada',
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta de la imagen del producto',
  `sunat_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código SUNAT del producto',
  `product_type` enum('product','service') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'product' COMMENT 'Tipo de ítem',
  `unit_code` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NIU' COMMENT 'Código unidad de medida según catálogo 03',
  `unit_description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UNIDAD (BIENES)' COMMENT 'Descripción unidad de medida',
  `unit_price` decimal(12,4) NOT NULL COMMENT 'Precio unitario sin IGV',
  `sale_price` decimal(12,4) NOT NULL COMMENT 'Precio de venta con IGV',
  `cost_price` decimal(12,4) DEFAULT NULL COMMENT 'Precio de costo',
  `minimum_price` decimal(12,4) DEFAULT NULL COMMENT 'Precio mínimo de venta',
  `tax_type` enum('10','11','12','13','14','15','16','17','20','21','30','31','32','33','34','35','36','37','40') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '10' COMMENT 'Tipo de afectación del IGV según catálogo 07',
  `tax_rate` decimal(5,4) NOT NULL DEFAULT '0.1800' COMMENT 'Tasa de impuesto (18% = 0.1800)',
  `current_stock` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Stock actual',
  `minimum_stock` decimal(12,4) NOT NULL DEFAULT '0.0000' COMMENT 'Stock mínimo',
  `maximum_stock` decimal(12,4) DEFAULT NULL COMMENT 'Stock máximo',
  `track_inventory` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Controlar inventario',
  `category_id` bigint unsigned DEFAULT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Categoría del producto',
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Marca',
  `model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Modelo',
  `weight` decimal(8,3) DEFAULT NULL COMMENT 'Peso en kg',
  `barcode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código de barras',
  `internal_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Referencia interna',
  `supplier_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código del proveedor',
  `status` enum('active','inactive','discontinued') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `taxable` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Producto gravado con impuestos',
  `for_sale` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Producto para venta',
  `for_purchase` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Producto para compra',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Observaciones',
  `additional_attributes` json DEFAULT NULL COMMENT 'Atributos adicionales JSON',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_product_code` (`company_id`,`code`),
  KEY `products_company_id_status_index` (`company_id`,`status`),
  KEY `products_name_index` (`name`),
  KEY `products_category_index` (`category`),
  KEY `products_tax_type_index` (`tax_type`),
  KEY `products_barcode_index` (`barcode`),
  KEY `products_created_at_index` (`created_at`),
  KEY `products_created_by_foreign` (`created_by`),
  KEY `products_category_id_index` (`category_id`),
  KEY `products_brand_id_index` (`brand_id`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=181 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.stocks
CREATE TABLE IF NOT EXISTS `stocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `qty` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `min_qty` decimal(12,4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_company_product_warehouse` (`company_id`,`product_id`,`warehouse_id`),
  KEY `idx_wh` (`warehouse_id`),
  KEY `idx_prod` (`product_id`),
  CONSTRAINT `stocks_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `stocks_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_stocks_qty_nonneg` CHECK ((`qty` >= 0))
) ENGINE=InnoDB AUTO_INCREMENT=878 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.sunat_logs
CREATE TABLE IF NOT EXISTS `sunat_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned DEFAULT NULL,
  `document_type` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de documento',
  `series` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Serie',
  `number` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número',
  `full_number` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Número completo',
  `operation_type` enum('send_document','send_summary','send_voiding','query_status','download_cdr') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de operación',
  `request_method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Método HTTP',
  `request_url` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL del servicio',
  `request_headers` text COLLATE utf8mb4_unicode_ci COMMENT 'Headers de la petición JSON',
  `request_body` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Cuerpo de la petición',
  `request_sent_at` timestamp NOT NULL COMMENT 'Fecha envío petición',
  `response_status_code` int DEFAULT NULL COMMENT 'Código estado HTTP',
  `response_headers` text COLLATE utf8mb4_unicode_ci COMMENT 'Headers de la respuesta JSON',
  `response_body` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Cuerpo de la respuesta',
  `response_received_at` timestamp NULL DEFAULT NULL COMMENT 'Fecha recepción respuesta',
  `sunat_response_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código respuesta SUNAT',
  `sunat_response_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Descripción respuesta SUNAT',
  `ticket_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de ticket SUNAT',
  `status` enum('pending','processing','success','error','timeout') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código de error',
  `error_message` text COLLATE utf8mb4_unicode_ci COMMENT 'Mensaje de error',
  `error_trace` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Trace del error',
  `response_time_ms` int DEFAULT NULL COMMENT 'Tiempo respuesta en ms',
  `retry_count` int NOT NULL DEFAULT '0' COMMENT 'Número de reintentos',
  `next_retry_at` timestamp NULL DEFAULT NULL COMMENT 'Próximo reintento',
  `xml_file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta archivo XML enviado',
  `cdr_file_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta archivo CDR recibido',
  `environment` enum('beta','production') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ambiente SUNAT',
  `service_version` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Versión del servicio',
  `additional_data` json DEFAULT NULL COMMENT 'Datos adicionales JSON',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sunat_logs_company_id_created_at_index` (`company_id`,`created_at`),
  KEY `sunat_logs_invoice_id_index` (`invoice_id`),
  KEY `sunat_logs_document_type_series_number_index` (`document_type`,`series`,`number`),
  KEY `sunat_logs_operation_type_index` (`operation_type`),
  KEY `sunat_logs_status_index` (`status`),
  KEY `sunat_logs_sunat_response_code_index` (`sunat_response_code`),
  KEY `sunat_logs_request_sent_at_index` (`request_sent_at`),
  KEY `sunat_logs_next_retry_at_index` (`next_retry_at`),
  CONSTRAINT `sunat_logs_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sunat_logs_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

-- Volcando estructura para tabla facturacion.warehouses
CREATE TABLE IF NOT EXISTS `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_id` bigint unsigned NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_company_code` (`company_id`,`code`),
  KEY `idx_company_active` (`company_id`,`is_active`),
  CONSTRAINT `warehouses_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La exportación de datos fue deseleccionada.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
