<?php

namespace App\Models\Convocatorias;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Convocatorias\RequisitosLey;

class Requisitos extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'requisitos';
    protected $fillable = [
        'id',
        'id_convocatoria',
        'descripcion',
        'tipo',
        'id_requisito_ley'
    ];

    public function convocatoria() {
        //return $this->belongsTo(Convocatoria::class, 'id_convocatoria', 'id');
        return $this->belongsTo(Convocatoria::class, 'id_convocatoria');
    }
    public function requisitosLey()
    {
        return $this->belongsTo(RequisitosLey::class, 'id_requisito_ley');
    }
}
