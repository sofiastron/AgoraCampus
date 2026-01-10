<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EtudiantController extends Controller
{
    // ✅ LISTER LES ETUDIANTS
    public function index()
    {
        return DB::table('etudiants')
            ->join('users', 'users.id', '=', 'etudiants.id')
            ->select(
                'etudiants.id',
                'users.nom',
                'users.email',
                'etudiants.cne',
                'etudiants.niveau',
                'etudiants.groupe_id'
            )
            ->get();
    }

    // ✅ AJOUTER UN ETUDIANT
    public function store(Request $request)
    {
        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => bcrypt('123456'),
            'role' => 'etudiant'
        ]);

        Etudiant::create([
            'id' => $user->id, // lien par id (selon ta DB)
            'cne' => $request->cne,
            'niveau' => $request->niveau,
            'groupe_id' => $request->groupe_id
        ]);

        return response()->json(['message' => 'Etudiant ajouté']);
    }

  
    public function destroy($id)
    {
        // supprime user + etudiant
        Etudiant::where('id', $id)->delete();
        User::where('id', $id)->delete();

        return response()->json(['message' => 'Etudiant supprimé']);
    }
}
