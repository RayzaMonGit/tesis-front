<?php

namespace App\Models\Convocatorias;

use Illuminate\Database\Eloquent\Model;

class ConvocatoriaAudit extends Model
{
    protected $table = 'convocatoria_audits';

    protected $fillable = [
        'convocatoria_id',
        'user_id',
        'accion',
        'cambios',
    ];

    protected $casts = [
        'cambios' => 'array',
    ];

    public function convocatoria()
    {
        return $this->belongsTo(\App\Models\Convocatorias\Convocatoria::class, 'convocatoria_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}