<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    protected $fillable = [
        'titre',
        'contenu',
        'date_creation',
        'enseignant_id',
    ];

    protected $casts = [
        'date_creation' => 'datetime',
    ];

    // Relations
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }
}