<?php

namespace App\Http\Controllers;

use App\Models\Presence;
use App\Models\Etudiant;
use App\Models\Utilisateur; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PresenceController extends Controller
{
    public function creerSeance(Request $request)
    {
        $validated = $request->validate([
            'module_id'   => 'required',
            'date'        => 'required|date',
            'heure_debut' => 'required',
            'heure_fin'   => 'required',
        ]);

        $seanceId = DB::table('seances')->insertGetId([
            'module_id'   => $validated['module_id'],
            'date'        => $validated['date'],
            'heure_debut' => $validated['heure_debut'],
            'heure_fin'   => $validated['heure_fin'],
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'id' => $seanceId,
            'message' => 'Séance créée avec succès'
        ]);
    }

    public function faceRecognition(Request $request)
    {
        $request->validate([
            'image'     => 'required|string', 
            'seance_id' => 'required|exists:seances,id',
        ]);

        try {
            $response = Http::post('http://127.0.0.1:5000/recognize', [
                'image' => $request->image,
            ]);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'error' => 'Microservice IA injoignable'], 500);
            }

            $aiData = $response->json();
            $idsPresents = [];
            $nomsReconnus = [];

            if (isset($aiData['status']) && $aiData['status'] === 'success' && isset($aiData['names'])) {
                foreach ($aiData['names'] as $nomReconnu) {
                    $utilisateur = Utilisateur::where('nom', $nomReconnu)->first();
                    if ($utilisateur) {
                        Presence::updateOrCreate(
                            ['etudiant_id' => $utilisateur->id, 'seance_id' => $request->seance_id],
                            ['statut' => 'present', 'horodatage' => Carbon::now()]
                        );
                        $idsPresents[] = $utilisateur->id;
                        $nomsReconnus[] = $utilisateur->nom;
                    }
                }
            }

            $tousLesEtudiantsIds = Etudiant::pluck('id')->toArray();
            $idsAbsents = array_diff($tousLesEtudiantsIds, $idsPresents);

            foreach ($idsAbsents as $idAbsent) {
                $dejaPresent = Presence::where('etudiant_id', $idAbsent)
                    ->where('seance_id', $request->seance_id)
                    ->where('statut', 'present')
                    ->exists();

                if (!$dejaPresent) {
                    Presence::updateOrCreate(
                        ['etudiant_id' => $idAbsent, 'seance_id' => $request->seance_id],
                        ['statut' => 'absent', 'horodatage' => Carbon::now()]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($nomsReconnus) . " étudiant(s) identifié(s).",
                'names' => $nomsReconnus
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function presencesSeance($seanceId)
    {
        $presences = DB::table('presences')
            ->join('utilisateurs', 'presences.etudiant_id', '=', 'utilisateurs.id')
            ->where('presences.seance_id', $seanceId)
            ->select('utilisateurs.nom', DB::raw('LOWER(presences.statut) as statut'), 'presences.horodatage as heure')
            ->orderByRaw("FIELD(statut, 'present', 'absent')")
            ->get();

        return response()->json(['success' => true, 'presences' => $presences]);
    }
}