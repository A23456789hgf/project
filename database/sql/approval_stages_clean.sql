-- Tabla: approval_stages
-- Versión limpia y compatible para importación desde phpMyAdmin

DROP TABLE IF EXISTS `approval_stages`;

CREATE TABLE `approval_stages` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `type` enum('documentation','association_president','committee_head','technical_review','financial_review','final_approval') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos de ejemplo
INSERT INTO `approval_stages` (`id`, `name`, `order`, `description`, `type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'مرحلة التقييم والتوثيق', 1, 'التقييم الأولي والتوثيق', 'documentation', 1, NOW(), NOW()),
(2, 'مرحلة رئيس الجمعية', 2, 'موافقة رئيس الجمعية', 'association_president', 1, NOW(), NOW()),
(3, 'مرحلة رئيس اللجنة', 3, 'موافقة رئيس اللجنة', 'committee_head', 1, NOW(), NOW()),
(4, 'المراجعة التقنية', 4, 'المراجعة التقنية للمشروع', 'technical_review', 1, NOW(), NOW()),
(5, 'المراجعة المالية', 5, 'المراجعة المالية للمشروع', 'financial_review', 1, NOW(), NOW()),
(6, 'الموافقة النهائية', 6, 'الموافقة النهائية على المشروع', 'final_approval', 1, NOW(), NOW());
