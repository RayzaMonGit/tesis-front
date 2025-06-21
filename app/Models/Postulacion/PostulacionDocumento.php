<?php

namespace App\Models\Postulacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostulacionDocumento extends Model
{
    use HasFactory;

    protected $table = 'postulacion_documentos';

    protected $fillable = [
        'postulacion_id',
        'requisito_id',
        'nombre',
        'archivo',
        'es_requisito_ley',
        'es_requisito_personalizado',
         'seccion_id',  
    'criterio_id', 
        
    ];
/*
    protected $casts = [
        'es_requisito_ley' => 'boolean',
        'es_requisito_personalizado' => 'boolean',
    ];*/

    // Relación con Postulacion
    public function postulacion()
    {
        return $this->belongsTo(\App\Models\Postulacion\Postulacion::class);
    }

    // Relación con Seccion
    public function seccion()
    {
        return $this->belongsTo(\App\Models\Formulario\SeccionFormulario::class, 'seccion_id');
    }

    // Relación con Criterio (si la usas)
    public function criterio()
    {
        return $this->belongsTo(\App\Models\Formulario\CriterioFormulario::class, 'criterio_id');
    }

    // Relación con Requisito (si la usas)
    public function requisito()
    {
        return $this->belongsTo(\App\Models\Convocatorias\Requisitos::class, 'requisito_id');
    }
}