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
        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => bcrypt('123456'),
            'role' => 'enseignant'
        ]);

        Enseignant::create([
            'user_id' => $user->id,
            'grade' => $request->grade,
            'departement' => $request->departement
        ]);

        return response()->json(['message' => 'Enseignant ajouté']);
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
