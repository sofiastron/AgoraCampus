<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semestre extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'filiere_id'];

    /**
     * Relation avec la filière
     */
    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    /**
     * Relation avec les groupes
     */
    public function groupes()
    {
        return $this->hasMany(Groupe::class);
    }

    /**
     * Relation avec les modules
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
    }
}