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

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $hasCreditReports  = false;
        $hasAccountsReport = false;

        if ($user && ($user->relationLoaded('area') ? $user->area : $user?->area)) {
            $area = $user->area()->with('areaModules')->first();
            $labels = $area?->areaModules()->pluck('modulo')->toArray() ?? [];
            $hasCreditReports  = in_array('Reportes de créditos', $labels, true);
            $hasAccountsReport = in_array('Reportería de cuentas', $labels, true);
        }

        // ✅ Asesores también pueden usar Clientes/Cuentas
        $isAdmin    = (bool) ($user->admin ?? false);
        $isAsesor   = (bool) ($user->asesor ?? false);
        $isOperador = (bool) ($user->operador ?? false);

        $canClientes = $user ? ($isAdmin || $isAsesor || $isOperador) : false;

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user ? [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'asesor'=> $isAsesor,
                    'admin' => $isAdmin,
                ] : null,
            ],
            'abilities' => [
                // Módulo Clientes/Cuentas
                'canClientes'        => $canClientes,
                'canViewClientes'    => $canClientes,
                'canCreateCliente'   => $canClientes,
                'canViewCuentas'     => $canClientes,
                'canCreateCuenta'    => $canClientes,

                // Créditos
                'canCreateCredito'   => true,

                // Reportes (por módulo o admin)
                'mod_credit_reports'  => $hasCreditReports  || $isAdmin,
                'mod_accounts_report' => $hasAccountsReport || $isAdmin,
            ],
        ]);
    }
}
