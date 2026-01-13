<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Module extends Model
{
    protected $fillable = ['titre', 'description', 'enseignant_id', 'photo'];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }
    public function etudiants(): BelongsToMany
{
    return $this->belongsToMany(
        Etudiant::class,
        'module_etudiant',
        'module_id',
        'etudiant_id'
    );
}

    public function seances()
    {
        return $this->hasMany(Seance::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function annonces()
    {
        return $this->hasMany(Annonce::class);
    }
}
