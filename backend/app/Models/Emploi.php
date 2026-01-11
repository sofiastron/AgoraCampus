<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Emploi extends Model
{
    protected $fillable = [
        'groupe_id',
        'module',
        'date',
        'heure_debut',
        'heure_fin',
        'statut'
    ];
}
