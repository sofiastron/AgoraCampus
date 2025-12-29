<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['titre', 'description', 'enseignant_id','groupe_id', 'photo'];

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

    public function groupe()
    {
        return $this->belongsTo(Groupe::class, 'groupe_id', 'id_groupe');
    }

}
