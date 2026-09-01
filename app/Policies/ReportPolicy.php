<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the reports dashboard.
     */
    public function view(User $user)
    {
        return $user->hasPermission('reports.view');
    }

    /**
     * Determine whether the user can view implementation reports.
     */
    public function viewImplementation(User $user)
    {
        return $user->hasPermission('reports.implementation.view');
    }

    /**
     * Determine whether the user can view quality reports.
     */
    public function viewQuality(User $user)
    {
        return $user->hasPermission('reports.quality.view');
    }

    /**
     * Determine whether the user can view financial reports.
     */
    public function viewFinancial(User $user)
    {
        return $user->hasPermission('reports.financial.view');
    }

    /**
     * Determine whether the user can view progress reports.
     */
    public function viewProgress(User $user)
    {
        return $user->hasPermission('reports.progress.view');
    }

    /**
     * Determine whether the user can view status reports.
     */
    public function viewStatus(User $user)
    {
        return $user->hasPermission('reports.status.view');
    }

    /**
     * Determine whether the user can view the overview.
     */
    public function viewOverview(User $user)
    {
        return $user->hasPermission('reports.overview.view');
    }

    /**
     * Determine whether the user can print/export reports.
     */
    public function print(User $user)
    {
        return $user->hasPermission('reports.print');
    }

    /**
     * Determine whether the user can view ERPNext financial report.
     */
    public function viewFinancialErpnext(User $user)
    {
        return $user->hasPermission('reports.financial_erpnext.view');
    }

    /**
     * Determine whether the user can view P&L expense summary report.
     */
    public function viewPlExpenseSummary(User $user)
    {
        return $user->hasPermission('reports.pl_expense_summary.view');
    }

    /**
     * Determine whether the user can view profit and loss report.
     */
    public function viewProfitAndLoss(User $user)
    {
        return $user->hasPermission('reports.profit_and_loss.view');
    }

    /**
     * Determine whether the user can view official summary report.
     */
    public function viewOfficialSummary(User $user)
    {
        return $user->hasPermission('reports.official_summary.view') || $user->hasPermission('reports.view');
    }

    /**
     * Determine whether the user can view stakeholders report.
     */
    public function viewStakeholders(User $user)
    {
        return $user->hasPermission('reports.stakeholders.view');
    }

    /**
     * Determine whether the user can view permissions report.
     */
    public function viewPermissions(User $user)
    {
        return $user->hasPermission('reports.permissions.view') || $user->hasPermission('permissions.report');
    }
}
