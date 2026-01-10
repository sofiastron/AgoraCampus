<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    protected $fillable = [
        'date', 'heure_debut', 'heure_fin', 'qr_code',
        'module_id', 'enseignant_id', 'groupe',
        'emploi_temps_id', 'salle_id', 'jour', 'statut'
    ];
    
    // Ajouter ces nouvelles relations
    public function emploiTemps()
    {
        return $this->belongsTo(EmploiDuTemps::class, 'emploi_temps_id');
    }
    
    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }
    
    // Garder les relations existantes
    public function module()
    {
        return $this->belongsTo(Module::class);
    }
    
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }
}