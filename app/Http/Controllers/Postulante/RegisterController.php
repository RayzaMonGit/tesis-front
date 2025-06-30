<?php

namespace App\Http\Controllers\Postulante;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Postulante\Postulante;;


use App\Http\Resources\Postulante\PostulanteCollection;
use App\Http\Resources\Postulante\PostulanteResource;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

use App\Http\Controllers\AuthController;
//para enviar email
use App\Mail\VerificationCodeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;



class RegisterController extends Controller
{
    public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $verificationCode = Str::random(6);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'verification_code' => $verificationCode,
        'is_verified' => false,
    ]);

    Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));

    return redirect()->route('verification.notice');
}

public function registerUser(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    $user = User::create([
        'email' => $request->email,
        'password' => bcrypt($request->password),
    ]);

    // Buscar el rol de postulante
    $rolPostulante = Role::where([
        ['name', '=', 'Postulante'],
        ['guard_name', '=', 'api']
    ])->first();

    if (!$rolPostulante) {
        return response()->json(['message' => 'Rol postulante no encontrado'], 500);
    }

    // Asignar el rol y guardar en la columna role_id si la estás usando
    $user->assignRole($rolPostulante);
    $user->role_id = $rolPostulante->id;
    $user->save();
/*
    // Cargar roles y permisos en el usuario antes de devolverlo
$user->load('roles');
$user->role = $user->roles->first();
$user->permissions = $user->getAllPermissions()->pluck('name');;

// Generar token (solo una vez, al final)
$token = Auth::guard('api')->fromUser($user);

return response()->json([
    'success' => true,
    'user' => $user,
    'access_token' => $token,
]);*/
$token = auth()->login($user);
return response()->json([
    'success' => true,
    'user' => $user->load('roles'),
]);
}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function registerpos(Request $request)
    {
        // Guardar el password plano para login
           

            $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'name' => 'required|string|max:100',
        'surname' => 'required|string|max:100',
        'telefono' => 'required|string|max:20',
        'gender' => 'required|string|in:M,F,O',
        'tipo_doc' => 'required|string',
        'n_doc' => 'required|string|unique:users,n_doc',
        'avatar' => 'nullable|image|max:2048',
        'grado_academico' => 'required|string|max:100',
        'experiencia_años' => 'required|integer|min:0',
        'fecha_nacimiento' => 'nullable|date',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Error de validación',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Subir imagen si existe
    if ($request->hasFile("avatar")) {
        $path = Storage::putFile("users", $request->file("avatar"));
        $request->merge(["avatar" => $path]);
    }
    // Actualizar usuario existente
    $user = User::findOrFail($request->user_id);
    $user->update([
        'name' => $request->name,
        'surname' => $request->surname,
        'telefono' => $request->telefono,
        'gender' => $request->gender,
        'tipo_doc' => $request->tipo_doc,
        'n_doc' => $request->n_doc,
        'avatar' => $request->avatar ?? $user->avatar,
    ]);
    
        // Crear postulante
        Postulante::create([
            'user_id' => $user->id,
            'grado_academico' => $request->grado_academico,
            'experiencia_años' => $request->experiencia_años,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            //'convocatoria_id' => $request->convocatoria_id,
        ]);
    
       
        
    // Asignar rol automáticamente si no lo tiene
    $rolPostulante = Role::where('name', 'Postulante')->first();
if (!$rolPostulante) {
    return response()->json(['message' => 'Rol postulante no encontrado'], 500);
}
if (!$user->hasRole($rolPostulante->name)) {
    $user->assignRole($rolPostulante);
}
/*
// Carga roles, asigna role y permisos
$user->load('roles');
$user->role = $user->roles->first();
$user->permissions = $user->getAllPermissions()->pluck('name');;

// Genera token después de tener usuario actualizado
$token = Auth::guard('api')->fromUser($user);

return response()->json([
    'success' => true,
    'message' => 'Datos de postulante guardados correctamente',
    'access_token' => $token,
    'user' => $user,
]);*/
$token = auth()->login($user);
return $this->respondWithToken($token);
        
    }
public function respondWithToken($token)
    {
    /*El getallpermissions devuelve todos lo permisos que tiene asignado
    un usuario a partir de su rol */
    $permissions= auth('api')->user()->getAllPermissions()->map(function($permission){
        return $permission->name;

    });
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            "user" => [
                "id"=> auth('api')->user()->id,
                "name"=> auth('api')->user()->name,
                "surname"=>auth('api')->user()->surname,
                "email"=>auth('api')->user()->email,
                "avatar"=>auth('api')->user()->avatar ? env("APP_URL")."storage/".auth('api')->user()->avatar : null,
                "role"=> auth('api')->user()->role,
                //"roles" => $user->roles->pluck('name'), 
                "permissions"=> $permissions,
            ]
        ]);
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
