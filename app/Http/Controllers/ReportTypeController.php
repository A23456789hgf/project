<?php

namespace App\Http\Controllers;

use App\Models\ReportType;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportTypeController extends Controller
{
    public function index()
    {
        $this->authorize('report-types.view');
        $reportTypes = ReportType::orderBy('name')->get();

        return view('configuration.report_type.index', compact('reportTypes'));
    }

    public function create()
    {
        $this->authorize('report-types.create');

        return view('configuration.report_type.create');
    }

    public function store(Request $request)
    {
        $this->authorize('report-types.create');
        $request->validate([
            'name' => 'required|string|max:255|unique:report_types,name',
            'is_active' => 'required|boolean',
        ], [
            'name.required' => 'اسم نوع التقرير مطلوب.',
            'name.unique' => 'اسم نوع التقرير موجود مسبقاً، يجب أن يكون فريداً.',
        ]);

        ReportType::create($request->only('name', 'is_active'));

        session()->flash('success', 'تم إضافة نوع التقرير بنجاح.');

        return redirect()->route('report-types.index');
    }

    public function importForm()
    {
        $this->authorize('report-types.import');

        return view('configuration.report_type.import');
    }

    public function edit(ReportType $reportType)
    {
        $this->authorize('report-types.edit');

        return view('configuration.report_type.edit', compact('reportType'));
    }

    public function update(Request $request, ReportType $reportType)
    {
        $this->authorize('report-types.edit');
        $request->validate([
            'name' => 'required|string|max:255|unique:report_types,name,'.$reportType->id,
            'is_active' => 'required|boolean',
        ], [
            'name.required' => 'اسم نوع التقرير مطلوب.',
            'name.unique' => 'اسم نوع التقرير موجود مسبقاً، يجب أن يكون فريداً.',
        ]);

        $reportType->update($request->only('name', 'is_active'));

        session()->flash('success', 'تم تحديث نوع التقرير بنجاح.');

        return redirect()->route('report-types.index');
    }

    public function destroy(ReportType $reportType)
    {
        $this->authorize('report-types.delete');
        $reportType->delete();

        session()->flash('success', 'تم حذف نوع التقرير بنجاح.');

        return redirect()->route('report-types.index');
    }

    /**
     * تصدير إلى Excel
     */
    public function export()
    {
        $this->authorize('report-types.export');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setRightToLeft(true);
        $sheet->setTitle('أنواع التقارير');

        // Headers
        $headers = ['المعرف', 'اسم نوع التقرير', 'الحالة', 'تاريخ الإنشاء', 'تاريخ التحديث'];
        foreach ($headers as $col => $header) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $header);
        }

        // Data
        $reportTypes = ReportType::orderBy('name')->get();
        foreach ($reportTypes as $i => $rt) {
            $row = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $row, $rt->id);
            $sheet->setCellValueByColumnAndRow(2, $row, $rt->name);
            $sheet->setCellValueByColumnAndRow(3, $row, $rt->is_active ? 'مفعل' : 'معطل');
            $sheet->setCellValueByColumnAndRow(4, $row, $rt->created_at?->format('Y-m-d'));
            $sheet->setCellValueByColumnAndRow(5, $row, $rt->updated_at?->format('Y-m-d'));
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'report_types_'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * تحميل قالب الاستيراد
     */
    public function downloadTemplate()
    {
        $this->authorize('report-types.import');

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setRightToLeft(true);
        $sheet->setTitle('قالب أنواع التقارير');

        $sheet->setCellValue('A1', 'اسم نوع التقرير (مطلوب - فريد)');
        $sheet->setCellValue('B1', 'الحالة (1=مفعل, 0=معطل)');
        // Example row
        $sheet->setCellValue('A2', 'نوع تقرير نموذجي');
        $sheet->setCellValue('B2', '1');

        $writer = new Xlsx($spreadsheet);
        $fileName = 'report_types_template.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * استيراد من Excel
     */
    public function import(Request $request)
    {
        $this->authorize('report-types.import');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ], [
            'file.required' => 'يرجى اختيار ملف للاستيراد.',
            'file.mimes' => 'يجب أن يكون الملف بصيغة Excel (xlsx أو xls).',
        ]);

        $file = $request->file('file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        // Remove header row
        array_shift($rows);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $name = trim($row[0] ?? '');
            $active = isset($row[1]) ? (int) $row[1] : 1;

            if (empty($name)) {
                $errors[] = "الصف {$rowNum}: اسم نوع التقرير فارغ، تم تخطيه.";
                $skipped++;

                continue;
            }

            // Check uniqueness
            if (ReportType::where('name', $name)->exists()) {
                $errors[] = "الصف {$rowNum}: «{$name}» موجود مسبقاً، تم تخطيه.";
                $skipped++;

                continue;
            }

            ReportType::create([
                'name' => $name,
                'is_active' => $active ? true : false,
            ]);
            $imported++;
        }

        session()->flash('success', "تم الاستيراد: {$imported} سجل. المتخطى: {$skipped} سجل.");
        if (! empty($errors)) {
            session()->flash('import_errors', $errors);
        }

        return redirect()->route('report-types.index');
    }
}
