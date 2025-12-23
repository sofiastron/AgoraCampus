<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    protected $fillable = ['nom', 'niveau'];

    public function etudiants()
    {
        return $this->hasMany(Etudiant::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class);
    }
}
