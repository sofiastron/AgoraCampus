<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    protected $table = 'enseignants';

    protected $fillable = [
        'user_id',
        'grade',
        'departement'
    ];
}
