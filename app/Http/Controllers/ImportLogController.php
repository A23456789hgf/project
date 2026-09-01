<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Models\User;
use App\Services\ImportTrackingService;
use Illuminate\Http\Request;

class ImportLogController extends Controller
{
    /**
     * Display a listing of the import logs.
     */
    public function index(Request $request)
    {
        $this->authorize('import-logs.view');

        $query = ImportLog::with('user')->latest();

        if ($request->filled('unit_name')) {
            $query->where('unit_name', 'like', '%'.$request->unit_name.'%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('started_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('started_at', '<=', $request->date_to);
        }

        $importLogs = $query->paginate(15);
        $users = User::all();

        return view('import_logs.index', compact('importLogs', 'users'));
    }

    /**
     * Display the specified import log.
     */
    public function show(ImportLog $importLog)
    {
        $this->authorize('import-logs.view');

        $importLog->load('user');

        return view('import_logs.show', compact('importLog'));
    }

    /**
     * Rollback the specified import log.
     */
    public function rollback(ImportLog $importLog, ImportTrackingService $service)
    {
        $this->authorize('import-logs.rollback');

        if ($importLog->status === 'Rolled Back') {
            return redirect()->back()->with('error', 'تم التراجع عن هذه العملية مسبقاً.');
        }

        try {
            $service->rollback($importLog);

            return redirect()->back()->with('success', 'تم التراجع عن عملية الاستيراد بنجاح، وتم حذف السجلات التي أضيفت.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء التراجع: '.$e->getMessage());
        }
    }
}
