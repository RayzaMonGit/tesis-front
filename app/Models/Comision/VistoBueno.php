<?php

namespace App\Models\Comision;

use Illuminate\Database\Eloquent\Model;

class VistoBueno extends Model
{
    protected $table = 'visto_bueno';

    protected $fillable = [
        'id_comision',
        'id_documento',
        'estado',
        'observacion',
    ];

    public function documento()
    {
        return $this->belongsTo(PostulacionDocumento::class, 'id_documento');
    }

    public function comision()
    {
        return $this->belongsTo(User::class, 'id_comision');
    }
}
