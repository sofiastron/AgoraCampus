<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['description', 'enseignant_id'];

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
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
