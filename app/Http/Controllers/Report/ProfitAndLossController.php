<?php

namespace App\Http\Controllers\Report;

use App\Exports\DynamicReportExport;
use App\Http\Controllers\Controller;
use App\Services\ErpNextReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel; // We will create this export class

class ProfitAndLossController extends Controller
{
    protected $erpService;

    public function __construct(ErpNextReportService $erpService)
    {
        $this->erpService = $erpService;
    }

    public function index(Request $request)
    {
        $filterBasedOn = $request->input('filter_based_on', 'Date Range');
        $fromDate = $request->input('from_date', now()->startOfYear()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfYear()->format('Y-m-d'));

        $user = auth()->user();
        $userEntityName = $user?->entity?->name;
        $userEntityErpId = $user?->entity?->erpnext_id ?: $userEntityName;
        $defaultCompany = $userEntityErpId ?: 'New Alfajr';
        $selectedCompany = $request->input('company', $defaultCompany);

        // Default Filters
        $filters = [
            'company' => $selectedCompany,
            'filter_based_on' => $filterBasedOn,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            // Frappe's P&L requires these keys exactly when filtering by Date Range:
            'period_start_date' => $fromDate,
            'period_end_date' => $toDate,
            'periodicity' => 'Yearly',
            'accumulated_values' => 1,
            'include_default_book_entries' => 1,
            'show_account_details' => 'Summary',
            'selected_view' => 'Report',
            'from_fiscal_year' => $request->input('from_fiscal_year', now()->year),
            'to_fiscal_year' => $request->input('to_fiscal_year', now()->year),
            'cost_center' => $request->input('cost_center'),
            'project' => $request->filled('project') ? [$request->input('project')] : null,
        ];

        // Clean null values
        $filters = array_filter($filters, function ($value) {
            return ! is_null($value) && $value !== '';
        });

        // Fetch data
        $useCache = $request->input('use_cache', true);
        $reportData = $this->erpService->getReport('Profit and Loss Statement', $filters, (bool) $useCache);

        // Translate specific columns
        if ($reportData['success'] && isset($reportData['columns'])) {
            foreach ($reportData['columns'] as &$column) {
                if (is_array($column) && isset($column['label'])) {
                    if ($column['label'] === 'Account') {
                        $column['label'] = 'الحساب';
                    } elseif ($column['label'] === 'Currency') {
                        $column['label'] = 'العملة';
                    } elseif (preg_match('/^20\d{2}$/', $column['label'])) {
                        $column['label'] = 'العام '.$column['label'];
                    }
                } elseif (is_string($column)) {
                    if ($column === 'Account') {
                        $column = 'الحساب';
                    } elseif ($column === 'Currency') {
                        $column = 'العملة';
                    } elseif (preg_match('/^20\d{2}$/', $column)) {
                        $column = 'العام '.$column;
                    }
                }
            }
            unset($column); // break reference
        }

        // Handle Export to Excel
        if ($request->has('export') && $request->export === 'excel') {
            if ($reportData['success']) {
                $columns = collect($reportData['columns'])->pluck('label')->toArray();

                // Normalizing row data matching columns
                $rows = [];
                foreach ($reportData['result'] as $row) {
                    $rowData = [];
                    foreach ($reportData['columns'] as $col) {
                        $fieldname = $col['fieldname'] ?? '';
                        $rowData[] = $row[$fieldname] ?? '';
                    }
                    $rows[] = $rowData;
                }

                return Excel::download(new DynamicReportExport($columns, $rows), 'Profit_And_Loss.xlsx');
            }
        }

        $companies = $this->erpService->getCompanies();
        if ($userEntityErpId && ! in_array($userEntityErpId, $companies)) {
            array_unshift($companies, $userEntityErpId);
        }
        $projects = $this->erpService->getProjects($selectedCompany);
        // Calculate summary dynamically from the result grid to ensure it matches the selected period
        $summaryData = [];
        $income = 0;
        $expense = 0;
        $profit = 0;
        $currency = 'YER'; // default

        if (isset($reportData['result']) && is_array($reportData['result'])) {
            foreach ($reportData['result'] as $row) {
                if (! isset($row['account'])) {
                    continue;
                }
                $account = $row['account'];

                if (isset($row['currency']) && $row['currency'] != '') {
                    $currency = $row['currency'];
                }

                $rowTotal = 0;
                if (isset($row['total'])) {
                    $rowTotal = $row['total'];
                } else {
                    if (isset($reportData['columns'])) {
                        foreach ($reportData['columns'] as $col) {
                            $fname = is_array($col) ? ($col['fieldname'] ?? '') : $col;
                            if (! in_array($fname, ['account', 'account_name', 'acc_number', 'currency', 'claim_expense_type', 'hidden'])) {
                                if (isset($row[$fname]) && is_numeric($row[$fname])) {
                                    $rowTotal += $row[$fname];
                                }
                            }
                        }
                    }
                }

                $accountNormalized = str_replace(['أ', 'إ', 'آ'], 'ا', mb_strtolower($account, 'UTF-8'));

                if (strpos($accountNormalized, 'total income') !== false || strpos($accountNormalized, 'اجمالي الايرادات') !== false) {
                    $income = $rowTotal;
                }
                if (strpos($accountNormalized, 'total expense') !== false || strpos($accountNormalized, 'اجمالي النفقات') !== false || strpos($accountNormalized, 'اجمالي المصروفات') !== false) {
                    $expense = $rowTotal;
                }
                if (strpos($accountNormalized, 'profit') !== false || strpos($accountNormalized, 'loss') !== false || strpos($accountNormalized, 'الربح') !== false || strpos($accountNormalized, 'الخسارة') !== false) {
                    $profit = $rowTotal;
                }
            }
        }

        $summaryData = [
            [
                'value' => $income,
                'label' => 'إجمالي الإيرادات',
                'datatype' => 'Currency',
                'currency' => $currency,
                'indicator' => 'blue',
            ],
            [
                'value' => $expense,
                'label' => 'إجمالي النفقات',
                'datatype' => 'Currency',
                'currency' => $currency,
                'indicator' => 'orange',
            ],
            [
                'value' => $profit,
                'label' => 'المتبقي',
                'datatype' => 'Currency',
                'currency' => $currency,
                'indicator' => $profit >= 0 ? 'green' : 'red',
            ],
        ];

        return view('reports.profit-and-loss', [
            'filters' => $filters,
            'reportData' => $reportData,
            'companies' => $companies,
            'projects' => $projects,
            'summaryData' => $summaryData,
        ]);
    }
}
