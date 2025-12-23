<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

    public function faceRecognition(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
            'seance_id' => 'required|integer'
        ]);

        $image = $request->file('image');

        $response = Http::attach(
            'image',
            file_get_contents($image->path()),
            $image->getClientOriginalName()
        )->post('http://127.0.0.1:5000/recognize');

        if ($response->failed()) {
            return response()->json([
                'success' => false,
                'error' => 'Face-AI unreachable'
            ], 500);
        }

        // Ici tu peux appeler la nouvelle méthode pour enregistrer les présences
        return $this->enregistrerPresencesDepuisIA(new Request([
            'face_ai_response' => $response->json(),
            'seance_id' => $request->seance_id
        ]));
    }

    public function enregistrerPresencesDepuisIA(Request $request)
    {
        $request->validate([
            'face_ai_response' => 'required|array',
            'seance_id' => 'required|integer',
        ]);

        $faceData = $request->face_ai_response;
        $seanceId = $request->seance_id;

        foreach ($faceData as $face) {
            if (isset($face['id'])) {
                Presence::updateOrCreate(
                    [
                        'etudiant_id' => $face['id'],
                        'seance_id' => $seanceId
                    ],
                    [
                        'statut' => $face['statut']
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Présences enregistrées'
        ]);
    }
}
