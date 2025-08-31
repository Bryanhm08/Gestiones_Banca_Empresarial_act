<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $table = 'cuentas';

    protected $fillable = [
        'cliente_id',
        'tipo_cuenta_id',
        'asesor_id',
        'fecha_apertura',
    ];

    protected $casts = [
        'fecha_apertura' => 'date',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tipo()
    {
        return $this->belongsTo(TipoCuenta::class, 'tipo_cuenta_id');
    }

    public function asesor()
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }
}
