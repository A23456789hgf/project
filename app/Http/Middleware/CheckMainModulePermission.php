<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMainModulePermission
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (! $user) {
            return $next($request);
        }

        // If the user is a super admin, they can access everything.
        // Adapt this based on your super admin checking logic.
        if ($user->hasRole('super-admin') || $user->hasRole('مدير النظام')) {
            return $next($request);
        }

        $routeName = $request->route() ? $request->route()->getName() : '';

        $routeMap = [
            'dashboard' => 'main_modules.dashboard',
            'projects.' => 'main_modules.projects',
            'execution.' => 'main_modules.projects',
            'tasks.' => 'main_modules.tasks',
            'requests_descend.' => 'main_modules.requests-descend',
            'project-referrals.' => 'main_modules.correspondence',
            'referrals.' => 'main_modules.correspondence',
            'correspondence.' => 'main_modules.correspondence',
            'memoirs.' => 'main_modules.correspondence',
            'plans.' => 'main_modules.planning',
            'projects.reports.' => 'main_modules.reports',
            'projects.empowerment' => 'main_modules.empowerment',
            'value-chains.' => 'main_modules.value-chains',
            'value-chain-members.' => 'main_modules.value-chains',
            'global-financings.' => 'main_modules.value-chains',
            'chain_plans.' => 'main_modules.value-chains',
            'programs.' => 'main_modules.encoding',
            'domains.' => 'main_modules.encoding',
            'subdomains.' => 'main_modules.encoding',
            'interventions.' => 'main_modules.encoding',
            'governorates.' => 'main_modules.encoding',
            'directorates.' => 'main_modules.encoding',
            'sub-areas.' => 'main_modules.encoding',
            'villages.' => 'main_modules.encoding',
            'financial-items.' => 'main_modules.encoding',
            'funding-sources.' => 'main_modules.encoding',
            'financing-types.' => 'main_modules.encoding',
            'value-chain-financing-types.' => 'main_modules.encoding',
            'formfinancing.' => 'main_modules.encoding',
            'subfinancing-forms.' => 'main_modules.encoding',
            'authorities.' => 'main_modules.encoding',
            'main-routers.' => 'main_modules.encoding',
            'sub-routers.' => 'main_modules.encoding',
            'priorities.' => 'main_modules.encoding',
            'units.' => 'main_modules.encoding',
            'beneficiary-groups.' => 'main_modules.encoding',
            'configuration.sms.' => 'main_modules.encoding',
            'signatures.' => 'main_modules.encoding',
            'internal-entities.' => 'main_modules.encoding',
            'entity-officers.' => 'main_modules.encoding',
            'entity-authorities.' => 'main_modules.encoding',
            'users.' => 'main_modules.users',
            'roles.' => 'main_modules.users',
            'roles-permissions.' => 'main_modules.users',
            'admin.audit-logs.' => 'main_modules.users',
        ];

        foreach ($routeMap as $prefix => $permission) {
            // Note: Some prefixes are exact matches like 'dashboard' or 'projects.empowerment'
            if ($routeName === $prefix || str_starts_with($routeName, $prefix)) {
                if (! $user->can($permission)) {
                    abort(403, 'ليس لديك الصلاحية للوصول إلى هذه الوحدة الرئيسية.');
                }
                break;
            }
        }

        return $next($request);
    }
}
