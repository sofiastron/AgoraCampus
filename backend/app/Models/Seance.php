<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    protected $fillable = [
        'module_id',
        'date',
        'heureDebut',
        'heureFin',
        'qrCode'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function qrcode()
    {
        return $this->hasOne(GenerateurQRCode::class);
    }
}
