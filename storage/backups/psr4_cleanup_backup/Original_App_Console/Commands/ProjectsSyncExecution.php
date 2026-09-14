<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Services\FrappeAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProjectsSyncExecution extends Command
{
    /**
     * اسم الأمر وسينتاكس الخيارات.
     *
     * @var string
     */
    protected $signature = 'projects:sync-execution
                            {--dry-run : محاكاة دون إرسال فعلي}
                            {--force : إعادة المزامنة حتى لو كان المشروع متزامناً}
                            {--project-id= : مزامنة مشروع معين فقط}';

    /**
     * وصف الأمر.
     *
     * @var string
     */
    protected $description = 'مزامنة المشاريع في مرحلة التنفيذ مع نظام Frappe (ERPNext) والتحقق من وجود الشركة أو إنشائها';

    /**
     * خدمة المزامنة.
     *
     * @var FrappeAPIService
     */
    protected $syncService;

    /**
     * إنشاء الأمر وحقن الخدمة.
     */
    public function __construct(FrappeAPIService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    /**
     * تنفيذ الأمر.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $projectId = $this->option('project-id');

        // بناء الاستعلام لجلب المشاريع
        $query = Project::query();

        if ($projectId) {
            $query->where('id', $projectId);
        } else {
            // مرحلة التنفيذ
            $query->whereIn('status', ['execution', 'in_execution']);
        }

        $query->with(['creatorEntity', 'createdBy.entity']);
        $projects = $query->get();

        if ($projects->isEmpty()) {
            $this->warn('لا يوجد مشاريع للمزامنة.');

            return 0;
        }

        $this->info('عدد المشاريع المراد مزامنتها: '.$projects->count());

        $results = [];
        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        foreach ($projects as $project) {
            try {
                // إذا كان dry-run، نعرض ما سيفعل فقط
                if ($dryRun) {
                    $this->line(" [DRY-RUN] المشروع #{$project->id} سيتم مزامنته (اسم الجهة سيكون حسب Hierarchy الخاص به)");
                    $results[] = [
                        'project_id' => $project->id,
                        'status' => 'dry-run',
                    ];
                    $bar->advance();

                    continue;
                }

                // المزامنة الفعلية إلى Frappe
                $syncResult = $this->syncService->sendProjectOnExecution($project, true); // true for force update

                if ($syncResult['success'] ?? false) {
                    $frappeProjectId = $syncResult['data']['name'] ?? $syncResult['name'] ?? null;
                    // تحديث المشروع بحفظ المعرف الجديد إذا لم يكن موجوداً
                    if (empty($project->erpnext_project_id) && $frappeProjectId) {
                        $project->erpnext_project_id = $frappeProjectId;
                        $project->save();
                    }
                    $this->info("المشروع #{$project->id} تمت مزامنته بنجاح (Frappe ID: {$frappeProjectId})");
                    $results[] = [
                        'project_id' => $project->id,
                        'status' => 'success',
                        'frappe_id' => $frappeProjectId,
                    ];
                } else {
                    $this->error("فشلت مزامنة المشروع #{$project->id}: ".($syncResult['message'] ?? 'خطأ غير معروف'));
                    $results[] = [
                        'project_id' => $project->id,
                        'status' => 'error',
                        'reason' => $syncResult['message'] ?? 'فشل المزامنة',
                    ];
                }

            } catch (\Exception $e) {
                Log::error("خطأ في مزامنة المشروع #{$project->id}: ".$e->getMessage());
                $this->error("خطأ استثنائي للمشروع #{$project->id}: ".$e->getMessage());
                $results[] = [
                    'project_id' => $project->id,
                    'status' => 'exception',
                    'reason' => $e->getMessage(),
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // عرض ملخص النتائج
        $this->table(
            ['المشروع', 'الحالة', 'الملاحظات'],
            collect($results)->map(function ($item) {
                return [
                    $item['project_id'],
                    $item['status'],
                    $item['reason'] ?? ($item['frappe_id'] ?? ''),
                ];
            })->toArray()
        );

        return 0;
    }
}
