<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploiTemps extends Model
{
    use SoftDeletes;
    
    protected $table = 'emplotemps';
    
    protected $fillable = [
        'titre',
        'filiere_id',
        'semestre',
        'niveau',
        'statut',
        'horaires',
        'affectations',
        'semaines',
        'statistiques',
        'date_debut',
        'date_fin',
        'created_by',
        'updated_by'
    ];
    
    protected $casts = [
        'horaires' => 'array',
        'affectations' => 'array',
        'semaines' => 'array',
        'statistiques' => 'array',
        'date_debut' => 'date',
        'date_fin' => 'date'
    ];
    
    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }
    
    public function scopeByFiliere($query, $filiere_id)
    {
        return $query->where('filiere_id', $filiere_id);
    }
    
    public function scopeBySemestre($query, $semestre)
    {
        return $query->where('semestre', $semestre);
    }
}