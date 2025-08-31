<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCuenta extends Model
{
    use HasFactory;

    protected $table = 'tipos_cuentas';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // Relación con cuentas
    public function cuentas()
    {
        return $this->hasMany(Cuenta::class, 'tipo_cuenta_id');
    }
}
