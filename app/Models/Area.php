<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $fillable = ['nombre'];
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function areaModules(): HasMany
    {
        return $this->hasMany(AreaModule::class, 'area_id');
    }
    public const MODULE_MAP = [
        'credit_reports'      => 'Reportes de créditos',
        'accounts_reporting'  => 'Reportería de cuentas',
    ];
    public function hasModule(string $slug): bool
    {
        $label = self::MODULE_MAP[$slug] ?? $slug;
        return $this->areaModules()->where('modulo', $label)->exists();
    }
}
