<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Annonce extends Model
{
    public $timestamps = false; 
    protected $fillable = [
        'titre', 'contenu', 'date_Creation', 'module_id','enseignant_id',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
