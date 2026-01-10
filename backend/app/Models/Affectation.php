<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affectation extends Model
{
    protected $fillable = [
        'enseignant_id', 'module_id', 'filiere_id', 
        'semestre_id', 'volume_horaire'
    ];
    
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }
    
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
    
    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }
    
    public function semestre()
    {
        return $this->belongsTo(Semestre::class);
    }
}