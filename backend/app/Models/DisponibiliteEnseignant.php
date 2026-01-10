<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisponibiliteEnseignant extends Model
{
    protected $fillable = [
        'enseignant_id', 'jour', 'creneau', 'disponible'
    ];
    
    protected $casts = [
        'disponible' => 'boolean'
    ];
    
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }
}