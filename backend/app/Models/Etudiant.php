<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;;
use App\Models\Groupe;
class Etudiant extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'cne',
        'niveau',
        'user_id',
        'groupe_id',
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relation avec le groupe
    public function groupe()
    {
        return $this->belongsTo(Groupe::class);
    }
}
