<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Annonce;

class AnnonceController extends Controller
{
    public function index($moduleId)
    {
        return response()->json(
            Annonce::where('idModule', $moduleId)
                ->with('enseignant.utilisateur')
                ->orderBy('dateCreation', 'desc')
                ->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Annonce::with('enseignant.utilisateur')->findOrFail($id)
        );
    }
}