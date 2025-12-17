<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\Seance;

class EtudiantDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user || !$user->etudiant) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $etudiant = $user->etudiant;

        $totalSeances = Seance::count();

        $presences = $etudiant->presences()
            ->where('statut', 'present')
            ->count();

        $absences = $etudiant->presences()
            ->where('statut', 'absent')
            ->count();

       
        return response()->json([
            'profil' => [
                'user' => [
                    'id'    => $user->id,
                    'nom'   => $user->nom,
                    'email'=> $user->email,
                    'photo'=> $user->photo,
                    'role' => $user->role,
                ],
                'etudiant' => [
                    'id'     => $etudiant->id,
                    'cne'    => $etudiant->cne,
                    'niveau' => $etudiant->niveau,
                    
                ]
            ],
            'stats' => [
                'total_seances' => $totalSeances,
                'presences'     => $presences,
                'absences'      => $absences,
            ]
        ]);
    }
}
