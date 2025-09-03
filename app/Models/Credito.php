<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Credito extends Model
{
    use SoftDeletes;

    protected $table = 'creditos';
    protected $guarded = [];

    protected $casts = [
        'fecha_concesion' => 'date:Y-m-d',
        'fecha_vencimiento' => 'date:Y-m-d',
        'monto' => 'decimal:2',
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function tipoCredito(): BelongsTo
    {
        return $this->belongsTo(TipoCredito::class, 'tipo_credito_id');
    }

    public function garantia(): BelongsTo
    {
        return $this->belongsTo(Garantia::class, 'garantia_id');
    }
}
