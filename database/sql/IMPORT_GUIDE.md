# دليل استيراد قاعدة البيانات من phpMyAdmin

## المشاكل الشائعة وحلولها

### 1. **مشاكل الكلمات المحجوزة (Reserved Keywords)**
- **المشكلة**: العمود `order` هو كلمة محجوزة في MySQL
- **الحل**: استخدم backticks `` `order` `` حول اسم العمود

### 2. **مشاكل التشفير (Collation)**
- **المشكلة**: الأحرف العربية قد لا تُعرض بشكل صحيح
- **الحل**: استخدم `utf8mb4_unicode_ci` أو `utf8mb4_general_ci`

### 3. **مشاكل ENUM**
- **المشكلة**: قيم ENUM قد تكون مفصولة بشكل خاطئ
- **الحل**: تأكد من استخدام فواصل صحيحة وعدم وجود مسافات

### 4. **مشاكل الفهارس والعلاقات**
- **المشكلة**: الفهارس الأجنبية قد تحتوي على أخطاء في الترتيب
- **الحل**: تأكد من وجود الجداول الأساسية قبل إضافة الفهارس

## خطوات الاستيراد الصحيحة

### الطريقة 1: استخدام phpMyAdmin (الأسهل)
1. افتح phpMyAdmin
2. انتقل إلى علامة تبويب **"Import"**
3. اختر الملف `approval_stages_clean.sql`
4. تأكد من تحديد:
   - ✅ **Character set**: `utf8mb4`
   - ✅ **Collation**: `utf8mb4_unicode_ci`
5. انقر على **"Go"**

### الطريقة 2: استخدام سطر الأوامر (Command Line)
```bash
# Windows
mysql -u root -p prot < database/sql/approval_stages_clean.sql

# أو مع كلمة المرور مباشرة
mysql -u root -pYOUR_PASSWORD prot < database/sql/approval_stages_clean.sql
```

### الطريقة 3: استخدام Laravel (الأفضل)
```bash
cd pro
php artisan db:seed --class=ApprovalStageSeeder
```

## حل المشاكل الشائعة

### ❌ خطأ: "Syntax error near 'order'"
**السبب**: كلمة `order` محجوزة
**الحل**: تأكد من استخدام backticks:
```sql
`order` int(11) NOT NULL
```

### ❌ خطأ: "Incorrect string value"
**السبب**: مشكلة في التشفير (Encoding)
**الحل**: 
1. في phpMyAdmin: اختر **UTF-8 (utf8mb4)**
2. أضف في بداية الملف:
```sql
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
```

### ❌ خطأ: "Foreign key constraint failed"
**السبب**: الجدول الأساسي لم يتم إنشاؤه بعد
**الحل**: احذف الفهرس الأجنبي مؤقتاً، ثم أضفه بعد الانتهاء:
```sql
-- أولاً: إنشاء الجدول بدون FK
ALTER TABLE approval_stages DROP CONSTRAINT approval_stages_parent_id_foreign;

-- ثانياً: بعد التأكد من وجود جميع الجداول
ALTER TABLE approval_stages 
ADD CONSTRAINT approval_stages_parent_id_foreign 
FOREIGN KEY (parent_id) REFERENCES approval_stages(id);
```

### ❌ خطأ: "Unknown storage engine"
**السبب**: محرك التخزين InnoDB غير متاح
**الحل**: غيّر إلى MyISAM مؤقتاً:
```sql
-- غيّر من
ENGINE=InnoDB

-- إلى
ENGINE=MyISAM
```

## التحقق من نجاح الاستيراد

بعد الاستيراد، تحقق باستخدام:

### 1. في phpMyAdmin
```
Navigate to: prot -> approval_stages -> Browse
```

### 2. في سطر الأوامر
```bash
mysql -u root -p prot -e "DESCRIBE approval_stages;"
```

### 3. في Laravel Tinker
```bash
php artisan tinker
>>> App\Models\ApprovalStage::all();
```

## الملفات المتوفرة

- ✅ `approval_stages_clean.sql` - النسخة الكاملة والنظيفة
- ✅ `ApprovalStageSeeder.php` - Laravel Seeder
- ✅ Migrations في `database/migrations/`

## خطوات التنظيف بعد الاستيراد

```bash
# 1. تشغيل جميع الـ Migrations
php artisan migrate

# 2. تشغيل جميع الـ Seeders
php artisan db:seed

# 3. التحقق من الاتصال
php artisan tinker
>>> DB::table('approval_stages')->count();
```

## ملاحظات مهمة

⚠️ **إذا كنت تستورد بيانات قديمة**:
1. احذف الجدول أولاً: `DROP TABLE IF EXISTS approval_stages;`
2. ثم استورد الملف الجديد
3. تحقق من عدم وجود تضارب في الـ IDs

⚠️ **للبيانات الإنتاجية**:
1. أنشئ backup أولاً
2. استورد في قاعدة اختبار أولاً
3. تحقق من النتائج قبل الاستيراد النهائي

## الدعم الإضافي

إذا واجهت مشكلة:
1. اطلب رسالة الخطأ الدقيقة
2. تحقق من إصدار MySQL
3. تحقق من صلاحيات المستخدم
4. جرّب استخدام طريقة مختلفة من الطرق أعلاه

---

**آخر تحديث**: 26 ديسمبر 2025
