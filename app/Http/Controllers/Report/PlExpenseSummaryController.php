<?php

namespace App\Http\Controllers\Report;

use App\Exports\DynamicReportExport;
use App\Http\Controllers\Controller;
use App\Models\ExecutiveActionCost;
use App\Models\FinancialItem;
use App\Models\Project;
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

        $selectedProject = $request->input('project');

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
            'project' => $request->filled('project') ? (is_array($selectedProject) ? $selectedProject : [$selectedProject]) : null,
            'categorize_by' => $request->input('categorize_by'),
            'include_dimensions' => 1,
        ];

        // Clean null values
        $filters = array_filter($filters, function ($value) {
            return ! is_null($value) && $value !== '';
        });

        $useCache = $request->boolean('use_cache', false);

        $includeChildren = $request->boolean('include_children', false);

        if ($includeChildren) {
            $children = $this->erpService->getChildCompanies($selectedCompany);
            $companiesToFetch = array_merge([$selectedCompany], $children);
        } else {
            // الافتراضي: الجهة المحددة فقط دون الأبناء
            $companiesToFetch = [$selectedCompany];
        }

        $mergedResult = [];
        $generalGlResult = [];
        $projectGlResult = [];
        $baseColumns = [];
        $success = false;
        $lastError = '';

        $accountsMapDetails = $this->erpService->getAccountsMap();
        $dbFinancialItems = FinancialItem::pluck('name')->toArray();

        foreach ($companiesToFetch as $comp) {
            $f = $filters;
            $f['company'] = $comp;

            $reportData = $this->erpService->getPlExpenseSummary($f, (bool) $useCache);
            $glData = $this->erpService->getGlReport($f, (bool) $useCache);

            if (isset($glData['success']) && $glData['success']) {
                $success = true;
                if (isset($glData['result']) && is_array($glData['result'])) {
                    foreach ($glData['result'] as $glRow) {
                        // Skip header / summary / total rows
                        $isHeaderOrTotal = (isset($glRow['is_opening']) && strtolower($glRow['is_opening']) === 'total')
                            || (empty($glRow['voucher_no']) && empty($glRow['posting_date']) && empty($glRow['name'] ?? ''))
                            || (isset($glRow['account']) && (str_starts_with(trim($glRow['account']), "'Total'") || str_starts_with(trim($glRow['account']), 'Total')));

                        if ($isHeaderOrTotal) {
                            continue;
                        }

                        $debit = (float) ($glRow['debit'] ?? 0);
                        $credit = (float) ($glRow['credit'] ?? 0);

                        // تجاهل السجلات التي قيمتها صفر في المدين والدائن وبدون رقم سند
                        if ($debit == 0 && $credit == 0 && empty($glRow['voucher_no'])) {
                            continue;
                        }

                        $glRow['company'] = $comp;

                        // Retrieve account details
                        $accName = trim($glRow['account'] ?? '', "'");
                        $baseAccName = explode(' - ', $accName)[0] ?? '';
                        $glRow['root_type'] = $accountsMapDetails[$accName]['root_type'] ?? ($accountsMapDetails[$baseAccName]['root_type'] ?? '');
                        $glRow['account_type'] = $accountsMapDetails[$accName]['account_type'] ?? ($accountsMapDetails[$baseAccName]['account_type'] ?? '');

                        // Smart resolution of claim expense type
                        $glRow['claim_expense_type'] = $this->resolveClaimExpenseType(
                            $glRow['claim_expense_type'] ?? '',
                            $accName,
                            $glRow['root_type'],
                            $glRow['account_type'],
                            $debit,
                            $credit,
                            $dbFinancialItems
                        );

                        // If user explicitly filtered by project or row has project, place in projectGlResult
                        if (empty($glRow['project']) && empty($selectedProject)) {
                            $generalGlResult[] = $glRow;
                        } else {
                            $projectGlResult[] = $glRow;
                        }
                    }
                }
            }

            if (isset($reportData['success']) && $reportData['success']) {
                $success = true;

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

                        // Resolve Account Number via details map
                        $accNumber = $row['acc_number'] ?? '';
                        if (empty($accNumber)) {
                            if (isset($accountsMapDetails[$accountName]['account_number']) && ! empty($accountsMapDetails[$accountName]['account_number'])) {
                                $accNumber = $accountsMapDetails[$accountName]['account_number'];
                            } elseif ($baseName) {
                                foreach ($accountsMapDetails as $key => $details) {
                                    if (! empty($details['account_number']) && strpos($key, $baseName) !== false) {
                                        $accNumber = $details['account_number'];
                                        break;
                                    }
                                }
                            }
                        }

                        $row['acc_number'] = $accNumber;
                        $row['account'] = $baseName;
                        $row['company'] = $comp;

                        $rowKey = $includeChildren ? ($baseName.'___'.$comp) : $baseName;

                        if (! isset($mergedResult[$rowKey])) {
                            $mergedResult[$rowKey] = $row;
                        } else {
                            foreach ($row as $k => $v) {
                                if (is_numeric($v) && $k !== 'hidden') {
                                    $mergedResult[$rowKey][$k] = ($mergedResult[$rowKey][$k] ?? 0) + $v;
                                }
                            }
                        }
                    }
                }
            } else {
                $lastError = $reportData['error'] ?? '';
            }
        }

        // Sort GL results by date descending
        usort($generalGlResult, function ($a, $b) {
            $dateA = $a['posting_date'] ?? '';
            $dateB = $b['posting_date'] ?? '';

            return strcmp($dateB, $dateA);
        });
        usort($projectGlResult, function ($a, $b) {
            $dateA = $a['posting_date'] ?? '';
            $dateB = $b['posting_date'] ?? '';

            return strcmp($dateB, $dateA);
        });

        // Map Project ID to Project Name for GL results
        $allProjects = $this->erpService->getProjects();
        $projectMap = [];
        foreach ($allProjects as $p) {
            $projectMap[$p['name']] = $p['project_name'];
        }
        foreach ($projectGlResult as &$glRow) {
            if (! empty($glRow['project']) && isset($projectMap[$glRow['project']])) {
                $glRow['project'] = $projectMap[$glRow['project']];
            }
        }
        unset($glRow);

        // Build account dates lookup from GL entries
        $accountDates = [];
        foreach ($generalGlResult as $gl) {
            $acc = explode(' - ', trim($gl['account'] ?? '', "'"))[0] ?? '';
            $c = $gl['company'] ?? '';
            $k1 = $c.'___'.$acc;
            if (! empty($gl['posting_date'])) {
                if (! isset($accountDates[$k1]) || strcmp($gl['posting_date'], $accountDates[$k1]) > 0) {
                    $accountDates[$k1] = $gl['posting_date'];
                }
                if (! isset($accountDates[$acc]) || strcmp($gl['posting_date'], $accountDates[$acc]) > 0) {
                    $accountDates[$acc] = $gl['posting_date'];
                }
            }
        }

        // Attach date, total, and resolved expense type to summary rows
        foreach ($mergedResult as &$r) {
            $bName = $r['account'] ?? '';
            $cName = $r['company'] ?? '';
            $k1 = $cName.'___'.$bName;
            $r['posting_date'] = $accountDates[$k1] ?? ($accountDates[$bName] ?? ($r['year_start_date'] ?? ($fromDate ?? date('Y-m-d'))));

            if (! isset($r['total']) || ! is_numeric($r['total'])) {
                $rowTotal = 0;
                foreach ($r as $k => $v) {
                    if (is_numeric($v) && ! in_array($k, ['indent', 'is_group', 'include_in_gross', 'acc_number', 'opening_balance'])) {
                        $rowTotal = (float) $v;
                    }
                }
                $r['total'] = $rowTotal;
            }

            $r['claim_expense_type'] = $this->resolveClaimExpenseType(
                $r['claim_expense_type'] ?? '',
                $bName,
                $r['root_type'] ?? ($accountsMapDetails[$bName]['root_type'] ?? ''),
                $r['account_type'] ?? ($accountsMapDetails[$bName]['account_type'] ?? ''),
                (float) ($r['total'] ?? 0),
                0,
                $dbFinancialItems
            );
        }
        unset($r);

        // Define report columns with posting_date always, and company when includeChildren
        $finalColumns = [];

        $finalColumns[] = [
            'fieldname' => 'posting_date',
            'label' => 'تاريخ العملية',
            'fieldtype' => 'Date',
            'width' => 110,
        ];

        if ($includeChildren) {
            $finalColumns[] = [
                'fieldname' => 'company',
                'label' => 'الجهة',
                'fieldtype' => 'Data',
                'width' => 200,
            ];
        }

        $finalColumns[] = [
            'fieldname' => 'account',
            'label' => 'الحساب',
            'fieldtype' => 'Link',
            'width' => 280,
        ];

        $finalColumns[] = [
            'fieldname' => 'acc_number',
            'label' => 'رقم الحساب',
            'fieldtype' => 'Data',
            'width' => 100,
        ];

        $finalColumns[] = [
            'fieldname' => 'claim_expense_type',
            'label' => 'نوع النفقة المطالب بها (البند)',
            'fieldtype' => 'Data',
            'width' => 180,
        ];

        $finalColumns[] = [
            'fieldname' => 'total',
            'label' => 'المبلغ',
            'fieldtype' => 'Currency',
            'width' => 140,
        ];

        // Reconstruct report data with Strict Partitioning
        $reportData = [
            'success' => $success,
            'columns' => $finalColumns,
            'result' => array_values($mergedResult),
            'gl_result' => $generalGlResult, // General GL transactions ONLY
            'project_gl_result' => $projectGlResult, // Project GL transactions ONLY
        ];
        if (! $success) {
            $reportData['error'] = $lastError ?: 'حدث خطأ أثناء جلب البيانات من النظام.';
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
        $allProjectsMap = [];
        foreach ($companiesToFetch as $comp) {
            $compProjects = $this->erpService->getProjects($comp);
            foreach ($compProjects as $proj) {
                if (! isset($allProjectsMap[$proj['name']])) {
                    $allProjectsMap[$proj['name']] = $proj;
                }
            }
        }
        $projects = array_values($allProjectsMap);
        $currency = 'YER';
        $income = 0;
        $expense = 0;
        $profit = 0;

        if (isset($generalGlResult) && is_array($generalGlResult)) {
            foreach ($generalGlResult as $gl) {
                $debit = (float) ($gl['debit'] ?? 0);
                $credit = (float) ($gl['credit'] ?? 0);
                $rootType = strtolower($gl['root_type'] ?? '');
                $accountType = strtolower($gl['account_type'] ?? '');

                if ($rootType === 'income' || strpos($accountType, 'income') !== false) {
                    $income += ($credit - $debit);
                } elseif ($rootType === 'expense' || strpos($accountType, 'expense') !== false) {
                    $expense += ($debit - $credit);
                }
            }
            $profit = $income - $expense;
        }

        // Calculate Project Summary from Project GL transactions
        $projectIncome = 0;
        $projectExpense = 0;
        $projectProfit = 0;

        if (isset($projectGlResult) && is_array($projectGlResult)) {
            foreach ($projectGlResult as $gl) {
                $debit = (float) ($gl['debit'] ?? 0);
                $credit = (float) ($gl['credit'] ?? 0);
                $rootType = strtolower($gl['root_type'] ?? '');
                $accountType = strtolower($gl['account_type'] ?? '');

                if (empty($rootType) && empty($accountType)) {
                    $accName = trim($gl['account'] ?? '', "'");
                    $lowerAcc = strtolower($accName);
                    if (str_contains($lowerAcc, 'expense') || str_contains($accName, 'مصاريف') || str_contains($accName, 'نفقات') || str_contains($accName, 'إيجار') || str_contains($accName, 'ايجار')) {
                        $rootType = 'expense';
                    } elseif (str_contains($lowerAcc, 'income') || str_contains($accName, 'إيرادات') || str_contains($accName, 'ايرادات') || str_contains($accName, 'مبيعات')) {
                        $rootType = 'income';
                    }
                }

                if ($rootType === 'income' || strpos($accountType, 'income') !== false) {
                    $projectIncome += ($credit - $debit);
                } elseif ($rootType === 'expense' || strpos($accountType, 'expense') !== false) {
                    $projectExpense += ($debit - $credit);
                }
            }
            $projectProfit = $projectIncome - $projectExpense;
        }

        $totalIncome = $income + $projectIncome;
        $totalExpense = $expense + $projectExpense;
        $totalProfit = $totalIncome - $totalExpense;

        $summaryData = [
            [
                'value' => $totalIncome,
                'label' => 'إجمالي الإيرادات الكلية',
                'datatype' => 'Currency',
                'currency' => $currency,
                'indicator' => 'blue',
            ],
            [
                'value' => $totalExpense,
                'label' => 'إجمالي النفقات الكلية',
                'datatype' => 'Currency',
                'currency' => $currency,
                'indicator' => 'orange',
            ],
            [
                'value' => $totalProfit,
                'label' => 'صافي النتيجة الكلية',
                'datatype' => 'Currency',
                'currency' => $currency,
                'indicator' => $totalProfit >= 0 ? 'green' : 'red',
            ],
            [
                'value' => $projectIncome,
                'label' => 'إيرادات المشاريع (ERPNext)',
                'datatype' => 'Currency',
                'currency' => $currency,
                'indicator' => 'info',
            ],
            [
                'value' => $projectExpense,
                'label' => 'نفقات المشاريع (ERPNext)',
                'datatype' => 'Currency',
                'currency' => $currency,
                'indicator' => 'danger',
            ],
        ];

        $itemsBreakdown = [];
        $totalItemsExpenses = 0.0;
        $allGlEntries = array_merge($generalGlResult, $projectGlResult);

        if (! empty($allGlEntries)) {
            foreach ($allGlEntries as $gl) {
                $debit = (float) ($gl['debit'] ?? 0);
                $credit = (float) ($gl['credit'] ?? 0);
                $rootType = strtolower($gl['root_type'] ?? '');
                $accountType = strtolower($gl['account_type'] ?? '');

                if ($rootType === 'expense' || strpos($accountType, 'expense') !== false) {
                    $amt = $debit - $credit;
                    $claimType = trim($gl['claim_expense_type'] ?? '');
                    if (empty($claimType) || $claimType === 'No Expense Type found' || $claimType === 'غير محدد' || str_starts_with($claimType, '-')) {
                        $claimType = $this->resolveClaimExpenseType('', $gl['account'] ?? '', $rootType, $accountType, $debit, $credit, $dbFinancialItems);
                    }

                    if (! isset($itemsBreakdown[$claimType])) {
                        $itemsBreakdown[$claimType] = ['count' => 0, 'amount' => 0.0];
                    }
                    $itemsBreakdown[$claimType]['count']++;
                    $itemsBreakdown[$claimType]['amount'] += $amt;
                    $totalItemsExpenses += $amt;
                }
            }
        }

        // Build Executive Activity/Action Costs breakdown
        // Linked via erpnext_project_id to filter only projects relevant to selected company
        $erpnextProjectIds = array_column($projects, 'name'); // ERPNext project IDs in scope
        $erpnextProjectNames = array_column($projects, 'project_name');
        $activityActionCosts = [];
        $totalActivityBudget = 0.0;

        if (! empty($erpnextProjectIds) || ! empty($erpnextProjectNames)) {
            $dbProjects = Project::where(function ($query) use ($erpnextProjectIds, $erpnextProjectNames) {
                if (! empty($erpnextProjectIds)) {
                    $query->whereIn('erpnext_project_id', $erpnextProjectIds);
                }
                if (! empty($erpnextProjectNames)) {
                    $query->orWhereIn('project_name', $erpnextProjectNames);
                }
            })
                ->select(['id', 'project_name', 'erpnext_project_id'])
                ->get()
                ->keyBy('id');

            if ($dbProjects->isNotEmpty()) {
                $actionCosts = ExecutiveActionCost::whereIn('project_id', $dbProjects->keys())
                    ->with([
                        'activity' => fn ($q) => $q->withoutGlobalScopes(),
                        'action' => fn ($q) => $q->withoutGlobalScopes(),
                        'financialItem',
                    ])
                    ->get();

                foreach ($actionCosts as $costRow) {
                    $projId = $costRow->project_id;
                    $projName = $dbProjects[$projId]?->project_name ?? 'مشروع غير محدد';
                    $erpId = $dbProjects[$projId]?->erpnext_project_id ?? '';
                    $actName = $costRow->activity?->name ?? 'نشاط غير محدد';
                    $actionName = $costRow->action?->action ?? 'إجراء غير محدد';
                    $finItemName = $costRow->financialItem?->name ?? 'بند غير محدد';
                    $approvedBudget = (float) ($costRow->total ?: ($costRow->amount * $costRow->quantity));

                    $activityActionCosts[] = [
                        'date' => $costRow->created_at ? $costRow->created_at->format('Y-m-d') : ($fromDate ?? date('Y-m-d')),
                        'project_name' => $projName,
                        'erpnext_project_id' => $erpId,
                        'activity_name' => $actName,
                        'action_name' => $actionName,
                        'financial_item_name' => $finItemName,
                        'quantity' => (float) $costRow->quantity,
                        'unit_cost' => (float) $costRow->amount,
                        'approved_budget' => $approvedBudget,
                    ];
                    $totalActivityBudget += $approvedBudget;
                }
            }
        }

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
            'itemsBreakdown' => $itemsBreakdown,
            'totalItemsExpenses' => $totalItemsExpenses,
            'activityActionCosts' => $activityActionCosts,
            'totalActivityBudget' => $totalActivityBudget,
        ]);
    }

    public function printGeneral(Request $request)
    {
        $request->merge(['export' => 'print_general']);
        $response = $this->index($request);
        $data = $response->getData();

        return view('reports.print_general_financial', [
            'gl_result' => $data['reportData']['gl_result'] ?? [],
            'company' => $request->input('company', 'الكل'),
            'filters' => $data['filters'] ?? [],
        ]);
    }

    public function exportGeneralExcel(Request $request)
    {
        $response = $this->index($request);
        $data = $response->getData();
        $glResult = $data['reportData']['gl_result'] ?? [];

        $includeChildren = $request->boolean('include_children', false);

        $headings = ['تاريخ العملية'];
        if ($includeChildren) {
            $headings[] = 'الجهة';
        }
        $headings = array_merge($headings, ['رقم السند', 'الحساب', 'بند النفقة', 'مدين', 'دائن', 'المشروع', 'مركز التكلفة', 'البيان']);

        $fileName = 'General_Financial_Ledger_'.date('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($headings, $glResult, $includeChildren) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headings);

            foreach ($glResult as $row) {
                $line = [$row['posting_date'] ?? ''];
                if ($includeChildren) {
                    $line[] = $row['company'] ?? '';
                }
                $line[] = $row['voucher_no'] ?? '';
                $line[] = $row['account'] ?? '';
                $line[] = $row['claim_expense_type'] ?? '';
                $line[] = $row['debit'] ?? '0';
                $line[] = $row['credit'] ?? '0';
                $line[] = $row['project'] ?? '';
                $line[] = $row['cost_center'] ?? '';
                $line[] = $row['remarks'] ?? '';

                fputcsv($output, $line);
            }
            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    public function printItems(Request $request)
    {
        $request->merge(['export' => 'print_items']);
        $response = $this->index($request);
        $data = $response->getData();

        return view('reports.print_financial_items', [
            'itemsBreakdown' => $data['itemsBreakdown'] ?? [],
            'totalExpenses' => $data['totalItemsExpenses'] ?? 0,
            'company' => $request->input('company', 'الكل'),
            'filters' => $data['filters'] ?? [],
        ]);
    }

    public function exportItemsExcel(Request $request)
    {
        $response = $this->index($request);
        $data = $response->getData();
        $itemsBreakdown = $data['itemsBreakdown'] ?? [];

        $headings = ['البند المالي', 'عدد حركات الصرف', 'المبلغ الإجمالي (ر.ي)'];
        $fileName = 'Financial_Items_Summary_'.date('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($headings, $itemsBreakdown) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $headings);

            foreach ($itemsBreakdown as $itemName => $info) {
                fputcsv($output, [
                    $itemName,
                    $info['count'],
                    $info['amount'],
                ]);
            }
            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    /**
     * Resolve claim expense type intelligently from ERPNext or local Financial Items.
     */
    protected function resolveClaimExpenseType(?string $rawClaimType, string $accountName, string $rootType = '', string $accountType = '', float $debit = 0, float $credit = 0, array $dbFinancialItems = []): string
    {
        $raw = trim($rawClaimType ?? '');
        if (! empty($raw) && $raw !== 'No Expense Type found' && $raw !== 'غير محدد') {
            return $raw;
        }

        $baseAcc = explode(' - ', trim($accountName, "'"))[0] ?? '';

        // 1. Try fuzzy match with DB Financial Items
        foreach ($dbFinancialItems as $fName) {
            if (! empty($fName) && (str_contains($baseAcc, $fName) || str_contains($fName, $baseAcc))) {
                return $fName;
            }
        }

        $lowerRoot = strtolower($rootType);
        $lowerAccType = strtolower($accountType);

        // 2. If it's an expense account
        if ($lowerRoot === 'expense' || str_contains($lowerAccType, 'expense') || str_contains($baseAcc, 'نفقات') || str_contains($baseAcc, 'مصاريف') || str_contains($baseAcc, 'ايجار') || str_contains($baseAcc, 'إيجار') || str_contains($baseAcc, 'صيانة') || str_contains($baseAcc, 'وقود') || str_contains($baseAcc, 'كهرباء') || str_contains($baseAcc, 'ماء') || str_contains($baseAcc, 'ضيافة') || str_contains($baseAcc, 'أجور') || str_contains($baseAcc, 'اجور') || str_contains($baseAcc, 'رواتب') || str_contains($baseAcc, 'قرطاسية') || str_contains($baseAcc, 'طباعة') || str_contains($baseAcc, 'سفر') || str_contains($baseAcc, 'هاتف') || str_contains($baseAcc, 'اتصالات')) {
            return ! empty($baseAcc) ? $baseAcc : 'مصروف غير محدد البند';
        }

        // 3. Cash / Bank accounts
        if (str_contains($baseAcc, 'نقد') || str_contains($baseAcc, 'صندوق') || str_contains($baseAcc, 'بنك') || str_contains($lowerAccType, 'bank') || str_contains($lowerAccType, 'cash')) {
            return '- (حساب نقدية / تسوية)';
        }

        // 4. Income / Revenue
        if ($lowerRoot === 'income' || str_contains($lowerAccType, 'income') || str_contains($baseAcc, 'مبيعات') || str_contains($baseAcc, 'إيرادات') || str_contains($baseAcc, 'ايرادات')) {
            return '- (حساب إيرادات)';
        }

        // 5. Equity / Capital / Assets
        if ($lowerRoot === 'equity' || $lowerRoot === 'liability' || $lowerRoot === 'asset' || str_contains($baseAcc, 'رأس المال') || str_contains($baseAcc, 'أصول') || str_contains($baseAcc, 'اصول') || str_contains($baseAcc, 'خصوم') || str_contains(strtolower($baseAcc), 'furniture')) {
            return '- (حساب ميزانية / أصول)';
        }

        return ! empty($baseAcc) ? $baseAcc : 'غير محدد';
    }
}
