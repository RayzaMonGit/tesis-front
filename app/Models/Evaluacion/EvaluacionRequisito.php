<?php
namespace App\Models\Evaluacion;

use Illuminate\Database\Eloquent\Model;



use App\Models\Convocatorias\Requisitos;
use App\Models\Convocatorias\RequisitosLey;
use App\Models\Postulacion\PostulacionDocumento;

class EvaluacionRequisito extends Model
{
    
    protected $table = 'evaluacion_requisitos';
    
    protected $fillable = [
       'evaluacion_id',
    'requisito_id',
    'requisito_ley_id',
    'estado',
    'comentarios',
    'es_requisito_ley',
    'postulacion_documento_id'
    ];

    protected $casts = [
        'es_requisito_ley' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'estado' => 'pendiente'
    ];

    // Relaciones
    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }

    

    public function requisitoPersonalizado()
    {
        return $this->belongsTo(Requisito::class, 'requisito_id');
    }

    public function documento()
    {
        return $this->belongsTo(PostulacionDocumento::class, 'postulacion_documento_id');
    }

    // Métodos auxiliares
    public function getRequisitoAttribute()
    {
        return $this->es_requisito_ley 
            ? $this->requisitoLey 
            : $this->requisitoPersonalizado;
    }

    public function estaAprobado()
    {
        return $this->estado === 'aprobado';
    }

    public function estaRechazado()
    {
        return $this->estado === 'rechazado';
    }

    public function estaPendiente()
    {
        return $this->estado === 'pendiente';
    }
    


public function requisito()
{
    return $this->belongsTo(\App\Models\Convocatorias\Requisitos::class, 'requisito_id');
}
public function requisitoLey()
{
    return $this->belongsTo(\App\Models\Convocatorias\RequisitosLey::class, 'requisito_ley_id');
}
    /*
    protected $table = 'evaluacion_requisitos';
    protected $fillable = [
    'evaluacion_id',
    'requisito_id',
    'estado',
    'comentarios',
    'es_requisito_ley',
    'postulacion_documento_id'
];
    protected $casts = [
        'es_requisito_ley' => 'boolean',
    ];
public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function requisito()
    {
        return $this->belongsTo(Requisito::class);
    }*/
}

