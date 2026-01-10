<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'nom', 'email', 'password', 'role'
    ];

    public function etudiant() {
        return $this->hasOne(Etudiant::class, 'id');
    }
}
