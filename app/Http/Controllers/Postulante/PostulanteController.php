<?php

namespace App\Http\Controllers\Postulante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Models\Postulante\Postulante;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\Postulante\PostulanteCollection;
use App\Http\Resources\Postulante\PostulanteResource;



class PostulanteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{

    $search = $request->get("search");
    $usuarios = User::with('postulante')
    ->whereHas('role', fn ($q) => $q->where('name', 'Postulante'))
    ->when($search, function ($query, $search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%$search%")
              ->orWhere('surname', 'ILIKE', "%$search%")
              ->orWhere('email', 'ILIKE', "%$search%");
        });
    })
    ->paginate(10);

return UserResource::collection($usuarios);

}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
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
    $is_user_exist = User::where("email", $request->email)->where("id", "<>", $id)->first();
    if ($is_user_exist) {
        return response()->json([
            "message" => 403,
            "message_text" => "El usuario ya existe"
        ]);
    }

    $user = User::findOrFail($id);

    // Procesar imagen nueva si se carga
    if ($request->hasFile("imagen")) {
        if ($user->avatar) {
            Storage::delete($user->avatar);
        }
        $path = Storage::putFile("users", $request->file("imagen"));
        $request->request->add(["avatar" => $path]);
    }

    // Actualizar datos del usuario
    $user->update($request->only([
        'name', 'surname', 'email', 'telefono',
        'gender', 'tipo_doc', 'n_doc', 'avatar'
    ]));

    // ✅ Actualizar datos del postulante relacionado
    if ($user->postulante) {
        $user->postulante->update($request->only([
            'grado_academico',
            'experiencia_años',
            //'fecha_nacimiento',
        ]));
    }

    return response()->json([
        "message" => 200,
        "user" => UserResource::make($user->load('postulante')), // para asegurar que se envíe actualizado
    ]);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

    // Eliminar imagen si existe
    if ($user->avatar) {
        Storage::delete($user->avatar);
    }

    // Eliminar al postulante si existe
    if ($user->postulante) {
        $user->postulante->delete();
    }

    // Eliminar al usuario
    $user->delete();

    return response()->json([
        "message" => 200,
    ]);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function miPerfil()
{
    $user = auth()->user();

    // Verifica si el usuario tiene un perfil de postulante
    $postulante = Postulante::where('user_id', $user->id)->first();

    if (!$postulante) {
        return response()->json(['error' => 'No se encontró el perfil del postulante.'], 404);
    }

    return response()->json([
        'postulante_id' => $postulante->id,
        'postulante' => new PostulanteResource($postulante), // opcional, por si quieres más info
    ]);
}

}
