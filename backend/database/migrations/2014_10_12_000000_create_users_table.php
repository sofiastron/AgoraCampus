<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes; // Ajoutez cette ligne

class User extends Authenticatable
{
    use HasFactory, SoftDeletes; // Ajoutez SoftDeletes ici
    
    protected $fillable = [
        'nom', 'email', 'password', 'role'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
    
    protected $dates = ['deleted_at']; // Ajoutez cette ligne
    
    // Vos relations existantes...
    public function enseignant()
    {
        return $this->hasOne(\App\Models\Enseignant::class, 'user_id');
    }
}