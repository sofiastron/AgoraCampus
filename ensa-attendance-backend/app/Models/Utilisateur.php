<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Utilisateur extends Authenticatable
{
    protected $table = 'utilisateurs';

    protected $fillable = [
        'nom', 'prenom', 'email', 'mot_de_passe', 'role', 'photo'
    ];

    protected $hidden = [
        'mot_de_passe'
    ];

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function etudiant()
    {
        return $this->hasOne(Etudiant::class);
    }

    public function enseignant()
    {
        return $this->hasOne(Enseignant::class);
    }
}
