<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditoEstado extends Model
{
    protected $table = 'estado_credito';
    protected $fillable = ['credito_id', 'estado_id'];

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'credito_id');
    }

    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadoCredito::class, 'estado_id');
    }
}
