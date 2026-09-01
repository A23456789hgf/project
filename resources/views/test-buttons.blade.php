@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4>اختبار أزرار المشروع - Test Project Buttons</h4>
                    <p class="text-muted">هذه الصفحة لاختبار جميع الأزرار التي تم إصلاحها</p>
                </div>
                <div class="card-body">
                    
                    <!-- Test 1: Add Specific Objectives Button -->
                    <div class="mb-4">
                        <h5>1. اختبار زر إضافة الأهداف الخاصة</h5>
                        <button type="button" class="btn btn-primary" id="addSpecialObjective">
                            <i class="fas fa-plus"></i> إضافة هدف خاص
                        </button>
                        <div class="mt-2">
                            <table class="table" id="specialObjectivesTable">
                                <tbody>
                                    <tr class="project-empty-row">
                                        <td colspan="5" class="text-center">لا توجد أهداف خاصة مضافة بعد</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Test 2: Add Preliminary Activity Button -->
                    <div class="mb-4">
                        <h5>2. اختبار زر إضافة النشاط التمهيدي</h5>
                        <button type="button" class="btn btn-success" id="addActivityBtn">
                            <i class="fas fa-plus"></i> إضافة نشاط تمهيدي
                        </button>
                        <div class="mt-2">
                            <table class="table" id="preliminaryActivitiesTable">
                                <tbody>
                                    <tr class="project-empty-row">
                                        <td colspan="5" class="text-center">لا توجد أنشطة مضافة بعد</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Test 3: Add Cost Button -->
                    <div class="mb-4">
                        <h5>3. اختبار زر إضافة التكلفة</h5>
                        <button type="button" class="btn btn-warning" onclick="addCost(123)">
                            <i class="fas fa-dollar-sign"></i> إضافة تكلفة
                        </button>
                    </div>

                    <!-- Test 4: Executive Activities Buttons -->
                    <div class="mb-4">
                        <h5>4. اختبار أزرار الأنشطة التنفيذية</h5>
                        <button type="button" class="btn btn-info" onclick="showAssigneesModal(0, 0)">
                            <i class="fas fa-users"></i> إضافة مكلفين
                        </button>
                        <button type="button" class="btn btn-warning" onclick="showCostsModal(0, 0)">
                            <i class="fas fa-dollar-sign"></i> إضافة تكاليف
                        </button>
                    </div>

                    <!-- Test 5: Add Entity Button -->
                    <div class="mb-4">
                        <h5>5. اختبار زر إضافة الجهات</h5>
                        <button type="button" class="btn btn-primary" id="add-entity-btn">
                            <i class="fas fa-building"></i> إضافة جهة
                        </button>
                        <div class="mt-2" id="entities-container">
                            <div class="alert alert-light text-center" id="no-entities-message">
                                لا توجد جهات مرتبطة بالمشروع
                            </div>
                        </div>
                    </div>

                    <!-- Test Results -->
                    <div class="mt-4">
                        <h5>نتائج الاختبار</h5>
                        <div id="test-results" class="alert alert-info">
                            انقر على الأزرار أعلاه لاختبار الوظائف. ستظهر النتائج هنا.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Entity Template for Testing -->
<template id="entity-template">
    <div class="entity-item mb-4 border rounded p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="text-primary mb-0">بيانات الجهة الجديدة</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-entity">
                <i class="fas fa-trash-alt"></i> حذف الجهة
            </button>
        </div>
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">نوع الجهة</label>
                <input type="text" name="entities[__INDEX__][entity_type]" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">اسم الجهة</label>
                <input type="text" name="entities[__INDEX__][entity_name]" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">مهمة الجهة</label>
                <textarea name="entities[__INDEX__][entity_task]" class="form-control" rows="2" required></textarea>
            </div>
        </div>
    </div>
</template>

<script>
// Test Results Logger
function logTestResult(testName, status, message) {
    const resultsDiv = document.getElementById('test-results');
    const timestamp = new Date().toLocaleTimeString();
    const statusClass = status === 'success' ? 'text-success' : status === 'error' ? 'text-danger' : 'text-info';
    
    resultsDiv.innerHTML += `
        <div class="${statusClass}">
            <strong>[${timestamp}]</strong> ${testName}: ${message}
        </div>
    `;
    resultsDiv.scrollTop = resultsDiv.scrollHeight;
}

// Override console.log to capture test results
const originalConsoleLog = console.log;
console.log = function(...args) {
    originalConsoleLog.apply(console, args);
    
    const message = args.join(' ');
    if (message.includes('✅')) {
        logTestResult('Success', 'success', message);
    } else if (message.includes('❌')) {
        logTestResult('Error', 'error', message);
    } else if (message.includes('clicked') || message.includes('WORKING')) {
        logTestResult('Button Test', 'info', message);
    }
};

// Test the preliminary activity manager
document.addEventListener('DOMContentLoaded', function() {
    logTestResult('Page Load', 'info', 'صفحة الاختبار تم تحميلها بنجاح');
    
    // Test if jQuery is loaded
    if (typeof $ !== 'undefined') {
        logTestResult('jQuery', 'success', 'jQuery تم تحميله بنجاح');
    } else {
        logTestResult('jQuery', 'error', 'jQuery لم يتم تحميله');
    }
    
    // Test if Bootstrap is loaded
    if (typeof bootstrap !== 'undefined') {
        logTestResult('Bootstrap', 'success', 'Bootstrap تم تحميله بنجاح');
    } else {
        logTestResult('Bootstrap', 'error', 'Bootstrap لم يتم تحميله');
    }
    
    // Test if project forms fix is loaded
    setTimeout(() => {
        const addBtn = document.getElementById('addActivityBtn');
        if (addBtn) {
            addBtn.click();
            logTestResult('Preliminary Activity', 'info', 'تم اختبار زر النشاط التمهيدي');
        }
    }, 1000);
});
</script>
@endsection