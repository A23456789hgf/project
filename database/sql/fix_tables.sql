-- ========================================
-- Fix Database Tables for phpMyAdmin Import
-- ========================================
-- هذا الملف يحتوي على إصلاحات شاملة لمشاكل الاستيراد الشائعة

SET NAMES utf8mb4;
SET CHARACTER_SET_CLIENT = utf8mb4;
SET CHARACTER_SET_CONNECTION = utf8mb4;
SET COLLATION_CONNECTION = utf8mb4_unicode_ci;

-- ========================================
-- 1. جدول: approval_stages
-- ========================================
DROP TABLE IF EXISTS `approval_stages`;

CREATE TABLE `approval_stages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NULL,
  `type` enum('documentation','association_president','committee_head','technical_review','financial_review','final_approval') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'documentation',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_approval_stages_order` (`order`),
  KEY `idx_approval_stages_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول مراحل الموافقة على المشاريع';

-- ========================================
-- 2. جدول: stages
-- ========================================
DROP TABLE IF EXISTS `stages`;

CREATE TABLE `stages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE,
  `description` longtext COLLATE utf8mb4_unicode_ci NULL,
  `sequence_order` int(11) NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci NULL,
  `parent_id` bigint(20) UNSIGNED NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_stages_sequence_order` (`sequence_order`),
  KEY `idx_stages_parent_id` (`parent_id`),
  CONSTRAINT `fk_stages_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `stages` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول المراحل الرئيسية للمشاريع';

-- ========================================
-- 3. جدول: stage_statuses
-- ========================================
DROP TABLE IF EXISTS `stage_statuses`;

CREATE TABLE `stage_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `stage_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','active','completed','blocked','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `description` longtext COLLATE utf8mb4_unicode_ci NULL,
  `sequence_order` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_stage_statuses_unique` (`stage_id`, `slug`),
  CONSTRAINT `fk_stage_statuses_stage_id` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='حالات كل مرحلة';

-- ========================================
-- 4. جدول: stage_flows
-- ========================================
DROP TABLE IF EXISTS `stage_flows`;

CREATE TABLE `stage_flows` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `from_stage_id` bigint(20) UNSIGNED NOT NULL,
  `to_stage_id` bigint(20) UNSIGNED NOT NULL,
  `transition_label` varchar(255) COLLATE utf8mb4_unicode_ci NULL,
  `is_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `require_approval` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `idx_stage_flows_unique` (`from_stage_id`, `to_stage_id`),
  CONSTRAINT `fk_stage_flows_from_stage` FOREIGN KEY (`from_stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_stage_flows_to_stage` FOREIGN KEY (`to_stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='تدفقات الانتقال بين المراحل';

-- ========================================
-- بيانات الإدراج: approval_stages
-- ========================================
INSERT INTO `approval_stages` (`id`, `name`, `order`, `description`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'مرحلة التقييم والتوثيق', 1, 'التقييم الأولي والتوثيق للمشروع', 'documentation', 1, NOW(), NOW()),
(2, 'مرحلة رئيس الجمعية', 2, 'موافقة رئيس الجمعية على المشروع', 'association_president', 1, NOW(), NOW()),
(3, 'مرحلة رئيس اللجنة', 3, 'موافقة رئيس اللجنة على المشروع', 'committee_head', 1, NOW(), NOW()),
(4, 'المراجعة التقنية', 4, 'المراجعة التقنية الشاملة للمشروع', 'technical_review', 1, NOW(), NOW()),
(5, 'المراجعة المالية', 5, 'المراجعة المالية والميزانية للمشروع', 'financial_review', 1, NOW(), NOW()),
(6, 'الموافقة النهائية', 6, 'الموافقة النهائية على المشروع', 'final_approval', 1, NOW(), NOW());

-- ========================================
-- بيانات الإدراج: stages
-- ========================================
INSERT INTO `stages` (`id`, `name`, `slug`, `sequence_order`, `icon`, `color`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'الجمعية', 'assembly', 1, 'fa-users', '#0066cc', 1, NOW(), NOW()),
(2, 'الاتحاد', 'union', 2, 'fa-sitemap', '#009933', 1, NOW(), NOW()),
(3, 'اللجنة', 'committee', 3, 'fa-table', '#ff9900', 1, NOW(), NOW()),
(4, 'التنفيذ', 'implementation', 4, 'fa-cog', '#cc0000', 1, NOW(), NOW());

-- ========================================
-- بيانات الإدراج: stage_statuses
-- ========================================
INSERT INTO `stage_statuses` (`stage_id`, `name`, `slug`, `status`, `sequence_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'قيد الانتظار', 'pending', 'pending', 1, 1, NOW(), NOW()),
(1, 'قيد المراجعة', 'under_review', 'active', 2, 1, NOW(), NOW()),
(1, 'موافق', 'approved', 'completed', 3, 1, NOW(), NOW()),
(1, 'مرفوض', 'rejected', 'rejected', 4, 1, NOW(), NOW()),
(2, 'قيد الانتظار', 'pending', 'pending', 1, 1, NOW(), NOW()),
(2, 'قيد المراجعة', 'under_review', 'active', 2, 1, NOW(), NOW()),
(2, 'موافق', 'approved', 'completed', 3, 1, NOW(), NOW()),
(2, 'مرفوض', 'rejected', 'rejected', 4, 1, NOW(), NOW()),
(3, 'قيد الانتظار', 'pending', 'pending', 1, 1, NOW(), NOW()),
(3, 'قيد المراجعة', 'under_review', 'active', 2, 1, NOW(), NOW()),
(3, 'موافق', 'approved', 'completed', 3, 1, NOW(), NOW()),
(3, 'مرفوض', 'rejected', 'rejected', 4, 1, NOW(), NOW()),
(4, 'قيد الانتظار', 'pending', 'pending', 1, 1, NOW(), NOW()),
(4, 'قيد التنفيذ', 'in_progress', 'active', 2, 1, NOW(), NOW()),
(4, 'مكتمل', 'completed', 'completed', 3, 1, NOW(), NOW());

-- ========================================
-- بيانات الإدراج: stage_flows
-- ========================================
INSERT INTO `stage_flows` (`from_stage_id`, `to_stage_id`, `transition_label`, `is_allowed`, `require_approval`, `created_at`, `updated_at`) VALUES
(1, 2, 'انتقل إلى الاتحاد', 1, 1, NOW(), NOW()),
(2, 3, 'انتقل إلى اللجنة', 1, 1, NOW(), NOW()),
(3, 4, 'انتقل إلى التنفيذ', 1, 1, NOW(), NOW()),
(2, 1, 'عودة إلى الجمعية', 1, 0, NOW(), NOW()),
(3, 2, 'عودة إلى الاتحاد', 1, 0, NOW(), NOW()),
(4, 3, 'عودة إلى اللجنة', 1, 0, NOW(), NOW());

-- ========================================
-- تحقق من نجاح الإنشاء
-- ========================================
SELECT 'تم إنشاء الجداول بنجاح' AS 'النتيجة';
SELECT COUNT(*) AS 'عدد مراحل الموافقة' FROM `approval_stages`;
SELECT COUNT(*) AS 'عدد المراحل' FROM `stages`;
