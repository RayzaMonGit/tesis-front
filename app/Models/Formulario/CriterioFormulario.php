<?php
namespace App\Models\Formulario;

use Illuminate\Database\Eloquent\Model;

class CriterioFormulario extends Model
{
    protected $table = 'criterios_formulario';

    protected $fillable = [
        'seccion_id',
        'nombre',
        //'puntaje',
        //'max_items',
        //'orden',
        'puntaje_por_item',
        'puntaje_maximo',
    ];

    public function seccion()
    {
        return $this->belongsTo(SeccionFormulario::class, 'seccion_id');
    }
}
