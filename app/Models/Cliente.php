<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    /** Tabla explícita (por claridad) */
    protected $table = 'clientes';

    /** Campos asignables en masa */
    protected $fillable = [
        'nombre_cliente',
        'categoria_id',
        'nit',
        'fecha_nacimiento',
        'telefono',
        'email',
        'asesor_id',
    ];

    /** Casts (se mantiene el formateo a Y-m-d que ya usabas) */
    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
    ];

    /* ===================== Relaciones ===================== */

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

    /* ===================== Mutators ===================== */

    public function setNitAttribute($value): void
    {
        // Soporta nulos y acentos; quita espacios
        $this->attributes['nit'] = isset($value)
            ? mb_strtoupper(trim($value), 'UTF-8')
            : null;
    }

    /* ===================== Scopes ===================== */

    /**
     * Búsqueda rápida por nombre/NIT/teléfono/email.
     *
     * Uso: Cliente::search($term)->get();
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function (Builder $q) use ($like) {
            $q->where('nombre_cliente', 'like', $like)
              ->orWhere('nit', 'like', $like)
              ->orWhere('telefono', 'like', $like)
              ->orWhere('email', 'like', $like);
        });
    }

    /**
     * Limita a los clientes de un asesor específico.
     *
     * Uso: Cliente::byAsesor($userId)->get();
     */
    public function scopeByAsesor(Builder $query, int $asesorId): Builder
    {
        return $query->where('asesor_id', $asesorId);
    }
}
