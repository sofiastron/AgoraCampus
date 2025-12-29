<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Seance;

class SeanceController extends Controller
{
    public function index($moduleId)
    {
        return response()->json(
            Seance::where('module_id', $moduleId)->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Seance::with('module')->findOrFail($id)
        );
    }
    public function calendar()
{
    $etudiant = auth()->user()->etudiant;

    if (!$etudiant || !$etudiant->groupe_id) {
        return response()->json([]);
    }

    $seances = Seance::whereHas('module', function ($query) use ($etudiant) {
        $query->where('groupe_id', $etudiant->groupe_id);
    })
    ->with('module')
    ->get();

    return response()->json(
        $seances->map(fn ($s) => [
            'title' => $s->module->titre,
            'start' => $s->date . 'T' . $s->heure_debut,
            'end'   => $s->date . 'T' . $s->heure_fin,
        ])
    );
}

}