<?php

namespace App\Models\Convocatorias;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
//use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Convocatorias\Requisitos;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Convocatorias\RequisitosLey;

class RequisitosLey extends Model
{
    
    //use SoftDeletes;
    use HasFactory;

    protected $table = 'requisitos_ley';

    protected $fillable = ['descripcion', 'tipo'];

    public $timestamps = false;

   // RequisitosLey.php
public function convocatorias()
{
    return $this->belongsToMany(Convocatoria::class, 'convocatoria_requisitos_ley', 'requisitos_ley_id', 'id_convocatoria');
}


}
