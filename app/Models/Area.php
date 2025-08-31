<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['nombre'];

    // Relación con usuarios
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Relación con módulos por área (tabla existente)
    public function areaModules(): HasMany
    {
        return $this->hasMany(AreaModule::class, 'area_id');
    }

    // Mapa slug → etiqueta que guardaste en BD (area_has_modules.modulo)
    public const MODULE_MAP = [
        'credit_reports'      => 'Reportes de créditos',
        'accounts_reporting'  => 'Reportería de cuentas',
    ];

    // ¿El área tiene el módulo X?
    public function hasModule(string $slug): bool
    {
        $label = self::MODULE_MAP[$slug] ?? $slug; // por si algún día pasas la etiqueta directa
        return $this->areaModules()->where('modulo', $label)->exists();
    }
}
