<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liberacion extends Model
{
    protected $table = 'liberaciones';

    protected $fillable = [
        'cliente_id',
        'nombre',
        'columns',
        'rows',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'columns' => 'array',
        'rows'    => 'array',
    ];

    public function cliente()
    {
        // Asegura el FK correcto
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
