<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Presence;
use App\Models\Seance;
use App\Models\Module;
use App\Models\Etudiant;

class DashboardController extends Controller
{
    private function getPresenceStatsByModule(int $enseignantId): array
    {
        $modules = Module::where('enseignant_id', $enseignantId)->get();

        $result = [];

        foreach ($modules as $module) {

            $totalEtudiants = DB::table('module_etudiant')
                ->where('module_id', $module->id)
                ->count();

            $seancesIds = Seance::where('module_id', $module->id)->pluck('id');
            $totalSeances = count($seancesIds);

            $totalPresents = Presence::whereIn('seance_id', $seancesIds)
                ->where('statut', 'present')
                ->count();

            $totalPresencesPossibles = $totalEtudiants * $totalSeances;

            $tauxPresence = $totalPresencesPossibles > 0
                ? round(($totalPresents / $totalPresencesPossibles) * 100, 2)
                : 0;

            $result[] = [
                'module' => $module->titre,
                'taux_presence' => $tauxPresence,
            ];
        }

        return $result;
    }

    public function stats(Request $request)
    {
        $utilisateur = $request->user();

        if (!$utilisateur->enseignant) {
            return response()->json([
                'message' => 'Utilisateur non enseignant'
            ], 403);
        }

        $enseignantId = $utilisateur->enseignant->id;


        $totalModules = Module::where('enseignant_id', $enseignantId)->count();

        $totalSeances = Seance::whereIn('module_id', function ($q) use ($enseignantId) {
            $q->select('id')
              ->from('modules')
              ->where('enseignant_id', $enseignantId);
        })->count();

        $totalEtudiants = Etudiant::whereHas('modules', function ($q) use ($enseignantId) {
            $q->where('enseignant_id', $enseignantId);
        })->distinct()->count();

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

        $presenceStats = $this->getPresenceStatsByModule($enseignantId);

        return response()->json([
            'totalModules'   => $totalModules,
            'totalSeances'   => $totalSeances,
            'totalEtudiants' => $totalEtudiants,
            'seancesToday'   => $seancesToday,
            'presenceStats'  => $presenceStats,
        ]);
    }
}
