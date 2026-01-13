<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class PresenceController extends Controller
{

    public function presencesSeance($seanceId)
    {
        $presences = Presence::with('etudiant.utilisateur')
            ->where('seance_id', $seanceId)
            ->get();

        return response()->json([
            'success' => true,
            'presences' => $presences
        ]);
    }

    public function enregistrerPresence(Request $request)
    {
        $validated = $request->validate([
            'etudiant_id' => 'required|exists:etudiants,id',
            'seance_id'   => 'required|exists:seances,id',
            'statut'      => 'required|in:present,absent',
        ]);

        $presence = Presence::updateOrCreate(
            [
                'etudiant_id' => $validated['etudiant_id'],
                'seance_id'   => $validated['seance_id'],
            ],
            [
                'statut' => $validated['statut'],
            ]
        );

        return response()->json([
            'success' => true,
            'presence' => $presence
        ]);
    }

    public function faceRecognition(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
            'seance_id' => 'required|exists:seances,id',
        ]);

        $image = $request->file('image');

        /** @var Response $response */
        $response = Http::attach(
            'image',
            file_get_contents($image->getRealPath()),
            $image->getClientOriginalName()
        )->post('http://127.0.0.1:5000/recognize');

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'error' => 'Face-AI unreachable or error occurred',
                'details' => $response->body(), 
            ], 500);
        }

        return response()->json([
            'success' => true,
            'face_ai_response' => $response->json(),
        ]);
    }
    public function getEtudiantsPresence(Request $request)
{
    $enseignant = $request->user()->enseignant;

    if (!$enseignant) {
        return response()->json(['message' => 'Utilisateur non enseignant'], 403);
    }

    $query = DB::table('presences')
        ->join('etudiants', 'presences.etudiant_id', '=', 'etudiants.id')
        ->join('seances', 'presences.seance_id', '=', 'seances.id')
        ->join('modules', 'seances.module_id', '=', 'modules.id')
        ->where('modules.enseignant_id', $enseignant->id);

    if ($request->module_id) {
        $query->where('modules.id', $request->module_id);
    }

    if ($request->seance_id) {
        $query->where('seances.id', $request->seance_id);
    }

    if ($request->statut) {
        $query->where('presences.statut', $request->statut);
    }

    $etudiants = $query->select(
        'etudiants.id',
        'etudiants.nom',
        'etudiants.prenom',
        'modules.titre as module_titre',
        'presences.statut'
    )->get();

    return response()->json($etudiants);
}

}
