<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $hasCreditReports = false;
        $hasAccountsReport = false;

        if ($user && ($user->relationLoaded('area') ? $user->area : $user?->area)) {
            $area = $user->area()->with('areaModules')->first();
            $labels = $area?->areaModules()->pluck('modulo')->toArray() ?? [];
            $hasCreditReports = in_array('Reportes de créditos', $labels, true);
            $hasAccountsReport = in_array('Reportería de cuentas', $labels, true);
        }

        $canClientes = $user ? ($user->admin || !$user->asesor) : false;

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'asesor' => (bool) $user->asesor,
                    'admin' => (bool) $user->admin,
                ] : null,
            ],
            'abilities' => [
                'canClientes' => $canClientes,
                'mod_credit_reports' => $hasCreditReports || ($user?->admin ?? false),
                'mod_accounts_report' => $hasAccountsReport || ($user?->admin ?? false),
                'canViewClientes' => $canClientes,
                'canCreateCliente' => $canClientes,
                'canViewCuentas' => $canClientes,
                'canCreateCuenta' => $canClientes,
                'canCreateCredito' => true,
            ],
        ]);
    }
}
