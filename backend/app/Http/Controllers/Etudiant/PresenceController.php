<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Carbon\Carbon;
class PresenceController extends Controller
{
    public function index()
    {
        $etudiant = auth()->user()->etudiant;

        return response()->json(
            Presence::where('etudiant_id', $etudiant->id)
                ->with('seance.module')
                ->get()
        );
    }

    public function store($seanceId)
    {
        $etudiant = auth()->user()->etudiant;

        $presence = Presence::updateOrCreate(
            [
                'etudiant_id' => $etudiant->id,
                'seance_id' => $seanceId
            ],
            [
                'statut' => 'présent',
                'horodatage' => Carbon::now()
            ]
        );

        return response()->json([
            'message' => 'Présence enregistrée',
            'data' => $presence
        ]);
    }
}