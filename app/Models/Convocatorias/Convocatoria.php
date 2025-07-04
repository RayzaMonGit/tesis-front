<?php

namespace App\Models\Convocatorias;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Convocatorias\Requisitos;
use App\Models\Convocatorias\RequisitosLey;
use App\Models\User;
use App\Models\Formulario\FormularioEvaluacion;

class Convocatoria extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'convocatorias';
    protected $fillable = [
        'id',
        'estado',
        'titulo',
        'descripcion',
        'area',
        'fecha_inicio',
        'fecha_fin',
        'plazas_disponibles',
        'sueldo_referencial',
        'documento',
        'formulario_id',
    ];
    // Requisitos personalizados
    public function requisitos()
    {
        return $this->hasMany(Requisitos::class, 'id_convocatoria'); // nombre real en la tabla
    }
    // Requisitos de ley    
    public function requisitosLey()
    {
        return $this->belongsToMany(RequisitosLey::class, 'convocatoria_requisito_ley');
    }
    public function evaluadores()
    {
        return $this->belongsToMany(User::class, 'convocatoria_comision', 'convocatoria_id', 'user_id');
    }

    public function formulario()
    {
        return $this->belongsTo(FormularioEvaluacion::class, 'formulario_id');
    }

    public function postulaciones()
    {
        return $this->hasMany('App\Models\Postulacion\Postulacion', 'convocatoria_id');
    }


    
}
