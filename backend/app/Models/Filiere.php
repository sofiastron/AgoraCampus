<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'code'];
    
    // Relation avec les groupes
    public function groupes()
    {
        return $this->hasMany(Groupe::class);
    }
    
    // Relation avec les modules
    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}