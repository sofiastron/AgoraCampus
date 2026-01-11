<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Récupérer tous les utilisateurs
    public function index()
    {
        return response()->json(User::all());
    }

    // Ajouter un utilisateur
  public function store(Request $request)
{
    // Vérifier si l'email existe déjà
    if (User::where('email', $request->email)->exists()) {
        return response()->json([
            'message' => 'Cet email est déjà utilisé.'
        ], 422); // code 422 = erreur de validation
    }

    // Créer l'utilisateur
    $user = User::create([
        'nom' => $request->nom,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'role' => 'enseignant',
    ]);

    return response()->json($user, 201);
}

      
    // Voir un utilisateur
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // Mettre à jour un utilisateur
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role' => 'sometimes|string',
            'password' => 'sometimes|min:6'
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Utilisateur mis à jour',
            'user' => $user
        ]);
    }

    // Supprimer un utilisateur
  public function destroy($id)
{
    try {
        $user = User::findOrFail($id);
        
        // Vérifiez si l'utilisateur est un enseignant
        if ($user->role === 'enseignant' || $user->enseignant) {
            // Supprimez d'abord les modules associés
            if (method_exists($user, 'enseignant') && $user->enseignant) {
                // Supprimez les modules liés à cet enseignant
                \App\Models\Module::where('enseignant_id', $user->enseignant->id)->delete();
                
                // Supprimez l'enregistrement enseignant
                $user->enseignant->delete();
            }
        }
        
        // Supprimez l'utilisateur
        $user->delete();
        
        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ], 200);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'message' => 'Utilisateur non trouvé'
        ], 404);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur serveur: ' . $e->getMessage()
        ], 500);
    }
}

}