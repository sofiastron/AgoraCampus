<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use Illuminate\Http\Request;
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

    public function store(Request $request, $seanceId)
    {
        $request->validate([
            'statut' => 'required|in:présent,absent'
        ]);

        $etudiant = auth()->user()->etudiant;

        $presence = Presence::updateOrCreate(
            [
                'etudiant_id' => $etudiant->id,
                'seance_id' => $seanceId
            ],
            [
                'statut' => $request->statut,
                'horodatage' => Carbon::now()
            ]
        );

        return response()->json([
            'message' => 'Statut enregistré',
            'data' => $presence
        ]);
    }
}
