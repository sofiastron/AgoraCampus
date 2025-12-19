<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    protected $fillable = [
        'user_id',
        'grade',
        'departement'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function annonces()
    {
        return $this->hasMany(Annonce::class);
    }
}
