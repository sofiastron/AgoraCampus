<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'titre',
        'chemin_fichier',
        'type_document',
        'date_upload',
        'module_id',
        'enseignant_id',
    ];

    protected $casts = [
        'date_upload' => 'datetime',
    ];

    // Relations
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class);
    }
}