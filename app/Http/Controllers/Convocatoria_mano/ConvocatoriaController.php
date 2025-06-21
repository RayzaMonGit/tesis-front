<?php

namespace App\Http\Controllers;

use App\Models\Convocatorias;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\Convocatorias\RequisitoResource;




class ConvocatoriaController extends Controller
{
    public function requisitos($id)
{
    try {
        $convocatoria = Convocatoria::with('requisitos')->findOrFail($id);

        return response()->json([
            'requisitos' => RequisitoResource::collection($convocatoria->requisitos),
            'status' => 'success'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'requisitos' => [],
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
}

    public function index(Request $request)
    {
        // Solo las publicadas
        $convocatorias = Convocatoria::where("estado", "Publicado")->orderBy("id", "desc")->get();

        return response()->json([
            "convocatorias" => $convocatorias,
        ]);
    }

    public function store(Request $request)
    {
        // Validación condicional
        $rules = [
            'estado' => 'required|in:Borrador,Publicado',
        ];

        if ($request->estado === 'Publicado') {
            $rules = array_merge($rules, [
                'titulo' => 'required|string',
                'descripcion' => 'required|string',
                'area' => 'required|string',
                'fecha_inicio' => 'required|date|after_or_equal:today',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'plazas_disponibles' => 'required|integer|min:1',
                'sueldo_referencial' => 'required|numeric|min:0',
                'documento' => 'required|file|mimes:pdf,doc,docx',
                'formulario_id'=>'required',
            ]);
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('documento')) {
            $path = Storage::putFile('convocatorias', $request->file('documento'));
            $validated['documento'] = $path;
        }

        $convocatoria = Convocatoria::create($validated);

        return response()->json([
            "message" => 200,
            "convocatoria" => $convocatoria,
        ]);
    }

    public function update(Request $request, $id)
    {
        $convocatoria = Convocatoria::findOrFail($id);

        $rules = [
            'estado' => 'required|in:Borrador,Publicado',
        ];

        if ($request->estado === 'Publicado') {
            $rules = array_merge($rules, [
                'titulo' => 'required|string',
                'descripcion' => 'required|string',
                'area' => 'required|string',
                'fecha_inicio' => 'required|date|after_or_equal:today',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'plazas_disponibles' => 'required|integer|min:1',
                'sueldo_referencial' => 'required|numeric|min:0',
                'documento' => 'nullable|file|mimes:pdf,doc,docx',
                'formulario_id'=>'required',
            ]);
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('documento')) {
            if ($convocatoria->documento) {
                Storage::delete($convocatoria->documento);
            }
            $path = Storage::putFile('convocatorias', $request->file('documento'));
            $validated['documento'] = $path;
        }

        $convocatoria->update($validated);

        return response()->json([
            "message" => 200,
            "convocatoria" => $convocatoria,
        ]);
    }

    public function destroy($id)
    {
        $convocatoria = Convocatoria::findOrFail($id);

        if ($convocatoria->documento) {
            Storage::delete($convocatoria->documento);
        }

        $convocatoria->delete();

        return response()->json([
            "message" => 200,
        ]);
    }
}
