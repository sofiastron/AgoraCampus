<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    protected $fillable = [
        'module_id',
        'date',
        'heure_debut',
        'heure_fin',
        'enseignant_id',
        'qr_code'         
    ];

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }
}
