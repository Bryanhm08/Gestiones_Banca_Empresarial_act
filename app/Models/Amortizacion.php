<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Amortizacion extends Model
{
    use SoftDeletes;

    protected $table = 'amortizaciones';
    protected $fillable = ['credito_id', 'fecha_pago', 'status'];
    protected $casts = [
        'fecha_pago' => 'date:Y-m-d',
    ];

    public function credito(): BelongsTo
    {
        return $this->belongsTo(Credito::class, 'credito_id');
    }
}
