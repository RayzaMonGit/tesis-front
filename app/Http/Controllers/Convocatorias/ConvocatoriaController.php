<?php

namespace App\Http\Controllers\Convocatorias;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Convocatorias\Convocatoria;
use App\Models\Convocatorias\Requisitos;
use App\Models\Convocatorias\RequisitosLey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Convocatorias\ConvocatoriaCollection;
use App\Http\Resources\Convocatorias\ConvocatoriaResource;
use App\Http\Resources\Convocatorias\RequisitoResource;
use App\Http\Resources\Requisitos\RequisitosLeyResource;




class ConvocatoriaController extends Controller
{
    public function requisitos($id) {
        try {
            // Cargar la convocatoria con sus requisitos
            $convocatoria = Convocatoria::with('requisitos')->findOrFail($id);
    
            return response()->json([
                'requisitos' => $convocatoria->requisitos->toArray(), // Convertir a array
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            // Manejar errores
            return response()->json([
                'requisitos' => [],
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function asignarRequisitosLey(Request $request, $id)
{
    $convocatoria = Convocatoria::findOrFail($id);
    $convocatoria->requisitosLey()->sync($request->requisitos_ley_ids); // sincroniza

    return response()->json([
        'message' => 'Requisitos ley asignados correctamente',
        'requisitos' => $convocatoria->requisitosLey,
    ]);
}

public function todosRequisitos($id)
{
    $convocatoria = Convocatoria::with(['requisitos', 'requisitosLey', 'formulario'])->findOrFail($id);


    return response()->json([
        //'convocatoria' => new ConvocatoriaResource($convocatoria), // ✅ Agrega esto
        'requisitos_personalizados' => RequisitoResource::collection($convocatoria->requisitos),
        'requisitos_ley' => RequisitosLeyResource::collection($convocatoria->requisitosLey),
        'todos_requisitos_ley' => RequisitosLeyResource::collection(\App\Models\Convocatorias\RequisitosLey::all()),
        'formulario' => $convocatoria->formulario,

    ]);
}

public function updateRequisitos(Request $request, $id)
{
    DB::beginTransaction();

    try {
        $convocatoria = Convocatoria::findOrFail($id);

        $original = $convocatoria->getOriginal();
        $requisitosOriginales = $convocatoria->requisitos()->pluck('descripcion')->toArray();
        $idsRequisitosLeyOriginales = $convocatoria->requisitosLey()->pluck('requisitos_ley.id')->toArray();

        $convocatoria->update([
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'area' => $request->area,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'estado' => $request->estado,
            'plazas_disponibles' => $request->plazas_disponibles,
            'sueldo_referencial' => $request->sueldo_referencial,
            'formulario_id' => $request->formulario_id,
        ]);

        if ($request->hasFile('documento')) {
            if ($convocatoria->documento) {
                Storage::delete($convocatoria->documento);
            }
            $path = $request->file("documento")->store("convocatorias", "public");
            $convocatoria->update(['documento' => $path]);
        }

        if ($request->has('requisitos_ley_ids')) {
            $convocatoria->requisitosLey()->sync($request->requisitos_ley_ids);
        }

        $idsEnviados = [];
        if ($request->has('requisitos_personalizados')) {
            $requisitos = json_decode($request->requisitos_personalizados, true);

            foreach ($requisitos as $req) {
                if (isset($req['id'])) {
                    $r = Requisitos::find($req['id']);
                    if ($r) {
                        $r->update([
                            'descripcion' => $req['nombre'],
                            'tipo' => $req['tipo'],
                            'req_sec' => 'Institucion'
                        ]);
                        $idsEnviados[] = $r->id;
                    }
                } else {
                    $nuevo = Requisitos::create([
                        'id_convocatoria' => $convocatoria->id,
                        'descripcion' => $req['nombre'],
                        'tipo' => $req['tipo'],
                        'req_sec' => 'Institucion'
                    ]);
                    $idsEnviados[] = $nuevo->id;
                }
            }

            Requisitos::where('id_convocatoria', $convocatoria->id)
                ->whereNotIn('id', $idsEnviados)
                ->delete();
        }

        $requisitosNuevos = $convocatoria->requisitos()->pluck('descripcion')->toArray();
        $idsRequisitosLeyNuevos = $convocatoria->requisitosLey()->pluck('requisitos_ley.id')->toArray();

        $cambios = [];
        foreach ($original as $key => $oldValue) {
    if ($request->has($key)) {
        $newValue = $request->$key;

        // Normalizar fechas
        if (in_array($key, ['fecha_inicio', 'fecha_fin'])) {
            $oldValueNorm = substr($oldValue, 0, 10); // Solo Y-m-d
            $newValueNorm = substr($newValue, 0, 10);
        } else {
            $oldValueNorm = $oldValue;
            $newValueNorm = $newValue;
        }

        // Normalizar documento (solo nombre de archivo)
        if ($key === 'documento') {
            $oldValueNorm = $oldValue ? basename($oldValue) : null;
            $newValueNorm = $newValue ? basename($newValue) : null;
        }

        if ($oldValueNorm != $newValueNorm) {
            $cambios[$key] = [
                'antes' => $oldValue,
                'despues' => $newValue
            ];
        }
    }
}

        if ($requisitosOriginales !== $requisitosNuevos) {
            $cambios['requisitos_personalizados'] = [
                'antes' => $requisitosOriginales,
                'despues' => $requisitosNuevos
            ];
        }

        if (array_diff($idsRequisitosLeyOriginales, $idsRequisitosLeyNuevos) || array_diff($idsRequisitosLeyNuevos, $idsRequisitosLeyOriginales)) {
            $cambios['requisitos_ley_ids'] = [
                'antes' => $idsRequisitosLeyOriginales,
                'despues' => $idsRequisitosLeyNuevos
            ];
        }

        if (!empty($cambios)) {
            \App\Models\Convocatorias\ConvocatoriaAudit::create([
                'convocatoria_id' => $convocatoria->id,
                'user_id' => auth()->id(),
                'accion' => 'update',
                'cambios' => $cambios,
            ]);
        }

        DB::commit();

        return response()->json(["message" => 200]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            "message" => 500,
            "error" => $e->getMessage()
        ], 500);
    }
}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $search = $request->get("search");
    $estado = $request->get("estado");
    $area = $request->get("area");

    $convocatorias = Convocatoria::with(['requisitos', 'evaluadores', 'formulario'])

        ->when($area, function ($q) use ($area) {
            $q->where("area", $area);
        })
        ->when($estado, function ($q) use ($estado) {
            $q->where("estado", $estado);
        })
        ->when($search, function ($q) use ($search) {
            $q->where(function($query) use ($search) {
                $query->where("titulo", "ilike", "%{$search}%")
                      ->orWhereHas("requisitos", function($q) use ($search){
                          $q->where("descripcion", "ilike", "%{$search}%");
                      });
            });
        })
        ->orderBy("id", "desc")
        ->paginate(10);

    return ConvocatoriaResource::collection($convocatorias);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Iniciar transacción para garantizar que se guarden tanto la convocatoria como sus requisitos
        DB::beginTransaction();
        
        try {
            // Procesar documento
            $documentoPath = null;
            if($request->hasFile("documento")){
                /*$path = $request->file("documento")->store("convocatorias", "public");
                $request->request->add(["documento" => $path]);*/
                $documentoPath = $request->file("documento")->store("convocatorias", "public");
            }
            
            // Crear la convocatoria
            $convocatoria = Convocatoria::create([
                'titulo' => $request->titulo,
                'descripcion' => $request->descripcion,
                'area' => $request->area,
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'estado' => $request->estado ?? 'Borrador',
                'plazas_disponibles' => $request->plazas_disponibles,
                'sueldo_referencial' => $request->sueldo_referencial,
                'documento' => $documentoPath,
                'formulario_id' => $request->formulario_id,
            ]);

            // sincroniza los requisitos de ley si se enviaron desde el frontend
                if ($request->has('requisitos_ley_ids')) {
                    $convocatoria->requisitosLey()->sync($request->requisitos_ley_ids);
                }
                            
            // Asume que requisitos_ley_ids es un array de IDs seleccionados, como [1, 3, 5].
            if ($request->has('requisitos_ley_ids')) {
                $convocatoria->requisitosLey()->sync($request->requisitos_ley_ids);
            }            
            
            
            // Procesar requisitos personalizados
            if ($request->has('requisitos_personalizados')) {
                $requisitosPersonalizados = json_decode($request->requisitos_personalizados, true);
                
                foreach ($requisitosPersonalizados as $req) {
                    Requisitos::create([
                        'id_convocatoria' => $convocatoria->id,
                        'descripcion' => $req['nombre'],
                        'tipo' => $req['tipo']
                    ]);
                }
            }

            // Auditoría de creación
\App\Models\Convocatorias\ConvocatoriaAudit::create([
    'convocatoria_id' => $convocatoria->id,
    'user_id' => auth()->id(),
    'accion' => 'create',
    'cambios' => [
        'campos' => $convocatoria->toArray(),
        'requisitos_personalizados' => $request->has('requisitos_personalizados')
            ? json_decode($request->requisitos_personalizados, true)
            : [],
        'requisitos_ley_ids' => $request->has('requisitos_ley_ids')
            ? $request->requisitos_ley_ids
            : [],
    ],
]);
            
            DB::commit();
            
            return response()->json([
                "message" => 200,
                "convocatoria" => $convocatoria->id
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                "message" => 500,
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $convocatoria = Convocatoria::with(['requisitos', 'requisitosLey', 'formulario'])->findOrFail($id);


        return response()->json([
            "convocatoria" => ConvocatoriaResource::make($convocatoria),
            'formulario' => $convocatoria->formulario,

        ]);
    }

    /**
     * Update the specified resource in storage.
     */

public function update(Request $request, string $id)
{
    DB::beginTransaction();

    try {
        $convocatoria = Convocatoria::findOrFail($id);

        // Guardar datos originales para auditoría
        $original = $convocatoria->getOriginal();
        $requisitosOriginales = $convocatoria->requisitos()->pluck('descripcion')->toArray();
        $idsRequisitosLeyOriginales = $convocatoria->requisitosLey()->pluck('requisitos_ley.id')->toArray();

        // Procesar documento
        if ($request->hasFile("documento")) {
            if ($convocatoria->documento) {
                Storage::delete($convocatoria->documento);
            }
            $path = $request->file("documento")->store("convocatorias", "public");
            $request->merge(["documento" => $path]);
        }

        // Actualizar campos
        $convocatoria->update([
            'titulo' => $request->titulo ?? $convocatoria->titulo,
            'descripcion' => $request->descripcion ?? $convocatoria->descripcion,
            'area' => $request->area ?? $convocatoria->area,
            'fecha_inicio' => $request->fecha_inicio ?? $convocatoria->fecha_inicio,
            'fecha_fin' => $request->fecha_fin ?? $convocatoria->fecha_fin,
            'estado' => $request->estado ?? $convocatoria->estado,
            'plazas_disponibles' => $request->plazas_disponibles ?? $convocatoria->plazas_disponibles,
            'sueldo_referencial' => $request->sueldo_referencial ?? $convocatoria->sueldo_referencial,
            'documento' => $request->documento ?? $convocatoria->documento,
            'formulario_id' => $request->formulario_id ?? $convocatoria->formulario_id,
        ]);

        // === Procesar requisitos personalizados y de ley ===
        $idsEnviados = [];

        if ($request->has('requisitos_obligatorios')) {
            $requisitosObligatorios = json_decode($request->requisitos_obligatorios, true);
            foreach ($requisitosObligatorios as $req) {
                if ($req['seleccionado']) {
                    $nuevo = Requisitos::create([
                        'id_convocatoria' => $convocatoria->id,
                        'descripcion' => $req['texto'],
                        'tipo' => 'Obligatorio'
                    ]);
                    $idsEnviados[] = $nuevo->id;
                }
            }
        }

        if ($request->has('requisitos_personalizados')) {
            $requisitosPersonalizados = json_decode($request->requisitos_personalizados, true);
            foreach ($requisitosPersonalizados as $req) {
                if (isset($req['id'])) {
                    $requisito = Requisitos::find($req['id']);
                    if ($requisito) {
                        $requisito->update([
                            'descripcion' => $req['nombre'],
                            'tipo' => $req['tipo'],
                            'req_sec' => 'Institucion'
                        ]);
                        $idsEnviados[] = $requisito->id;
                    }
                } else {
                    $nuevo = Requisitos::create([
                        'id_convocatoria' => $convocatoria->id,
                        'descripcion' => $req['nombre'],
                        'tipo' => $req['tipo'],
                        'req_sec' => 'Institucion'
                    ]);
                    $idsEnviados[] = $nuevo->id;
                }
            }

            Requisitos::where('id_convocatoria', $convocatoria->id)
                ->whereNotIn('id', $idsEnviados)
                ->delete();
        }

        if ($request->has('requisitos_ley_ids')) {
            $convocatoria->requisitosLey()->sync($request->requisitos_ley_ids);
        }

        // Obtener nuevos datos para auditoría
        $requisitosNuevos = $convocatoria->requisitos()->pluck('descripcion')->toArray();
        $idsRequisitosLeyNuevos = $convocatoria->requisitosLey()->pluck('requisitos_ley.id')->toArray();

        // Comparar y armar cambios
        $cambios = [];
        foreach ($original as $key => $oldValue) {
    if ($request->has($key)) {
        $newValue = $request->$key;

        // Normalizar fechas
        if (in_array($key, ['fecha_inicio', 'fecha_fin'])) {
            $oldValueNorm = substr($oldValue, 0, 10); // Solo Y-m-d
            $newValueNorm = substr($newValue, 0, 10);
        } else {
            $oldValueNorm = $oldValue;
            $newValueNorm = $newValue;
        }

        // Normalizar documento (solo nombre de archivo)
        if ($key === 'documento') {
            $oldValueNorm = $oldValue ? basename($oldValue) : null;
            $newValueNorm = $newValue ? basename($newValue) : null;
        }

        if ($oldValueNorm != $newValueNorm) {
            $cambios[$key] = [
                'antes' => $oldValue,
                'despues' => $newValue
            ];
        }
    }
}

        if ($requisitosOriginales !== $requisitosNuevos) {
            $cambios['requisitos_personalizados'] = [
                'antes' => $requisitosOriginales,
                'despues' => $requisitosNuevos
            ];
        }

        if (array_diff($idsRequisitosLeyOriginales, $idsRequisitosLeyNuevos) || array_diff($idsRequisitosLeyNuevos, $idsRequisitosLeyOriginales)) {
            $cambios['requisitos_ley_ids'] = [
                'antes' => $idsRequisitosLeyOriginales,
                'despues' => $idsRequisitosLeyNuevos
            ];
        }

        if (!empty($cambios)) {
            \App\Models\Convocatorias\ConvocatoriaAudit::create([
                'convocatoria_id' => $convocatoria->id,
                'user_id' => auth()->id(),
                'accion' => 'update',
                'cambios' => $cambios,
            ]);
        }

        DB::commit();

        return response()->json(["message" => 200]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            "message" => 500,
            "error" => $e->getMessage()
        ], 500);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    DB::beginTransaction();

    try {
        $convocatoria = Convocatoria::findOrFail($id);

        // Eliminar documento asociado si existe
        if ($convocatoria->documento) {
            Storage::delete($convocatoria->documento);
        }

        // Eliminar relaciones (requisitos personalizados y de ley)
        $convocatoria->requisitos()->delete();
        $convocatoria->requisitosLey()->detach();

        // Auditoría de eliminación
\App\Models\Convocatorias\ConvocatoriaAudit::create([
    'convocatoria_id' => $convocatoria->id,
    'user_id' => auth()->id(),
    'accion' => 'delete',
    'cambios' => [
        'campos' => $convocatoria->toArray(),
        'requisitos_personalizados' => $convocatoria->requisitos()->pluck('descripcion')->toArray(),
        'requisitos_ley_ids' => $convocatoria->requisitosLey()->pluck('requisitos_ley.id')->toArray(),
    ],
]);

        $convocatoria->delete();

        DB::commit();

        return response()->json([
            'message' => 200,
            'msg_text' => 'Convocatoria eliminada correctamente'
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 500,
            'error' => $e->getMessage()
        ], 500);
    }
}

}