<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 
use App\Models\Postulante\Postulante;



class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'surname',
        'role_id',
        'avatar',
        'telefono',
        'designacion',
        'gender',
        'tipo_doc',
        'n_doc',
        //para verificacion
        'verification_code',
        'is_verified',
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

    public function role(){
        return $this->belongsTo(Role::class,"role_id");
    }
    public function postulante(){
        return $this->hasOne(Postulante::class);
    }
    public function comision()
{
    return $this->belongsToMany(User::class, 'convocatoria_comision')->withTimestamps();
}
public function convocatorias()
{
    return $this->belongsToMany(Convocatoria::class, 'convocatoria_comision', 'user_id', 'convocatoria_id');
}
public function convocatoriasComoEvaluador()
{
    return $this->belongsToMany(\App\Models\Convocatorias\Convocatoria::class, 'convocatoria_comision', 'user_id', 'convocatoria_id');
}




}
