<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    protected $fillable = [
        'module_id',
        'enseignant_id',
        'titre',
        'contenu'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }
}
