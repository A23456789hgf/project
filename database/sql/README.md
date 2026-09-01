# دليل استيراد قاعدة البيانات - Database Import Guide

## 📋 الملفات المتوفرة - Available Files

| الملف | الوصف | الاستخدام |
|------|--------|---------|
| `fix_tables.sql` | ملف شامل يحتوي على جميع الإصلاحات | ✅ **موصى به** |
| `approval_stages_clean.sql` | نسخة نظيفة من جدول واحد | لاستيراد جدول محدد فقط |
| `import.ps1` | دسكريبت PowerShell للاستيراد | الخيار الأفضل لـ Windows |
| `import.bat` | ملف Batch للاستيراد | بديل بسيط |
| `IMPORT_GUIDE.md` | دليل تفصيلي بالمشاكل والحلول | قراءة وفهم المشاكل |

---

## 🚀 طرق الاستيراد السريعة

### الطريقة 1: استخدام PowerShell (الأفضل والأسهل)

```powershell
# Windows PowerShell
cd "C:\Users\Am\Desktop\Newfolder3\pro\database\sql"
.\import.ps1
```

**المميزات:**
✅ واجهة تفاعلية  
✅ تحقق تلقائي من MySQL  
✅ اختيار الملف بسهولة  
✅ رسائل واضحة بالعربية  

---

### الطريقة 2: استخدام Batch File (بسيط)

```batch
# Command Prompt
cd C:\Users\Am\Desktop\Newfolder3\pro\database\sql
import.bat
```

**المميزات:**
✅ ملف تنفيذي بسيط  
✅ لا يتطلب محطة أوامر متقدمة  
✅ رسائل واضحة  

---

### الطريقة 3: phpMyAdmin (الأكثر شيوعاً)

```
1. افتح phpMyAdmin: http://localhost/phpmyadmin
2. انتقل إلى قاعدة البيانات: prot
3. انقر على علامة تبويب: Import
4. اختر الملف: database/sql/fix_tables.sql
5. تأكد من:
   - Character set: utf8mb4
   - Collation: utf8mb4_unicode_ci
6. انقر على: Go
```

**الخطوات بالتفصيل:**

![phpMyAdmin Import Steps](./phpMyAdmin-import-guide.txt)

```
أولاً: اختر الملف
┌─────────────────────────────┐
│ Choose file to upload       │
│ ┌───────────────────────┐   │
│ │ [Browse...]           │   │
│ └───────────────────────┘   │
│ Select: fix_tables.sql      │
└─────────────────────────────┘

ثانياً: تحقق من الإعدادات
┌─────────────────────────────┐
│ Character set of the file   │
│ ┌───────────────────────┐   │
│ │ utf8mb4           ▼ │   │
│ └───────────────────────┘   │
│                             │
│ Collation:                  │
│ ┌───────────────────────┐   │
│ │ utf8mb4_unicode_ci▼  │   │
│ └───────────────────────┘   │
└─────────────────────────────┘

ثالثاً: انقر على الزر الأخضر
┌──────────────────────┐
│   ✓  Import  (أخضر) │
└──────────────────────┘
```

---

### الطريقة 4: سطر الأوامر المباشر

```bash
# نسخ الملف والعودة إليه
cd c:\Users\Am\Desktop\Newfolder3\pro\database\sql

# الاستيراد بدون كلمة مرور
mysql -u root prot < fix_tables.sql

# الاستيراد مع كلمة مرور
mysql -u root -p prot < fix_tables.sql

# تحديد المضيف والمنفذ
mysql -h 127.0.0.1 -P 3306 -u root prot < fix_tables.sql
```

---

### الطريقة 5: Laravel Artisan (الأفضل للمشروع)

```bash
# من مجلد المشروع الرئيسي
cd c:\Users\Am\Desktop\Newfolder3\pro

# تشغيل جميع الـ migrations
php artisan migrate

# تشغيل جميع الـ seeders (ينشئ بيانات افتراضية)
php artisan db:seed

# أو تشغيل seeder محدد
php artisan db:seed --class=ApprovalStageSeeder
```

---

## ⚠️ معالجة الأخطاء الشائعة

### ❌ خطأ 1: "Access denied for user 'root'@'localhost'"

**السبب:** كلمة المرور غير صحيحة

**الحل:**
```bash
# تحقق من كلمة المرور في .env
cat .env | grep DB_PASSWORD

# جرب بدون كلمة مرور
mysql -u root prot < fix_tables.sql

# أو حدد كلمة المرور
mysql -u root -p"YOUR_PASSWORD" prot < fix_tables.sql
```

---

### ❌ خطأ 2: "Unknown database 'prot'"

**السبب:** قاعدة البيانات غير موجودة

**الحل:**
```bash
# أنشئ قاعدة البيانات أولاً
mysql -u root -e "CREATE DATABASE prot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# ثم استورد الملف
mysql -u root prot < fix_tables.sql
```

---

### ❌ خطأ 3: "Syntax error near 'order'"

**السبب:** كلمة `order` محجوزة في MySQL

**الحل:** 
استخدم ملف `fix_tables.sql` الذي يحتوي على الإصلاح

---

### ❌ خطأ 4: "Incorrect string value for column 'name'"

**السبب:** مشكلة في التشفير (Encoding)

**الحل:**
```sql
-- تشغيل هذه الأوامر قبل الاستيراد
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET COLLATION_CONNECTION utf8mb4_unicode_ci;
```

---

### ❌ خطأ 5: "Foreign key constraint failed"

**السبب:** ترتيب إنشاء الجداول غير صحيح

**الحل:**
استخدم ملف `fix_tables.sql` الذي يرتب الجداول بشكل صحيح

---

## ✅ التحقق من نجاح الاستيراد

### 1. في phpMyAdmin
```
prot → Tables → approval_stages → Browse
تأكد من ظهور البيانات
```

### 2. في سطر الأوامر
```bash
# عرض جميع الجداول
mysql -u root prot -e "SHOW TABLES;"

# عرض عدد الصفوف
mysql -u root prot -e "SELECT COUNT(*) as 'عدد الجداول' FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'prot';"

# عرض محتوى جدول محدد
mysql -u root prot -e "SELECT * FROM approval_stages;"
```

### 3. في Laravel Tinker
```bash
# دخول Laravel Tinker
php artisan tinker

# التحقق من البيانات
>>> DB::table('approval_stages')->count();
=> 6

>>> DB::table('approval_stages')->first();
=> {id: 1, name: "مرحلة التقييم والتوثيق", ...}

# الخروج
>>> exit
```

---

## 📊 محتوى الجداول المنشأة

### جدول: approval_stages (مراحل الموافقة)
```
ID | الاسم | الترتيب | النوع | النشط
---|------|--------|-------|------
1  | التقييم والتوثيق | 1 | documentation | 1
2  | رئيس الجمعية | 2 | association_president | 1
3  | رئيس اللجنة | 3 | committee_head | 1
4  | المراجعة التقنية | 4 | technical_review | 1
5  | المراجعة المالية | 5 | financial_review | 1
6  | الموافقة النهائية | 6 | final_approval | 1
```

### جدول: stages (المراحل الرئيسية)
```
ID | الاسم | الترتيب | اللون
---|------|--------|------
1  | الجمعية | 1 | #0066cc (أزرق)
2  | الاتحاد | 2 | #009933 (أخضر)
3  | اللجنة | 3 | #ff9900 (برتقالي)
4  | التنفيذ | 4 | #cc0000 (أحمر)
```

---

## 🔄 نصائح إضافية

### 📌 تنظيف قاعدة البيانات
```bash
# حذف جميع الجداول وإعادة الإنشاء
php artisan migrate:refresh

# حذف وإعادة مع بيانات افتراضية
php artisan migrate:refresh --seed
```

### 📌 نسخ احتياطي
```bash
# إنشاء نسخة احتياطية
mysqldump -u root prot > backup_prot_$(date +%Y%m%d_%H%M%S).sql

# استعادة من نسخة احتياطية
mysql -u root prot < backup_prot_20251226_120000.sql
```

### 📌 عرض حالة الجداول
```bash
# عرض معلومات جدول محدد
mysql -u root prot -e "DESCRIBE approval_stages;"

# عرض إحصائيات الجدول
mysql -u root prot -e "SHOW TABLE STATUS LIKE 'approval_stages';"
```

---

## 🆘 طلب الدعم

إذا واجهت مشكلة:

1. **انسخ رسالة الخطأ الكاملة**
   ```
   خطأ: [رسالة الخطأ هنا]
   ```

2. **تحقق من إصدار MySQL**
   ```bash
   mysql --version
   ```

3. **تحقق من قاعدة البيانات**
   ```bash
   mysql -u root -e "SHOW VARIABLES LIKE 'version';"
   ```

4. **اطلب المساعدة مع هذه المعلومات:**
   - إصدار MySQL
   - رسالة الخطأ الكاملة
   - الملف المستخدم في الاستيراد
   - نتيجة `mysql --version`

---

## 📝 ملاحظات مهمة

⚠️ **إذا كنت تستورد بيانات قديمة:**
1. احذف الجداول القديمة أولاً
2. استورد الملف الجديد
3. تحقق من عدم وجود تضارب

⚠️ **للبيئة الإنتاجية:**
1. أنشئ backup قبل الاستيراد
2. اختبر في بيئة تطوير أولاً
3. تحقق من النتائج بعناية

⚠️ **الأداء:**
- الاستيراد الأول قد يستغرق بعض الوقت
- الجداول الكبيرة قد تحتاج وقتاً أطول
- لا تغلق الاتصال أثناء الاستيراد

---

## 📚 مراجع إضافية

- [MySQL Documentation](https://dev.mysql.com/doc/)
- [phpMyAdmin Documentation](https://docs.phpmyadmin.net/)
- [Laravel Database Migrations](https://laravel.com/docs/migrations)
- [Laravel Tinker](https://laravel.com/docs/artisan#tinker)

---

**آخر تحديث:** 26 ديسمبر 2025  
**الإصدار:** 1.0  
**الحالة:** ✅ نشط ومختبر

