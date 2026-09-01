<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير المشروع - {{ $project->project_name }}</title>
    {{-- <style>
        /* الأساسيات */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
            text-align: right;
            line-height: 1.6;
            color: #333;
            background: #f5f7fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        
        /* الرأس */
        .header {
            background: linear-gradient(135deg, #1e5799 0%, #2c6cb0 100%);
            color: white;
            padding: 35px 30px;
            text-align: center;
            border-bottom: 6px solid #ffc107;
            position: relative;
        }
        
        .header::after {
            content: '';
            position: absolute;
            bottom: -6px;
            right: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent);
        }
        
        .header h1 {
            margin: 0 0 12px 0;
            font-size: 32px;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        .header h2 {
            margin: 0 0 18px 0;
            font-size: 24px;
            font-weight: 400;
            opacity: 0.95;
        }
        
        .header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.85;
        }
        
        /* الأقسام */
        .section {
            margin: 25px;
            padding: 25px;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-right: 4px solid #1e5799;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        
        .section::before {
            content: '';
            position: absolute;
            top: 0;
            right: -4px;
            width: 4px;
            height: 0;
            background: #ffc107;
            transition: height 0.4s ease;
        }
        
        .section:hover::before {
            height: 100%;
        }
        
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
        }
        
        .section h3 {
            color: #1e5799;
            border-bottom: 2px solid #eaeaea;
            padding-bottom: 15px;
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .section h3:before {
            content: "■";
            margin-left: 12px;
            color: #ffc107;
            font-size: 20px;
        }
        
        /* شبكة المعلومات */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 18px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 18px;
            border-radius: 8px;
            border-right: 4px solid #1e5799;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .info-item:hover {
            background: #e9ecef;
            transform: translateX(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        .info-value {
            color: #1e5799;
            font-size: 17px;
            font-weight: 500;
        }
        
        /* الأقسام الفرعية */
        .list-section {
            background: #f8f9fa;
            padding: 22px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #eaeaea;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .list-section h4 {
            color: #1e5799;
            margin-top: 0;
            margin-bottom: 18px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .list-section h4:before {
            content: "▸";
            margin-left: 10px;
            color: #ffc107;
        }
        
        .list-section ul {
            margin: 0;
            padding-right: 22px;
        }
        
        .list-section li {
            margin-bottom: 12px;
            line-height: 1.6;
            padding: 10px 0;
            border-bottom: 1px dashed #eaeaea;
            transition: padding-right 0.2s ease;
        }
        
        .list-section li:hover {
            padding-right: 8px;
        }
        
        .list-section li:last-child {
            border-bottom: none;
        }
        
        /* الأنشطة */
        .activity-item {
            background: #f8f9fa;
            padding: 18px;
            margin-bottom: 18px;
            border-radius: 8px;
            border-right: 4px solid #28a745;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .activity-item:hover {
            transform: translateX(-5px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .activity-name {
            font-weight: 600;
            color: #28a745;
            margin-bottom: 10px;
            font-size: 17px;
        }
        
        .activity-weight {
            color: #6c757d;
            font-size: 15px;
            background: #e9ecef;
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
        }
        
        .actions-list {
            margin-top: 15px;
            padding-right: 18px;
        }
        
        .actions-list li {
            font-size: 15px;
            color: #495057;
            margin-bottom: 8px;
            padding: 6px 0;
            border-bottom: 1px dotted #dee2e6;
        }
        
        .actions-list li:last-child {
            border-bottom: none;
        }
        
        /* التمويل */
        .financing-summary {
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            padding: 25px;
            border-radius: 8px;
            border: 1px solid #c3e6cb;
            text-align: center;
            margin-bottom: 25px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
        }
        
        .financing-summary h4 {
            color: #155724;
            margin: 0 0 18px 0;
            font-size: 20px;
            font-weight: 600;
        }
        
        .total-cost {
            font-size: 32px;
            font-weight: 700;
            color: #155724;
            margin: 12px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        /* التذييل */
        .footer {
            margin-top: 40px;
            text-align: center;
            padding: 30px;
            background: #1e5799;
            color: white;
            position: relative;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #ffc107, #1e5799, #ffc107);
        }
        
        .footer p {
            margin: 8px 0;
            opacity: 0.9;
        }
        
        /* الطباعة */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                max-width: 100%;
                box-shadow: none;
                margin: 0;
                border-radius: 0;
            }
            
            .header {
                background: #1e5799 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .section {
                page-break-inside: avoid;
                margin: 15px 0;
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .section:hover, .info-item:hover, .activity-item:hover {
                transform: none;
                box-shadow: none;
                background: #f8f9fa;
            }
            
            .footer {
                background: #1e5799 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .section::before, .footer::before, .header::after {
                display: none;
            }
        }
        
        /* الوسائط المتعددة */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .section {
                margin: 15px;
                padding: 18px;
            }
            
            .header {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 26px;
            }
            
            .header h2 {
                font-size: 20px;
            }
        }
        
        /* تأثيرات إضافية */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        .badge-primary {
            background: #1e5799;
            color: white;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .badge-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        
        /* شارات الحالة */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-draft {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        /* شريط التقدم */
        .progress-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }
        
        .progress-value {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        
        /* ملخص المشروع */
        .project-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .summary-item {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #1e5799;
            margin: 8px 0;
        }
        
        .summary-label {
            font-size: 14px;
            color: #6c757d;
        }
        
        /* تحسينات للعناوين الطويلة */
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        /* تحسينات للجداول */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .data-table th, .data-table td {
            padding: 12px 15px;
            text-align: right;
            border-bottom: 1px solid #eaeaea;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .data-table tr:hover {
            background: #f1f3f5;
        }
    </style> --}}
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>تقرير المشروع</h1>
            <h2>{{ $project->project_name }}</h2>
            <p>تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</p>
        </div>

        <!-- Project Basic Information -->
        <div class="section">
            <h3>البيانات الأساسية للمشروع</h3>
            
            <!-- ملخص المشروع -->
            <div class="project-summary">
                <div class="summary-item">
                    <div class="summary-value">{{ $project->form_number }}</div>
                    <div class="summary-label">رقم المشروع</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ $project->number_of_beneficiaries ? number_format($project->number_of_beneficiaries) : '0' }}</div>
                    <div class="summary-label">عدد المستفيدين</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ $project->start_date_gregorian }}</div>
                    <div class="summary-label">تاريخ البداية</div>
                </div>
                <div class="summary-item">
                    <div class="summary-value">{{ $project->end_date_gregorian }}</div>
                    <div class="summary-label">تاريخ النهاية</div>
                </div>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">اسم المشروع:</div>
                    <div class="info-value">{{ $project->project_name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">رقم المشروع:</div>
                    <div class="info-value">{{ $project->form_number }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">البرنامج:</div>
                    <div class="info-value">{{ $project->program->program_name ?? 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">المجال:</div>
                    <div class="info-value">{{ $project->domain->domain_name ?? 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">المجال الفرعي:</div>
                    <div class="info-value">{{ $project->subdomain->subdomain_name ?? 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">نوع التدخل:</div>
                    <div class="info-value">{{ $project->intervention->intervention_name ?? 'غير محدد' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">تاريخ البداية:</div>
                    <div class="info-value">{{ $project->start_date_gregorian }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">تاريخ النهاية:</div>
                    <div class="info-value">{{ $project->end_date_gregorian }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">عدد المستفيدين:</div>
                    <div class="info-value">{{ $project->number_of_beneficiaries ? number_format($project->number_of_beneficiaries) : '0' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">حالة المشروع:</div>
                    <div class="info-value">
                        <span class="status-badge {{ $project->status == 'draft' ? 'status-draft' : ($project->status == 'completed' ? 'status-completed' : 'status-active') }}">
                            {{ $project->status == 'draft' ? 'مسودة' : ($project->status == 'completed' ? 'مكتمل' : 'نشط') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Details -->
        @if($project->detail)
        <div class="section">
            <h3>تفاصيل المشروع</h3>
            <div class="info-item">
                <div class="info-label">وصف المشروع:</div>
                <div class="info-value">{{ $project->detail->project_description }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">مبررات المشروع:</div>
                <div class="info-value">{{ $project->detail->project_justification }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">أهداف المشروع:</div>
                <div class="info-value">{{ $project->detail->project_goals }}</div>
            </div>
        </div>
        @endif

        <!-- Project Locations -->
        @if($project->locations->count() > 0)
        <div class="section">
            <h3>مواقع المشروع</h3>
            <div class="list-section">
                <ul>
                    @foreach($project->locations as $location)
                    <li>
                        {{ $location->governorate->governorate_name ?? 'غير محدد' }} - 
                        {{ $location->directorate->directorate_name ?? 'غير محدد' }} - 
                        {{ $location->subArea->sub_area_name ?? 'غير محدد' }} - 
                        {{ $location->village->village_name ?? 'غير محدد' }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Objectives -->
        @if($project->mainObjectives->count() > 0 || $project->specialObjectives->count() > 0)
        <div class="section">
            <h3>أهداف المشروع</h3>
            
            @if($project->mainObjectives->count() > 0)
            <div class="list-section">
                <h4>الأهداف الرئيسية</h4>
                <ul>
                    @foreach($project->mainObjectives as $objective)
                    <li>
                        {{ $objective->objective_description }} 
                        <span class="badge badge-primary">الوزن: {{ $objective->objective_weight }}%</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($project->specialObjectives->count() > 0)
            <div class="list-section">
                <h4>الأهداف الخاصة</h4>
                <ul>
                    @foreach($project->specialObjectives as $objective)
                    <li>
                        {{ $objective->objective_description }} 
                        <span class="badge badge-primary">الوزن: {{ $objective->objective_weight }}%</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endif

        <!-- Stakeholders and Risks -->
        <div class="section">
            <h3>أصحاب المصلحة والمخاطر</h3>
            
            @if($project->entities->count() > 0)
            <div class="list-section">
                <h4>أصحاب المصلحة</h4>
                <ul>
                    @foreach($project->entities as $entity)
                    <li>
                        <strong>{{ $entity->entity->entity_name ?? 'غير محدد' }}</strong> - {{ $entity->role }}
                        <br><small>المسؤوليات: {{ $entity->responsibilities }}</small>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($project->risks->count() > 0)
            <div class="list-section">
                <h4>المخاطر</h4>
                <ul>
                    @foreach($project->risks as $risk)
                    <li>
                        <strong>{{ $risk->risk_description }}</strong> 
                        <span class="badge 
                            @if($risk->risk_level == 'منخفض') badge-success
                            @elseif($risk->risk_level == 'متوسط') badge-warning
                            @else badge-danger
                            @endif">
                            المستوى: {{ $risk->risk_level }}
                        </span>
                        <br><small>استراتيجية التخفيف: {{ $risk->mitigation_strategy }}</small>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        <!-- Activities -->
        @if($project->preliminaryActivities->count() > 0 || $project->executiveActivities->count() > 0)
        <div class="section">
            <h3>أنشطة المشروع</h3>
            
            @if($project->preliminaryActivities->count() > 0)
            <div class="list-section">
                <h4>الأنشطة التمهيدية</h4>
                @foreach($project->preliminaryActivities as $activity)
                <div class="activity-item">
                    <div class="activity-name">{{ $activity->activity_name }}</div>
                    <div class="activity-weight">الوزن: {{ $activity->activity_weight }}%</div>
                    @if($activity->actions->count() > 0)
                    <ul class="actions-list">
                        @foreach($activity->actions as $action)
                        <li>{{ $action->action }} ({{ $action->start_date }} - {{ $action->end_date }})</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if($project->executiveActivities->count() > 0)
            <div class="list-section">
                <h4>الأنشطة التنفيذية</h4>
                @foreach($project->executiveActivities as $activity)
                <div class="activity-item">
                    <div class="activity-name">{{ $activity->activity_name }}</div>
                    <div class="activity-weight">الوزن: {{ $activity->activity_weight }}%</div>
                    @if($activity->actions->count() > 0)
                    <ul class="actions-list">
                        @foreach($activity->actions as $action)
                        <li>{{ $action->action }} ({{ $action->start_date }} - {{ $action->end_date }})</li>
                        @endforeach
                    </ul>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @endif

        <!-- Cost and Financing -->
        @if($project->cost || $project->financings->count() > 0)
        <div class="section">
            <h3>التكلفة والتمويل</h3>
            
            @if($project->cost)
            <div class="financing-summary">
                <h4>إجمالي تكلفة المشروع</h4>
                <div class="total-cost">{{ number_format($project->cost->total_cost, 2) }} {{ $project->cost->currency }}</div>
                @if($project->cost->cost_breakdown)
                <p><strong>تفصيل التكلفة:</strong> {{ $project->cost->cost_breakdown }}</p>
                @endif
            </div>
            @endif

            @if($project->financings->count() > 0)
            <div class="list-section">
                <h4>مصادر التمويل</h4>
                <ul>
                    @foreach($project->financings as $financing)
                    <li>
                        <strong>{{ $financing->fundingSource->source_name ?? 'غير محدد' }}</strong>
                        ({{ $financing->financingType->type_name ?? 'غير محدد' }})
                        - {{ number_format($financing->amount, 2) }} ({{ $financing->percentage }}%)
                        @if($financing->notes)
                        <br><small>ملاحظات: {{ $financing->notes }}</small>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
        @endif

        <div class="footer">
            <p>تم إنشاء هذا التقرير بواسطة نظام متابعة المشاريع</p>
            <p>وزارة الزراعة والثروة السمكية والموارد المائية</p>
        </div>
    </div>

    <script>
        // إضافة تأثيرات تفاعلية بسيطة
        document.addEventListener('DOMContentLoaded', function() {
            // تأثيرات للعناصر عند التمرير
            const sections = document.querySelectorAll('.section');
            
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);
            
            sections.forEach(section => {
                section.style.opacity = 0;
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(section);
            });
        });
    </script>
</body>
</html>