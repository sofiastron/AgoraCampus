<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploiDuTemps extends Model
{
    use HasFactory;

    protected $table = 'emploi_du_temps';
    
    protected $fillable = [
        'filiere_id',
        'semestre_id',
        'statut',
        'date_generation',
        'date_validation',
        'admin_id',
        'data_emploi'
    ];
    
    protected $casts = [
        'data_emploi' => 'array',
        'date_generation' => 'datetime',
        'date_validation' => 'datetime',
    ];
    
    // Relation avec la filière
    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }
    
    // Relation avec l'admin qui a validé
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}