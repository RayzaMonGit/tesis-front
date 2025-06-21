<?php
namespace App\Models\Formulario;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeccionFormulario extends Model
{
    use HasFactory;

    protected $table = 'secciones_formulario';

    protected $fillable = [
        'formulario_id',
        'titulo',
        'puntaje_max',
        //'orden',
    ];

    public function formulario()
    {
        return $this->belongsTo(FormularioEvaluacion::class, 'formulario_id');
    }

    public function criterios()
    {
        return $this->hasMany(CriterioFormulario::class, 'seccion_id');
    }
}
