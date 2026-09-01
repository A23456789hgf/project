<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>غير مصرح بالوصول - 403</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
            margin: 0;
        }
        .error-container {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 500px;
            width: 90%;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .error-icon {
            color: #ef4444;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        h1 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #0f172a;
        }
        p {
            color: #64748b;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        .btn-back {
            background: #3b82f6;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.4);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-back:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
            color: white;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <x-icon name="shield-alt" style="width: 80px; height: 80px;" />
        </div>
        <h1>وصول غير مصرح به</h1>
        <p>عذراً، ليس لديك الصلاحيات الكافية للوصول إلى هذه الصفحة أو تنفيذ هذا الإجراء.</p>
        <div class="d-flex flex-column gap-2">
            <a href="{{ url()->previous() }}" class="btn-back mb-2">
                <x-icon name="arrow-right" class="ms-2" style="width: 16px; height: 16px;" /> العودة للخلف
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm py-2 d-inline-flex align-items-center justify-content-center" style="border-radius: 0.75rem;">
                <x-icon name="home" class="ms-2" style="width: 16px; height: 16px;" /> الصفحة الرئيسية
            </a>
        </div>
    </div>
</body>
</html>
