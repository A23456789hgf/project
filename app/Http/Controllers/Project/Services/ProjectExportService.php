<?php

namespace App\Http\Controllers\Project\Services;

use App\Exports\ProjectComprehensiveExport;
use App\Exports\ProjectDetailedExport;
use App\Exports\ProjectHierarchicalExport;
use App\Exports\ProjectsExport;
use App\Http\Controllers\ProjectPdfExportController;
use App\Models\Project;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mpdf\Mpdf;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectExportService
{
    protected $projectPdfExportController;

    protected $projectService;

    public function __construct()
    {
        $this->projectPdfExportController = new ProjectPdfExportController;
        // Lazy-resolve to avoid circular dependency at boot time
        $this->projectService = null;
    }

    /**
     * Get the ProjectService instance (lazy to avoid circular dependency).
     */
    protected function getProjectService(): ProjectService
    {
        if ($this->projectService === null) {
            $this->projectService = app(ProjectService::class);
        }

        return $this->projectService;
    }

    /**
     * تصدير مشروع واحد كـ PDF
     */
    public function exportProjectPdf(Project $project)
    {
        try {
            $project->load([
                'program', 'domain', 'subdomain', 'intervention', 'priority',
                'detail', 'locations.governorate', 'locations.directorate',
                'locations.subArea', 'locations.village', 'mainObjectives',
                'specialObjectives.results.outputs', 'objectiveResults', 'resultOutputs',
                'risks', 'cost', 'financings.fundingSource', 'financings.authority',
                'financings.financingType', 'financings.financingForm',
                'financings.subFinancingForm', 'supervisingAuthorities.authority',
                'supervisingAuthorities.parent', 'implementingEntities.authority',
                'implementingEntities.parent', 'participatingEntities.authority',
                'participatingEntities.parent', 'beneficiaryEntities.authority',
                'beneficiaryEntities.parent', 'preliminaryActivities.procedures.costs',
                'preliminaryFinancialSummaries.financialItem',
                'preliminaryFinancialSummaries.activity',
                'preliminaryFinancialSummaries.procedure',
                'preliminaryFinancialSummaries.cost',
                'executiveActivities.actions.assignedEntities',
                'executiveActivities.actions.costs', 'executiveFinancialSummaries',
            ]);

            $qrCodeUrl = route('projects.show', $project->id);
            $qrCodeImage = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl='.urlencode($qrCodeUrl).'&choe=UTF-8';

            try {
                $qrImageData = @file_get_contents($qrCodeImage);
                if ($qrImageData !== false) {
                    $qrCodeBase64 = 'data:image/png;base64,'.base64_encode($qrImageData);
                } else {
                    $qrCodeBase64 = $qrCodeImage;
                }
            } catch (\Exception $e) {
                $qrCodeBase64 = $qrCodeImage;
            }

            // استخدام mPDF لدعم أفضل للغة العربية
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 40,  // زيادة الهامش العلوي لاستيعاب الترويسة
                'margin_bottom' => 25,  // زيادة الهامش السفلي لاستيعاب الذيل
                'margin_header' => 10,
                'margin_footer' => 10,
                'default_font' => 'dejavusans',
                'tempDir' => storage_path('app/tmp/mpdf'),
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'autoArabic' => true,  // تمكين المعالجة التلقائية للنص العربي
            ]);

            // ضبط الاتجاه من اليمين لليسار للغة العربية
            $mpdf->SetDirectionality('rtl');

            // تحضير الشعار
            $logoPath = public_path('images/logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,'.base64_encode($logoData);
            }

            $printDate = date('Y-m-d');

            // تحضير الترويسة (Header) - سيتم استخدامها لجميع الصفحات ما عدا الغلاف
            $headerHtml = '
            <table style="width: 100%; border-bottom: 2px solid #0C5B47; padding-bottom: 5px; font-family: dejavusans;">
                <tr>
                    <td width="33%" style="text-align: right; vertical-align: top; font-size: 8pt;">
                        <b>التاريخ:</b> '.$printDate.'<br>
                        <b>الرقم:</b> '.($project->form_number ?? '---').'
                    </td>
                    <td width="33%" style="text-align: center;">
                        <img src="'.$logoBase64.'" style="height: 50px;">
                    </td>
                    <td width="33%" style="text-align: left; vertical-align: top; font-size: 8pt;">
                        <b>الجمهورية اليمنية</b><br>
                        اللجنة الزراعية والسمكية العليا
                    </td>
                </tr>
            </table>';

            // تحضير الذيل (Footer)
            $userName = auth()->user()->name ?? '---';
            $footerHtml = '
            <table style="width: 100%; border-top: 1px solid #ccc; font-size: 8pt; padding-top: 5px; font-family: dejavusans;">
                <tr>
                    <td width="33%" style="text-align: right;">طبع بواسطة: '.$userName.'</td>
                    <td width="34%" style="text-align: center; font-weight: bold;">صفحة {PAGENO} من {nbpg}</td>
                    <td width="33%" style="text-align: left;">تاريخ الطباعة: '.$printDate.'</td>
                </tr>
            </table>';

            // عرض محتوى الـ PDF كاملاً
            $html = view('projects.pdf-export', compact('project', 'qrCodeBase64', 'qrCodeUrl'))->render();

            // تقسيم المحتوى: الغلاف والمحتوى الداخلي
            // نبحث عن نهاية صفحة الغلاف (page-break)
            $coverEndMarker = '<div class="page-break"></div>';
            $parts = explode($coverEndMarker, $html, 2);

            if (count($parts) === 2) {
                // الجزء الأول: صفحة الغلاف (بدون ترويسة أو ذيل)
                $coverHtml = $parts[0];
                // الجزء الثاني: باقي المحتوى
                $contentHtml = $parts[1];

                // كتابة صفحة الغلاف بدون ترويسة أو ذيل
                $mpdf->WriteHTML($coverHtml);

                // إضافة صفحة جديدة
                $mpdf->AddPage();

                // تفعيل الذيل لجميع الصفحات
                $mpdf->SetHTMLFooter($footerHtml);

                // إضافة الترويسة مباشرة في بداية المحتوى (ستظهر مرة واحدة فقط في الصفحة الأولى)
                $contentWithHeader = $headerHtml.'<div style="margin-top: 15px;"></div>'.$contentHtml;

                // كتابة باقي المحتوى
                $mpdf->WriteHTML($contentWithHeader);
            } else {
                // إذا لم يتم العثور على page-break، نكتب كل المحتوى مع الترويسة والذيل
                $mpdf->SetHTMLHeader($headerHtml);
                $mpdf->SetHTMLFooter($footerHtml);
                $mpdf->WriteHTML($html);
            }

            $fileName = 'project_'.$project->form_number.'_'.date('Y-m-d').'.pdf';

            return $mpdf->Output($fileName, 'D');

        } catch (\Exception $e) {
            Log::error('Failed to export PDF: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \Exception('An error occurred while exporting PDF: '.$e->getMessage());
        }
    }

    /**
     * تصدير مشروع واحد كـ PDF مفصل
     */
    public function exportDetailedProjectPdf(Project $project)
    {
        try {
            return $this->projectPdfExportController->exportProjectPdf($project);
        } catch (\Exception $e) {
            Log::error('Failed to export detailed project PDF: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);
            throw new \Exception('Failed to export detailed PDF: '.$e->getMessage());
        }
    }

    /**
     * تصدير مشروع واحد كـ Excel (hierarchical)
     */
    public function exportProjectExcel(Project $project)
    {
        try {
            // Use hierarchical export for better structure
            $exporter = new ProjectHierarchicalExport(request());
            $spreadsheet = $exporter->exportSingleProject($project);

            $fileName = 'Project_'.($project->form_number ?? $project->id).'_'.date('Y-m-d').'.xlsx';

            $writer = new Xlsx($spreadsheet);

            return new StreamedResponse(
                function () use ($writer) {
                    if (ob_get_length()) {
                        ob_end_clean();
                    }
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                    'Cache-Control' => 'max-age=0',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to export project Excel: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);
            throw new \Exception('Failed to export Excel file: '.$e->getMessage());
        }
    }

    /**
     * تصدير مشروع واحد كـ Excel (Comprehensive Single Sheet)
     */
    public function exportProjectExcelComprehensive(Project $project)
    {
        try {
            $exporter = new ProjectComprehensiveExport;
            $spreadsheet = $exporter->export($project);

            $fileName = 'Project_Comprehensive_'.($project->form_number ?? $project->id).'_'.date('Y-m-d').'.xlsx';

            $writer = new Xlsx($spreadsheet);

            return new StreamedResponse(
                function () use ($writer) {
                    if (ob_get_length()) {
                        ob_end_clean();
                    }
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                    'Cache-Control' => 'max-age=0',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to export project comprehensive Excel: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);
            throw new \Exception('Failed to export Excel file: '.$e->getMessage());
        }
    }

    /**
     * تصدير جميع المشاريع كـ Excel (Comprehensive Single Sheet)
     * يستخدم نطاق المستخدم الجغرافي والإداري عبر ProjectService
     */
    public function exportAllProjectsExcelComprehensive(Request $request)
    {
        try {
            // جلب الاستعلام مع تطبيق نطاق المستخدم عبر ProjectService
            $query = $this->getProjectService()->getProjects(
                $request,
                null,
                null,
                [],
                true, // returnQueryOnly
                true  // skipEagerLoading
            );

            // Handle selection
            $selectedProjects = $request->get('selected_projects');
            if ($selectedProjects) {
                if (is_string($selectedProjects)) {
                    $selectedProjects = json_decode($selectedProjects, true);
                }
                if (! empty($selectedProjects)) {
                    $query->whereIn('id', $selectedProjects);
                }
            } else {
                // Apply additional filters
                if ($request->has('program_id') && $request->program_id) {
                    $query->where('program_id', $request->program_id);
                }
                if ($request->has('domain_id') && $request->domain_id) {
                    $query->where('domain_id', $request->domain_id);
                }
                if ($request->has('status') && $request->status) {
                    $query->where('status', $request->status);
                }
            }

            $projects = $query->latest()->get();

            if ($projects->isEmpty()) {
                throw new \Exception('No projects found to export');
            }

            $exporter = new ProjectComprehensiveExport;
            $spreadsheet = $exporter->export($projects);

            $fileName = 'Projects_Comprehensive_Export_'.date('Y-m-d_H-i-s').'.xlsx';

            $writer = new Xlsx($spreadsheet);

            return new StreamedResponse(
                function () use ($writer) {
                    if (ob_get_length()) {
                        ob_end_clean();
                    }
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                    'Cache-Control' => 'max-age=0',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to export all projects comprehensive Excel: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            throw new \Exception('Failed to export Excel files: '.$e->getMessage());
        }
    }

    /**
     * تصدير جميع المشاريع كـ Excel (with hierarchical structure)
     * يستخدم نطاق المستخدم الجغرافي والإداري عبر ProjectService
     */
    public function exportAllProjectsExcel(Request $request)
    {
        try {
            // جلب الاستعلام المُقيَّد بنطاق المستخدم عبر ProjectService
            $query = $this->getProjectService()->getProjects(
                $request,
                null,
                null,
                [],
                true, // returnQueryOnly
                true  // skipEagerLoading
            );

            // إضافة العلاقات المطلوبة للتصدير
            $query->with([
                'program', 'domain', 'subdomain', 'intervention', 'priority', 'targetCategory',
                'detail', 'locations.governorate', 'locations.directorate', 'locations.subArea', 'locations.village',
                'mainObjectives', 'specialObjectives.results.outputs', 'risks', 'cost',
                'financings.fundingSource', 'financings.authority', 'financings.financingType', 'financings.financingForm', 'financings.subFinancingForm',
                'supervisingAuthorities.authority', 'supervisingAuthorities.parent',
                'implementingEntities.authority', 'implementingEntities.parent',
                'participatingEntities.authority', 'participatingEntities.parent',
                'beneficiaryEntities.authority', 'beneficiaryEntities.parent',
                'beneficiaryGroups',
                'preliminaryActivities.procedures.costs.financialItem',
                'preliminaryActivities.procedures.executions',
                'preliminaryFinancialSummaries.financialItem',
                'executiveActivities.actions.assignedEntities',
                'executiveActivities.actions.costs.financialItem',
                'executiveActivities.actions.executions',
                'executiveFinancialSummaries.financialItem',
                'projectApprovals.stage', 'projectApprovals.entity', 'projectApprovals.createdBy',
                'activityHistory.user',
                'documents.uploader',
            ]);

            // Handle different export types
            $exportType = $request->get('export_type', 'all');

            if ($exportType === 'selected') {
                $selectedProjects = $request->get('selected_projects');
                if ($selectedProjects) {
                    if (is_string($selectedProjects)) {
                        $selectedProjects = json_decode($selectedProjects, true);
                    }
                    if (! empty($selectedProjects)) {
                        $query->whereIn('id', $selectedProjects);
                    }
                }
            } else {
                // Apply additional filters (for 'all' or 'filtered' or 'custom')
                if ($request->has('program_id') && $request->program_id) {
                    $query->where('program_id', $request->program_id);
                }

                if ($request->has('domain_id') && $request->domain_id) {
                    $query->where('domain_id', $request->domain_id);
                }

                if ($request->has('subdomain_id') && $request->subdomain_id) {
                    $query->where('subdomain_id', $request->subdomain_id);
                }

                if ($request->has('status') && $request->status) {
                    $query->where('status', $request->status);
                }
            }

            $projects = $query->latest()->get();

            if ($projects->isEmpty()) {
                throw new \Exception('No projects found to export');
            }

            // Use hierarchical export for better structure
            $exporter = new ProjectHierarchicalExport($request);
            $spreadsheet = $exporter->exportMultipleProjects($projects);

            $fileName = 'Projects_Export_'.date('Y-m-d_H-i-s').'.xlsx';

            $writer = new Xlsx($spreadsheet);

            return new StreamedResponse(
                function () use ($writer) {
                    if (ob_get_length()) {
                        ob_end_clean();
                    }
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                    'Cache-Control' => 'max-age=0',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to export all projects Excel: '.$e->getMessage(), [
                'exception' => $e,
                'export_type' => $request->get('export_type'),
            ]);
            throw new \Exception('Failed to export Excel files: '.$e->getMessage());
        }
    }

    /**
     * تصدير جميع المشاريع كـ PDF
     */
    public function exportAllProjectsPdf(Request $request)
    {
        try {
            return $this->projectPdfExportController->exportProjectsListPdf($request);
        } catch (\Exception $e) {
            Log::error('Failed to export all projects PDF: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            throw new \Exception('Failed to export PDF files: '.$e->getMessage());
        }
    }

    /**
     * تصدير المشاريع إلى Excel (الطريقة الأساسية)
     */
    public function exportProjectsExcel()
    {
        $startTime = microtime(true);

        try {
            // تسجيل بدء التصدير
            Log::info('Excel Export Started', [
                'module' => 'Projects',
                'user_id' => auth()->id() ?? 'guest',
                'timestamp' => now()->toDateTimeString(),
            ]);

            // التحقق من وجود مشاريع للتصدير — نستخدم نطاق المستخدم
            $scopedQuery = $this->getProjectService()->getProjects(
                request(),
                null, null, [], true, true
            );
            $projectsCount = (clone $scopedQuery)->count();

            if ($projectsCount === 0) {
                Log::warning('Excel Export Warning', [
                    'module' => 'Projects',
                    'reason' => 'No projects found in database',
                    'timestamp' => now()->toDateTimeString(),
                ]);
                throw new \Exception('No projects found to export');
            }

            // إنشاء التصدير
            $export = new ProjectsExport;
            $spreadsheet = $export->export();

            // إنشاء ملف مؤقت
            $tempFile = tempnam(sys_get_temp_dir(), 'projects_export');

            if (! $tempFile) {
                throw new \Exception('Failed to create temporary file for export');
            }

            // الكتابة إلى الملف المؤقت
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            // التحقق من إنشاء الملف بنجاح
            if (! file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('Export file was not created or is empty');
            }

            $fileName = 'projects_export_'.date('Y-m-d_H-i-s').'.xlsx';
            $fileSize = filesize($tempFile);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // بالميلي ثانية

            // تسجيل نجاح التصدير
            Log::info('Excel Export Success', [
                'module' => 'Projects',
                'user_id' => auth()->id() ?? 'guest',
                'file_name' => $fileName,
                'file_size' => $fileSize.' bytes',
                'records_exported' => $projectsCount,
                'execution_time' => $executionTime.' ms',
            ]);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error('Excel Export Failed', [
                'module' => 'Projects',
                'user_id' => auth()->id() ?? 'guest',
                'error_message' => $e->getMessage(),
                'execution_time' => $executionTime.' ms',
                'timestamp' => now()->toDateTimeString(),
            ]);

            throw new \Exception('Failed to export Excel file: '.$e->getMessage());
        }
    }

    /**
     * تصدير قائمة بالمشاريع كـ PDF
     * يستخدم نطاق المستخدم الجغرافي والإداري عبر ProjectService
     */
    public function exportProjectsListPdf(Request $request)
    {
        try {
            // جلب الاستعلام المُقيَّد بنطاق المستخدم
            $query = $this->getProjectService()->getProjects(
                $request,
                null, null, [], true, true
            );

            // تحميل العلاقات الضرورية للعرض في PDF
            $query->with(['program', 'domain', 'subdomain', 'intervention']);

            // تطبيق الفلاتر الإضافية
            if ($request->has('program_id') && $request->program_id) {
                $query->where('program_id', $request->program_id);
            }

            if ($request->has('domain_id') && $request->domain_id) {
                $query->where('domain_id', $request->domain_id);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            $projects = $query->latest()->get();

            if ($projects->isEmpty()) {
                throw new \Exception('No projects found to export');
            }

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 15,
                'margin_bottom' => 10,
                'default_font' => 'dejavusans',
                'tempDir' => storage_path('app/tmp/mpdf'),
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'autoArabic' => true,
            ]);

            $mpdf->SetDirectionality('rtl');

            $html = view('projects.pdf-list', compact('projects'))->render();
            $mpdf->WriteHTML($html);

            $fileName = 'projects_list_'.date('Y-m-d').'.pdf';

            return $mpdf->Output($fileName, 'D');

        } catch (\Exception $e) {
            Log::error('Failed to export projects list PDF: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            throw new \Exception('Failed to export projects list PDF: '.$e->getMessage());
        }
    }

    /**
     * تصدير التلخيص المالي للمشروع كـ PDF
     */
    public function exportFinancialSummaryPdf(Project $project)
    {
        try {
            $project->load([
                'preliminaryFinancialSummaries.financialItem',
                'executiveFinancialSummaries.financialItem',
                'financings.fundingSource',
                'financings.authority',
            ]);

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 15,
                'margin_bottom' => 10,
                'default_font' => 'dejavusans',
                'tempDir' => storage_path('app/tmp/mpdf'),
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'autoArabic' => true,
            ]);

            $mpdf->SetDirectionality('rtl');

            $html = view('projects.pdf-financial-summary', compact('project'))->render();
            $mpdf->WriteHTML($html);

            $fileName = 'financial_summary_'.$project->form_number.'_'.date('Y-m-d').'.pdf';

            return $mpdf->Output($fileName, 'D');

        } catch (\Exception $e) {
            Log::error('Failed to export financial summary PDF: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);
            throw new \Exception('Failed to export financial summary PDF: '.$e->getMessage());
        }
    }

    /**
     * تصدير التلخيص المالي للمشروع كـ Excel
     */
    public function exportFinancialSummaryExcel(Project $project)
    {
        try {
            $project->load([
                'preliminaryFinancialSummaries.financialItem',
                'executiveFinancialSummaries.financialItem',
                'financings.fundingSource',
                'financings.authority',
            ]);

            $exporter = new ProjectDetailedExport;
            $spreadsheet = $exporter->exportFinancialSummary($project);

            $fileName = 'financial_summary_'.$project->form_number.'_'.date('Y-m-d').'.xlsx';

            $writer = new Xlsx($spreadsheet);

            return new StreamedResponse(
                function () use ($writer) {
                    if (ob_get_length()) {
                        ob_end_clean();
                    }
                    $writer->save('php://output');
                },
                200,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
                    'Cache-Control' => 'max-age=0',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to export financial summary Excel: '.$e->getMessage(), [
                'project_id' => $project->id,
                'exception' => $e,
            ]);
            throw new \Exception('Failed to export financial summary Excel: '.$e->getMessage());
        }
    }

    /**
     * معالجة النص العربي للعرض الصحيح في PDF
     */
    private function processArabicText($text)
    {
        if (! class_exists(Arabic::class)) {
            return $text;
        }

        $arabic = new Arabic;

        $processText = function ($value) use ($arabic, &$processText) {
            if (is_string($value) && $this->containsArabic($value)) {
                return $arabic->utf8Glue($arabic->utf8Strrev($value));
            }

            if (is_object($value)) {
                foreach (get_object_vars($value) as $key => $prop) {
                    $value->$key = $processText($prop);
                }

                return $value;
            }

            if (is_array($value)) {
                return array_map($processText, $value);
            }

            return $value;
        };

        return $processText($text);
    }

    /**
     * التحقق إذا كان النص يحتوي على أحرف عربية
     */
    private function containsArabic($text)
    {
        if (! is_string($text)) {
            return false;
        }

        return preg_match('/[\x{0600}-\x{06FF}]/u', $text) === 1;
    }

    /**
     * إنشاء QR code للمشروع
     */
    private function generateQrCode($project)
    {
        $qrCodeUrl = route('projects.show', $project->id);

        try {
            // استخدام خدمة QR code بديلة إذا كانت Google غير متاحة
            $qrCodeImage = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.urlencode($qrCodeUrl);

            $qrImageData = @file_get_contents($qrCodeImage);
            if ($qrImageData !== false) {
                return 'data:image/png;base64,'.base64_encode($qrImageData);
            }

            // إذا فشلت المحاولة الأولى، استخدم خدمة أخرى
            $qrCodeImage = 'https://quickchart.io/qr?text='.urlencode($qrCodeUrl).'&size=200';
            $qrImageData = @file_get_contents($qrCodeImage);
            if ($qrImageData !== false) {
                return 'data:image/png;base64,'.base64_encode($qrImageData);
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to generate QR code', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * الحصول على إحصائيات التصدير (مقيّدة بنطاق المستخدم)
     */
    public function getExportStatistics()
    {
        try {
            $scopedBase = $this->getProjectService()->getProjects(
                request(), null, null, [], true, true
            );

            $totalProjects = (clone $scopedBase)->count();
            $draftProjects = (clone $scopedBase)->where('status', 'draft')->count();
            $finalProjects = (clone $scopedBase)->where('status', 'final')->count();

            $recentExports = [
                'last_week' => (clone $scopedBase)->where('created_at', '>=', now()->subWeek())->count(),
                'last_month' => (clone $scopedBase)->where('created_at', '>=', now()->subMonth())->count(),
            ];

            return [
                'total_projects' => $totalProjects,
                'draft_projects' => $draftProjects,
                'final_projects' => $finalProjects,
                'recent_exports' => $recentExports,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get export statistics: '.$e->getMessage());

            return [];
        }
    }

    /**
     * تصدير تقارير مخصصة (فردية أو جماعية) مع خيار الاتجاه
     */
    public function exportCustomReports(Request $request)
    {
        try {
            $reportType = $request->get('report_type');
            $orientation = $request->get('orientation', 'P'); // P for Portrait, L for Landscape

            $projects = collect();

            if ($reportType === 'individual') {
                $projects = Project::with([
                    'program', 'domain', 'subdomain', 'intervention', 'priority',
                    'detail', 'locations.governorate', 'locations.directorate',
                    'locations.subArea', 'locations.village', 'mainObjectives',
                    'specialObjectives.results.outputs', 'objectiveResults', 'resultOutputs',
                    'risks', 'cost', 'financings.fundingSource', 'financings.authority',
                    'financings.financingType', 'financings.financingForm',
                    'financings.subFinancingForm', 'supervisingAuthorities.authority',
                    'supervisingAuthorities.parent', 'implementingEntities.authority',
                    'implementingEntities.parent', 'participatingEntities.authority',
                    'participatingEntities.parent', 'beneficiaryEntities.authority',
                    'beneficiaryEntities.parent', 'preliminaryActivities.procedures.costs',
                    'preliminaryFinancialSummaries.financialItem',
                    'preliminaryFinancialSummaries.activity',
                    'preliminaryFinancialSummaries.procedure',
                    'preliminaryFinancialSummaries.cost',
                    'executiveActivities.actions.assignedEntities',
                    'executiveActivities.actions.costs', 'executiveFinancialSummaries',
                ])->where('id', $request->get('project_id'))->get();
            } else {
                // Group or All — نطبق نطاق المستخدم عبر getProjects
                $query = $this->getProjectService()->getProjects(
                    $request, null, null, [], true, true
                );

                $query->with([
                    'detail', 'locations.governorate',
                    'cost', 'financings', 'executiveActivities', 'preliminaryActivities',
                ]);

                if ($reportType === 'group' && $request->has('project_ids')) {
                    $query->whereIn('id', $request->get('project_ids'));
                }

                $projects = $query->get();
            }

            if ($projects->isEmpty()) {
                throw new \Exception('لا توجد مشاريع للعرض');
            }

            // إعداد mPDF
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4'.($orientation === 'L' ? '-L' : ''),
                'orientation' => $orientation,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 15,
                'margin_bottom' => 10,
                'default_font' => 'dejavusans',
                'tempDir' => storage_path('app/tmp/mpdf'),
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'autoArabic' => true,
            ]);

            $mpdf->SetDirectionality('rtl');

            // Generate QR Codes for each project
            foreach ($projects as $project) {
                $project->qrCodeBase64 = $this->generateQrCode($project);
            }

            // Select view based on orientation
            $viewName = $orientation === 'L'
                ? 'projects.pdf.report_landscape'
                : 'projects.pdf-export'; // Or generic portrait view

            // If multiple projects in portrait, we might want to use the list view or loop the detailed view
            // For now, if orientation is Landscape, use the new landscape view which handles multiple projects
            // If Portrait and multiple, we might default to the existing single export in a loop (concatenated)

            if ($orientation === 'L') {
                $html = view($viewName, compact('projects'))->render();
            } else {
                // Portrait mode
                if ($projects->count() === 1) {
                    $project = $projects->first();
                    $qrCodeBase64 = $project->qrCodeBase64;
                    $qrCodeUrl = route('projects.show', $project->id);
                    $html = view('projects.pdf-export', compact('project', 'qrCodeBase64', 'qrCodeUrl'))->render();
                } else {
                    // Loop through projects and concat views? Or create a multi-project portrait view
                    // For simplicity, let's reuse the landscape view logic but adapted for portrait lists or just use list view
                    // The user specifically asked for "all/group + Arabic + A4 Horizontal".
                    // If they choose Portrait for group, we can use the list view or a concatenated detailed view.
                    // Let's use the list view for group portrait for now, or the new template if it supports it.
                    // Actually, let's make the new template support both or just use it.

                    // Let's pass to the new template, assuming it can handle the loop.
                    $html = view('projects.pdf.report_landscape', compact('projects'))->render();
                }
            }

            $mpdf->WriteHTML($html);

            $fileName = 'Reports_'.date('Y-m-d_H-i').'.pdf';

            return $mpdf->Output($fileName, 'D');

        } catch (\Exception $e) {
            Log::error('Failed to export custom reports: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            throw new \Exception('فشل في تصدير التقرير: '.$e->getMessage());
        }
    }
}
