<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AreaModule extends Model
{
    protected $table = 'area_has_modules';
    protected $fillable = ['area_id', 'modulo'];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
