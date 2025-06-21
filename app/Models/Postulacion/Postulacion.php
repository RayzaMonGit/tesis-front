<?php

namespace App\Models\Postulacion;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Postulante\Postulante; // Asegúrate de importar el namespace correcto
use App\Models\Convocatorias\Convocatoria;
class Postulacion extends Model
{
    protected $table = 'postulaciones';
    use HasFactory;

    protected $fillable = [
        'postulante_id',
        'convocatoria_id',
        'estado',
        'nota_preliminar',
    ];

    public function postulante()
    {
        return $this->belongsTo(Postulante::class);
    }

    public function convocatoria()
    {
        return $this->belongsTo(Convocatoria::class);
    }

    public function documentos()
    {
        return $this->hasMany(PostulacionDocumento::class);
    }

    public function evaluaciones()
    {
       return $this->hasMany('App\Models\Evaluacion\Evaluacion');
    }
    public function documentosFormulario()
{
    return $this->hasMany(PostulacionDocumento::class)
                ->whereNotNull('seccion_id')
                ->whereNotNull('criterio_id');
}
    
}