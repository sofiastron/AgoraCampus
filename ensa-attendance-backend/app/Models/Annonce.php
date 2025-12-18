<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    protected $fillable = [
        'titre', 'description', 'dateCreation', 'module_id'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
