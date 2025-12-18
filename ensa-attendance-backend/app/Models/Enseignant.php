<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    protected $fillable = [
        'grade', 'departement', 'utilisateur_id'
    ];

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
