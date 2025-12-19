<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Etudiant;
use App\Models\Presence;
use App\Models\Seance;
use App\Models\Module;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $enseignantId = $request->user()->id; // ID de l'enseignant connecté

        // Total modules de l'enseignant
        $totalModules = Module::where('enseignant_id', $enseignantId)->count();

        // Total séances (classes) des modules de l'enseignant
        $totalSeances = Seance::whereIn('module_id', function ($query) use ($enseignantId) {
            $query->select('id')
                  ->from('modules')
                  ->where('enseignant_id', $enseignantId);
        })->count();

        // Total étudiants uniques liés à l'enseignant via présences et séances
        $totalEtudiants = Presence::join('seances', 'presences.seance_id', '=', 'seances.id')
            ->join('modules', 'seances.module_id', '=', 'modules.id')
            ->where('modules.enseignant_id', $enseignantId)
            ->distinct('presences.etudiant_id')
            ->count('presences.etudiant_id');

        // Séances (classes) aujourd'hui pour cet enseignant
        $seancesToday = Seance::whereDate('date', now()->toDateString())
            ->whereIn('module_id', function ($query) use ($enseignantId) {
                $query->select('id')
                      ->from('modules')
                      ->where('enseignant_id', $enseignantId);
            })->count();

        return response()->json([
            'totalModules' => $totalModules,
            'totalSeances' => $totalSeances,
            'totalEtudiants' => $totalEtudiants,
            'seancesToday' => $seancesToday,
        ]);
    }
}
