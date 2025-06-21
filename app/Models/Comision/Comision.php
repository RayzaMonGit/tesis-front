<?php

namespace App\Models\Comision;

use Illuminate\Database\Eloquent\Model;

class Comision extends Model
{
    public function comision()
{
    return $this->belongsToMany(User::class, 'convocatoria_comision')->withTimestamps();
}

}
