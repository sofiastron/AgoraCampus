<?php

namespace App\Http\Controllers;

use App\Models\Annonce;
use Illuminate\Http\Request;

class AnnonceController extends Controller
{
    // Liste toutes les annonces (général, pas par module)
    public function index()
    {
        return response()->json([
            'success' => true,
            'annonces' => Annonce::with('enseignant.utilisateur')
                ->orderBy('date_creation', 'desc')
                ->get()
        ]);
    }

    // Créer une annonce
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:150',
            'contenu' => 'required|string',
        ]);

        $annonce = Annonce::create([
            'titre' => $validated['titre'],
            'contenu' => $validated['contenu'],
            'date_creation' => now(),
            'enseignant_id' => auth()->user()->enseignant->id,
        ]);

        return response()->json([
            'success' => true,
            'annonce' => $annonce->load('enseignant.utilisateur')
        ], 201);
    }

    // Modifier une annonce
    public function update(Request $request, $id)
    {
        $annonce = Annonce::findOrFail($id);

        // Vérifier que l'enseignant est l'auteur
        if ($annonce->enseignant_id !== auth()->user()->enseignant->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $validated = $request->validate([
            'titre' => 'sometimes|string|max:150',
            'contenu' => 'sometimes|string',
        ]);

        $annonce->update($validated);

        return response()->json([
            'success' => true,
            'annonce' => $annonce
        ]);
    }

    // Supprimer une annonce
    public function destroy($id)
    {
        $annonce = Annonce::findOrFail($id);

        if ($annonce->enseignant_id !== auth()->user()->enseignant->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        $annonce->delete();

        return response()->json([
            'success' => true,
            'message' => 'Annonce supprimée'
        ]);
    }
}