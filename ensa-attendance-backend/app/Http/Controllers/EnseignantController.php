<?php

namespace App\Http\Controllers;

use App\Models\Enseignant;

class EnseignantController extends Controller
{
    public function profil($id)
    {
        return response()->json([
            'success' => true,
            'enseignant' => Enseignant::with('utilisateur')->findOrFail($id)
        ]);
    }
}
