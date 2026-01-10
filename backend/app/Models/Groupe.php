<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'filiere_id', 'semestre_id', 'capacite'];

    /**
     * Relation avec la filière
     */
    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    /**
     * Relation avec le semestre
     */
    public function semestre()
    {
        return $this->belongsTo(Semestre::class);
    }

    /**
     * Relation avec les étudiants (si vous avez une table étudiants)
     */
    public function etudiants()
    {
        return $this->hasMany(Etudiant::class);
    }
}