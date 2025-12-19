<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use Illuminate\Http\Request;

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

    public function enregistrerPresence(Request $request)
    {
        $presence = Presence::create([
            'etudiant_id' => $request->etudiant_id,
            'seance_id' => $request->seance_id,
            'statut' => $request->statut,
        ]);

        return response()->json([
            'success' => true,
            'presence' => $presence
        ]);
    }
}
