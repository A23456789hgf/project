<?php

namespace App\Http\Controllers\Report;

use App\Exports\DynamicReportExport;
use App\Http\Controllers\Controller;
use App\Services\ErpNextReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PlExpenseSummaryController extends Controller
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

        // Default Filters matching the required payload
        $filters = [
            'company' => $selectedCompany,
            'filter_based_on' => $filterBasedOn,
            'from_date' => $fromDate,
            'to_date' => $toDate,
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

        $useCache = $request->input('use_cache', true);

        // Fetch children and merge them into our targets
        $children = $this->erpService->getChildCompanies($selectedCompany);
        $companiesToFetch = array_merge([$selectedCompany], $children);

        $mergedResult = [];
        $mergedGlResult = [];
        $baseColumns = [];
        $success = false;
        $lastError = '';

        $accountsMap = $this->erpService->getAccountsMap();

        foreach ($companiesToFetch as $comp) {
            $f = $filters;
            $f['company'] = $comp;

            $reportData = $this->erpService->getPlExpenseSummary($f, (bool) $useCache);
            $glData = $this->erpService->getGlReport($f, (bool) $useCache);

            if (isset($glData['success']) && $glData['success']) {
                if (isset($glData['result']) && is_array($glData['result'])) {
                    foreach ($glData['result'] as $glRow) {
                        $debit = (float) ($glRow['debit'] ?? 0);
                        $credit = (float) ($glRow['credit'] ?? 0);

                        // تجاهل السجلات التي قيمتها صفر في المدين والدائن (مثل الأرصدة الافتتاحية أو مجاميع فارغة)
                        if ($debit != 0 || $credit != 0) {
                            if (empty($glRow['claim_expense_type']) || $glRow['claim_expense_type'] == 'No Expense Type found') {
                                $glRow['claim_expense_type'] = 'غير محدد';
                            }
                            $mergedGlResult[] = $glRow;
                        }
                    }
                }
            }

            if ($reportData['success']) {
                $success = true;

                // Keep columns from the first successful fetch
                if (empty($baseColumns) && isset($reportData['columns'])) {
                    $baseColumns = $reportData['columns'];
                }

                if (isset($reportData['result']) && is_array($reportData['result'])) {
                    foreach ($reportData['result'] as $row) {
                        if (! isset($row['account'])) {
                            continue;
                        }

                        $accountName = trim($row['account'], "'");
                        $baseName = explode(' - ', $accountName)[0] ?? '';
                        if (empty($baseName)) {
                            continue;
                        }

                        // Resolve Account Number via fuzzy match
                        $accNumber = $row['acc_number'] ?? '';
                        if (empty($accNumber)) {
                            if (isset($accountsMap[$accountName]) && ! empty($accountsMap[$accountName])) {
                                $accNumber = $accountsMap[$accountName];
                            } elseif ($baseName) {
                                foreach ($accountsMap as $key => $num) {
                                    if (! empty($num) && strpos($key, $baseName) !== false) {
                                        $accNumber = $num;
                                        break;
                                    }
                                }
                            }
                        }

                        $row['acc_number'] = $accNumber;
                        // Unify account name to base name so same accounts merge correctly across companies
                        $row['account'] = $baseName;

                        if (! isset($mergedResult[$baseName])) {
                            // First time adding this account
                            $mergedResult[$baseName] = $row;
                        } else {
                            // Account exists, merge numeric values (e.g. totals)
                            foreach ($row as $k => $v) {
                                if (is_numeric($v) && $k !== 'hidden') {
                                    $mergedResult[$baseName][$k] = ($mergedResult[$baseName][$k] ?? 0) + $v;
                                }
                            }
                        }
                    }
                }
            } else {
                $lastError = $reportData['error'] ?? '';
            }
        }

        // Sort GL results by date just in case
        usort($mergedGlResult, function ($a, $b) {
            $dateA = $a['posting_date'] ?? '';
            $dateB = $b['posting_date'] ?? '';

            return strcmp($dateB, $dateA); // descending
        });

        // Map Project ID to Project Name for GL results
        $allProjects = $this->erpService->getProjects();
        $projectMap = [];
        foreach ($allProjects as $p) {
            $projectMap[$p['name']] = $p['project_name'];
        }
        foreach ($mergedGlResult as &$glRow) {
            if (! empty($glRow['project']) && isset($projectMap[$glRow['project']])) {
                $glRow['project'] = $projectMap[$glRow['project']];
            }
        }
        unset($glRow);

        // Reconstruct the report data
        $reportData = [
            'success' => $success,
            'columns' => $baseColumns,
            'result' => array_values($mergedResult),
            'gl_result' => $mergedGlResult,
        ];
        if (! $success) {
            $reportData['error'] = $lastError ?: 'حدث خطأ أثناء جلب البيانات من النظام.';
        }

        // Translate specific columns
        if ($reportData['success'] && isset($reportData['columns'])) {
            foreach ($reportData['columns'] as &$column) {
                if (is_array($column) && isset($column['label'])) {
                    if ($column['label'] === 'Account') {
                        $column['label'] = 'الحساب';
                    } elseif ($column['label'] === 'Account Name') {
                        $column['label'] = 'اسم الحساب';
                    } elseif ($column['label'] === 'Account Number') {
                        $column['label'] = 'رقم الحساب';
                    } elseif ($column['label'] === 'Currency') {
                        $column['label'] = 'العملة';
                    } elseif ($column['label'] === 'Claim Expense Type') {
                        $column['label'] = 'نوع النفقة المطالب بها (البند)';
                    } elseif (preg_match('/^20\d{2}$/', $column['label'])) {
                        $column['label'] = 'المبلغ';
                    }
                }
            }
            unset($column);
        }

        // Handle Export to Excel
        if ($request->has('export') && $request->export === 'excel') {
            if ($reportData['success']) {
                $columns = collect($reportData['columns'])->pluck('label')->toArray();

                $rows = [];
                foreach ($reportData['result'] as $row) {
                    $rowData = [];
                    foreach ($reportData['columns'] as $col) {
                        $fieldname = $col['fieldname'] ?? '';
                        $rowData[] = $row[$fieldname] ?? '';
                    }
                    $rows[] = $rowData;
                }

                return Excel::download(new DynamicReportExport($columns, $rows), 'PL_Expense_Summary.xlsx');
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

        if ($request->input('export') == 'print') {
            return view('reports.pl-expense-summary-print', [
                'reportData' => $reportData,
                'summaryData' => $summaryData,
                'company' => $selectedCompany,
            ]);
        }

        return view('reports.pl-expense-summary', [
            'filters' => $filters,
            'reportData' => $reportData,
            'companies' => $companies,
            'projects' => $projects,
            'summaryData' => $summaryData,
        ]);
    }
}
