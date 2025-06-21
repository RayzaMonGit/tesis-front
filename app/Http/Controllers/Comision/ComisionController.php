<?php

namespace App\Http\Controllers\Comision;
use App\Models\Convocatorias\Convocatoria;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ComisionController extends Controller
{
    public function asignarComision(Request $request, $id)
{
    $convocatoria = Convocatoria::findOrFail($id);

    // Validar que sea un array de IDs
    $evaluadoresIds = $request->input('evaluadores', []);

    if (!is_array($evaluadoresIds)) {
        return response()->json([
            'message' => 'El formato de evaluadores no es válido.'
        ], 422);
    }

    // Sincronizar evaluadores (esto reemplaza los anteriores)
    $convocatoria->evaluadores()->sync($evaluadoresIds);

    return response()->json([
        'message' => 'Evaluadores asignados correctamente'
    ]);
}


public function obtenerComision($id)
{
    $convocatoria = Convocatoria::with('evaluadores')->findOrFail($id);

    return response()->json([
        'evaluadores' => $convocatoria->evaluadores
    ]);
}
public function convocatoriasPorEvaluador()
{
    $user = auth()->user();

    // Asegúrate de que es una comisión (si usas roles)
    /*if (!$user->hasRole('Evaluador')) {
        return response()->json(['error' => 'No autorizado.'], 403);
    }*/

    $convocatorias = $user->convocatoriasComoEvaluador()->get();
 // puedes cargar relaciones si quieres

    return response()->json([
        'evaluador_id' => $user->id,
        'convocatorias' => $convocatorias,
    ]);
}


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
