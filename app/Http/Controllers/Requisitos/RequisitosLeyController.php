<?php

namespace App\Http\Controllers\Requisitos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Convocatorias\RequisitosLey;
use App\Http\Resources\Requisitos\ReqisitosLeyCollection;
use App\Http\Resources\Requisitos\ReqisitosLeyResource;
use App\Models\Convocatorias\Requisitos;
use App\Models\Convocatorias\Convocatoria;
use App\Http\Resources\RequisitosLey\RequisitosLeyResource;
use App\Http\Resources\RequisitosLey\RequisitosLeyCollection;

class RequisitosLeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $requisitos = RequisitosLey::all();

    return response()->json([
        'requisitos' => $requisitos
    ]);
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
