<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Seance;
use App\Models\Module;
class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $enseignantId = $request->user()->id;
        $totalModules = Module::where('enseignant_id', $enseignantId)->count();

        $totalSeances = Seance::whereIn('module_id', function ($q) use ($enseignantId) {
            $q->select('id')
              ->from('modules')
              ->where('enseignant_id', $enseignantId);
        })->count();

        $totalEtudiants = Presence::join('seances', 'presences.seance_id', '=', 'seances.id')
            ->join('modules', 'seances.module_id', '=', 'modules.id')
            ->where('modules.enseignant_id', $enseignantId)
            ->distinct()
            ->count('presences.etudiant_id');

        $seancesToday = Seance::with('module:id,titre')
            ->whereDate('date', now()->toDateString())
            ->whereIn('module_id', function ($q) use ($enseignantId) {
                $q->select('id')
                  ->from('modules')
                  ->where('enseignant_id', $enseignantId);
            })
            ->get([
                'id',
                'date',
                'heure_debut',
                'heure_fin',
                'module_id'
            ]);

        return response()->json([
            'totalModules'   => $totalModules,
            'totalSeances'   => $totalSeances,
            'totalEtudiants' => $totalEtudiants,
            'seancesToday'   => $seancesToday,
        ]);
    }
}
