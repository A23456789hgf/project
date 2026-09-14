<?php

namespace App\Http\Controllers;

use App\Models\ValueChain;
use App\Services\FileImportService;
use App\Services\ImportTrackingService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValueChainController extends Controller
{
    public function __construct()
    {
        // Middleware removed in favor of explicit $this->authorize() calls in methods
    }

    public function index()
    {
        $this->authorize('value_chains.view');
        $valueChains = ValueChain::with(['parent', 'createdBy'])
            ->latest()
            ->paginate(20);

        return view('value_chains.value_chains.index', compact('valueChains'));
    }

    public function create()
    {
        $this->authorize('value_chains.create');
        $parents = ValueChain::orderBy('name')->get();

        return view('value_chains.value_chains.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $this->authorize('value_chains.create');
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:value_chains,id',
        ]);

        $valueChain = ValueChain::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'created_by' => auth()->id(),
        ]);

        try {
            app(NotificationService::class)->notifyValueChain($valueChain, 'created', auth()->user());
        } catch (\Exception $e) {
            \Log::error('Notification error in ValueChainController store: '.$e->getMessage());
        }

        session()->flash('success', 'تم إضافة بنجاح');

        return redirect()->route('value-chains.index');
    }

    public function edit(ValueChain $valueChain)
    {
        $this->authorize('value_chains.edit');
        $parents = ValueChain::where('id', '!=', $valueChain->id)->get();

        return view('value_chains.value_chains.edit', compact('valueChain', 'parents'));
    }

    public function update(Request $request, ValueChain $valueChain)
    {
        $this->authorize('value_chains.edit');
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:value_chains,id',
        ]);

        $valueChain->update(
            $request->only(['name', 'parent_id'])
        );

        try {
            app(NotificationService::class)->notifyValueChain($valueChain, 'updated', auth()->user());
        } catch (\Exception $e) {
            \Log::error('Notification error in ValueChainController update: '.$e->getMessage());
        }

        return redirect()->route('value-chains.index')
            ->with('success', 'تم التعديل بنجاح');
    }

    public function destroy(ValueChain $valueChain)
    {
        $this->authorize('value_chains.delete');
        $valueChain->delete();

        return back()->with('success', 'تم الحذف بنجاح');
    }

    /* =====================================================
        IMPORT SYSTEM
    ===================================================== */

    public function showImportForm()
    {
        $this->authorize('value_chains.import');

        return view('value_chains.value_chains.import');
    }

    public function previewImport(Request $request)
    {
        $this->authorize('value_chains.import');
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $fileName = 'valuechain_preview_'.time().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('temp', $fileName);

        $service = new FileImportService;
        $fileData = $service->readFile($path, true);

        return view('value_chains.value_chains.import_preview', [
            'headers' => $fileData['headers'],
            'rows' => array_slice($fileData['data'], 0, 10),
            'filePath' => $path,
            'mappingFields' => ['name', 'parent_id'],
        ]);
    }

    public function processImport(Request $request)
    {
        $this->authorize('value_chains.import');
        $request->validate([
            'file_path' => 'required',
            'operation' => 'required|in:insert,update,both',
            'mapping' => 'required|array',
        ]);

        $filePath = $request->file_path;
        $operation = $request->operation;
        $mapping = $request->mapping;

        $service = new FileImportService;

        try {
            $fileData = $service->readFile($filePath, true);
            $records = $fileData['data'];
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'خطأ في قراءة الملف: '.$e->getMessage()]);
        }

        $total = 0;
        $success = 0;
        $failed = 0;
        $errors = [];

        $trackingService = new ImportTrackingService;
        $importLog = $trackingService->startImport('سلاسل القيمة', ValueChain::class, basename($filePath));

        foreach ($records as $index => $row) {

            $total++;

            $mapped = [];

            foreach ($mapping as $field => $header) {
                if ($header && isset($row[$header])) {
                    $value = trim($row[$header]);
                    if ($value !== '') {
                        $mapped[$field] = $value;
                    }
                }
            }

            if (empty($mapped['name'])) {
                continue;
            }

            $validator = Validator::make($mapped, [
                'name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:value_chains,id',
            ]);

            if ($validator->fails()) {
                $failed++;
                $errors[$index + 2] = $validator->errors()->all();

                continue;
            }

            try {
                switch ($operation) {

                    case 'insert':
                        $record = ValueChain::create([
                            'name' => $mapped['name'],
                            'parent_id' => $mapped['parent_id'] ?? null,
                            'created_by' => auth()->id(),
                        ]);
                        $trackingService->recordSuccess($importLog, $record, 'created');
                        break;

                    case 'update':
                        if (isset($mapped['id'])) {
                            $valueChain = ValueChain::find($mapped['id']);
                            if ($valueChain) {
                                $valueChain->update($mapped);
                                $trackingService->recordSuccess($importLog, $valueChain, 'updated');
                            }
                        }
                        break;

                    case 'both':
                        $record = ValueChain::updateOrCreate(
                            ['name' => $mapped['name']],
                            [
                                'parent_id' => $mapped['parent_id'] ?? null,
                                'created_by' => auth()->id(),
                            ]
                        );
                        $action = $record->wasRecentlyCreated ? 'created' : 'updated';
                        $trackingService->recordSuccess($importLog, $record, $action);
                        break;
                }

                $success++;

            } catch (\Exception $e) {
                $failed++;
                $errors[$index + 2] = [$e->getMessage()];
            }
        }

        $trackingService->finishImport($importLog, $total, $success, $failed, $errors);

        return view('value_chains.value_chains.import_report', [
            'report' => [
                'total' => $total,
                'successful' => $success,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ]);
    }

    public function downloadTemplate(Request $request)
    {
        $this->authorize('value_chains.import');
        $format = $request->get('format', 'xlsx');
        $template = [
            'headers' => ['name', 'parent_id'],
        ];

        $service = new FileImportService;
        $path = $service->createTemplate($template, $format);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
