<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seance extends Model
{
    protected $fillable = [
        'date', 'heureDebut', 'heureFin', 'qrCode', 'module_id'
    ];

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
