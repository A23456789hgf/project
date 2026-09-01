<?php

namespace App\Http\Controllers;

use App\Exports\AuditLogExport;
use App\Models\AuditLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

class AuditLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:audit-logs.view')->only(['index', 'show']);
        $this->middleware('permission:audit-logs.export')->only(['export', 'exportExcel', 'exportPdf']);
    }

    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Filtering
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('entity_name', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 100, 500, 1000]) ? $perPage : 20;

        $logs = $query->paginate($perPage);

        $users = Cache::remember('audit_log_users_list', 600, function () {
            return User::select('id', 'name')->orderBy('name')->get();
        });

        $actions = Cache::remember('audit_log_actions_list', 1800, function () {
            return AuditLog::distinct()->pluck('action');
        });

        $modules = Cache::remember('audit_log_modules_list', 1800, function () {
            return AuditLog::distinct()->pluck('module')->filter();
        });

        return view('admin.audit-logs.index', compact('logs', 'users', 'actions', 'modules'));
    }

    public function show(AuditLog $auditLog)
    {
        // Return JSON for AJAX requests if needed, but for now we have inline modals
        if (request()->ajax()) {
            return response()->json($auditLog);
        }

        return view('admin.audit-logs.show', compact('auditLog'));
    }

    public function export(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if (! $request->filled('from_date') && ! $request->filled('to_date')) {
            $query->take(2000);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $fileName = 'سجل_العمليات_'.now()->format('Y_m_d_H_i_s').'.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            // Headers
            fputcsv($file, ['المستخدم', 'الجهة', 'الإجراء', 'القسم', 'الوصف', 'رابط الصفحة', 'IP Address', 'التاريخ والوقت']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->user_name ?? ($log->user->name ?? 'النظام'),
                    $log->entity_name ?? '-',
                    $log->translated_action,
                    $log->translated_module,
                    $log->translated_description,
                    $log->url,
                    $log->ip_address,
                    $log->arabic_date,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if (! $request->filled('from_date') && ! $request->filled('to_date')) {
            $query->take(2000);
        }

        $query->orderBy('created_at', 'desc');
        $fileName = 'سجل_العمليات_'.now()->format('Y_m_d_H_i_s').'.xlsx';

        // Clear any output buffers to prevent file corruption
        if (ob_get_length()) {
            ob_end_clean();
        }

        return Excel::download(new AuditLogExport($query), $fileName);
    }

    public function exportPdf(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if (! $request->filled('from_date') && ! $request->filled('to_date')) {
            $query->take(1000);
        }

        $logs = $query->orderBy('created_at', 'desc')->cursor();

        $pdf = Pdf::loadView('user.audit-log-pdf', compact('logs'));
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
        ]);

        $fileName = 'سجل_العمليات_'.now()->format('Y_m_d_H_i_s').'.pdf';

        return $pdf->download($fileName);
    }
}
