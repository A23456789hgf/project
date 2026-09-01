-- Referral Management Permissions
-- Run this SQL to add referral permissions to your database

INSERT INTO permissions (name, slug, module, description, created_at, updated_at) VALUES
('عرض قائمة الإحالات', 'referrals.view', 'referrals', 'القدرة على عرض قائمة الإحالات وتفاصيلها', NOW(), NOW()),
('عرض جميع الإحالات', 'referrals.view-all', 'referrals', 'القدرة على عرض جميع الإحالات في النظام (صلاحية إدارية)', NOW(), NOW()),
('إنشاء إحالة جديدة', 'referrals.create', 'referrals', 'القدرة على إنشاء إحالات جديدة', NOW(), NOW()),
('الرد على الإحالات', 'referrals.respond', 'referrals', 'القدرة على الرد على الإحالات الواردة', NOW(), NOW()),
('تعديل الإحالات', 'referrals.edit', 'referrals', 'القدرة على تعديل الإحالات', NOW(), NOW()),
('حذف الإحالات', 'referrals.delete', 'referrals', 'القدرة على حذف الإحالات', NOW(), NOW());
