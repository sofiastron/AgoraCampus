<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;

    protected $table = 'enseignants';
    
    protected $fillable = [
        'nom',
        'email',
        'specialite',
        'statut',
        'heures_max_semaine',
        'telephone',
        'user_id'
    ];
    
    protected $casts = [
        'heures_max_semaine' => 'integer'
    ];
}