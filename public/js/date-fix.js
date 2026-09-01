/**
 * إصلاح مشكلة التواريخ القديمة في جميع حقول التاريخ
 * Date Fix for Old Dates Issue
 */

document.addEventListener('DOMContentLoaded', function() {
    // إعداد جميع حقول التاريخ في الصفحة
    function setupDateInputs() {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        
        dateInputs.forEach(input => {
            // إزالة أي قيود موجودة على التاريخ
            input.removeAttribute('min');
            input.removeAttribute('max');
            
            // إضافة نطاق واسع للتواريخ (من 1900 إلى 2100)
            input.setAttribute('min', '1900-01-01');
            input.setAttribute('max', '2100-12-31');
            
            // إضافة معالج للتحقق من صحة التاريخ
            input.addEventListener('change', function() {
                validateDateInput(this);
            });
        });
    }

    // التحقق من صحة التاريخ المدخل
    function validateDateInput(input) {
        const dateValue = new Date(input.value);
        const minDate = new Date('1900-01-01');
        const maxDate = new Date('2100-12-31');
        
        if (dateValue < minDate || dateValue > maxDate) {
            input.setCustomValidity('يرجى إدخال تاريخ صحيح بين عامي 1900 و 2100');
        } else {
            input.setCustomValidity('');
        }
    }

    // التحقق من تواريخ البداية والنهاية
    function setupDateRangeValidation() {
        // البحث عن أزواج تواريخ البداية والنهاية
        const startDateInputs = document.querySelectorAll('input[name*="start"], input[id*="start"], input[name*="planned_start"]');
        const endDateInputs = document.querySelectorAll('input[name*="end"], input[id*="end"], input[name*="planned_end"]');
        
        // ربط كل تاريخ بداية مع تاريخ النهاية المقابل
        startDateInputs.forEach(startInput => {
            const endInput = findCorrespondingEndDate(startInput);
            if (endInput) {
                setupDateRangeValidationPair(startInput, endInput);
            }
        });
    }

    // البحث عن تاريخ النهاية المقابل لتاريخ البداية
    function findCorrespondingEndDate(startInput) {
        const startName = startInput.name || startInput.id;
        const endName = startName.replace(/start/i, 'end');
        
        return document.querySelector(`input[name="${endName}"], input[id="${endName}"]`);
    }

    // إعداد التحقق من صحة نطاق التواريخ
    function setupDateRangeValidationPair(startInput, endInput) {
        function validateRange() {
            if (startInput.value && endInput.value) {
                const startDate = new Date(startInput.value);
                const endDate = new Date(endInput.value);
                
                if (startDate > endDate) {
                    endInput.setCustomValidity('تاريخ النهاية يجب أن يكون بعد تاريخ البداية');
                } else {
                    endInput.setCustomValidity('');
                }
            }
        }
        
        startInput.addEventListener('change', validateRange);
        endInput.addEventListener('change', validateRange);
    }

    // تشغيل الإعدادات
    setupDateInputs();
    setupDateRangeValidation();
    
    // إعادة تشغيل الإعدادات عند إضافة محتوى جديد (للنوافذ المنبثقة)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                // تأخير قصير للتأكد من تحميل المحتوى
                setTimeout(() => {
                    setupDateInputs();
                    setupDateRangeValidation();
                }, 100);
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});

// دالة عامة لإعداد التواريخ الهجرية
function setupHijriDateValidation() {
    const hijriInputs = document.querySelectorAll('input[name*="hijri"], input[id*="hijri"]');
    
    hijriInputs.forEach(input => {
        input.addEventListener('input', function() {
            validateHijriDate(this);
        });
        
        // إضافة placeholder إذا لم يكن موجوداً
        if (!input.placeholder) {
            input.placeholder = 'مثال: 1445/01/01';
        }
    });
}

// التحقق من صحة التاريخ الهجري
function validateHijriDate(input) {
    const hijriPattern = /^\d{4}\/\d{1,2}\/\d{1,2}$/;
    
    if (input.value && !hijriPattern.test(input.value)) {
        input.setCustomValidity('يرجى إدخال التاريخ بالصيغة: سنة/شهر/يوم (مثال: 1445/01/01)');
    } else {
        input.setCustomValidity('');
    }
}

// تشغيل إعداد التواريخ الهجرية عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', setupHijriDateValidation);