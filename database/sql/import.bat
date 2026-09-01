@echo off
REM Batch Script for Database Import
REM دسكريبت للاستيراد من SQL في Windows

chcp 65001 >nul
cls

echo.
echo ========================================
echo استيراد قاعدة البيانات
echo Database Import
echo ========================================
echo.

REM Set database credentials
set DB_HOST=127.0.0.1
set DB_PORT=3306
set DB_NAME=prot
set DB_USER=root

echo.
echo إدخال بيانات قاعدة البيانات
echo Enter Database Credentials:
echo.

set /p DB_PASSWORD="كلمة المرور (Password): "

echo.
echo البيانات المدخلة:
echo Host: %DB_HOST%
echo Port: %DB_PORT%
echo Database: %DB_NAME%
echo User: %DB_USER%
echo.

REM Check if mysql is installed
where mysql >nul 2>nul

if %errorlevel% neq 0 (
    echo.
    echo خطأ: MySQL غير مثبت
    echo Error: MySQL is not installed or not in PATH
    echo.
    pause
    exit /b 1
)

echo تم العثور على MySQL
echo MySQL found successfully
echo.

REM Choose import file
echo.
echo اختر الملف المراد استيراده:
echo Choose the file to import:
echo.
echo 1. fix_tables.sql (الخيار الموصى به - Recommended)
echo 2. approval_stages_clean.sql
echo.

set /p CHOICE="اختر رقم الملف (Choose 1 or 2): "

if "%CHOICE%"=="1" (
    set SQL_FILE=fix_tables.sql
) else if "%CHOICE%"=="2" (
    set SQL_FILE=approval_stages_clean.sql
) else (
    set SQL_FILE=fix_tables.sql
)

echo.
echo جاري استيراد الملف: %SQL_FILE%
echo Importing file: %SQL_FILE%
echo.

REM Execute mysql import
mysql -h %DB_HOST% -u %DB_USER% -p%DB_PASSWORD% -P %DB_PORT% %DB_NAME% < database\sql\%SQL_FILE%

if %errorlevel% equ 0 (
    echo.
    echo ✓ تم الاستيراد بنجاح
    echo ✓ Import successful
    echo.
) else (
    echo.
    echo ✗ حدث خطأ أثناء الاستيراد
    echo ✗ Import failed
    echo.
    pause
    exit /b 1
)

REM Verify import
echo جاري التحقق من الاستيراد...
echo Verifying import...
echo.

mysql -h %DB_HOST% -u %DB_USER% -p%DB_PASSWORD% -P %DB_PORT% -e "SELECT COUNT(*) as 'عدد الجداول' FROM information_schema.TABLES WHERE TABLE_SCHEMA = '%DB_NAME%';" %DB_NAME%

echo.
echo ========================================
echo اكتملت عملية الاستيراد
echo Import completed
echo ========================================
echo.

pause
