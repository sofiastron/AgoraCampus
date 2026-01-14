<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'module_id',
        'titre',
        'cheminFichier',
        'type_document'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
