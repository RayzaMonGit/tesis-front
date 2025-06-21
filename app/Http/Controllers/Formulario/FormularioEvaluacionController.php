<?php
namespace App\Http\Controllers\Formulario;

use App\Http\Controllers\Controller;

use App\Models\Formulario\FormularioEvaluacion;
use App\Models\Formulario\SeccionFormulario;
use App\Models\Formulario\CriterioFormulario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\Formularios\FormularioEvaluacionResource;

class FormularioEvaluacionController extends Controller
{
    public function index()
    {
        return FormularioEvaluacion::with('secciones.criterios')->get();
    }


public function show($id)
{
    $formulario = FormularioEvaluacion::with('secciones.criterios')->findOrFail($id);

    return new FormularioEvaluacionResource($formulario);
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            
            //'resolucion' => 'nullable|string|max:255',
            'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'puntaje_total' => 'required|numeric',
        'secciones' => 'required|array|min:1',
        'secciones.*.titulo' => 'required|string|max:255',
        'secciones.*.puntaje_max' => 'required|numeric',
        'secciones.*.criterios' => 'required|array|min:1',
        'secciones.*.criterios.*.nombre' => 'required|string|max:255',
        'secciones.*.criterios.*.puntaje_por_item' => 'nullable|numeric',
        'secciones.*.criterios.*.puntaje_maximo' => 'nullable|numeric',
        //'secciones.*.criterios.*.max_items' => 'nullable|integer',
        ]);

        DB::beginTransaction();

        try {
            $formulario = FormularioEvaluacion::create($validated);

            foreach ($validated['secciones'] as $s) {
                $seccion = $formulario->secciones()->create([
                    'titulo' => $s['titulo'],
                    'puntaje_max' => $s['puntaje_max'],
                    //'orden' => $s['orden'],
                ]);

                foreach ($s['criterios'] as $c) {
                    $seccion->criterios()->create([
                        'nombre' => $c['nombre'],
                        'puntaje_por_item'=> $c['puntaje_por_item'],
                        //'puntaje' => $c['puntaje'],
                        'puntaje_maximo' => $c['puntaje_maximo'],
                        //'orden' => $c['orden'],
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Formulario creado correctamente.'], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al crear formulario.',
                'detail' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
            
        }
    }


public function update(Request $request, $id)
{
    $validated = $request->validate([
        'nombre' => 'string|max:255',
        'descripcion' => 'string',
        'puntaje_total' => 'required|numeric',
        'secciones' => 'array|min:1',
        'secciones.*.id' => 'integer|exists:secciones_formulario,id',
        'secciones.*.titulo' => 'string|max:255',
        'secciones.*.puntaje_max' => 'required|numeric',
        'secciones.*.criterios' => 'array|min:1',
        'secciones.*.criterios.*.id' => 'integer|exists:criterios_formulario,id',
        'secciones.*.criterios.*.nombre' => 'string|max:255',
        'secciones.*.criterios.*.puntaje_por_item' => 'required|numeric',
        'secciones.*.criterios.*.puntaje_maximo' => 'required|numeric',
    ]);
    
    DB::beginTransaction();
    try {
        $formulario = FormularioEvaluacion::findOrFail($id);
        $formulario->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'],
            'puntaje_total' => $validated['puntaje_total'],
        ]);

        $existingSectionIds = $formulario->secciones()->pluck('id')->toArray();
        $receivedSectionIds = [];
        
        foreach ($validated['secciones'] as $sectionData) {
            // Buscar o crear sección
            if (isset($sectionData['id'])) {
                $seccion = $formulario->secciones()->findOrFail($sectionData['id']);
                // Actualizar campos de la sección
                $seccion->update([
                    'titulo' => $sectionData['titulo'],
                    'puntaje_max' => $sectionData['puntaje_max'],
                ]);
            } else {
                $seccion = $formulario->secciones()->create([
                    'titulo' => $sectionData['titulo'],
                    'puntaje_max' => $sectionData['puntaje_max'],
                ]);
            }
            $receivedSectionIds[] = $seccion->id;

            $existingCriterionIds = $seccion->criterios()->pluck('id')->toArray();
            $receivedCriterionIds = [];

            foreach ($sectionData['criterios'] as $criterionData) {
                if (isset($criterionData['id'])) {
                    $criterio = $seccion->criterios()->findOrFail($criterionData['id']);
                    // Actualizar campos del criterio
                    $criterio->update([
                        'nombre' => $criterionData['nombre'],
                        'puntaje_por_item' => $criterionData['puntaje_por_item'],
                        'puntaje_maximo' => $criterionData['puntaje_maximo'],
                    ]);
                } else {
                    $criterio = $seccion->criterios()->create([
                        'nombre' => $criterionData['nombre'],
                        'puntaje_por_item' => $criterionData['puntaje_por_item'],
                        'puntaje_maximo' => $criterionData['puntaje_maximo'],
                    ]);
                }
                $receivedCriterionIds[] = $criterio->id;
            }

            // Eliminar criterios no incluidos
            $seccion->criterios()
                ->whereIn('id', array_diff($existingCriterionIds, $receivedCriterionIds))
                ->delete();
        }
        
        // Eliminar secciones no incluidas
        $formulario->secciones()
            ->whereIn('id', array_diff($existingSectionIds, $receivedSectionIds))
            ->delete();

        DB::commit();
        return response()->json(['message' => 'Formulario actualizado correctamente.']);
        
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'error' => 'Error al actualizar formulario.',
            'detail' => $e->getMessage(),
            'trace' => env('APP_DEBUG') ? $e->getTrace() : null
        ], 500);
    }
}

    public function destroy($id)
    {
        $formulario = FormularioEvaluacion::findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($formulario->secciones as $s) {
                $s->criterios()->delete();
            }
            $formulario->secciones()->delete();
            $formulario->delete();

            DB::commit();
            return response()->json(['message' => 'Formulario eliminado correctamente.']);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al eliminar formulario.', 'detail' => $e->getMessage()], 500);
        }
    }
}
