<?php
namespace App\Http\Controllers;
use Inertia\Inertia;

class CreditoController extends Controller
{
    public function index()
    {
        return Inertia::render('Creditos/Index');
    }
}
