<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Module;

class ModuleController extends Controller
{
    public function index()
    {
        $etudiant = auth()->user()->etudiant;

        return response()->json(
            Module::whereHas('seances.presences', function ($q) use ($etudiant) {
                $q->where('idEtudiant', $etudiant->id);
            })->with('enseignant.utilisateur')->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Module::with(['enseignant.utilisateur', 'documents', 'annonces'])
                ->findOrFail($id)
        );
    }
}