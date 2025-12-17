<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'nom',
        'email',
        'password',
        'role',
        'photo'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    /* Relations héritées */
    public function etudiant()
    {
        return $this->hasOne(Etudiant::class);
    }

    public function enseignant()
    {
        return $this->hasOne(Enseignant::class);
    }

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class);
    }
}
