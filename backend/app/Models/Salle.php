<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
  protected $fillable = [
        'nom',
        'batiment',
        'capacite',
        'type',
        'disponible'
    ];
       protected $casts = [
        'capacite' => 'integer',
        'disponible' => 'boolean'
    ];
    
    public function seances()
    {
        return $this->hasMany(Seance::class);
    }
}