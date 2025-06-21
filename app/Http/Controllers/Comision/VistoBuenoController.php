<?php

namespace App\Http\Controllers\Comision;

use App\Models\Comision\VistoBueno;
use App\Http\Resources\Comision\VistoBuenoResource;
use Illuminate\Http\Request;

class VistoBuenoController extends Controller
{
    public function index(Request $request)
    {
        $vbs = VistoBueno::with(['comision', 'documento'])->get();
        return VistoBuenoResource::collection($vbs);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_comision' => 'required|exists:users,id',
            'id_documento' => 'required|exists:postulacion_documentos,id',
            'estado' => 'required|string|max:15',
            'observacion' => 'nullable|string|max:255',
        ]);

        $vb = VistoBueno::updateOrCreate(
            [
                'id_comision' => $data['id_comision'],
                'id_documento' => $data['id_documento'],
            ],
            $data
        );

        return new VistoBuenoResource($vb);
    }

    public function show($id)
    {
        $vb = VistoBueno::with(['comision', 'documento'])->findOrFail($id);
        return new VistoBuenoResource($vb);
    }
}
