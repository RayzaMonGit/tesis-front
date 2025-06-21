<?php

namespace App\Models\Postulante;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 
use App\Models\User;

class Postulante extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'postulantes';
    protected $fillable = [
       'user_id',
        'convocatoria_id',
        //'telefono',
       // 'direccion',
        'grado_academico',
        'fecha_nacimiento',
        'experiencia_años'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
 
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }



    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function convocatoria()
    {
        return $this->belongsTo(Convocatoria::class);
    }

   
public function postulaciones()
{
    return $this->hasMany(Postulacion::class);
}

public function postulante()
{
    return $this->belongsTo(\App\Models\Postulante::class, 'postulante_id');
}

}
