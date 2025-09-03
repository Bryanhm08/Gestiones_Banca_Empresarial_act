<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoCredito extends Model
{
    protected $table = 'estados_credito';
    protected $fillable = ['nombre'];
}
