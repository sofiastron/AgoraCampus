<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enseignant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class EnseignantController extends Controller
{
    // ✅ LISTER
    public function index()
    {
        return DB::table('enseignants')
            ->join('users', 'users.id', '=', 'enseignants.user_id')
            ->select(
                'enseignants.id',
                'enseignants.user_id',
                'users.nom',
                'users.email',
                'enseignants.grade',
                'enseignants.departement'
            )
            ->get();
    }

    // ✅ AJOUTER
 public function store(Request $request)
{
    // Vérifie si un utilisateur existe déjà avec cet email
    $user = User::where('email', $request->email)->first();

    // On utilise l'id si l'utilisateur existe, sinon null
    $userId = $user ? $user->id : null;

    // Crée juste un enseignant
    $enseignant = Enseignant::create([
        'nom' => $request->nom,
        'email' => $request->email,
        'specialite' => $request->specialite,
        'user_id' => $userId, // lien vers l'utilisateur existant
        'heures_max_semaine' => $request->heures_max_semaine ?? 18,
        'statut' => $request->statut ?? 'permanent',
    ]);

    return response()->json($enseignant, 201);
}

    // ✅ MODIFIER ENSEIGNANT
public function update(Request $request, $id)
{
    $enseignant = Enseignant::findOrFail($id);

    $enseignant->update([
        'grade' => $request->grade,
        'departement' => $request->departement
    ]);

    return response()->json(['message' => 'Enseignant modifié']);
}




    // ✅ SUPPRIMER
    public function destroy($id)
    {
        $enseignant = Enseignant::findOrFail($id);

        User::where('id', $enseignant->user_id)->delete();
        $enseignant->delete();

        return response()->json(['message' => 'Enseignant supprimé']);
    }
}
