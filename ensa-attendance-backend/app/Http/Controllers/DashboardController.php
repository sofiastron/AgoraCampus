<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Etudiant;
use App\Models\Presence;
use App\Models\Seance;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $totalEtudiants = Etudiant::count();

        // Absences aujourd'hui (en supposant que 'statut' = 'absent')
        $absencesToday = Presence::whereDate('created_at', now()->toDateString())
                                ->where('statut', 'absent')
                                ->count();

        // Séances (classes) aujourd'hui
        $classesToday = Seance::whereDate('date', now()->toDateString())->count();

        return response()->json([
            'totalEtudiants' => $totalEtudiants,
            'absencesToday' => $absencesToday,
            'classesToday' => $classesToday,
        ]);
    }
}
