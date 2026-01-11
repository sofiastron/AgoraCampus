<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmploiTemps extends Model
{
    use SoftDeletes;
    
    protected $table = 'emplotemps'; // Assurez-vous que c'est le bon nom
    
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
        'updated_by',
        'date_validation', // Ajoutez ces champs
        'valide_par',      // Ajoutez ces champs
        'raison_rejet'     // Ajoutez ces champs
    ];
    
    protected $casts = [
        'horaires' => 'array',
        'affectations' => 'array',
        'semaines' => 'array',
        'statistiques' => 'array',
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_validation' => 'datetime'
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
    
    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
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
    
    public function scopeValide($query)
    {
        return $query->where('statut', 'valide');
    }
    
    public function scopeEnAttente($query)
    {
        return $query->where('statut', 'en_attente');
    }
}