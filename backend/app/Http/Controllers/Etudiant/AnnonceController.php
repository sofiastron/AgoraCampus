<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Annonce;

class AnnonceController extends Controller
{
    public function index($moduleId)
    {
        return response()->json(
            Annonce::where('module_id', $moduleId)
                ->with('enseignant.user')
                ->orderBy('date_creation', 'desc')
                ->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Annonce::with('enseignant.user')->findOrFail($id)
        );
    }
}