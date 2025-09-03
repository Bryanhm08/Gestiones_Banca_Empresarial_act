<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoCredito extends Model
{
    protected $table = 'tipos_credito';
    protected $fillable = ['nombre'];
}
