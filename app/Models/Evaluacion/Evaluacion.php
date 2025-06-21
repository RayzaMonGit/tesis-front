<?php
namespace App\Models\Evaluacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Postulacion\Postulacion;
use App\Models\User;

class Evaluacion extends Model
{
    use SoftDeletes;
    
    protected $table = 'evaluaciones';
    
    protected $fillable = [
        'postulacion_id',
        'evaluador_id',
        'puntaje_total',
        'comentarios_generales',
        'estado'
    ];

    protected $casts = [
        'puntaje_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $attributes = [
        'estado' => 'borrador',
        'puntaje_total' => 0
    ];

    // Relaciones
    public function postulacion()
    {
        return $this->belongsTo(Postulacion::class);
    }

    public function evaluador()
    {
        return $this->belongsTo(User::class, 'evaluador_id');
    }

    public function requisitos()
{
    return $this->hasMany(\App\Models\Evaluacion\EvaluacionRequisito::class, 'evaluacion_id');
}

    public function documentos()
    {
        return $this->hasMany(EvaluacionDocumento::class);
    }

    // Scopes
    public function scopeFinalizadas($query)
    {
        return $query->where('estado', 'finalizada');
    }

    public function scopeBorradores($query)
    {
        return $query->where('estado', 'borrador');
    }

    public function scopeDelEvaluador($query, $evaluadorId)
    {
        return $query->where('evaluador_id', $evaluadorId);
    }

    // Métodos auxiliares
    public function esBorrador()
    {
        return $this->estado === 'borrador';
    }

    public function estaFinalizada()
    {
        return $this->estado === 'finalizada';
    }
    /*
    use SoftDeletes;
    protected $table = 'evaluaciones';
    protected $fillable = [
        'postulacion_id',
        'evaluador_id',
        'puntaje_total',
        'comentarios',
        'estado'
    ];

    protected $casts = [
        'puntaje_total' => 'decimal:2',
    ];

    public function postulacion()
    {
        return $this->belongsTo(Postulacion::class);
    }

    public function evaluador()
    {
        return $this->belongsTo(User::class, 'evaluador_id');
    }

    public function documentos()
    {
        return $this->hasMany(EvaluacionDocumento::class);
    }*/
}