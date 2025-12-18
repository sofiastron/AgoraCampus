<?php

namespace App\Http\Controllers;

use App\Models\Presence;

class PresenceController extends Controller
{
    public function presencesSeance($seanceId)
    {
        return response()->json([
            'success' => true,
            'presences' => Presence::with('etudiant.utilisateur')
                ->where('seance_id', $seanceId)
                ->get()
        ]);
    }
}
