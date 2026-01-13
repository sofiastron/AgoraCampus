<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{

    public function mesModules(Request $request)
    {
        $utilisateur = $request->user();


        if (!$utilisateur->enseignant) {
            return response()->json([
                'message' => 'Utilisateur non enseignant'
            ], 403);
        }

        $modules = Module::where(
            'enseignant_id',
            $utilisateur->enseignant->id
        )->get(['id', 'titre']);

        return response()->json([
            'success' => true,
            'modules' => $modules
        ]);
    }

    public function store(Request $request)
    {
        $utilisateur = $request->user();

        if (!$utilisateur->enseignant) {
            return response()->json([
                'message' => 'Action non autorisée'
            ], 403);
        }

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $module = Module::create([
            'titre' => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'enseignant_id' => $utilisateur->enseignant->id
        ]);

        return response()->json([
            'success' => true,
            'module' => $module
        ], 201);
    }
}
