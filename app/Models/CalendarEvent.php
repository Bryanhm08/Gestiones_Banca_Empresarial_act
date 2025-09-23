<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'asesor_id', 'cliente_id', 'title', 'description', 'start_at', 'end_at', 'location'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    public function asesor()  { return $this->belongsTo(User::class, 'asesor_id'); }
    public function cliente() { return $this->belongsTo(Cliente::class, 'cliente_id'); }
}
