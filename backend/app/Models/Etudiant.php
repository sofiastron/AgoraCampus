<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Etudiant extends Model
{
    protected $fillable = [
        'cne', 'niveau', 'id_groupe', 'utilisateur_id'
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }
    public function modules(): BelongsToMany
{
    return $this->belongsToMany(
        Module::class,
        'module_etudiant',
        'etudiant_id',
        'module_id'
    );
}
    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'id_groupe');
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }
}
