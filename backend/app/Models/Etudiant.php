<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $fillable = [
        'id', 'cne', 'niveau', 'groupe_id'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'id');
    }
}
