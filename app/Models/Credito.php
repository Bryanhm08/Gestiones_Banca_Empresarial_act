<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Credito extends Model
{
    use HasFactory;

    /** Tabla explícita */
    protected $table = 'creditos';

    /** Campos asignables */
    protected $fillable = [
        'cliente_id',
        'tipo_credito_id',
        'garantia_id',
        'asesor_id',
        'monto',
        'plazo',
        // fecha_concesion y fecha_vencimiento se mantienen en BD para históricos,
        // pero ya no se asignan masivamente en el pipeline.
    ];

    /** Casts útiles (se conservan para registros históricos) */
    protected $casts = [
        'fecha_concesion'   => 'date',
        'fecha_vencimiento' => 'date',
        'monto'             => 'decimal:2',
        'plazo'             => 'integer',
    ];

    /* ===================== Relaciones ===================== */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function tipoCredito()
    {
        return $this->belongsTo(TipoCredito::class, 'tipo_credito_id');
    }

    public function garantia()
    {
        return $this->belongsTo(Garantia::class, 'garantia_id');
    }

    /**
     * Asesor (usuario dueño/gestor del crédito)
     */
    public function asesor()
    {
        return $this->belongsTo(\App\Models\User::class, 'asesor_id');
    }

    public function amortizaciones()
    {
        return $this->hasMany(Amortizacion::class, 'credito_id');
    }

    public function estados()
    {
        return $this->hasMany(CreditoEstado::class, 'credito_id');
    }

    /** Último estado (Laravel 9+) */
    public function ultimoEstado()
    {
        return $this->hasOne(CreditoEstado::class, 'credito_id')->latestOfMany();
    }

    /* ===================== Scopes ===================== */

    /** Filtrar por asesor */
    public function scopeByAsesor(Builder $query, int $asesorId): Builder
    {
        return $query->where('asesor_id', $asesorId);
    }
}
