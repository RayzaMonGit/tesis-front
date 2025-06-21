<?php

namespace App\Http\Controllers\Formulario\EvaluacionController;
use App\Http\Controllers\Controller;
use App\Models\Evaluacion;
use App\Http\Requests\StoreEvaluacionRequest;
use App\Services\ScoringService;
use App\Http\Resources\Evaluacion\EvaluacionResource;

class EvaluacionController extends Controller {
    // Guarda las respuestas y calcula el puntaje (API)
    public function store(StoreEvaluacionRequest $request) {
        $evaluacion = Evaluacion::create($request->validated());
        $scoringService = new ScoringService();
        $resultado = $scoringService->calcularPuntajeTotal($evaluacion->id);
        
        return new EvaluacionResource($evaluacion); // Retorna JSON con resultados
    }

    // Muestra resultados en web
    public function resultado($evaluacionId) {
        $scoringService = new ScoringService();
        $resultado = $scoringService->calcularPuntajeTotal($evaluacionId);
        return view('evaluaciones.resultado', compact('resultado'));
    }
}