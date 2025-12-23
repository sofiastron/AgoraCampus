<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerateurQRCode extends Model
{
    protected $fillable = [
        'contenu', 'dateExpiration', 'seance_id'
    ];

    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }
}
