-- =====================================================================
-- نظام إدارة المستندات والتحقق عبر QR Code
-- ملف إنشاء قاعدة البيانات
-- متوافق مع MySQL 5.7+ / MariaDB 10.3+
-- طريقة الاستخدام: استيراد هذا الملف من phpMyAdmin أو Plesk Database
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- جدول المستخدمين (الإدارة)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `must_change_password` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'إجبار تغيير كلمة المرور عند أول دخول',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- جدول العملاء
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(191) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- جدول المستندات
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `doc_number` VARCHAR(30) NOT NULL COMMENT 'رقم المستند الداخلي',
  `client_id` INT UNSIGNED DEFAULT NULL,
  `original_name` VARCHAR(255) NOT NULL COMMENT 'اسم الملف الأصلي للعرض فقط',
  `stored_name` VARCHAR(100) NOT NULL COMMENT 'اسم الملف العشوائي الآمن على القرص',
  `mime_type` VARCHAR(100) NOT NULL,
  `file_size` BIGINT UNSIGNED NOT NULL COMMENT 'الحجم بالبايت',
  `status` ENUM('inactive','active','disabled') NOT NULL DEFAULT 'inactive' COMMENT 'حالة QR: غير مفعل / مفعل / معطل',
  `token` CHAR(32) NOT NULL COMMENT 'رمز التحقق الفريد',
  `verify_url` VARCHAR(500) DEFAULT NULL COMMENT 'رابط التحقق الكامل المستخدم في QR',
  `qr_path` VARCHAR(255) DEFAULT NULL COMMENT 'مسار ملف صورة QR النسبي',
  `qr_generated_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_documents_doc_number` (`doc_number`),
  UNIQUE KEY `uq_documents_token` (`token`),
  UNIQUE KEY `uq_documents_stored_name` (`stored_name`),
  KEY `idx_documents_client` (`client_id`),
  KEY `idx_documents_status` (`status`),
  KEY `idx_documents_created` (`created_at`),
  CONSTRAINT `fk_documents_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- جدول سجل عمليات التحقق
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `verification_logs`;
CREATE TABLE `verification_logs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `document_id` INT UNSIGNED NOT NULL,
  `doc_status` ENUM('inactive','active','disabled') NOT NULL COMMENT 'حالة المستند وقت التحقق',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_verification_document` (`document_id`),
  KEY `idx_verification_created` (`created_at`),
  CONSTRAINT `fk_verification_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- جدول الإعدادات
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_settings_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- جدول محاولات تسجيل الدخول (حماية من التخمين)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE `login_attempts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(191) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `last_attempt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_email` (`email`),
  KEY `idx_login_attempts_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- البيانات الافتراضية
-- ------------------------------------------------------------

-- الإعدادات الافتراضية للنظام
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('system_name', 'نظام التحقق من المستندات'),
('org_name', ''),
('org_email', ''),
('org_phone', ''),
('org_address', ''),
('base_url', 'https://portal.ourum-explor.com'),
('ip_logging', '1'),
('logo_path', '')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);

-- حساب المدير الافتراضي
-- البريد: admin@example.com
-- كلمة المرور: Admin@12345
-- ملاحظة: سيُجبر النظام على تغيير كلمة المرور عند أول تسجيل دخول.
-- بعد التثبيت، غيّر كلمة المرور فورًا واحذف هذا السطر من الملف لضمان الأمان.
INSERT INTO `users` (`name`, `email`, `password`, `must_change_password`) VALUES
('مدير النظام', 'admin@example.com', '$2y$10$kDpld1j6e.MQvoTcjzqCJug38JNIpFP1BWvaVnuhXTrhckgPpvY5y', 1);

SET FOREIGN_KEY_CHECKS = 1;
