<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enseignant extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['user_id', 'specialite', 'departement'];
    
    protected $dates = ['deleted_at'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function modules()
    {
        return $this->hasMany(Module::class, 'enseignant_id');
    }
}