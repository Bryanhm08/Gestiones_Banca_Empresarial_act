<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre_cliente',
        'categoria_id',
        'nit',
        'fecha_nacimiento',
        'telefono',
        'email',
        'asesor_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asesor_id');
    }

    public function creditos(): HasMany
    {
        return $this->hasMany(Credito::class, 'cliente_id');
    }

    public function cuentas(): HasMany
    {
        return $this->hasMany(Cuenta::class, 'cliente_id');
    }
    public function setNitAttribute($value): void
    {
        $this->attributes['nit'] = strtoupper(trim($value));
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!$term)
            return $query;

        $like = '%' . trim($term) . '%';

        return $query->where(function ($q) use ($like) {
            $q->where('nombre_cliente', 'like', $like)
                ->orWhere('nit', 'like', $like)
                ->orWhere('telefono', 'like', $like)
                ->orWhere('email', 'like', $like);
        });
    }
    public function scopeByAsesor($query, int $asesorId)
    {
        return $query->where('asesor_id', $asesorId);
    }
}
