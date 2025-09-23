<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstadoCredito extends Model
{
    protected $table = 'estados_credito';

    protected $fillable = ['nombre', 'orden'];

    protected $casts = [
        'orden' => 'integer',
    ];

    /**
     * Solo etapas vigentes (nuevas), es decir, aquellas que tienen 'orden' asignado.
     */
    public function scopeVigentes($query)
    {
        return $query->whereNotNull('orden');
    }

    public function creditoEstados(): HasMany
    {
        return $this->hasMany(CreditoEstado::class, 'estado_id');
    }
}
