<?php
namespace App\Models\Evaluacion;

use Illuminate\Database\Eloquent\Model;



use App\Models\Postulacion\PostulacionDocumento;

class EvaluacionDocumento extends Model
{
    protected $table = 'evaluacion_documentos';
    
    protected $fillable = [
        'evaluacion_id',
        'postulacion_documento_id',
        'estado',
        'puntaje',
        'comentarios'
    ];

    protected $casts = [
        'puntaje' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'estado' => 'pendiente',
        'puntaje' => 0
    ];

    // Relaciones
    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function documento()
    {
        return $this->belongsTo(PostulacionDocumento::class, 'postulacion_documento_id');
    }

    // Métodos auxiliares
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

    public function tienePuntaje()
    {
        return $this->puntaje > 0;
    }
    
    /*
    protected $fillable = [
        'id',
        'evaluacion_id',
        'postulacion_documento_id',
        'estado',
        'puntaje',
        'comentarios'
    ];

    protected $casts = [
        'puntaje' => 'decimal:2',
    ];

    public function evaluacion()
    {
        return $this->belongsTo(Evaluacion::class);
    }

    public function documento()
    {
        return $this->belongsTo(PostulacionDocumento::class, 'postulacion_documento_id');
    }*/
}