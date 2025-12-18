<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'titre', 'cheminFichier', 'type', 'dateUpload', 'module_id'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
