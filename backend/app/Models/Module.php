<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'titre', 
        'description', 
        'filiere_id', 
        'is_common',
        'niveau',
        'semaine_debut',
        'semaine_fin'
    ];

    public function getPeriodeAttribute()
    {
        if ($this->semaine_debut && $this->semaine_fin) {
            return "S{$this->semaine_debut}-S{$this->semaine_fin}";
        }
        return 'S1-S16'; // Par défaut
    }

    public function getDureeSemainesAttribute()
    {
        if ($this->semaine_debut && $this->semaine_fin) {
            return $this->semaine_fin - $this->semaine_debut + 1;
        }
        return 16;
    }
}