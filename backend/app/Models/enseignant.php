<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    protected $table = 'enseignants'; // optionnel si le nom suit la convention

    protected $fillable = [
        'user_id',
        'nom',
        'email',
        'specialite',
        'statut',
        'heures_max_semaine'
    ];

    // Relation vers l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
