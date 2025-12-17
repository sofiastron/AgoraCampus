<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerateurQRCode extends Model
{
    protected $fillable = [
        'seance_id',
        'contenu',
        'expiration'
    ];

    public function seance()
    {
        return $this->belongsTo(Seance::class);
    }
}
