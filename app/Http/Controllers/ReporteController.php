<?php
namespace App\Http\Controllers;
use Inertia\Inertia;

class ReporteController extends Controller
{
    public function index()
    {
        // Podés pasar flags para render condicional en el hub:
        return Inertia::render('Reportes/Index', [
            // 'canCreditReports' => auth()->user()->canAccessModule('credit_reports'),
            // 'canAccountsReporting' => auth()->user()->canAccessModule('accounts_reporting'),
        ]);
    }
}
