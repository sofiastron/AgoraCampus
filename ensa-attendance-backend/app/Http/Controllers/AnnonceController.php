<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Http\Request;

class AnnonceController extends Controller
{
    public function annoncesModule($moduleId)
    {
        return response()->json([
            'success' => true,
            'annonces' => Annonce::where('module_id', $moduleId)->get()
        ]);
    }

    public function store(Request $request)
{
    return response()->json([
        'success' => true,
        'annonce' => Annonce::create([
            'titre' => $request->titre,
            'contenu' => $request->contenu,
            'date_Creation' => now(),
            'module_id' => $request->module_id,
            'enseignant_id' => $request->enseignant_id ?? null,  
        ])
    ]);
}

}
