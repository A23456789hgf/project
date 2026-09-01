# PowerShell Script for Database Import
# دسكريبت للاستيراد من SQL في Windows

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "استيراد قاعدة البيانات - Database Import" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Get database credentials
$dbHost = "127.0.0.1"
$dbPort = "3306"
$dbName = "prot"
$dbUser = "root"

Write-Host "إدخال بيانات قاعدة البيانات - Enter Database Credentials:" -ForegroundColor Yellow
Write-Host ""

# Prompt for password
$dbPassword = Read-Host "كلمة المرور (Password)" -AsSecureString
$dbPasswordPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToCoTaskMemUnicode($dbPassword))

Write-Host ""
Write-Host "البيانات المدخلة:" -ForegroundColor Green
Write-Host "Host: $dbHost" 
Write-Host "Port: $dbPort"
Write-Host "Database: $dbName"
Write-Host "User: $dbUser"
Write-Host ""

# Check if mysql is installed
Write-Host "جاري التحقق من MySQL..." -ForegroundColor Yellow
$mysqlPath = Get-Command mysql -ErrorAction SilentlyContinue

if ($null -eq $mysqlPath) {
    Write-Host "خطأ: MySQL غير مثبت أو لم يتم العثور عليه في المسار" -ForegroundColor Red
    Write-Host "Error: MySQL not found. Make sure it's installed and added to PATH" -ForegroundColor Red
    exit 1
}

Write-Host "✓ تم العثور على MySQL" -ForegroundColor Green
Write-Host ""

# Choose import file
Write-Host "اختر الملف المراد استيراده:" -ForegroundColor Yellow
Write-Host "1. fix_tables.sql (الخيار الموصى به)"
Write-Host "2. approval_stages_clean.sql"
Write-Host ""

$choice = Read-Host "اختر رقم الملف (1 أو 2)"

$sqlFile = switch($choice) {
    "1" { "fix_tables.sql" }
    "2" { "approval_stages_clean.sql" }
    default { "fix_tables.sql" }
}

$fullPath = Join-Path (Get-Location) "database\sql\$sqlFile"

Write-Host ""
Write-Host "جاري استيراد الملف: $sqlFile" -ForegroundColor Cyan
Write-Host "Importing file: $sqlFile" -ForegroundColor Cyan
Write-Host ""

try {
    # Build mysql command
    $cmd = @(
        "-h", $dbHost,
        "-u", $dbUser,
        "-p$dbPasswordPlain",
        "-P", $dbPort,
        $dbName
    )

    # Import the SQL file
    Get-Content $fullPath | mysql @cmd

    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✓ تم الاستيراد بنجاح" -ForegroundColor Green
        Write-Host "✓ Import successful" -ForegroundColor Green
        Write-Host ""
        Write-Host "الجدول الآن جاهز للاستخدام" -ForegroundColor Green
    } else {
        Write-Host ""
        Write-Host "✗ حدث خطأ أثناء الاستيراد" -ForegroundColor Red
        Write-Host "✗ Import failed" -ForegroundColor Red
        exit 1
    }
}
catch {
    Write-Host ""
    Write-Host "✗ خطأ: $_" -ForegroundColor Red
    Write-Host "✗ Error: $_" -ForegroundColor Red
    exit 1
}

# Verify import
Write-Host ""
Write-Host "جاري التحقق من الاستيراد..." -ForegroundColor Yellow
Write-Host "Verifying import..." -ForegroundColor Yellow
Write-Host ""

try {
    $verifyCmd = @(
        "-h", $dbHost,
        "-u", $dbUser,
        "-p$dbPasswordPlain",
        "-P", $dbPort,
        "-e", "SELECT COUNT(*) as '📊 عدد الجداول' FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$dbName';",
        $dbName
    )

    mysql @verifyCmd

    Write-Host ""
    Write-Host "✓ تمت العملية بنجاح!" -ForegroundColor Green
}
catch {
    Write-Host "تنبيه: لم يتمكن من التحقق: $_" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "اكتملت عملية الاستيراد" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
