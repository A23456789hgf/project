<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار القوائم المتتالية للمواقع</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script data-auto-replace-svg="nest" defer src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/unified-design.css') }}" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .location-group {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 0;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .location-group:hover {
            border-color: var(--secondary-color);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
        }
        
        .result-box {
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 2rem;
            border: none;
            animation: slideIn 0.3s ease;
        }
        
        .result-box strong {
            font-size: 1.2rem;
            display: block;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .result-item {
            padding: 0.5rem 0;
            font-size: 1.05rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .result-item i {
            font-size: 1.2rem;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <div class="card">
                    <div class="card-header">
                        <h4>
                            <i class="fas fa-map-marked-alt"></i>
                            اختبار القوائم المتتالية للمواقع
                        </h4>
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="location-group">
                                        <label class="form-label">
                                            <i class="fas fa-map"></i>
                                            المحافظة
                                        </label>
                                        <select class="form-select" id="governorate" name="governorate_id">
                                            <option value="">اختر المحافظة</option>
                                            @foreach($governorates as $governorate)
                                                <option value="{{ $governorate->id }}">{{ $governorate->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="location-group">
                                        <label class="form-label">
                                            <i class="fas fa-building"></i>
                                            المديرية
                                        </label>
                                        <select class="form-select" id="directorate" name="directorate_id" disabled>
                                            <option value="">اختر المديرية</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="location-group">
                                        <label class="form-label">
                                            <i class="fas fa-signpost"></i>
                                            المنطقة الفرعية
                                        </label>
                                        <select class="form-select" id="subArea" name="sub_area_id" disabled>
                                            <option value="">اختر المنطقة الفرعية</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="location-group">
                                        <label class="form-label">
                                            <i class="fas fa-home"></i>
                                            القرية
                                        </label>
                                        <select class="form-select" id="village" name="village_id" disabled>
                                            <option value="">اختر القرية</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="result" class="alert result-box" style="display: none;"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // تغيير المحافظة
            $('#governorate').change(function() {
                const governorateId = $(this).val();
                const directorateSelect = $('#directorate');
                const subAreaSelect = $('#subArea');
                const villageSelect = $('#village');
                
                // إعادة تعيين القوائم التابعة
                directorateSelect.html('<option value="">اختر المديرية</option>').prop('disabled', true);
                subAreaSelect.html('<option value="">اختر المنطقة الفرعية</option>').prop('disabled', true);
                villageSelect.html('<option value="">اختر القرية</option>').prop('disabled', true);
                
                if (governorateId) {
                    // عرض مؤشر التحميل
                    $('#result').show().html('<i class="fas fa-hourglass-half"></i> جاري تحميل المديريات...').removeClass('alert-danger alert-success').addClass('alert-info');
                    
                    $.ajax({
                        url: `/api/directorates/${governorateId}`,
                        type: 'GET',
                        success: function(data) {
                            console.log('المديريات:', data);
                            directorateSelect.prop('disabled', false);
                            $.each(data, function(key, directorate) {
                                directorateSelect.append(`<option value="${directorate.id}">${directorate.name}</option>`);
                            });
                            $('#result').show().html(`
                                <i class="fas fa-check-circle"></i> 
                                تم جلب ${data.length} مديرية بنجاح
                            `).removeClass('alert-danger alert-success').addClass('alert-info');
                        },
                        error: function(xhr, status, error) {
                            console.error('خطأ في جلب المديريات:', error);
                            $('#result').show().html(`
                                <i class="fas fa-exclamation-triangle"></i> 
                                خطأ في جلب المديريات: ${error}
                            `).removeClass('alert-info alert-success').addClass('alert-danger');
                        }
                    });
                } else {
                    $('#result').hide();
                }
            });
            
            // تغيير المديرية
            $('#directorate').change(function() {
                const directorateId = $(this).val();
                const governorateId = $('#governorate').val();
                const subAreaSelect = $('#subArea');
                const villageSelect = $('#village');
                
                // إعادة تعيين القوائم التابعة
                subAreaSelect.html('<option value="">اختر المنطقة الفرعية</option>').prop('disabled', true);
                villageSelect.html('<option value="">اختر القرية</option>').prop('disabled', true);
                
                if (directorateId && governorateId) {
                    // عرض مؤشر التحميل
                    $('#result').show().html('<i class="fas fa-hourglass-half"></i> جاري تحميل المناطق الفرعية...').removeClass('alert-danger alert-success').addClass('alert-info');
                    
                    $.ajax({
                        url: `/api/sub-areas/${governorateId}/${directorateId}`,
                        type: 'GET',
                        success: function(data) {
                            console.log('المناطق الفرعية:', data);
                            subAreaSelect.prop('disabled', false);
                            $.each(data, function(key, subArea) {
                                subAreaSelect.append(`<option value="${subArea.id}">${subArea.name}</option>`);
                            });
                            $('#result').show().html(`
                                <i class="fas fa-check-circle"></i> 
                                تم جلب ${data.length} منطقة فرعية بنجاح
                            `).removeClass('alert-danger alert-success').addClass('alert-info');
                        },
                        error: function(xhr, status, error) {
                            console.error('خطأ في جلب المناطق الفرعية:', error);
                            $('#result').show().html(`
                                <i class="fas fa-exclamation-triangle"></i> 
                                خطأ في جلب المناطق الفرعية: ${error}
                            `).removeClass('alert-info alert-success').addClass('alert-danger');
                        }
                    });
                }
            });
            
            // تغيير المنطقة الفرعية
            $('#subArea').change(function() {
                const subAreaId = $(this).val();
                const directorateId = $('#directorate').val();
                const governorateId = $('#governorate').val();
                const villageSelect = $('#village');
                
                // إعادة تعيين قائمة القرى
                villageSelect.html('<option value="">اختر القرية</option>').prop('disabled', true);
                
                if (subAreaId && directorateId && governorateId) {
                    // عرض مؤشر التحميل
                    $('#result').show().html('<i class="fas fa-hourglass-half"></i> جاري تحميل القرى...').removeClass('alert-danger alert-success').addClass('alert-info');
                    
                    $.ajax({
                        url: `/api/villages/${governorateId}/${directorateId}/${subAreaId}`,
                        type: 'GET',
                        success: function(data) {
                            console.log('القرى:', data);
                            villageSelect.prop('disabled', false);
                            $.each(data, function(key, village) {
                                villageSelect.append(`<option value="${village.id}">${village.name}</option>`);
                            });
                            $('#result').show().html(`
                                <i class="fas fa-check-circle"></i> 
                                تم جلب ${data.length} قرية بنجاح
                            `).removeClass('alert-danger alert-success').addClass('alert-info');
                        },
                        error: function(xhr, status, error) {
                            console.error('خطأ في جلب القرى:', error);
                            $('#result').show().html(`
                                <i class="fas fa-exclamation-triangle"></i> 
                                خطأ في جلب القرى: ${error}
                            `).removeClass('alert-info alert-success').addClass('alert-danger');
                        }
                    });
                }
            });
            
            // عرض النتيجة النهائية عند اختيار القرية
            $('#village').change(function() {
                const villageId = $(this).val();
                if (villageId) {
                    const governorate = $('#governorate option:selected').text();
                    const directorate = $('#directorate option:selected').text();
                    const subArea = $('#subArea option:selected').text();
                    const village = $('#village option:selected').text();
                    
                    $('#result').show().html(`
                        <strong><i class="fas fa-map-pin"></i> الموقع المحدد:</strong>
                        <div class="result-item">
                            <i class="fas fa-map"></i>
                            <span><strong>المحافظة:</strong> ${governorate}</span>
                        </div>
                        <div class="result-item">
                            <i class="fas fa-building"></i>
                            <span><strong>المديرية:</strong> ${directorate}</span>
                        </div>
                        <div class="result-item">
                            <i class="fas fa-signpost"></i>
                            <span><strong>المنطقة الفرعية:</strong> ${subArea}</span>
                        </div>
                        <div class="result-item">
                            <i class="fas fa-home"></i>
                            <span><strong>القرية:</strong> ${village}</span>
                        </div>
                    `).removeClass('alert-danger alert-info').addClass('alert-success');
                }
            });
        });
    </script>
</body>
</html>