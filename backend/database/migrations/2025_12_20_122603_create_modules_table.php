<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['nom', 'code', 'enseignant_id'];
    
    protected $dates = ['deleted_at'];
    
    public function enseignant()
    {
        return $this->belongsTo(Enseignant::class, 'enseignant_id');
    }
}